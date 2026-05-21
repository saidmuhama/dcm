<?php
session_start();
include('../config/db.php');
include('../config/url_crypt_config.php');
header('Content-Type: application/json');

$me   = $_SESSION['usr_code']   ?? '';
$role = $_SESSION['user_role']  ?? 0;
if (!$me) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

/* ── Notification helper ──────────────────────────────────── */
function pushNotification(mysqli $db, string $userCode, string $type, string $title, ?string $body = null, ?string $link = null, string $icon = 'bi-bell', string $color = '#6366f1'): void {
    $stmt = $db->prepare("INSERT INTO tbl_notifications (user_code, type, title, body, link, icon, color) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('sssssss', $userCode, $type, $title, $body, $link, $icon, $color);
    $stmt->execute();
}

function notifyAdmins(mysqli $db, string $type, string $title, ?string $body, ?string $link, string $icon, string $color): void {
    $res = $db->query("SELECT usr_code FROM tbl_all_users WHERE user_role='5' AND user_status='Active'");
    while ($row = $res->fetch_assoc()) {
        pushNotification($db, $row['usr_code'], $type, $title, $body, $link, $icon, $color);
    }
}

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

    // Notify all admins
    $courseTitle = $db->query("SELECT title FROM tbl_courses WHERE id=$course_id")->fetch_row()[0] ?? 'a course';
    notifyAdmins($db, 'course_submitted',
        'New Course Submitted for Review',
        "\"$courseTitle\" is awaiting your review.",
        'ajax/ajax_course_review.php?view=admin_course_reviews',
        'bi-collection-play', '#6366f1'
    );

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
        JOIN tbl_all_users u ON u.usr_code = r.instructor_id
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
        JOIN tbl_all_users u ON u.usr_code = r.instructor_id
        WHERE $whereStr
        ORDER BY FIELD(r.status,'pending','revision_needed','rejected','approved'), r.submitted_at DESC
        LIMIT ? OFFSET ?
    ");
    $pTypes = $types . 'ii';
    $pParams = array_merge($params, [$per, $offset]);
    $stmt->bind_param($pTypes, ...$pParams);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$r) {
        $r['course_token'] = encryptURLId((int)$r['course_id'], ctx: 'course');
    }
    unset($r);

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
        JOIN tbl_all_users u ON u.usr_code = r.instructor_id
        WHERE r.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $row['course_token'] = encryptURLId((int)$row['course_id'], ctx: 'course');
    }
    echo json_encode(['status'=>'success','data'=>$row]);
    exit;
}

/* ══════════════════════════════════════════════════════
   ADMIN: approve / reject / revision / comment
══════════════════════════════════════════════════════ */
// Map short JS action names → valid DB ENUM values
$actionMap = [
    'approve'          => 'approved',
    'reject'           => 'rejected',
    'revision_needed'  => 'revision_needed',
    'comment'          => 'comment',
];

if (isset($actionMap[$action]) && $role == 5) {
    $id         = intval($body['id'] ?? 0);
    $comment    = trim($body['comment'] ?? '');
    $new_status = $actionMap[$action];

    if (!$id) { echo json_encode(['status'=>'error','message'=>'Missing id']); exit; }

    $req = $db->prepare("SELECT course_id FROM tbl_course_review_requests WHERE id=?");
    $req->bind_param("i", $id);
    $req->execute();
    $reqRow = $req->get_result()->fetch_assoc();
    if (!$reqRow) { echo json_encode(['status'=>'error','message'=>'Request not found']); exit; }

    $course_id = $reqRow['course_id'];

    // Get course + instructor for notifications
    $courseRow = $db->query("
        SELECT c.title, c.instructor_id
        FROM tbl_courses c
        WHERE c.id = $course_id
    ")->fetch_assoc();
    $courseTitle  = $courseRow['title']         ?? 'Course';
    $instructorId = $courseRow['instructor_id'] ?? '';

    if ($new_status === 'comment') {
        $upd = $db->prepare("UPDATE tbl_course_review_requests SET admin_comment=? WHERE id=?");
        $upd->bind_param("si", $comment, $id);
        $upd->execute();
        // Notify instructor
        if ($instructorId) {
            $preview = strlen($comment) > 120 ? substr($comment, 0, 120) . '…' : $comment;
            pushNotification($db, $instructorId, 'course_comment',
                'Admin Comment on Your Course',
                "\"$courseTitle\": $preview",
                '../data_files/?view=course_contents_management&course_id=' . $course_id,
                'bi-chat-dots-fill', '#6366f1'
            );
        }
        echo json_encode(['status'=>'success','message'=>'Comment sent to instructor']);
        exit;
    }

    $upd = $db->prepare("
        UPDATE tbl_course_review_requests
        SET status=?, admin_comment=?, reviewed_at=NOW(), reviewed_by=?
        WHERE id=?
    ");
    $upd->bind_param("sssi", $new_status, $comment, $me, $id);
    if (!$upd->execute()) {
        echo json_encode(['status'=>'error','message'=>'DB update failed: '.$upd->error]); exit;
    }

    // Sync tbl_courses
    if ($new_status === 'approved') {
        $db->query("UPDATE tbl_courses SET is_approved='approved', status='active' WHERE id=$course_id");
    } elseif ($new_status === 'rejected') {
        $db->query("UPDATE tbl_courses SET is_approved='rejected', status='is_draft' WHERE id=$course_id");
    } elseif ($new_status === 'revision_needed') {
        $db->query("UPDATE tbl_courses SET is_approved='pending', status='is_draft' WHERE id=$course_id");
    }

    // Notify instructor
    $notifMap = [
        'approved'         => ['Course Approved & Published!', "\"$courseTitle\" is now live for students.", 'bi-check-circle-fill', '#16a34a'],
        'rejected'         => ['Course Rejected', "\"$courseTitle\" was rejected." . ($comment ? " Feedback: " . substr($comment,0,100) : ''), 'bi-x-circle-fill', '#dc2626'],
        'revision_needed'  => ['Revision Requested', "\"$courseTitle\" needs revision." . ($comment ? " " . substr($comment,0,100) : ''), 'bi-arrow-repeat', '#d97706'],
    ];
    if ($instructorId && isset($notifMap[$new_status])) {
        [$nTitle, $nBody, $nIcon, $nColor] = $notifMap[$new_status];
        pushNotification($db, $instructorId, 'course_' . $new_status,
            $nTitle, $nBody,
            '../data_files/?view=course_contents_management&course_id=' . $course_id,
            $nIcon, $nColor
        );
    }

    $labels = ['approved'=>'Course approved and published','rejected'=>'Course rejected','revision_needed'=>'Revision requested'];
    echo json_encode(['status'=>'success','message'=>$labels[$new_status] ?? 'Done']);
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
