<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

if (!isset($_SESSION['usr_code'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}
$me = $_SESSION['usr_code'];

$method = $_SERVER['REQUEST_METHOD'];
$action = $method === 'GET' ? ($_GET['action'] ?? '') : '';

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) $body = $_POST;
    $action = $body['action'] ?? '';
}

// ── GET: list notes for a lesson ───────────────────────────────────────────
if ($method === 'GET' && $action === 'list') {
    $lesson_id = intval($_GET['lesson_id'] ?? 0);
    if (!$lesson_id) { echo json_encode(['status'=>'error','message'=>'Missing lesson_id']); exit; }

    $stmt = $db->prepare("
        SELECT sn.*,
               IF(snb.id IS NOT NULL, 1, 0) AS bookmarked
        FROM study_notes sn
        LEFT JOIN study_note_bookmarks snb
               ON snb.study_note_id = sn.id AND snb.user_id = ?
        WHERE sn.lesson_id = ?
        ORDER BY sn.sort_order ASC, sn.id ASC
    ");
    $stmt->bind_param("si", $me, $lesson_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $rows]);
    exit;
}

// ── GET: bookmarks for current user ────────────────────────────────────────
if ($method === 'GET' && $action === 'bookmarks') {
    $lesson_id = intval($_GET['lesson_id'] ?? 0);
    $stmt = $db->prepare("
        SELECT sn.*, 1 AS bookmarked
        FROM study_note_bookmarks snb
        JOIN study_notes sn ON sn.id = snb.study_note_id
        WHERE snb.user_id = ? AND sn.lesson_id = ?
        ORDER BY sn.sort_order ASC, sn.id ASC
    ");
    $stmt->bind_param("si", $me, $lesson_id);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status' => 'success', 'data' => $rows]);
    exit;
}

// ── POST: save (insert or update) ──────────────────────────────────────────
if ($action === 'save') {
    $id         = intval($body['id'] ?? 0);
    $lesson_id  = intval($body['lesson_id'] ?? 0);
    $chapter_id = intval($body['chapter_id'] ?? 0);
    $course_id  = intval($body['course_id'] ?? 0);
    $question   = trim($body['question'] ?? '');
    $answer     = trim($body['answer'] ?? '');
    $language   = trim($body['language'] ?? 'EN');
    $important  = intval($body['is_important'] ?? 0);
    $sort_order = intval($body['sort_order'] ?? 0);

    if (!$lesson_id || !$question || !$answer) {
        echo json_encode(['status'=>'error','message'=>'Question and answer are required']); exit;
    }

    if ($id) {
        $stmt = $db->prepare("
            UPDATE study_notes
            SET question=?, answer=?, language=?, is_important=?, sort_order=?, updated_at=NOW()
            WHERE id=? AND created_by=?
        ");
        $stmt->bind_param("sssiiis", $question, $answer, $language, $important, $sort_order, $id, $me);
        $stmt->execute();
        if ($stmt->affected_rows < 0) { echo json_encode(['status'=>'error','message'=>'Update failed']); exit; }
        echo json_encode(['status'=>'success','message'=>'Note updated','id'=>$id]);
    } else {
        if (!$sort_order) {
            $r = $db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 FROM study_notes WHERE lesson_id=?");
            $r->bind_param("i", $lesson_id);
            $r->execute();
            $sort_order = $r->get_result()->fetch_row()[0];
        }
        $stmt = $db->prepare("
            INSERT INTO study_notes (course_id,chapter_id,lesson_id,question,answer,language,is_important,sort_order,created_by)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        $stmt->bind_param("iiisssiis", $course_id, $chapter_id, $lesson_id, $question, $answer, $language, $important, $sort_order, $me);
        $stmt->execute();
        echo json_encode(['status'=>'success','message'=>'Note saved','id'=>$db->insert_id]);
    }
    exit;
}

// ── POST: delete ───────────────────────────────────────────────────────────
if ($action === 'delete') {
    $id = intval($body['id'] ?? 0);
    if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing id']); exit; }
    $db->begin_transaction();
    $db->query("DELETE FROM study_note_bookmarks WHERE study_note_id = $id");
    $stmt = $db->prepare("DELETE FROM study_notes WHERE id=? AND created_by=?");
    $stmt->bind_param("is", $id, $me);
    $stmt->execute();
    if ($stmt->affected_rows < 1) {
        $db->rollback();
        echo json_encode(['status'=>'error','message'=>'Not found or permission denied']); exit;
    }
    $db->commit();
    echo json_encode(['status'=>'success','message'=>'Deleted']);
    exit;
}

// ── POST: reorder ──────────────────────────────────────────────────────────
if ($action === 'reorder') {
    $ids = $body['ids'] ?? [];
    if (!is_array($ids)) { echo json_encode(['status'=>'error','message'=>'Invalid ids']); exit; }
    $stmt = $db->prepare("UPDATE study_notes SET sort_order=? WHERE id=? AND created_by=?");
    foreach ($ids as $order => $id) {
        $o = $order + 1;
        $i = intval($id);
        $stmt->bind_param("iis", $o, $i, $me);
        $stmt->execute();
    }
    echo json_encode(['status'=>'success','message'=>'Reordered']);
    exit;
}

// ── POST: toggle bookmark ──────────────────────────────────────────────────
if ($action === 'toggle_bookmark') {
    $note_id = intval($body['note_id'] ?? 0);
    if (!$note_id) { echo json_encode(['status'=>'error','message'=>'Missing note_id']); exit; }

    $check = $db->prepare("SELECT id FROM study_note_bookmarks WHERE user_id=? AND study_note_id=?");
    $check->bind_param("si", $me, $note_id);
    $check->execute();
    $exists = $check->get_result()->fetch_row();

    if ($exists) {
        $del = $db->prepare("DELETE FROM study_note_bookmarks WHERE user_id=? AND study_note_id=?");
        $del->bind_param("si", $me, $note_id);
        $del->execute();
        echo json_encode(['status'=>'success','bookmarked'=>false]);
    } else {
        $ins = $db->prepare("INSERT INTO study_note_bookmarks (user_id, study_note_id) VALUES (?,?)");
        $ins->bind_param("si", $me, $note_id);
        $ins->execute();
        echo json_encode(['status'=>'success','bookmarked'=>true]);
    }
    exit;
}

echo json_encode(['status'=>'error','message'=>'Unknown action']);
