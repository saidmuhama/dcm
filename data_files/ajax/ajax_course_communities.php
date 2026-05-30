<?php
/**
 * ajax_course_communities.php
 * Manages auto-creation of course chat communities and org groups.
 */
ini_set('display_errors', 0);
ob_start();
session_start();
include('../config/db.php');
header('Content-Type: application/json');

function _ccj(array $d): never { ob_clean(); echo json_encode($d); exit; }
function _cce(mysqli $db, $v): string { return $db->real_escape_string((string)$v); }

$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me) _ccj(['status' => 'error', 'message' => 'Unauthorized']);

/* ── Ensure chat tables exist (mirrors ajax_chat bootstrap) ───────────── */
$db->query("CREATE TABLE IF NOT EXISTS `tbl_chat_conversations` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type`            ENUM('direct','group') NOT NULL DEFAULT 'direct',
    `name`            VARCHAR(255) DEFAULT NULL,
    `avatar`          VARCHAR(500) DEFAULT NULL,
    `created_by`      VARCHAR(50) NOT NULL,
    `linked_type`     VARCHAR(50) DEFAULT NULL,
    `linked_id`       BIGINT UNSIGNED DEFAULT NULL,
    `auto_managed`    TINYINT(1) NOT NULL DEFAULT 0,
    `org_code`        VARCHAR(100) DEFAULT NULL,
    `dept_id`         INT UNSIGNED DEFAULT NULL,
    `last_message`    TEXT DEFAULT NULL,
    `last_msg_type`   VARCHAR(20) DEFAULT 'text',
    `last_message_at` TIMESTAMP NULL DEFAULT NULL,
    `last_message_by` VARCHAR(50) DEFAULT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_linked` (`linked_type`,`linked_id`),
    KEY `idx_lm`     (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS `tbl_chat_participants` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conv_id`          BIGINT UNSIGNED NOT NULL,
    `usr_code`         VARCHAR(50) NOT NULL,
    `role`             ENUM('member','admin') DEFAULT 'member',
    `last_read_at`     TIMESTAMP NULL DEFAULT NULL,
    `last_read_msg_id` BIGINT UNSIGNED DEFAULT 0,
    `is_muted`         TINYINT(1) DEFAULT 0,
    `joined_at`        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `left_at`          TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uniq_cp` (`conv_id`,`usr_code`),
    KEY `idx_usr` (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* ── Route ──────────────────────────────────────────────────────────────── */
$method = $_SERVER['REQUEST_METHOD'];
$body   = $method === 'POST' ? (json_decode(file_get_contents('php://input'), true) ?? $_POST) : [];
$action = $method === 'GET'  ? ($_GET['action'] ?? '') : ($body['action'] ?? '');

/* ── Helper: find or create course community ────────────────────────────── */
function ensureCourseCommunity(mysqli $db, int $course_id): int {
    $row = $db->query("
        SELECT id FROM tbl_chat_conversations
        WHERE linked_type='course' AND linked_id=$course_id AND auto_managed=1
        LIMIT 1
    ")->fetch_assoc();

    if ($row) return (int)$row['id'];

    /* Fetch course title */
    $tr  = $db->query("SELECT title FROM tbl_courses WHERE id=$course_id LIMIT 1")->fetch_row();
    $raw = ($tr && $tr[0]) ? $tr[0] : 'Course Community';
    $nm  = _cce($db, $raw . ' Community');

    $db->query("
        INSERT INTO tbl_chat_conversations
            (type, name, linked_type, linked_id, auto_managed, created_by, last_message_at)
        VALUES
            ('group', '$nm', 'course', $course_id, 1, 'system', NOW())
    ");
    return (int)$db->insert_id;
}

/* ══════════════════════════════════════════════════════════════════════════
   ENSURE_COURSE_COMMUNITY
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'ensure_course_community') {
    $course_id = (int)($_GET['course_id'] ?? $body['course_id'] ?? 0);
    if (!$course_id) _ccj(['status' => 'error', 'message' => 'course_id required']);

    $conv_id = ensureCourseCommunity($db, $course_id);
    _ccj(['status' => 'success', 'conv_id' => $conv_id]);
}

/* ══════════════════════════════════════════════════════════════════════════
   JOIN_COURSE_COMMUNITY  (called on enrollment)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'join_course_community') {
    $course_id  = (int)($_GET['course_id'] ?? $body['course_id'] ?? 0);
    $target_usr = trim($_GET['usr_code'] ?? $body['usr_code'] ?? $me);
    if (!$course_id) _ccj(['status' => 'error', 'message' => 'course_id required']);

    $conv_id  = ensureCourseCommunity($db, $course_id);
    $esc_usr  = _cce($db, $target_usr);

    $db->query("INSERT IGNORE INTO tbl_chat_participants (conv_id, usr_code, role, joined_at)
                VALUES ($conv_id, '$esc_usr', 'member', NOW())");

    _ccj(['status' => 'success', 'conv_id' => $conv_id, 'joined' => $db->affected_rows > 0]);
}

/* ══════════════════════════════════════════════════════════════════════════
   LEAVE_COURSE_COMMUNITY  (called on unenrollment)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'leave_course_community') {
    $course_id  = (int)($body['course_id'] ?? $_GET['course_id'] ?? 0);
    $target_usr = trim($body['usr_code'] ?? $_GET['usr_code'] ?? $me);
    if (!$course_id) _ccj(['status' => 'error', 'message' => 'course_id required']);

    $esc_usr = _cce($db, $target_usr);
    $row = $db->query("SELECT id FROM tbl_chat_conversations WHERE linked_type='course' AND linked_id=$course_id AND auto_managed=1 LIMIT 1")->fetch_assoc();
    if (!$row) _ccj(['status' => 'success', 'message' => 'No community found']);

    $conv_id = (int)$row['id'];
    $db->query("UPDATE tbl_chat_participants SET left_at=NOW() WHERE conv_id=$conv_id AND usr_code='$esc_usr' AND left_at IS NULL");

    _ccj(['status' => 'success', 'left' => $db->affected_rows > 0]);
}

/* ══════════════════════════════════════════════════════════════════════════
   GET_COMMUNITY_URL  — redirect URL for course chat
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_community_url') {
    $course_id = (int)($_GET['course_id'] ?? $body['course_id'] ?? 0);
    if (!$course_id) _ccj(['status' => 'error', 'message' => 'course_id required']);

    $row = $db->query("SELECT id FROM tbl_chat_conversations WHERE linked_type='course' AND linked_id=$course_id AND auto_managed=1 LIMIT 1")->fetch_assoc();
    if (!$row) _ccj(['status' => 'error', 'message' => 'Community not yet created']);

    _ccj([
        'status'  => 'success',
        'conv_id' => (int)$row['id'],
        'url'     => '?view=learning-chat-call&conv_id=' . (int)$row['id'],
    ]);
}

/* ══════════════════════════════════════════════════════════════════════════
   CREATE_ORG_GROUP
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'create_org_group') {
    if (!in_array($role, [4, 5])) _ccj(['status' => 'error', 'message' => 'Forbidden']);

    $name    = trim($body['name'] ?? '');
    $org_code = trim($body['org_code'] ?? '');
    $dept_id  = (int)($body['dept_id'] ?? 0) ?: null;

    if (!$name)     _ccj(['status' => 'error', 'message' => 'Group name required']);
    if (!$org_code) _ccj(['status' => 'error', 'message' => 'org_code required']);

    $esc_name = _cce($db, $name);
    $esc_oc   = _cce($db, $org_code);
    $esc_me   = _cce($db, $me);
    $dept_val = $dept_id ? $dept_id : 'NULL';

    $db->begin_transaction();
    try {
        $db->query("
            INSERT INTO tbl_chat_conversations
                (type, name, linked_type, org_code, dept_id, auto_managed, created_by, last_message_at)
            VALUES
                ('group', '$esc_name', 'org_group', '$esc_oc', $dept_val, 0, '$esc_me', NOW())
        ");
        $conv_id = (int)$db->insert_id;
        if (!$conv_id) throw new RuntimeException('Insert failed');

        $db->query("INSERT IGNORE INTO tbl_chat_participants (conv_id, usr_code, role, joined_at) VALUES ($conv_id, '$esc_me', 'admin', NOW())");
        $db->commit();
        _ccj(['status' => 'success', 'conv_id' => $conv_id]);
    } catch (Throwable $e) {
        $db->rollback();
        _ccj(['status' => 'error', 'message' => 'Failed to create group']);
    }
}

/* ══════════════════════════════════════════════════════════════════════════
   LIST_ORG_GROUPS
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'list_org_groups') {
    $org_code = trim($_GET['org_code'] ?? $body['org_code'] ?? '');
    if (!$org_code) _ccj(['status' => 'error', 'message' => 'org_code required']);

    $esc_oc = _cce($db, $org_code);
    $rows   = $db->query("
        SELECT c.*,
               (SELECT COUNT(*) FROM tbl_chat_participants cp WHERE cp.conv_id=c.id AND cp.left_at IS NULL) AS member_count
        FROM tbl_chat_conversations c
        WHERE c.org_code='$esc_oc' AND c.linked_type='org_group'
        ORDER BY c.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    _ccj(['status' => 'success', 'data' => $rows]);
}

/* ══════════════════════════════════════════════════════════════════════════
   ADD_MEMBER_TO_COMMUNITY  (admin: bulk add enrolled students)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'bulk_join_course') {
    if (!in_array($role, [3, 4, 5])) _ccj(['status' => 'error', 'message' => 'Forbidden']);

    $course_id = (int)($body['course_id'] ?? 0);
    if (!$course_id) _ccj(['status' => 'error', 'message' => 'course_id required']);

    $conv_id = ensureCourseCommunity($db, $course_id);

    /* Add all enrolled students who are not yet participants */
    $rs = $db->query("
        SELECT u.usr_code
        FROM tbl_course_enrollments ce
        JOIN tbl_all_users u ON u.id = ce.user_id
        WHERE ce.course_id = $course_id AND ce.has_access = 1
    ");
    $added = 0;
    while ($r = $rs->fetch_assoc()) {
        $eu = _cce($db, $r['usr_code']);
        $db->query("INSERT IGNORE INTO tbl_chat_participants (conv_id, usr_code, role, joined_at) VALUES ($conv_id, '$eu', 'member', NOW())");
        if ($db->affected_rows > 0) $added++;
    }

    _ccj(['status' => 'success', 'conv_id' => $conv_id, 'added' => $added]);
}

_ccj(['status' => 'error', 'message' => 'Unknown action']);
