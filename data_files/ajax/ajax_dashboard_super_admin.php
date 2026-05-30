<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? 0) != 5) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

/* ── Commercial stats ──────────────────────────────────────────────── */
case 'get_commercial_stats':
    $pending = (int)$db->query("
        SELECT COUNT(*) FROM tbl_org_purchase_requests WHERE status='pending'
    ")->fetch_row()[0];

    $awaiting = (int)$db->query("
        SELECT COUNT(*) FROM tbl_org_purchase_requests WHERE status='awaiting_payment'
    ")->fetch_row()[0];

    $active_licenses = (int)$db->query("
        SELECT COUNT(*) FROM tbl_org_course_access WHERE is_active=1
    ")->fetch_row()[0];

    $revenue_row = $db->query("
        SELECT COALESCE(SUM(final_price), 0) AS rev
        FROM tbl_org_purchase_requests
        WHERE status IN ('paid','active')
          AND MONTH(paid_at) = MONTH(NOW())
          AND YEAR(paid_at)  = YEAR(NOW())
    ")->fetch_assoc();
    $revenue_this_month = round((float)($revenue_row['rev'] ?? 0), 2);

    $total_orgs = (int)$db->query("
        SELECT COUNT(DISTINCT org_code) FROM tbl_organizations WHERE status='active' AND deleted_at IS NULL
    ")->fetch_row()[0];

    $bundle_sales = (int)$db->query("
        SELECT COUNT(*) FROM tbl_org_purchase_requests
        WHERE bundle_id IS NOT NULL AND status IN ('paid','active')
    ")->fetch_row()[0];

    // Top 5 courses by purchase request count this month
    $topRes = $db->query("
        SELECT c.title, COUNT(pri.id) AS req_count
        FROM tbl_org_purchase_requests pr
        JOIN tbl_purchase_request_items pri ON pri.request_id = pr.id
        JOIN tbl_courses c ON c.id = pri.course_id
        WHERE MONTH(pr.created_at) = MONTH(NOW())
          AND YEAR(pr.created_at)  = YEAR(NOW())
        GROUP BY pri.course_id, c.title
        ORDER BY req_count DESC
        LIMIT 5
    ");
    $top_courses = [];
    if ($topRes) {
        while ($row = $topRes->fetch_assoc()) {
            $top_courses[] = ['title' => $row['title'], 'count' => (int)$row['req_count']];
        }
    }

    echo json_encode([
        'status'             => 'success',
        'pending_requests'   => $pending,
        'awaiting_payment'   => $awaiting,
        'active_licenses'    => $active_licenses,
        'revenue_this_month' => $revenue_this_month,
        'total_orgs'         => $total_orgs,
        'bundle_sales'       => $bundle_sales,
        'top_courses'        => $top_courses,
    ]);
    break;

/* ── Revenue chart — last 12 months ────────────────────────────────── */
case 'get_revenue_chart':
    $rows = $db->query("
        SELECT
            DATE_FORMAT(paid_at, '%Y-%m') AS ym,
            DATE_FORMAT(paid_at, '%b %Y') AS label,
            COALESCE(SUM(final_price), 0) AS revenue
        FROM tbl_org_purchase_requests
        WHERE status IN ('paid','active')
          AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
        GROUP BY ym, label
        ORDER BY ym ASC
    ");

    $chart = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $chart[] = [
                'label'   => $r['label'],
                'revenue' => round((float)$r['revenue'], 2),
            ];
        }
    }
    echo json_encode(['status' => 'success', 'chart' => $chart]);
    break;

/* ── Org leaderboard — top 10 by total spend ────────────────────────── */
case 'get_org_leaderboard':
    $rows = $db->query("
        SELECT
            o.org_name,
            o.org_code,
            COALESCE(SUM(pr.final_price), 0) AS total_spend,
            COUNT(pr.id)                      AS total_requests
        FROM tbl_organizations o
        LEFT JOIN tbl_org_purchase_requests pr ON pr.org_code = o.org_code
            AND pr.status IN ('paid','active')
        WHERE o.deleted_at IS NULL
        GROUP BY o.org_code, o.org_name
        ORDER BY total_spend DESC
        LIMIT 10
    ");

    $orgs = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $orgs[] = [
                'org_name'       => $r['org_name'],
                'org_code'       => $r['org_code'],
                'total_spend'    => round((float)$r['total_spend'], 2),
                'total_requests' => (int)$r['total_requests'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'orgs' => $orgs]);
    break;

default:
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
