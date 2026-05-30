<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? 0) != 4) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}

$me     = $_SESSION['usr_code'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$body   = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
}
$action = $action ?: ($body['action'] ?? '');

/* ── Get org for current admin ─────────────────────────────── */
function lm_getMyOrg(mysqli $db, string $usrCode): ?array {
    $s = $db->prepare("
        SELECT o.* FROM tbl_organizations o
        JOIN tbl_org_members m ON m.org_code = o.org_code
        WHERE m.usr_code = ? AND m.org_role = 'admin' AND m.status = 'active' AND o.deleted_at IS NULL
        LIMIT 1
    ");
    $s->bind_param('s', $usrCode);
    $s->execute();
    return $s->get_result()->fetch_assoc();
}

/* ── Notification helper ────────────────────────────────────── */
function lm_notify(mysqli $db, string $userCode, string $type, string $title, ?string $body = null, ?string $link = null, string $icon = 'bi-key', string $color = '#6366f1'): void {
    $st = $db->prepare("INSERT INTO tbl_notifications (user_code, type, title, body, link, icon, color) VALUES (?,?,?,?,?,?,?)");
    $st->bind_param('sssssss', $userCode, $type, $title, $body, $link, $icon, $color);
    $st->execute();
}

/* ── Org activity log ───────────────────────────────────────── */
function lm_orgLog(mysqli $db, string $oc, string $actor, string $action, string $tt = '', string $tid = '', array $d = []): void {
    $det = $d ? json_encode($d) : null;
    $ip  = $_SERVER['REMOTE_ADDR'] ?? null;
    $s   = $db->prepare("INSERT INTO tbl_org_activity (org_code, actor_usr_code, action, target_type, target_id, details, ip_address) VALUES (?,?,?,?,?,?,?)");
    $s->bind_param('sssssss', $oc, $actor, $action, $tt, $tid, $det, $ip);
    $s->execute();
}

/* ── Update seats_used count ────────────────────────────────── */
function lm_refreshSeatsUsed(mysqli $db, int $accessId): void {
    $s = $db->prepare("
        UPDATE tbl_org_course_access
        SET seats_used = (
            SELECT COUNT(*) FROM tbl_org_seat_assignments
            WHERE access_id = ? AND is_active = 1
        )
        WHERE id = ?
    ");
    $s->bind_param('ii', $accessId, $accessId);
    $s->execute();
}

$myOrg = lm_getMyOrg($db, $me);
if (!$myOrg) {
    echo json_encode(['status' => 'error', 'message' => 'No active organization found for your account']); exit;
}
$orgCode = $myOrg['org_code'];

/* ═══════════════════════════════════════════════════════════════ */
switch ($action) {

/* ── List all licenses (org_course_access) for this org ──── */
case 'list_licenses':
    $rows = $db->query("
        SELECT
            oca.id, oca.course_id, oca.access_type, oca.seats_purchased,
            oca.seats_used, oca.expires_at, oca.is_active, oca.granted_at,
            (COALESCE(oca.seats_purchased, 0) - COALESCE(oca.seats_used, 0)) AS remaining,
            c.title, c.thumbnail, c.status AS course_status,
            CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
            (
                SELECT COUNT(*)
                FROM tbl_org_seat_assignments sa
                WHERE sa.access_id = oca.id AND sa.is_active = 1
            ) AS assigned_count
        FROM tbl_org_course_access oca
        JOIN tbl_courses c ON c.id = oca.course_id
        LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
        WHERE oca.org_code = '$orgCode' AND oca.is_active = 1
        ORDER BY oca.granted_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    // Attach brief assignee list per license
    foreach ($rows as &$row) {
        $aid = (int)$row['id'];
        $assignees = $db->query("
            SELECT sa.usr_code, sa.assigned_at,
                   CONCAT(u.first_name,' ',u.last_name) AS full_name,
                   u.email_address AS email,
                   COALESCE((
                       SELECT ROUND(100 * COUNT(DISTINCT CASE WHEN p.watched=1 THEN p.lesson_id END)
                              / GREATEST(COUNT(DISTINCT l.id), 1), 1)
                       FROM tbl_course_chapter_lessons l
                       LEFT JOIN tbl_course_progress p ON p.lesson_id = l.id AND p.user_id = sa.usr_code
                       WHERE l.course_id = " . (int)$row['course_id'] . " AND l.status = 'active'
                   ), 0) AS progress_pct
            FROM tbl_org_seat_assignments sa
            JOIN tbl_all_users u ON u.usr_code = sa.usr_code
            WHERE sa.access_id = $aid AND sa.is_active = 1
            ORDER BY sa.assigned_at DESC
        ")->fetch_all(MYSQLI_ASSOC);
        $row['assignees'] = $assignees;
    }
    unset($row);

    echo json_encode(['status' => 'success', 'data' => $rows]);
    break;

/* ── Get single license detail with full assignee + progress ── */
case 'get_license_detail':
    $accessId = (int)($body['access_id'] ?? $_GET['access_id'] ?? 0);
    if (!$accessId) { echo json_encode(['status' => 'error', 'message' => 'Missing access_id']); exit; }

    $lic = $db->query("
        SELECT oca.*, c.title, c.thumbnail,
               CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
               (COALESCE(oca.seats_purchased, 0) - COALESCE(oca.seats_used, 0)) AS remaining
        FROM tbl_org_course_access oca
        JOIN tbl_courses c ON c.id = oca.course_id
        LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
        WHERE oca.id = $accessId AND oca.org_code = '$orgCode'
        LIMIT 1
    ")->fetch_assoc();

    if (!$lic) { echo json_encode(['status' => 'error', 'message' => 'License not found']); exit; }

    $courseId = (int)$lic['course_id'];
    $assignees = $db->query("
        SELECT sa.id AS assignment_id, sa.usr_code, sa.assigned_at, sa.assigned_by,
               CONCAT(u.first_name,' ',u.last_name) AS full_name,
               u.email_address AS email,
               d.dept_name,
               COALESCE((
                   SELECT ROUND(100 * COUNT(DISTINCT CASE WHEN p.watched=1 THEN p.lesson_id END)
                          / GREATEST(COUNT(DISTINCT l.id), 1), 1)
                   FROM tbl_course_chapter_lessons l
                   LEFT JOIN tbl_course_progress p ON p.lesson_id = l.id AND p.user_id = sa.usr_code
                   WHERE l.course_id = $courseId AND l.status = 'active'
               ), 0) AS progress_pct
        FROM tbl_org_seat_assignments sa
        JOIN tbl_all_users u ON u.usr_code = sa.usr_code
        LEFT JOIN tbl_org_members om ON om.usr_code = sa.usr_code AND om.org_code = '$orgCode'
        LEFT JOIN tbl_org_departments d ON d.id = om.dept_id
        WHERE sa.access_id = $accessId AND sa.is_active = 1
        ORDER BY sa.assigned_at DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $lic['assignees'] = $assignees;
    echo json_encode(['status' => 'success', 'data' => $lic]);
    break;

/* ── Assign seats (batch) ───────────────────────────────────── */
case 'assign_seats':
    $accessId  = (int)($body['access_id'] ?? 0);
    $usrCodes  = $body['usr_codes'] ?? [];

    if (!$accessId || empty($usrCodes) || !is_array($usrCodes)) {
        echo json_encode(['status' => 'error', 'message' => 'access_id and usr_codes[] are required']); exit;
    }

    // Load license
    $lic = $db->prepare("
        SELECT id, course_id, seats_purchased, seats_used, access_type, is_active, expires_at
        FROM tbl_org_course_access
        WHERE id = ? AND org_code = ? AND is_active = 1
        LIMIT 1
    ");
    $lic->bind_param('is', $accessId, $orgCode);
    $lic->execute();
    $licRow = $lic->get_result()->fetch_assoc();
    if (!$licRow) { echo json_encode(['status' => 'error', 'message' => 'License not found or inactive']); exit; }

    $courseId       = (int)$licRow['course_id'];
    $seatsPurchased = (int)$licRow['seats_purchased'];
    $seatsUsed      = (int)$licRow['seats_used'];

    // Validate seat availability for seat_limited licenses
    if ($licRow['access_type'] === 'seat_limited') {
        $remaining = $seatsPurchased - $seatsUsed;
        if (count($usrCodes) > $remaining) {
            echo json_encode([
                'status'    => 'error',
                'message'   => "Not enough seats. Remaining: $remaining, Requested: " . count($usrCodes)
            ]); exit;
        }
    }

    $db->begin_transaction();
    $assigned = 0; $skipped = 0; $errors = [];

    try {
        foreach ($usrCodes as $usrCode) {
            $usrCode = $db->real_escape_string(trim($usrCode));
            if (!$usrCode) { $skipped++; continue; }

            // Verify member belongs to org
            $memChk = $db->query("SELECT id FROM tbl_org_members WHERE org_code='$orgCode' AND usr_code='$usrCode' AND status='active' LIMIT 1");
            if ($memChk->num_rows === 0) { $skipped++; $errors[] = "$usrCode not in org"; continue; }

            // Check not already assigned
            $dupChk = $db->query("SELECT id FROM tbl_org_seat_assignments WHERE access_id=$accessId AND usr_code='$usrCode' AND is_active=1 LIMIT 1");
            if ($dupChk->num_rows > 0) { $skipped++; $errors[] = "$usrCode already assigned"; continue; }

            // Insert seat assignment
            $sa = $db->prepare("INSERT INTO tbl_org_seat_assignments (access_id, org_code, course_id, usr_code, assigned_by, is_active) VALUES (?,?,?,?,?,1)");
            $sa->bind_param('isiss', $accessId, $orgCode, $courseId, $usrCode, $me);
            $sa->execute();

            // Ensure course enrollment exists
            $enr = $db->prepare("
                INSERT INTO tbl_course_enrollments (user_id, course_id, has_access, enrolled_at)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE has_access = 1
            ");
            $enr->bind_param('si', $usrCode, $courseId);
            $enr->execute();

            // Notify user
            $courseTitle = $db->query("SELECT title FROM tbl_courses WHERE id=$courseId LIMIT 1")->fetch_row()[0] ?? 'a course';
            $notifBody   = "You have been assigned access to: $courseTitle";
            $notifLink   = "?view=read_course_details_data&course_id=$courseId";
            lm_notify($db, $usrCode, 'seat_assigned', 'Course Access Granted', $notifBody, $notifLink, 'bi-key-fill', '#10b981');

            $assigned++;
        }

        // Refresh seats_used
        lm_refreshSeatsUsed($db, $accessId);

        $db->commit();
        lm_orgLog($db, $orgCode, $me, 'seats_assigned', 'license', (string)$accessId, ['assigned' => $assigned, 'course_id' => $courseId]);
        echo json_encode(['status' => 'success', 'assigned' => $assigned, 'skipped' => $skipped, 'errors' => $errors,
            'message' => "Assigned $assigned seat(s)" . ($skipped ? ", skipped $skipped" : '')]);

    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Assignment failed: ' . $e->getMessage()]);
    }
    break;

/* ── Revoke a single seat assignment ────────────────────────── */
case 'revoke_seat':
    $assignmentId = (int)($body['assignment_id'] ?? 0);
    if (!$assignmentId) { echo json_encode(['status' => 'error', 'message' => 'assignment_id required']); exit; }

    // Verify assignment belongs to this org
    $sa = $db->query("
        SELECT sa.id, sa.usr_code, sa.access_id, sa.course_id
        FROM tbl_org_seat_assignments sa
        WHERE sa.id = $assignmentId AND sa.org_code = '$orgCode' AND sa.is_active = 1
        LIMIT 1
    ")->fetch_assoc();
    if (!$sa) { echo json_encode(['status' => 'error', 'message' => 'Assignment not found']); exit; }

    $db->query("UPDATE tbl_org_seat_assignments SET is_active = 0, revoked_at = NOW() WHERE id = $assignmentId");
    lm_refreshSeatsUsed($db, (int)$sa['access_id']);

    $courseId    = (int)$sa['course_id'];
    $courseTitle = $db->query("SELECT title FROM tbl_courses WHERE id=$courseId LIMIT 1")->fetch_row()[0] ?? 'a course';
    $notifBody   = "Your seat access to \"$courseTitle\" has been revoked.";
    lm_notify($db, $sa['usr_code'], 'seat_revoked', 'Course Access Revoked', $notifBody, null, 'bi-key', '#ef4444');

    lm_orgLog($db, $orgCode, $me, 'seat_revoked', 'assignment', (string)$assignmentId, ['usr_code' => $sa['usr_code']]);
    echo json_encode(['status' => 'success', 'message' => 'Seat revoked successfully']);
    break;

/* ── Bulk assign all members of a department ────────────────── */
case 'bulk_assign':
    $accessId = (int)($body['access_id'] ?? 0);
    $deptId   = (int)($body['dept_id']   ?? 0);
    if (!$accessId || !$deptId) { echo json_encode(['status' => 'error', 'message' => 'access_id and dept_id required']); exit; }

    // Load license
    $lic = $db->prepare("
        SELECT id, course_id, seats_purchased, seats_used, access_type, is_active
        FROM tbl_org_course_access
        WHERE id = ? AND org_code = ? AND is_active = 1
        LIMIT 1
    ");
    $lic->bind_param('is', $accessId, $orgCode);
    $lic->execute();
    $licRow = $lic->get_result()->fetch_assoc();
    if (!$licRow) { echo json_encode(['status' => 'error', 'message' => 'License not found']); exit; }

    $courseId = (int)$licRow['course_id'];

    // Get dept members not yet assigned
    $members = $db->query("
        SELECT m.usr_code FROM tbl_org_members m
        WHERE m.org_code = '$orgCode' AND m.dept_id = $deptId AND m.status = 'active'
          AND m.usr_code NOT IN (
              SELECT sa.usr_code FROM tbl_org_seat_assignments sa
              WHERE sa.access_id = $accessId AND sa.is_active = 1
          )
    ")->fetch_all(MYSQLI_ASSOC);

    if (empty($members)) {
        echo json_encode(['status' => 'success', 'assigned' => 0, 'message' => 'All department members are already assigned']);
        break;
    }

    $usrCodes = array_column($members, 'usr_code');

    // Check seats for seat_limited
    if ($licRow['access_type'] === 'seat_limited') {
        $remaining = (int)$licRow['seats_purchased'] - (int)$licRow['seats_used'];
        if (count($usrCodes) > $remaining) {
            echo json_encode([
                'status'  => 'error',
                'message' => "Not enough seats. Remaining: $remaining, Department members needing seats: " . count($usrCodes)
            ]); exit;
        }
    }

    $db->begin_transaction();
    $assigned = 0;
    try {
        $courseTitle = $db->query("SELECT title FROM tbl_courses WHERE id=$courseId LIMIT 1")->fetch_row()[0] ?? 'a course';
        foreach ($usrCodes as $usrCode) {
            $usrCode = $db->real_escape_string(trim($usrCode));
            $sa = $db->prepare("INSERT INTO tbl_org_seat_assignments (access_id, org_code, course_id, usr_code, assigned_by, is_active) VALUES (?,?,?,?,?,1)");
            $sa->bind_param('isiss', $accessId, $orgCode, $courseId, $usrCode, $me);
            $sa->execute();

            $enr = $db->prepare("
                INSERT INTO tbl_course_enrollments (user_id, course_id, has_access, enrolled_at)
                VALUES (?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE has_access = 1
            ");
            $enr->bind_param('si', $usrCode, $courseId);
            $enr->execute();

            $notifBody = "You have been assigned access to: $courseTitle";
            $notifLink = "?view=read_course_details_data&course_id=$courseId";
            lm_notify($db, $usrCode, 'seat_assigned', 'Course Access Granted', $notifBody, $notifLink, 'bi-key-fill', '#10b981');
            $assigned++;
        }

        lm_refreshSeatsUsed($db, $accessId);
        $db->commit();
        lm_orgLog($db, $orgCode, $me, 'bulk_assign', 'license', (string)$accessId, ['dept_id' => $deptId, 'assigned' => $assigned]);
        echo json_encode(['status' => 'success', 'assigned' => $assigned, 'message' => "Assigned $assigned seat(s) to department members"]);

    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Bulk assignment failed: ' . $e->getMessage()]);
    }
    break;

/* ── Get org members NOT yet assigned to a license ─────────── */
case 'get_members_for_assignment':
    $accessId = (int)($body['access_id'] ?? $_GET['access_id'] ?? 0);
    $q        = $db->real_escape_string(trim($_GET['q'] ?? $body['q'] ?? ''));
    if (!$accessId) { echo json_encode(['status' => 'error', 'message' => 'access_id required']); exit; }

    $search = $q ? "AND (u.first_name LIKE '%$q%' OR u.last_name LIKE '%$q%' OR u.email_address LIKE '%$q%')" : '';

    $rows = $db->query("
        SELECT m.usr_code, m.org_role, m.dept_id,
               CONCAT(u.first_name,' ',u.last_name) AS full_name,
               u.email_address AS email,
               d.dept_name
        FROM tbl_org_members m
        JOIN tbl_all_users u ON u.usr_code = m.usr_code
        LEFT JOIN tbl_org_departments d ON d.id = m.dept_id
        WHERE m.org_code = '$orgCode' AND m.status = 'active'
          $search
          AND m.usr_code NOT IN (
              SELECT sa.usr_code FROM tbl_org_seat_assignments sa
              WHERE sa.access_id = $accessId AND sa.is_active = 1
          )
        ORDER BY u.first_name ASC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status' => 'success', 'members' => $rows]);
    break;

default:
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
