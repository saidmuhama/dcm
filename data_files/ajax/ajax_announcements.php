<?php
/**
 * ajax_announcements.php
 * Course Announcements AJAX handler
 * Roles: 3 = instructor, 5 = super admin (send), 1 = student (read)
 */
ini_set('display_errors', 0);
ob_start();
session_start();
include('../config/db.php');
header('Content-Type: application/json');

function _annj(array $d): never { ob_clean(); echo json_encode($d); exit; }
function _annce(mysqli $db, $v): string { return $db->real_escape_string((string)$v); }

$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me) _annj(['status' => 'error', 'message' => 'Unauthorized']);

/* ── Bootstrap tables ──────────────────────────────────────────────────── */
$db->query("CREATE TABLE IF NOT EXISTS `tbl_announcements` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `course_id`   INT UNSIGNED NOT NULL,
    `sender_code` VARCHAR(50) NOT NULL,
    `subject`     VARCHAR(255) NOT NULL,
    `type`        ENUM('announcement','reminder','assignment_notice','assessment_notice','discussion') NOT NULL DEFAULT 'announcement',
    `audience`    ENUM('all','org_only','selected') NOT NULL DEFAULT 'all',
    `org_code`    VARCHAR(100) DEFAULT NULL,
    `body`        TEXT DEFAULT NULL,
    `attachment`  VARCHAR(500) DEFAULT NULL,
    `sent_at`     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_ann_course` (`course_id`),
    KEY `idx_ann_sender` (`sender_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS `tbl_announcement_recipients` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `announcement_id` BIGINT UNSIGNED NOT NULL,
    `usr_code`        VARCHAR(50) NOT NULL,
    `is_read`         TINYINT(1) NOT NULL DEFAULT 0,
    `read_at`         TIMESTAMP NULL DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_ar` (`announcement_id`,`usr_code`),
    KEY `idx_ar_usr` (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* ── Routing ────────────────────────────────────────────────────────────── */
$method = $_SERVER['REQUEST_METHOD'];
$body   = $method === 'POST' ? (json_decode(file_get_contents('php://input'), true) ?? $_POST) : [];
$action = $method === 'GET'  ? ($_GET['action'] ?? '') : ($body['action'] ?? '');

/* ══════════════════════════════════════════════════════════════════════════
   SEND_ANNOUNCEMENT  (instructor role=3 or super admin role=5)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'send_announcement') {
    if (!in_array($role, [3, 5])) _annj(['status' => 'error', 'message' => 'Forbidden']);

    $course_id = (int)($body['course_id'] ?? 0);
    $subject   = trim($body['subject'] ?? '');
    $type      = $body['type'] ?? 'announcement';
    $audience  = $body['audience'] ?? 'all';
    $ann_body  = trim($body['body'] ?? '');
    $org_code  = trim($body['org_code'] ?? '');
    $sel_codes = $body['usr_codes'] ?? [];

    if (!$course_id)  _annj(['status' => 'error', 'message' => 'Course ID required']);
    if (!$subject)    _annj(['status' => 'error', 'message' => 'Subject required']);
    if (!$ann_body)   _annj(['status' => 'error', 'message' => 'Message body required']);

    $allowed_types = ['announcement','reminder','assignment_notice','assessment_notice','discussion'];
    if (!in_array($type, $allowed_types)) $type = 'announcement';
    if (!in_array($audience, ['all','org_only','selected'])) $audience = 'all';

    /* Verify course ownership (skip for super admin) */
    if ($role === 3) {
        $esc_me = _annce($db, $me);
        $chk = $db->query("SELECT id FROM tbl_courses WHERE id=$course_id AND instructor_id='$esc_me' AND deleted_at IS NULL LIMIT 1");
        if (!$chk || $chk->num_rows === 0) _annj(['status' => 'error', 'message' => 'Course not found or access denied']);
    }

    /* Handle file attachment */
    $attachment = null;
    if (!empty($_FILES['attachment']['tmp_name'])) {
        $ext  = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
        $safe = ['pdf','doc','docx','xls','xlsx','ppt','pptx','txt','jpg','jpeg','png','gif','zip'];
        if (in_array($ext, $safe)) {
            $fname = 'ann_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $dest  = __DIR__ . '/../uploads/announcements/';
            if (!is_dir($dest)) mkdir($dest, 0755, true);
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $dest . $fname)) {
                $attachment = 'uploads/announcements/' . $fname;
            }
        }
    }

    /* Insert announcement */
    $stmt = $db->prepare("
        INSERT INTO tbl_announcements (course_id, sender_code, subject, type, audience, org_code, body, attachment, sent_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $esc_oc = $org_code ?: null;
    $stmt->bind_param('isssssss', $course_id, $me, $subject, $type, $audience, $esc_oc, $ann_body, $attachment);
    $stmt->execute();
    $ann_id = $db->insert_id;
    if (!$ann_id) _annj(['status' => 'error', 'message' => 'Failed to create announcement']);

    /* Resolve recipient usr_codes */
    $recipients = [];
    if ($audience === 'all') {
        $rs = $db->query("
            SELECT u.usr_code
            FROM tbl_course_enrollments ce
            JOIN tbl_all_users u ON u.id = ce.user_id
            WHERE ce.course_id = $course_id AND ce.has_access = 1
        ");
        while ($rr = $rs->fetch_assoc()) $recipients[] = $rr['usr_code'];
    } elseif ($audience === 'org_only' && $org_code) {
        $esc_oc2 = _annce($db, $org_code);
        $rs = $db->query("
            SELECT u.usr_code
            FROM tbl_course_enrollments ce
            JOIN tbl_all_users u ON u.id = ce.user_id
            JOIN tbl_org_members om ON om.usr_code = u.usr_code AND om.org_code = '$esc_oc2' AND om.status = 'active'
            WHERE ce.course_id = $course_id AND ce.has_access = 1
        ");
        while ($rr = $rs->fetch_assoc()) $recipients[] = $rr['usr_code'];
    } elseif ($audience === 'selected' && !empty($sel_codes)) {
        foreach ($sel_codes as $sc) {
            $sc = trim((string)$sc);
            if ($sc) $recipients[] = $sc;
        }
        $recipients = array_unique($recipients);
    }

    /* Insert recipients + notifications */
    $inserted = 0;
    $esc_subj = _annce($db, $subject);
    $esc_body_short = _annce($db, mb_substr(strip_tags($ann_body), 0, 120));
    $link = '?view=learning-student-home';

    foreach ($recipients as $uc) {
        $esc_uc = _annce($db, $uc);
        if (!$esc_uc) continue;

        $db->query("INSERT IGNORE INTO tbl_announcement_recipients (announcement_id, usr_code) VALUES ($ann_id, '$esc_uc')");
        if ($db->affected_rows > 0) {
            $inserted++;
            /* Insert notification */
            $db->query("
                INSERT INTO tbl_notifications (user_code, type, title, body, link, icon, color, is_read, created_at)
                VALUES ('$esc_uc','announcement','$esc_subj','$esc_body_short','$link','bi-megaphone-fill','#6366f1',0,NOW())
            ");
        }
    }

    _annj([
        'status'     => 'success',
        'message'    => 'Announcement sent',
        'ann_id'     => $ann_id,
        'recipients' => $inserted,
    ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   LIST_ANNOUNCEMENTS_SENT  (instructor)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'list_announcements_sent') {
    if (!in_array($role, [3, 5])) _annj(['status' => 'error', 'message' => 'Forbidden']);

    $course_id = (int)($_GET['course_id'] ?? 0);
    $esc_me    = _annce($db, $me);
    $where_c   = $course_id ? "AND a.course_id = $course_id" : '';
    $where_s   = $role === 3 ? "AND a.sender_code = '$esc_me'" : '';

    $rows = $db->query("
        SELECT a.*,
            c.title AS course_title,
            COUNT(DISTINCT ar.usr_code) AS total_count,
            SUM(ar.is_read) AS read_count
        FROM tbl_announcements a
        LEFT JOIN tbl_courses c ON c.id = a.course_id
        LEFT JOIN tbl_announcement_recipients ar ON ar.announcement_id = a.id
        WHERE 1=1 $where_s $where_c
        GROUP BY a.id
        ORDER BY a.sent_at DESC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);

    foreach ($rows as &$r) {
        $r['total_count'] = (int)$r['total_count'];
        $r['read_count']  = (int)$r['read_count'];
        $r['read_rate']   = $r['total_count'] > 0 ? round(($r['read_count'] / $r['total_count']) * 100) : 0;
    }
    unset($r);

    _annj(['status' => 'success', 'data' => $rows]);
}

/* ══════════════════════════════════════════════════════════════════════════
   GET_ANNOUNCEMENT_STATS  (instructor)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_announcement_stats') {
    if (!in_array($role, [3, 5])) _annj(['status' => 'error', 'message' => 'Forbidden']);

    $ann_id  = (int)($_GET['ann_id'] ?? 0);
    $esc_me  = _annce($db, $me);
    $where_s = $role === 3 ? "AND a.sender_code = '$esc_me'" : '';

    if ($ann_id) {
        $row = $db->query("
            SELECT a.id, a.subject, a.sent_at, a.audience,
                COUNT(DISTINCT ar.usr_code) AS total_count,
                SUM(ar.is_read) AS read_count
            FROM tbl_announcements a
            LEFT JOIN tbl_announcement_recipients ar ON ar.announcement_id = a.id
            WHERE a.id = $ann_id $where_s
            GROUP BY a.id
        ")->fetch_assoc();

        if (!$row) _annj(['status' => 'error', 'message' => 'Not found']);
        $row['read_rate'] = (int)$row['total_count'] > 0
            ? round(((int)$row['read_count'] / (int)$row['total_count']) * 100) : 0;

        /* per-recipient list */
        $recips = $db->query("
            SELECT ar.usr_code, ar.is_read, ar.read_at,
                   u.first_name, u.last_name
            FROM tbl_announcement_recipients ar
            LEFT JOIN tbl_all_users u ON u.usr_code = ar.usr_code
            WHERE ar.announcement_id = $ann_id
            ORDER BY ar.is_read DESC, u.first_name
        ")->fetch_all(MYSQLI_ASSOC);

        _annj(['status' => 'success', 'data' => $row, 'recipients' => $recips]);
    }

    /* Summary stats for all my announcements */
    $esc_me2 = _annce($db, $me);
    $where_s2 = $role === 3 ? "WHERE a.sender_code = '$esc_me2'" : 'WHERE 1=1';
    $summary = $db->query("
        SELECT
            COUNT(DISTINCT a.id)              AS total_sent,
            COUNT(DISTINCT a.course_id)       AS courses_count,
            COALESCE(SUM(ar.is_read),0)       AS total_reads,
            COUNT(DISTINCT ar.usr_code)       AS total_recipients
        FROM tbl_announcements a
        LEFT JOIN tbl_announcement_recipients ar ON ar.announcement_id = a.id
        $where_s2
    ")->fetch_assoc();

    $summary['avg_read_rate'] = (int)($summary['total_recipients'] ?? 0) > 0
        ? round(((int)$summary['total_reads'] / (int)$summary['total_recipients']) * 100) : 0;

    _annj(['status' => 'success', 'data' => $summary]);
}

/* ══════════════════════════════════════════════════════════════════════════
   LIST_ANNOUNCEMENTS_RECEIVED  (any logged-in user)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'list_announcements_received') {
    $limit     = min(50, (int)($_GET['limit'] ?? 20));
    $unread_only = !empty($_GET['unread_only']);
    $esc_me    = _annce($db, $me);
    $where_u   = $unread_only ? 'AND ar.is_read = 0' : '';

    $rows = $db->query("
        SELECT a.id, a.course_id, a.subject, a.type, a.body, a.attachment, a.sent_at,
               c.title AS course_title,
               ar.is_read, ar.read_at,
               u.first_name AS sender_first, u.last_name AS sender_last
        FROM tbl_announcement_recipients ar
        JOIN tbl_announcements a ON a.id = ar.announcement_id
        LEFT JOIN tbl_courses c ON c.id = a.course_id
        LEFT JOIN tbl_all_users u ON u.usr_code = a.sender_code
        WHERE ar.usr_code = '$esc_me' $where_u
        ORDER BY a.sent_at DESC
        LIMIT $limit
    ")->fetch_all(MYSQLI_ASSOC);

    $unread = (int)$db->query("SELECT COUNT(*) FROM tbl_announcement_recipients WHERE usr_code='$esc_me' AND is_read=0")->fetch_row()[0];

    _annj(['status' => 'success', 'data' => $rows, 'unread' => $unread]);
}

/* ══════════════════════════════════════════════════════════════════════════
   MARK_READ
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'mark_read') {
    $ann_id = (int)($body['ann_id'] ?? 0);
    $esc_me = _annce($db, $me);

    if ($ann_id) {
        $stmt = $db->prepare("UPDATE tbl_announcement_recipients SET is_read=1, read_at=NOW() WHERE announcement_id=? AND usr_code=? AND is_read=0");
        $stmt->bind_param('is', $ann_id, $me);
        $stmt->execute();
    } else {
        /* mark all read */
        $db->query("UPDATE tbl_announcement_recipients SET is_read=1, read_at=NOW() WHERE usr_code='$esc_me' AND is_read=0");
    }
    _annj(['status' => 'success', 'affected' => $db->affected_rows]);
}

/* ══════════════════════════════════════════════════════════════════════════
   GET_COURSE_STUDENTS  (for selected-audience picker)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_course_students') {
    if (!in_array($role, [3, 5])) _annj(['status' => 'error', 'message' => 'Forbidden']);

    $course_id = (int)($_GET['course_id'] ?? 0);
    if (!$course_id) _annj(['status' => 'error', 'message' => 'Course ID required']);

    $rows = $db->query("
        SELECT u.usr_code, u.first_name, u.last_name, u.email_address
        FROM tbl_course_enrollments ce
        JOIN tbl_all_users u ON u.id = ce.user_id
        WHERE ce.course_id = $course_id AND ce.has_access = 1
        ORDER BY u.first_name, u.last_name
        LIMIT 500
    ")->fetch_all(MYSQLI_ASSOC);

    _annj(['status' => 'success', 'data' => $rows]);
}

_annj(['status' => 'error', 'message' => 'Unknown action']);
