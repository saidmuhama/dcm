<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
header('Content-Type: application/json');

$role = (int)($_SESSION['user_role'] ?? 0);
if (!in_array($role, [4, 5])) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}

$me     = $_SESSION['usr_code'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── Resolve org_code based on role ───────────────────────────────────
if ($role === 5) {
    // Super admin may pass ?org_code= to inspect any org
    $orgCode = trim($_GET['org_code'] ?? $_POST['org_code'] ?? '');
    if ($orgCode === '') {
        // Default: first active org for listing purposes
        $orgRow = $db->query("
            SELECT org_code FROM tbl_organizations
            WHERE status='active' AND deleted_at IS NULL
            LIMIT 1
        ")->fetch_assoc();
        $orgCode = $orgRow['org_code'] ?? '';
    }
} else {
    // Org admin — resolve their org
    $stmt = $db->prepare("
        SELECT o.org_code FROM tbl_organizations o
        JOIN tbl_org_members m ON m.org_code = o.org_code
        WHERE m.usr_code = ? AND m.org_role = 'admin' AND m.status = 'active'
          AND o.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->bind_param('s', $me);
    $stmt->execute();
    $orgRow  = $stmt->get_result()->fetch_assoc();
    $orgCode = $orgRow['org_code'] ?? '';
}

if ($orgCode === '' && $action !== 'license_utilization_all') {
    echo json_encode(['status' => 'error', 'message' => 'No organization found']); exit;
}

$safeOrg = $db->real_escape_string($orgCode);

switch ($action) {

/* ── License utilization per course ─────────────────────────────────── */
case 'license_utilization':
    $rows = $db->query("
        SELECT
            c.id          AS course_id,
            c.title,
            oca.seats_purchased,
            COUNT(sa.id)  AS seats_used,
            oca.expires_at,
            COALESCE(
                ROUND(
                    AVG(ce.progress_pct), 1
                ), 0
            ) AS avg_completion
        FROM tbl_org_course_access oca
        JOIN tbl_courses c ON c.id = oca.course_id
        LEFT JOIN tbl_org_seat_assignments sa
               ON sa.org_code = oca.org_code AND sa.course_id = oca.course_id
        LEFT JOIN tbl_course_enrollments ce
               ON ce.course_id = oca.course_id
              AND ce.user_id IN (
                  SELECT usr_code FROM tbl_org_members WHERE org_code = '$safeOrg' AND status = 'active'
              )
        WHERE oca.org_code = '$safeOrg' AND oca.is_active = 1
        GROUP BY c.id, c.title, oca.seats_purchased, oca.expires_at
        ORDER BY c.title ASC
    ");

    $data = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $purchased = (int)$r['seats_purchased'];
            $used      = (int)$r['seats_used'];
            $data[] = [
                'course_id'       => (int)$r['course_id'],
                'title'           => $r['title'],
                'seats_purchased' => $purchased,
                'seats_used'      => $used,
                'seats_available' => max(0, $purchased - $used),
                'utilization_pct' => $purchased > 0 ? round(($used / $purchased) * 100) : 0,
                'avg_completion'  => (float)$r['avg_completion'],
                'expires_at'      => $r['expires_at'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'licenses' => $data]);
    break;

/* ── License utilization across all orgs (super admin aggregate) ────── */
case 'license_utilization_all':
    if ($role !== 5) {
        echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
    }
    $rows = $db->query("
        SELECT
            o.org_name,
            o.org_code,
            c.title,
            oca.seats_purchased,
            COUNT(sa.id)  AS seats_used,
            COALESCE(ROUND(AVG(ce.progress_pct), 1), 0) AS avg_completion
        FROM tbl_org_course_access oca
        JOIN tbl_organizations o  ON o.org_code  = oca.org_code
        JOIN tbl_courses c        ON c.id         = oca.course_id
        LEFT JOIN tbl_org_seat_assignments sa
               ON sa.org_code = oca.org_code AND sa.course_id = oca.course_id
        LEFT JOIN tbl_course_enrollments ce
               ON ce.course_id = oca.course_id
              AND ce.user_id IN (
                  SELECT usr_code FROM tbl_org_members
                  WHERE org_code = oca.org_code AND status = 'active'
              )
        WHERE oca.is_active = 1 AND o.deleted_at IS NULL
        GROUP BY o.org_code, o.org_name, c.id, c.title, oca.seats_purchased
        ORDER BY o.org_name, c.title
    ");

    $data = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $purchased = (int)$r['seats_purchased'];
            $used      = (int)$r['seats_used'];
            $data[] = [
                'org_name'        => $r['org_name'],
                'org_code'        => $r['org_code'],
                'title'           => $r['title'],
                'seats_purchased' => $purchased,
                'seats_used'      => $used,
                'utilization_pct' => $purchased > 0 ? round(($used / $purchased) * 100) : 0,
                'avg_completion'  => (float)$r['avg_completion'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'licenses' => $data]);
    break;

/* ── Purchase history for the org ───────────────────────────────────── */
case 'purchase_history':
    $rows = $db->query("
        SELECT
            pr.id,
            pr.request_code,
            pr.status,
            pr.final_price,
            pr.currency,
            pr.notes,
            pr.created_at,
            pr.paid_at,
            b.bundle_name,
            COUNT(DISTINCT pri.course_id) AS course_count
        FROM tbl_org_purchase_requests pr
        LEFT JOIN tbl_course_bundles b ON b.id = pr.bundle_id
        LEFT JOIN tbl_purchase_request_items pri ON pri.request_id = pr.id
        WHERE pr.org_code = '$safeOrg'
        GROUP BY pr.id, pr.request_code, pr.status, pr.final_price, pr.currency,
                 pr.notes, pr.created_at, pr.paid_at, b.bundle_name
        ORDER BY pr.created_at DESC
        LIMIT 100
    ");

    $history = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $history[] = [
                'id'           => (int)$r['id'],
                'request_code' => $r['request_code'],
                'status'       => $r['status'],
                'final_price'  => (float)$r['final_price'],
                'currency'     => $r['currency'] ?? 'TZS',
                'notes'        => $r['notes'],
                'bundle_name'  => $r['bundle_name'],
                'course_count' => (int)$r['course_count'],
                'created_at'   => $r['created_at'],
                'paid_at'      => $r['paid_at'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'history' => $history]);
    break;

/* ── Member progress across all licensed courses ────────────────────── */
case 'member_progress':
    $rows = $db->query("
        SELECT
            u.usr_code,
            u.first_name,
            u.last_name,
            u.email,
            om.org_role,
            COUNT(DISTINCT oca.course_id)          AS licensed_courses,
            COUNT(DISTINCT ce.course_id)           AS enrolled_courses,
            COALESCE(ROUND(AVG(ce.progress_pct), 1), 0) AS avg_progress,
            COUNT(DISTINCT CASE WHEN ce.status = 'completed' THEN ce.course_id END) AS completed_courses
        FROM tbl_org_members om
        JOIN tbl_users u ON u.usr_code = om.usr_code
        LEFT JOIN tbl_org_course_access oca
               ON oca.org_code = om.org_code AND oca.is_active = 1
        LEFT JOIN tbl_course_enrollments ce
               ON ce.user_id   = om.usr_code
              AND ce.course_id = oca.course_id
        WHERE om.org_code = '$safeOrg' AND om.status = 'active'
        GROUP BY u.usr_code, u.first_name, u.last_name, u.email, om.org_role
        ORDER BY avg_progress DESC
    ");

    $members = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $members[] = [
                'usr_code'         => $r['usr_code'],
                'first_name'       => $r['first_name'],
                'last_name'        => $r['last_name'],
                'email'            => $r['email'],
                'org_role'         => $r['org_role'],
                'licensed_courses' => (int)$r['licensed_courses'],
                'enrolled_courses' => (int)$r['enrolled_courses'],
                'completed_courses'=> (int)$r['completed_courses'],
                'avg_progress'     => (float)$r['avg_progress'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'members' => $members]);
    break;

/* ── Expiry calendar — licenses expiring in next 30/60/90 days ─────── */
case 'expiry_calendar':
    $window = (int)($_GET['days'] ?? $_POST['days'] ?? 90);
    if (!in_array($window, [30, 60, 90])) $window = 90;

    $rows = $db->query("
        SELECT
            c.id AS course_id,
            c.title,
            oca.seats_purchased,
            COUNT(sa.id) AS seats_used,
            oca.expires_at,
            DATEDIFF(oca.expires_at, CURDATE()) AS days_remaining
        FROM tbl_org_course_access oca
        JOIN tbl_courses c ON c.id = oca.course_id
        LEFT JOIN tbl_org_seat_assignments sa
               ON sa.org_code = oca.org_code AND sa.course_id = oca.course_id
        WHERE oca.org_code = '$safeOrg'
          AND oca.is_active = 1
          AND oca.expires_at IS NOT NULL
          AND oca.expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL $window DAY)
        GROUP BY c.id, c.title, oca.seats_purchased, oca.expires_at
        ORDER BY oca.expires_at ASC
    ");

    $expiring = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $dr = (int)$r['days_remaining'];
            $expiring[] = [
                'course_id'       => (int)$r['course_id'],
                'title'           => $r['title'],
                'seats_purchased' => (int)$r['seats_purchased'],
                'seats_used'      => (int)$r['seats_used'],
                'expires_at'      => $r['expires_at'],
                'days_remaining'  => $dr,
                'urgency'         => $dr <= 14 ? 'critical' : ($dr <= 30 ? 'warning' : 'info'),
            ];
        }
    }
    echo json_encode(['status' => 'success', 'expiring' => $expiring, 'window_days' => $window]);
    break;

/* ── Revenue by org — super admin aggregate ─────────────────────────── */
case 'revenue_by_org':
    if ($role !== 5) {
        echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
    }
    $rows = $db->query("
        SELECT
            o.org_name,
            o.org_code,
            o.status AS org_status,
            COALESCE(SUM(CASE WHEN pr.status IN ('paid','active') THEN pr.final_price ELSE 0 END), 0) AS total_revenue,
            COUNT(DISTINCT pr.id) AS total_requests,
            COUNT(DISTINCT CASE WHEN pr.status IN ('paid','active') THEN pr.id END) AS paid_requests,
            COUNT(DISTINCT CASE WHEN pr.status = 'pending' THEN pr.id END) AS pending_requests
        FROM tbl_organizations o
        LEFT JOIN tbl_org_purchase_requests pr ON pr.org_code = o.org_code
        WHERE o.deleted_at IS NULL
        GROUP BY o.org_code, o.org_name, o.status
        ORDER BY total_revenue DESC
    ");

    $orgs = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $orgs[] = [
                'org_name'        => $r['org_name'],
                'org_code'        => $r['org_code'],
                'org_status'      => $r['org_status'],
                'total_revenue'   => round((float)$r['total_revenue'], 2),
                'total_requests'  => (int)$r['total_requests'],
                'paid_requests'   => (int)$r['paid_requests'],
                'pending_requests'=> (int)$r['pending_requests'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'orgs' => $orgs]);
    break;

/* ── Purchase requests with status funnel ───────────────────────────── */
case 'purchase_requests_funnel':
    if ($role !== 5) {
        echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
    }
    $funnel = $db->query("
        SELECT status, COUNT(*) AS cnt, COALESCE(SUM(final_price), 0) AS total_amount
        FROM tbl_org_purchase_requests
        GROUP BY status
        ORDER BY FIELD(status,'pending','awaiting_payment','paid','active','rejected','cancelled')
    ");

    $statuses = [];
    if ($funnel) {
        while ($r = $funnel->fetch_assoc()) {
            $statuses[] = [
                'status'       => $r['status'],
                'count'        => (int)$r['cnt'],
                'total_amount' => round((float)$r['total_amount'], 2),
            ];
        }
    }

    // Recent requests table
    $recent = $db->query("
        SELECT
            pr.id, pr.request_code, pr.status, pr.final_price, pr.currency,
            pr.created_at, pr.paid_at,
            o.org_name,
            b.bundle_name
        FROM tbl_org_purchase_requests pr
        JOIN tbl_organizations o ON o.org_code = pr.org_code
        LEFT JOIN tbl_course_bundles b ON b.id = pr.bundle_id
        ORDER BY pr.created_at DESC
        LIMIT 50
    ");

    $requests = [];
    if ($recent) {
        while ($r = $recent->fetch_assoc()) {
            $requests[] = [
                'id'           => (int)$r['id'],
                'request_code' => $r['request_code'],
                'org_name'     => $r['org_name'],
                'status'       => $r['status'],
                'final_price'  => round((float)$r['final_price'], 2),
                'currency'     => $r['currency'] ?? 'TZS',
                'bundle_name'  => $r['bundle_name'],
                'created_at'   => $r['created_at'],
                'paid_at'      => $r['paid_at'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'funnel' => $statuses, 'requests' => $requests]);
    break;

default:
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
