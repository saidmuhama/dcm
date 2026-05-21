<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

$me   = $_SESSION['usr_code'] ?? '';
$role = $_SESSION['usr_role']  ?? 0;
if (!$me) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$body   = [];
if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?? $_POST;
}
$action = $method === 'GET' ? ($_GET['action'] ?? '') : ($body['action'] ?? '');

/* ══════════════════════════════════════════════════════
   INSTRUCTOR: submit course for review
══════════════════════════════════════════════════════ */
if ($action === 'submit') {
    $course_id = intval($body['course_id'] ?? 0);
    $note      = trim($body['note'] ?? '');

    if (!$course_id) { echo json_encode(['status'=>'error','message'=>'Missing course_id']); exit; }

    // verify ownership
    $own = $db->prepare("SELECT id FROM tbl_courses WHERE id=? AND instructor_id=?");
    $own->bind_param("is", $course_id, $me);
    $own->execute();
    if (!$own->get_result()->fetch_row()) {
        echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
    }

    // cancel any existing pending request first
    $db->query("UPDATE tbl_course_review_requests SET status='pending' WHERE course_id=$course_id AND status='pending'");

    // insert new review request
    $ins = $db->prepare("
        INSERT INTO tbl_course_review_requests (course_id, instructor_id, status, instructor_note)
        VALUES (?, ?, 'pending', ?)
    ");
    $ins->bind_param("iss", $course_id, $me, $note);
    $ins->execute();

    // update course approval status
    $db->query("UPDATE tbl_courses SET is_approved='pending' WHERE id=$course_id");

    echo json_encode(['status'=>'success','message'=>'Course submitted for review']);
    exit;
}

/* ══════════════════════════════════════════════════════
   INSTRUCTOR: get review status for a course
══════════════════════════════════════════════════════ */
if ($action === 'get_status') {
    $course_id = intval($_GET['course_id'] ?? 0);
    if (!$course_id) { echo json_encode(['status'=>'error','message'=>'Missing course_id']); exit; }

    $stmt = $db->prepare("
        SELECT r.*, c.status AS course_status, c.is_approved
        FROM tbl_course_review_requests r
        JOIN tbl_courses c ON c.id = r.course_id
        WHERE r.course_id = ? AND r.instructor_id = ?
        ORDER BY r.submitted_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("is", $course_id, $me);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    // Also get plain course status if no review request
    if (!$row) {
        $cs = $db->prepare("SELECT status, is_approved FROM tbl_courses WHERE id=? AND instructor_id=?");
        $cs->bind_param("is", $course_id, $me);
        $cs->execute();
        $row = $cs->get_result()->fetch_assoc();
    }

    echo json_encode(['status'=>'success','data'=>$row]);
    exit;
}

/* ══════════════════════════════════════════════════════
   ADMIN: list review requests
══════════════════════════════════════════════════════ */
if ($action === 'list' && $role == 5) {
    $filter_status = $_GET['filter_status'] ?? '';
    $search        = trim($_GET['search'] ?? '');
    $page          = max(1, intval($_GET['page'] ?? 1));
    $per           = 15;
    $offset        = ($page - 1) * $per;

    $where = ['1=1'];
    $params = []; $types = '';

    if ($filter_status) { $where[] = 'r.status = ?'; $params[] = $filter_status; $types .= 's'; }
    if ($search) {
        $like = "%$search%";
        $where[] = '(c.title LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ? OR u.email_address LIKE ?)';
        $params = array_merge($params, [$like,$like,$like,$like]); $types .= 'ssss';
    }

    $whereStr = implode(' AND ', $where);

    $countStmt = $db->prepare("
        SELECT COUNT(*) FROM tbl_course_review_requests r
        JOIN tbl_courses c ON c.id = r.course_id
        JOIN tbl_users u ON u.usr_code = r.instructor_id
        WHERE $whereStr
    ");
    if ($types) { $countStmt->bind_param($types, ...$params); }
    $countStmt->execute();
    $total = $countStmt->get_result()->fetch_row()[0];

    $stmt = $db->prepare("
        SELECT r.*,
               c.title, c.thumbnail, c.price, c.status AS course_status,
               u.first_name, u.last_name, u.email_address,
               (SELECT COUNT(*) FROM tbl_course_chapters ch WHERE ch.course_id = r.course_id) AS chapters,
               (SELECT COUNT(*) FROM tbl_course_chapter_lessons l WHERE l.course_id = r.course_id AND l.status='active') AS lessons
        FROM tbl_course_review_requests r
        JOIN tbl_courses c ON c.id = r.course_id
        JOIN tbl_users u ON u.usr_code = r.instructor_id
        WHERE $whereStr
        ORDER BY FIELD(r.status,'pending','revision_needed','rejected','approved'), r.submitted_at DESC
        LIMIT ? OFFSET ?
    ");
    $pTypes = $types . 'ii';
    $pParams = array_merge($params, [$per, $offset]);
    $stmt->bind_param($pTypes, ...$pParams);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // pending count for badge
    $pendingCount = $db->query("SELECT COUNT(*) FROM tbl_course_review_requests WHERE status='pending'")->fetch_row()[0];

    echo json_encode(['status'=>'success','data'=>$rows,'total'=>$total,'per'=>$per,'pending_count'=>$pendingCount]);
    exit;
}

/* ══════════════════════════════════════════════════════
   ADMIN: get single review request detail
══════════════════════════════════════════════════════ */
if ($action === 'get' && $role == 5) {
    $id = intval($_GET['id'] ?? 0);
    $stmt = $db->prepare("
        SELECT r.*,
               c.title, c.thumbnail, c.price, c.description, c.status AS course_status,
               c.library_id,
               u.first_name, u.last_name, u.email_address,
               (SELECT COUNT(*) FROM tbl_course_chapters ch WHERE ch.course_id = r.course_id) AS chapters,
               (SELECT COUNT(*) FROM tbl_course_chapter_lessons l WHERE l.course_id = r.course_id AND l.status='active') AS lessons,
               (SELECT COUNT(*) FROM tbl_course_chapter_lessons l WHERE l.course_id = r.course_id AND l.isFreePreviewLesson=1 AND l.status='active') AS free_lessons
        FROM tbl_course_review_requests r
        JOIN tbl_courses c ON c.id = r.course_id
        JOIN tbl_users u ON u.usr_code = r.instructor_id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo json_encode(['status'=>'success','data'=>$row]);
    exit;
}

/* ══════════════════════════════════════════════════════
   ADMIN: approve / reject / revision / comment
══════════════════════════════════════════════════════ */
if (in_array($action, ['approve','reject','revision_needed','comment']) && $role == 5) {
    $id      = intval($body['id'] ?? 0);
    $comment = trim($body['comment'] ?? '');

    if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing id']); exit; }

    $req = $db->prepare("SELECT course_id FROM tbl_course_review_requests WHERE id=?");
    $req->bind_param("i", $id);
    $req->execute();
    $reqRow = $req->get_result()->fetch_assoc();
    if (!$reqRow) { echo json_encode(['status'=>'error','message'=>'Request not found']); exit; }

    $course_id = $reqRow['course_id'];

    if ($action === 'comment') {
        // Just save the comment, don't change status
        $upd = $db->prepare("UPDATE tbl_course_review_requests SET admin_comment=? WHERE id=?");
        $upd->bind_param("si", $comment, $id);
        $upd->execute();
        echo json_encode(['status'=>'success','message'=>'Comment saved']);
        exit;
    }

    $new_status = $action; // 'approved','rejected','revision_needed'
    $upd = $db->prepare("
        UPDATE tbl_course_review_requests
        SET status=?, admin_comment=?, reviewed_at=NOW(), reviewed_by=?
        WHERE id=?
    ");
    $upd->bind_param("sssi", $new_status, $comment, $me, $id);
    $upd->execute();

    // Update course accordingly
    if ($action === 'approved') {
        $db->query("UPDATE tbl_courses SET is_approved='approved', status='active' WHERE id=$course_id");
    } elseif ($action === 'rejected') {
        $db->query("UPDATE tbl_courses SET is_approved='rejected', status='is_draft' WHERE id=$course_id");
    } elseif ($action === 'revision_needed') {
        $db->query("UPDATE tbl_courses SET is_approved='pending', status='is_draft' WHERE id=$course_id");
    }

    echo json_encode(['status'=>'success','message'=>ucfirst(str_replace('_',' ',$action)).' successfully']);
    exit;
}

/* ══════════════════════════════════════════════════════
   ADMIN: stats
══════════════════════════════════════════════════════ */
if ($action === 'stats' && $role == 5) {
    $pending   = $db->query("SELECT COUNT(*) FROM tbl_course_review_requests WHERE status='pending'")->fetch_row()[0];
    $approved  = $db->query("SELECT COUNT(*) FROM tbl_course_review_requests WHERE status='approved' AND DATE(reviewed_at)=CURDATE()")->fetch_row()[0];
    $rejected  = $db->query("SELECT COUNT(*) FROM tbl_course_review_requests WHERE status='rejected'")->fetch_row()[0];
    $total     = $db->query("SELECT COUNT(*) FROM tbl_course_review_requests")->fetch_row()[0];
    echo json_encode(['status'=>'success','pending'=>$pending,'approved_today'=>$approved,'rejected'=>$rejected,'total'=>$total]);
    exit;
}

echo json_encode(['status'=>'error','message'=>'Unknown action or insufficient permissions']);
