<?php
include('../config/db.php');
session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$body = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? [];
    $action = $body['action'] ?? $action;
}

/* ── COUNTS ─────────────────────────────────────────────────── */
if ($action === 'counts') {
    $res = $db->query("SELECT status, COUNT(*) AS cnt FROM qb_questions GROUP BY status");
    $counts = ['total' => 0, 'draft' => 0, 'review' => 0, 'approved' => 0, 'published' => 0, 'archived' => 0];
    while ($row = $res->fetch_assoc()) {
        $s = $row['status'];
        if (isset($counts[$s])) $counts[$s] = (int)$row['cnt'];
        $counts['total'] += (int)$row['cnt'];
    }
    echo json_encode(['status' => 'success', 'data' => $counts]);
    exit;
}

/* ── LIST ───────────────────────────────────────────────────── */
if ($action === 'list') {
    $status_filter = trim($_GET['status_filter'] ?? '');
    $subject_id    = (int)($_GET['subject_id']    ?? 0);
    $level_id      = (int)($_GET['level_id']      ?? 0);
    $chapter_id    = (int)($_GET['chapter_id']    ?? 0);
    $difficulty_id = (int)($_GET['difficulty_id'] ?? 0);
    $type          = trim($_GET['type']  ?? '');
    $q             = trim($_GET['q']     ?? '');
    $page          = max(1, (int)($_GET['page']     ?? 1));
    $per_page      = min(100, max(5, (int)($_GET['per_page'] ?? 20)));
    $offset        = ($page - 1) * $per_page;

    $where  = ['1=1'];
    $types  = '';
    $params = [];

    if ($status_filter !== '') {
        $where[] = 'q.status = ?'; $types .= 's'; $params[] = $status_filter;
    }
    if ($subject_id) {
        $where[] = 'q.subject_id = ?'; $types .= 'i'; $params[] = $subject_id;
    }
    if ($level_id) {
        $where[] = 'q.level_id = ?'; $types .= 'i'; $params[] = $level_id;
    }
    if ($chapter_id) {
        $where[] = 'q.chapter_id = ?'; $types .= 'i'; $params[] = $chapter_id;
    }
    if ($difficulty_id) {
        $where[] = 'q.difficulty_id = ?'; $types .= 'i'; $params[] = $difficulty_id;
    }
    if ($type !== '') {
        $where[] = 'q.question_type = ?'; $types .= 's'; $params[] = $type;
    }
    if ($q !== '') {
        $where[] = '(q.q_uid LIKE ? OR q.question_stem LIKE ?)';
        $types .= 'ss'; $params[] = "%$q%"; $params[] = "%$q%";
    }

    $whereStr = implode(' AND ', $where);

    // Total count
    $cntSql = "SELECT COUNT(*) AS cnt FROM qb_questions q WHERE $whereStr";
    $stmt   = $db->prepare($cntSql);
    if ($types) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['cnt'];

    // Paginated rows
    $listSql = "SELECT q.question_id, q.q_uid, q.question_type, q.status, q.marks,
                       s.subject_name, l.level_name, c.chapter_name,
                       d.difficulty_name
                FROM   qb_questions q
                JOIN   qb_subjects          s  ON s.subject_id    = q.subject_id
                JOIN   qb_levels            l  ON l.level_id      = q.level_id
                JOIN   qb_chapters          c  ON c.chapter_id    = q.chapter_id
                LEFT JOIN qb_difficulty_levels d ON d.difficulty_id = q.difficulty_id
                WHERE  $whereStr
                ORDER  BY q.question_id DESC
                LIMIT  ? OFFSET ?";

    $stmt2 = $db->prepare($listSql);
    $allTypes  = $types . 'ii';
    $allParams = array_merge($params, [$per_page, $offset]);
    $stmt2->bind_param($allTypes, ...$allParams);
    $stmt2->execute();
    $rows = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $rows, 'total' => $total]);
    exit;
}

/* ── GET (single question + options) ───────────────────────── */
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['status' => 'error', 'message' => 'ID missing']); exit; }

    $stmt = $db->prepare(
        "SELECT q.*,
                s.subject_name, l.level_name, c.chapter_name, st.subtopic_name,
                d.difficulty_name, b.bloom_name
         FROM   qb_questions q
         JOIN   qb_subjects           s  ON s.subject_id    = q.subject_id
         JOIN   qb_levels             l  ON l.level_id      = q.level_id
         JOIN   qb_chapters           c  ON c.chapter_id    = q.chapter_id
         JOIN   qb_subtopics          st ON st.subtopic_id  = q.subtopic_id
         LEFT JOIN qb_difficulty_levels d ON d.difficulty_id = q.difficulty_id
         LEFT JOIN qb_bloom_levels      b ON b.bloom_id      = q.bloom_id
         WHERE  q.question_id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $q = $stmt->get_result()->fetch_assoc();

    if (!$q) { echo json_encode(['status' => 'error', 'message' => 'Question not found']); exit; }

    $stmt2 = $db->prepare(
        "SELECT option_label, option_text, is_correct
         FROM   qb_question_options
         WHERE  question_id = ?
         ORDER  BY sort_order, option_label"
    );
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $q['options'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'data' => $q]);
    exit;
}

/* ── CHANGE STATUS ──────────────────────────────────────────── */
if ($action === 'change_status') {
    $ids     = array_map('intval', $body['ids'] ?? []);
    $status  = trim($body['status'] ?? '');
    $allowed = ['draft', 'review', 'approved', 'published', 'archived'];

    if (empty($ids) || !in_array($status, $allowed)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']); exit;
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $bindTypes    = 's' . str_repeat('i', count($ids));
    $bindParams   = array_merge([$status], $ids);

    $stmt = $db->prepare("UPDATE qb_questions SET status = ? WHERE question_id IN ($placeholders)");
    $stmt->bind_param($bindTypes, ...$bindParams);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

/* ── DELETE ─────────────────────────────────────────────────── */
if ($action === 'delete') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { echo json_encode(['status' => 'error', 'message' => 'ID missing']); exit; }

    $db->begin_transaction();
    try {
        $s1 = $db->prepare("DELETE FROM qb_question_options WHERE question_id = ?");
        $s1->bind_param('i', $id); $s1->execute();

        $s2 = $db->prepare("DELETE FROM qb_questions WHERE question_id = ?");
        $s2->bind_param('i', $id); $s2->execute();

        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'Question deleted']);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => "Unknown action: $action"]);
