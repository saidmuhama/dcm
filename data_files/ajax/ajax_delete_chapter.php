<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
include('../config/cache.php');
header('Content-Type: application/json');

$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit; }

/* ── Ensure requests table exists ─────────────────────────── */
$db->query("CREATE TABLE IF NOT EXISTS tbl_chapter_deletion_requests (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    chapter_id      INT          NOT NULL,
    course_id       INT          NOT NULL,
    instructor_id   VARCHAR(100) NOT NULL,
    chapter_title   VARCHAR(500) NOT NULL,
    lesson_count    INT          DEFAULT 0,
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    instructor_note TEXT,
    admin_comment   TEXT,
    reviewed_by     VARCHAR(100),
    requested_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    reviewed_at     TIMESTAMP    NULL
)");

/* ── Helpers ──────────────────────────────────────────────── */
function pushNotif(mysqli $db, string $to, string $type, string $title, ?string $body, ?string $link, string $icon, string $color): void {
    $s = $db->prepare("INSERT INTO tbl_notifications (user_code,type,title,body,link,icon,color) VALUES (?,?,?,?,?,?,?)");
    $s->bind_param('sssssss', $to, $type, $title, $body, $link, $icon, $color);
    $s->execute();
}
function notifyAdmins(mysqli $db, string $type, string $title, ?string $body, ?string $link, string $icon, string $color): void {
    $res = $db->query("SELECT usr_code FROM tbl_all_users WHERE user_role='5' AND user_status='Active'");
    while ($r = $res->fetch_assoc()) pushNotif($db, $r['usr_code'], $type, $title, $body, $link, $icon, $color);
}

$method = $_SERVER['REQUEST_METHOD'];
$body   = $method === 'POST' ? (json_decode(file_get_contents('php://input'), true) ?? []) : [];
$action = $method === 'GET' ? ($_GET['action'] ?? '') : ($body['action'] ?? '');

/* ══════════════════════════════════════════════════════════
   INSTRUCTOR: request chapter deletion
══════════════════════════════════════════════════════════ */
if ($action === 'request') {
    $chapter_id = intval($body['chapter_id'] ?? 0);
    $note       = trim($body['note'] ?? '');
    if (!$chapter_id) { echo json_encode(['status' => 'error', 'message' => 'Invalid chapter']); exit; }

    /* verify ownership */
    $ch = $db->prepare("
        SELECT cc.id, cc.chapter_title, cc.course_id,
               c.instructor_id, c.status AS course_status, c.title AS course_title
        FROM tbl_course_chapters cc
        JOIN tbl_courses c ON c.id = cc.course_id
        WHERE cc.id = ? AND c.instructor_id = ?
    ");
    $ch->bind_param('is', $chapter_id, $me);
    $ch->execute();
    $chRow = $ch->get_result()->fetch_assoc();
    if (!$chRow) { echo json_encode(['status' => 'error', 'message' => 'Chapter not found or unauthorized']); exit; }

    $course_id = $chRow['course_id'];
    $isPublic  = $chRow['course_status'] === 'active';

    if (!$isPublic) {
        /* course is not live — delete chapter + lessons + CDN resources immediately */
        $lessons     = $db->query("SELECT * FROM tbl_course_chapter_lessons WHERE chapter_id = $chapter_id")->fetch_all(MYSQLI_ASSOC);
        $libraryKey  = App::getWhatFromWHere('library_key', 'tbl_courses', 'id', $course_id);
        $storageZone = App::getBunnyStorageZone();
        $storageKey  = App::getBunnyStorageZoneAccessKey();
        foreach ($lessons as $lesson) {
            if (!empty($lesson['video_id']) && !empty($lesson['library_id'])) {
                App::deleteVideo($lesson['library_id'], $lesson['video_id'], $libraryKey);
            } elseif (!empty($lesson['file_path']) && str_starts_with((string)$lesson['file_path'], 'http')) {
                $parsed      = parse_url($lesson['file_path']);
                $storagePath = ltrim($parsed['path'] ?? '', '/');
                if ($storagePath) App::deleteBunnyStorageFile($storageZone, $storagePath, $storageKey);
            }
        }
        $db->query("DELETE FROM tbl_course_chapter_lessons WHERE chapter_id = $chapter_id");
        $db->query("DELETE FROM tbl_course_chapters WHERE id = $chapter_id");
        DcmCache::delete('chapters_course_' . $course_id, 'ccm');
        echo json_encode(['status' => 'success', 'message' => 'Chapter deleted', 'deleted' => true]);
        exit;
    }

    /* course is live — require admin approval */
    $lessonCount = (int)$db->query("SELECT COUNT(*) FROM tbl_course_chapter_lessons WHERE chapter_id = $chapter_id")->fetch_row()[0];

    /* check if a pending request already exists */
    $existing = $db->query("SELECT id FROM tbl_chapter_deletion_requests WHERE chapter_id = $chapter_id AND status = 'pending'")->fetch_assoc();
    if ($existing) {
        echo json_encode(['status' => 'pending', 'message' => 'A deletion request for this chapter is already awaiting admin review']);
        exit;
    }

    /* insert the request */
    $ins = $db->prepare("
        INSERT INTO tbl_chapter_deletion_requests (chapter_id, course_id, instructor_id, chapter_title, lesson_count, instructor_note)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $ins->bind_param('iissis', $chapter_id, $course_id, $me, $chRow['chapter_title'], $lessonCount, $note);
    $ins->execute();

    $reasonStr = 'course is live' . ($lessonCount > 0 ? ", $lessonCount lesson(s)" : '');

    notifyAdmins($db, 'chapter_delete_request',
        'Chapter Deletion Requested',
        "Instructor wants to delete \"{$chRow['chapter_title']}\" from \"{$chRow['course_title']}\" ({$reasonStr}).",
        '?view=admin_course_reviews&tab=chapter_del',
        'bi-folder-x', '#f59e0b'
    );

    echo json_encode(['status' => 'approval_requested', 'message' => 'Deletion request submitted. The admin has been notified and will review it shortly.']);
    exit;
}

/* ══════════════════════════════════════════════════════════
   ADMIN: list chapter deletion requests
══════════════════════════════════════════════════════════ */
if ($action === 'list_del' && $role == 5) {
    $filter = $_GET['filter'] ?? 'pending';
    $esc    = $db->real_escape_string($filter);
    $where  = $filter ? "WHERE r.status = '$esc'" : "WHERE 1=1";

    $rows = $db->query("
        SELECT r.*,
               u.first_name, u.last_name, u.email_address,
               c.title AS course_title, c.thumbnail AS course_thumb, c.status AS course_status
        FROM tbl_chapter_deletion_requests r
        JOIN tbl_all_users u  ON u.usr_code  = r.instructor_id
        JOIN tbl_courses c    ON c.id         = r.course_id
        $where
        ORDER BY FIELD(r.status,'pending','approved','rejected'), r.requested_at DESC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);

    $pending = (int)$db->query("SELECT COUNT(*) FROM tbl_chapter_deletion_requests WHERE status = 'pending'")->fetch_row()[0];

    echo json_encode(['status' => 'success', 'data' => $rows, 'pending' => $pending]);
    exit;
}

/* ══════════════════════════════════════════════════════════
   ADMIN: get single request detail
══════════════════════════════════════════════════════════ */
if ($action === 'get_del' && $role == 5) {
    $id  = intval($_GET['id'] ?? 0);
    $row = $db->query("
        SELECT r.*,
               u.first_name, u.last_name, u.email_address,
               c.title AS course_title, c.thumbnail AS course_thumb
        FROM tbl_chapter_deletion_requests r
        JOIN tbl_all_users u ON u.usr_code = r.instructor_id
        JOIN tbl_courses c   ON c.id        = r.course_id
        WHERE r.id = $id
    ")->fetch_assoc();
    echo json_encode(['status' => 'success', 'data' => $row ?? null]);
    exit;
}

/* ══════════════════════════════════════════════════════════
   ADMIN: approve — delete chapter & all its lessons
══════════════════════════════════════════════════════════ */
if ($action === 'approve_del' && $role == 5) {
    $id      = intval($body['id'] ?? 0);
    $comment = trim($body['comment'] ?? '');
    if (!$id) { echo json_encode(['status' => 'error', 'message' => 'Missing id']); exit; }

    $req = $db->query("SELECT * FROM tbl_chapter_deletion_requests WHERE id = $id AND status = 'pending'")->fetch_assoc();
    if (!$req) { echo json_encode(['status' => 'error', 'message' => 'Request not found or already processed']); exit; }

    $chapter_id = $req['chapter_id'];
    $course_id  = $req['course_id'];

    /* delete CDN resources for each lesson (best-effort) */
    $lessons     = $db->query("SELECT * FROM tbl_course_chapter_lessons WHERE chapter_id = $chapter_id")->fetch_all(MYSQLI_ASSOC);
    $libraryKey  = App::getWhatFromWHere('library_key', 'tbl_courses', 'id', $course_id);
    $storageZone = App::getBunnyStorageZone();
    $storageKey  = App::getBunnyStorageZoneAccessKey();

    foreach ($lessons as $lesson) {
        if (!empty($lesson['video_id']) && !empty($lesson['library_id'])) {
            /* Bunny Stream — use video_id presence, not content_type (legacy rows may have NULL) */
            App::deleteVideo($lesson['library_id'], $lesson['video_id'], $libraryKey);
        } elseif (!empty($lesson['file_path']) && str_starts_with((string)$lesson['file_path'], 'http')) {
            /* Bunny Storage — audio, PDF, or any other CDN-hosted file */
            $parsed = parse_url($lesson['file_path']);
            $storagePath = ltrim($parsed['path'] ?? '', '/');
            if ($storagePath) App::deleteBunnyStorageFile($storageZone, $storagePath, $storageKey);
        }
    }

    /* delete lessons + chapter */
    $db->query("DELETE FROM tbl_course_chapter_lessons WHERE chapter_id = $chapter_id");
    $db->query("DELETE FROM tbl_course_chapters WHERE id = $chapter_id");

    /* mark request approved */
    $upd = $db->prepare("UPDATE tbl_chapter_deletion_requests SET status='approved', admin_comment=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?");
    $upd->bind_param('ssi', $comment, $me, $id);
    $upd->execute();

    DcmCache::delete('chapters_course_' . $course_id, 'ccm');

    /* notify instructor */
    $msg = "Chapter \"{$req['chapter_title']}\" has been deleted by admin.";
    if ($comment) $msg .= " Admin note: $comment";
    pushNotif($db, $req['instructor_id'], 'chapter_deleted',
        'Chapter Deletion Approved',
        $msg, null, 'bi-check-circle-fill', '#16a34a'
    );

    echo json_encode(['status' => 'success', 'message' => 'Chapter and all its lessons have been deleted.']);
    exit;
}

/* ══════════════════════════════════════════════════════════
   ADMIN: reject
══════════════════════════════════════════════════════════ */
if ($action === 'reject_del' && $role == 5) {
    $id      = intval($body['id'] ?? 0);
    $comment = trim($body['comment'] ?? '');
    if (!$id)      { echo json_encode(['status' => 'error', 'message' => 'Missing id']); exit; }
    if (!$comment) { echo json_encode(['status' => 'error', 'message' => 'A reason is required when rejecting']); exit; }

    $req = $db->query("SELECT * FROM tbl_chapter_deletion_requests WHERE id = $id AND status = 'pending'")->fetch_assoc();
    if (!$req) { echo json_encode(['status' => 'error', 'message' => 'Request not found or already processed']); exit; }

    $upd = $db->prepare("UPDATE tbl_chapter_deletion_requests SET status='rejected', admin_comment=?, reviewed_by=?, reviewed_at=NOW() WHERE id=?");
    $upd->bind_param('ssi', $comment, $me, $id);
    $upd->execute();

    pushNotif($db, $req['instructor_id'], 'chapter_del_rejected',
        'Chapter Deletion Rejected',
        "Request to delete \"{$req['chapter_title']}\" was rejected. Reason: $comment",
        null, 'bi-x-circle-fill', '#dc2626'
    );

    echo json_encode(['status' => 'success', 'message' => 'Request rejected and instructor notified.']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action or insufficient permissions']);
