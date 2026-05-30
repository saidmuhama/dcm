<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
include('../config/url_crypt_config.php');
header('Content-Type: application/json');

$userRole = (int)($_SESSION['user_role'] ?? 0);
$me       = $_SESSION['usr_code'] ?? '';
$action   = $_POST['action'] ?? $_GET['action'] ?? '';

if (!in_array($userRole, [4, 5])) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}
if (!$me) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']); exit;
}

// ── Helper: generate unique request code PR-YYYY-NNN ──────────────────────────
function generate_request_code(mysqli $db): string {
    $year = date('Y');
    $row  = $db->query("SELECT COUNT(*) FROM tbl_org_purchase_requests WHERE YEAR(created_at)='$year'")->fetch_row();
    $seq  = ($row[0] ?? 0) + 1;
    return 'PR-' . $year . '-' . str_pad($seq, 3, '0', STR_PAD_LEFT);
}

// ── Helper: notify a list of users ───────────────────────────────────────────
function notify_users(mysqli $db, array $usrCodes, string $title, string $body, string $link = '', string $icon = 'bi-bell-fill', string $color = '#6366f1'): void {
    if (empty($usrCodes)) return;
    $stmt = $db->prepare(
        "INSERT INTO tbl_notifications (user_code, type, title, body, link, icon, color, is_read, created_at)
         VALUES (?, 'purchase_request', ?, ?, ?, ?, ?, 0, NOW())"
    );
    foreach ($usrCodes as $code) {
        $code = (string)$code;
        $stmt->bind_param('ssssss', $code, $title, $body, $link, $icon, $color);
        $stmt->execute();
    }
}

// ── Helper: get org for current org_admin ──────────────────────────────────
function getMyOrg(mysqli $db, string $usrCode): ?array {
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

// ── Helper: get all super admin usr_codes ─────────────────────────────────
function getSuperAdmins(mysqli $db): array {
    $r = $db->query("SELECT usr_code FROM tbl_all_users WHERE user_role = 5 AND user_status = 'Active'");
    return array_column($r->fetch_all(MYSQLI_ASSOC), 'usr_code');
}

// ── Helper: get org_admin usr_codes for an org ──────────────────────────────
function getOrgAdmins(mysqli $db, string $orgCode): array {
    $s = $db->prepare("SELECT usr_code FROM tbl_org_members WHERE org_code = ? AND org_role = 'admin' AND status = 'active'");
    $s->bind_param('s', $orgCode);
    $s->execute();
    return array_column($s->get_result()->fetch_all(MYSQLI_ASSOC), 'usr_code');
}

// ── Route by role ─────────────────────────────────────────────────────────
if ($userRole === 4) {
    // ORG ADMIN actions
    $myOrg = getMyOrg($db, $me);
    if (!$myOrg && $action !== 'list_requests') {
        echo json_encode(['status' => 'error', 'message' => 'No active organization found for your account']); exit;
    }
    $orgCode = $myOrg['org_code'] ?? '';

    switch ($action) {

    /* ── Submit new purchase request ───────────────────────────── */
    case 'submit_request':
        $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;

        $itemType       = $db->real_escape_string(trim($body['item_type']     ?? 'course')); // course | bundle | custom
        $courseId       = (int)($body['course_id']   ?? 0);
        $bundleId       = (int)($body['bundle_id']   ?? 0);
        $seatsRequested = max(1, (int)($body['seats_requested'] ?? 1));
        $expectedStart  = $db->real_escape_string(trim($body['expected_start'] ?? ''));
        $notes          = $db->real_escape_string(trim($body['notes']          ?? ''));
        $staffCount     = (int)($body['staff_count']    ?? $seatsRequested);
        $budget         = $db->real_escape_string(trim($body['budget']         ?? ''));
        $requirements   = $db->real_escape_string(trim($body['requirements']   ?? ''));

        if (!in_array($itemType, ['course', 'bundle', 'custom'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid item type']); exit;
        }
        if ($itemType === 'course'  && !$courseId) { echo json_encode(['status' => 'error', 'message' => 'Course is required']); exit; }
        if ($itemType === 'bundle'  && !$bundleId) { echo json_encode(['status' => 'error', 'message' => 'Bundle is required']); exit; }

        // Fetch original price
        $originalPrice = 0;
        $itemTitle     = '';
        if ($itemType === 'course') {
            $cs = $db->prepare("SELECT title, price FROM tbl_courses WHERE id = ? AND status = 'active' LIMIT 1");
            $cs->bind_param('i', $courseId);
            $cs->execute();
            $cr = $cs->get_result()->fetch_assoc();
            if (!$cr) { echo json_encode(['status' => 'error', 'message' => 'Course not found or inactive']); exit; }
            $originalPrice = (float)$cr['price'] * $seatsRequested;
            $itemTitle     = $cr['title'];
        } elseif ($itemType === 'bundle') {
            $bs = $db->prepare("SELECT bundle_name, org_price FROM tbl_course_bundles WHERE id = ? LIMIT 1");
            $bs->bind_param('i', $bundleId);
            $bs->execute();
            $br = $bs->get_result()->fetch_assoc();
            if (!$br) { echo json_encode(['status' => 'error', 'message' => 'Bundle not found']); exit; }
            $originalPrice = (float)$br['org_price'] * $seatsRequested;
            $itemTitle     = $br['bundle_name'];
        } else {
            $itemTitle = 'Custom Quote Request';
        }

        $requestCode = generate_request_code($db);
        $expStartSql = $expectedStart ? "'$expectedStart'" : 'NULL';
        $cIdSql      = $courseId  ? $courseId  : 'NULL';
        $bIdSql      = $bundleId  ? $bundleId  : 'NULL';
        $budgetSql   = $budget    ? "'$budget'" : 'NULL';

        $db->begin_transaction();
        try {
            $ins = $db->prepare("
                INSERT INTO tbl_org_purchase_requests
                    (request_code, org_code, requested_by, item_type, course_id, bundle_id,
                     seats_requested, original_price, final_price, status,
                     expected_start_date, notes, staff_count, budget, requirements, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, NOW())
            ");
            $ins->bind_param(
                'ssssiiddsssss',
                $requestCode, $orgCode, $me, $itemType,
                $courseId, $bundleId,
                $seatsRequested, $originalPrice, $originalPrice,
                $expectedStart, $notes,
                $staffCount, $budget, $requirements
            );
            $ins->execute();
            $requestId = $db->insert_id;

            // History entry
            $hist = $db->prepare("
                INSERT INTO tbl_org_request_history (request_id, actor_usr_code, action, notes, created_at)
                VALUES (?, ?, 'submitted', 'Request submitted by org admin', NOW())
            ");
            $hist->bind_param('is', $requestId, $me);
            $hist->execute();

            $db->commit();

            // Notify super admins
            $superAdmins = getSuperAdmins($db);
            $orgName     = $myOrg['org_name'] ?? 'An organization';
            notify_users(
                $db, $superAdmins,
                'New Purchase Request',
                "$orgName submitted a new purchase request ($requestCode) for \"$itemTitle\" — $seatsRequested seat(s).",
                '?view=admin_purchase_requests',
                'bi-cart-fill', '#6366f1'
            );

            echo json_encode([
                'status'       => 'success',
                'message'      => 'Purchase request submitted successfully',
                'request_id'   => $requestId,
                'request_code' => $requestCode,
            ]);
        } catch (Throwable $e) {
            $db->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit request: ' . $e->getMessage()]);
        }
        break;

    /* ── List org's own requests ───────────────────────────────── */
    case 'list_requests':
        if (!$myOrg) { echo json_encode(['status' => 'success', 'data' => [], 'stats' => []]); exit; }
        $statusFilter = $db->real_escape_string($_GET['status'] ?? $_POST['status'] ?? '');
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $per   = 20;
        $offset = ($page - 1) * $per;

        $where = "WHERE r.org_code = '$orgCode'";
        if ($statusFilter) $where .= " AND r.status = '$statusFilter'";

        $total = $db->query("SELECT COUNT(*) FROM tbl_org_purchase_requests r $where")->fetch_row()[0];

        $rows = $db->query("
            SELECT r.*,
                   c.title AS course_title,
                   b.bundle_name,
                   d.name AS discount_name, d.discount_percent,
                   CONCAT(u.first_name,' ',u.last_name) AS requested_by_name
            FROM tbl_org_purchase_requests r
            LEFT JOIN tbl_courses c ON c.id = r.course_id
            LEFT JOIN tbl_course_bundles b ON b.id = r.bundle_id
            LEFT JOIN tbl_discounts d ON d.id = r.discount_id
            LEFT JOIN tbl_all_users u ON u.usr_code = r.requested_by
            $where
            ORDER BY r.created_at DESC
            LIMIT $per OFFSET $offset
        ")->fetch_all(MYSQLI_ASSOC);

        // Stats
        $statsQ = $db->query("
            SELECT status, COUNT(*) AS cnt, COALESCE(SUM(final_price),0) AS total_amt
            FROM tbl_org_purchase_requests WHERE org_code = '$orgCode' GROUP BY status
        ")->fetch_all(MYSQLI_ASSOC);
        $stats = ['total' => 0, 'pending' => 0, 'active' => 0, 'total_spent' => 0];
        foreach ($statsQ as $s) {
            $stats['total']  += (int)$s['cnt'];
            $stats[$s['status']] = (int)$s['cnt'];
            if ($s['status'] === 'active') $stats['total_spent'] += (float)$s['total_amt'];
        }

        echo json_encode(['status' => 'success', 'data' => $rows, 'total' => (int)$total, 'page' => $page, 'per' => $per, 'stats' => $stats]);
        break;

    /* ── Get single request detail ─────────────────────────────── */
    case 'get_request':
        $reqId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$reqId) { echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']); exit; }

        $s = $db->prepare("
            SELECT r.*,
                   c.title AS course_title,
                   b.bundle_name,
                   d.name AS discount_name, d.discount_percent,
                   CONCAT(u.first_name,' ',u.last_name) AS requested_by_name
            FROM tbl_org_purchase_requests r
            LEFT JOIN tbl_courses c ON c.id = r.course_id
            LEFT JOIN tbl_course_bundles b ON b.id = r.bundle_id
            LEFT JOIN tbl_discounts d ON d.id = r.discount_id
            LEFT JOIN tbl_all_users u ON u.usr_code = r.requested_by
            WHERE r.id = ? AND r.org_code = ?
            LIMIT 1
        ");
        $s->bind_param('is', $reqId, $orgCode);
        $s->execute();
        $req = $s->get_result()->fetch_assoc();
        if (!$req) { echo json_encode(['status' => 'error', 'message' => 'Request not found']); exit; }

        // History
        $hs = $db->prepare("
            SELECT h.*, CONCAT(u.first_name,' ',u.last_name) AS actor_name
            FROM tbl_org_request_history h
            LEFT JOIN tbl_all_users u ON u.usr_code = h.actor_usr_code
            WHERE h.request_id = ? ORDER BY h.created_at ASC
        ");
        $hs->bind_param('i', $reqId);
        $hs->execute();
        $history = $hs->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $req, 'history' => $history]);
        break;

    /* ── Cancel a pending request ──────────────────────────────── */
    case 'cancel_request':
        $body  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $reqId = (int)($body['id'] ?? 0);
        if (!$reqId) { echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']); exit; }

        $s = $db->prepare("SELECT id, status FROM tbl_org_purchase_requests WHERE id = ? AND org_code = ? LIMIT 1");
        $s->bind_param('is', $reqId, $orgCode);
        $s->execute();
        $req = $s->get_result()->fetch_assoc();
        if (!$req)                       { echo json_encode(['status' => 'error', 'message' => 'Request not found']); exit; }
        if ($req['status'] !== 'pending') { echo json_encode(['status' => 'error', 'message' => 'Only pending requests can be cancelled']); exit; }

        $db->prepare("UPDATE tbl_org_purchase_requests SET status = 'cancelled', updated_at = NOW() WHERE id = ?")->execute() || null;
        $upd = $db->prepare("UPDATE tbl_org_purchase_requests SET status = 'cancelled', updated_at = NOW() WHERE id = ?");
        $upd->bind_param('i', $reqId);
        $upd->execute();

        $hist = $db->prepare("INSERT INTO tbl_org_request_history (request_id, actor_usr_code, action, notes, created_at) VALUES (?, ?, 'cancelled', 'Cancelled by org admin', NOW())");
        $hist->bind_param('is', $reqId, $me);
        $hist->execute();

        echo json_encode(['status' => 'success', 'message' => 'Request cancelled']);
        break;

    /* ── Load courses/bundles for dropdown ────────────────────── */
    case 'list_items':
        $courses = $db->query("SELECT id, title, price FROM tbl_courses WHERE status = 'active' AND is_approved = 'approved' AND deleted_at IS NULL ORDER BY title ASC")->fetch_all(MYSQLI_ASSOC);
        $bundles = $db->query("SELECT id, bundle_name, org_price FROM tbl_course_bundles ORDER BY bundle_name ASC")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'courses' => $courses, 'bundles' => $bundles]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    }

} else {
    // SUPER ADMIN actions (role=5)

    switch ($action) {

    /* ── List all requests (paginated, filterable) ─────────────── */
    case 'list_all_requests':
        $statusFilter = $db->real_escape_string($_GET['status'] ?? $_POST['status'] ?? '');
        $orgFilter    = $db->real_escape_string($_GET['org']    ?? $_POST['org']    ?? '');
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $per   = 25;
        $offset = ($page - 1) * $per;

        $where = "WHERE 1=1";
        if ($statusFilter) $where .= " AND r.status = '$statusFilter'";
        if ($orgFilter)    $where .= " AND r.org_code = '$orgFilter'";

        $total = $db->query("SELECT COUNT(*) FROM tbl_org_purchase_requests r $where")->fetch_row()[0];

        $rows = $db->query("
            SELECT r.*,
                   o.org_name,
                   c.title AS course_title,
                   b.bundle_name,
                   d.name AS discount_name, d.discount_percent,
                   CONCAT(u.first_name,' ',u.last_name) AS requested_by_name,
                   CONCAT(rev.first_name,' ',rev.last_name) AS reviewed_by_name
            FROM tbl_org_purchase_requests r
            LEFT JOIN tbl_organizations o ON o.org_code = r.org_code
            LEFT JOIN tbl_courses c ON c.id = r.course_id
            LEFT JOIN tbl_course_bundles b ON b.id = r.bundle_id
            LEFT JOIN tbl_discounts d ON d.id = r.discount_id
            LEFT JOIN tbl_all_users u ON u.usr_code = r.requested_by
            LEFT JOIN tbl_all_users rev ON rev.usr_code = r.reviewed_by
            $where
            ORDER BY FIELD(r.status,'pending','reviewed','awaiting_payment','paid','active','rejected','cancelled'), r.created_at DESC
            LIMIT $per OFFSET $offset
        ")->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $rows, 'total' => (int)$total, 'page' => $page, 'per' => $per]);
        break;

    /* ── Review a request (set status=reviewed, apply discount) ── */
    case 'review_request':
        $body       = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $reqId      = (int)($body['id']          ?? 0);
        $discountId = (int)($body['discount_id'] ?? 0);
        $finalPrice = isset($body['final_price']) ? (float)$body['final_price'] : null;
        $remarks    = $db->real_escape_string(trim($body['admin_remarks'] ?? ''));

        if (!$reqId) { echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']); exit; }

        $s = $db->prepare("SELECT * FROM tbl_org_purchase_requests WHERE id = ? LIMIT 1");
        $s->bind_param('i', $reqId);
        $s->execute();
        $req = $s->get_result()->fetch_assoc();
        if (!$req) { echo json_encode(['status' => 'error', 'message' => 'Request not found']); exit; }
        if (!in_array($req['status'], ['pending', 'reviewed'])) {
            echo json_encode(['status' => 'error', 'message' => 'Request cannot be reviewed in its current state']); exit;
        }

        // Calculate final price if not explicitly provided
        if ($finalPrice === null) {
            $finalPrice = (float)$req['original_price'];
            if ($discountId) {
                $ds = $db->prepare("SELECT discount_percent FROM tbl_discounts WHERE id = ? AND is_active = 1 LIMIT 1");
                $ds->bind_param('i', $discountId);
                $ds->execute();
                $dr = $ds->get_result()->fetch_assoc();
                if ($dr) $finalPrice = $finalPrice * (1 - $dr['discount_percent'] / 100);
            }
        }

        $discSql = $discountId ? $discountId : 'NULL';
        $upd = $db->prepare("
            UPDATE tbl_org_purchase_requests
            SET status = 'reviewed', discount_id = ?, final_price = ?, admin_remarks = ?, reviewed_by = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $upd->bind_param('idssi', $discountId, $finalPrice, $remarks, $me, $reqId);
        $upd->execute();

        $hist = $db->prepare("INSERT INTO tbl_org_request_history (request_id, actor_usr_code, action, notes, created_at) VALUES (?, ?, 'reviewed', ?, NOW())");
        $histNote = "Reviewed by admin. Final price set to $finalPrice. " . ($remarks ? "Remarks: $remarks" : '');
        $hist->bind_param('iss', $reqId, $me, $histNote);
        $hist->execute();

        echo json_encode(['status' => 'success', 'message' => 'Request reviewed successfully', 'final_price' => round($finalPrice, 2)]);
        break;

    /* ── Set awaiting payment ──────────────────────────────────── */
    case 'set_awaiting_payment':
        $body  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $reqId = (int)($body['id'] ?? 0);
        if (!$reqId) { echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']); exit; }

        $s = $db->prepare("SELECT * FROM tbl_org_purchase_requests WHERE id = ? LIMIT 1");
        $s->bind_param('i', $reqId);
        $s->execute();
        $req = $s->get_result()->fetch_assoc();
        if (!$req)                          { echo json_encode(['status' => 'error', 'message' => 'Request not found']); exit; }
        if ($req['status'] !== 'reviewed')   { echo json_encode(['status' => 'error', 'message' => 'Request must be reviewed first']); exit; }

        $upd = $db->prepare("UPDATE tbl_org_purchase_requests SET status = 'awaiting_payment', updated_at = NOW() WHERE id = ?");
        $upd->bind_param('i', $reqId);
        $upd->execute();

        $hist = $db->prepare("INSERT INTO tbl_org_request_history (request_id, actor_usr_code, action, notes, created_at) VALUES (?, ?, 'awaiting_payment', 'Awaiting payment notification sent to organization', NOW())");
        $hist->bind_param('is', $reqId, $me);
        $hist->execute();

        // Notify org admins
        $orgAdmins = getOrgAdmins($db, $req['org_code']);
        notify_users(
            $db, $orgAdmins,
            'Payment Required',
            "Your purchase request ({$req['request_code']}) has been reviewed. Final amount: " . number_format($req['final_price'], 2) . ". Please proceed with payment.",
            '?view=org_purchase_requests',
            'bi-credit-card-fill', '#f59e0b'
        );

        echo json_encode(['status' => 'success', 'message' => 'Request moved to awaiting payment']);
        break;

    /* ── Mark as paid and activate ────────────────────────────── */
    case 'mark_paid':
        $body  = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $reqId = (int)($body['id'] ?? 0);
        $payRef = $db->real_escape_string(trim($body['payment_ref'] ?? ''));
        if (!$reqId) { echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']); exit; }

        $s = $db->prepare("SELECT * FROM tbl_org_purchase_requests WHERE id = ? LIMIT 1");
        $s->bind_param('i', $reqId);
        $s->execute();
        $req = $s->get_result()->fetch_assoc();
        if (!$req) { echo json_encode(['status' => 'error', 'message' => 'Request not found']); exit; }
        if (!in_array($req['status'], ['awaiting_payment', 'reviewed'])) {
            echo json_encode(['status' => 'error', 'message' => 'Request is not in a payable state']); exit;
        }

        $db->begin_transaction();
        try {
            // Mark paid then active
            $upd = $db->prepare("UPDATE tbl_org_purchase_requests SET status = 'active', payment_ref = ?, paid_at = NOW(), updated_at = NOW() WHERE id = ?");
            $upd->bind_param('si', $payRef, $reqId);
            $upd->execute();

            // Grant/update course access
            if ($req['item_type'] === 'course' && $req['course_id']) {
                $cId   = (int)$req['course_id'];
                $seats = (int)$req['seats_requested'];
                $acc = $db->prepare("
                    INSERT INTO tbl_org_course_access (org_code, course_id, is_active, granted_by, seats_purchased, purchase_request_id, granted_at)
                    VALUES (?, ?, 1, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE is_active = 1, seats_purchased = seats_purchased + ?, purchase_request_id = ?, granted_by = ?, granted_at = NOW()
                ");
                $acc->bind_param('sisiiiis', $req['org_code'], $cId, $me, $seats, $reqId, $seats, $reqId, $me);
                $acc->execute();
            } elseif ($req['item_type'] === 'bundle' && $req['bundle_id']) {
                // Grant access to all courses in the bundle
                $bId   = (int)$req['bundle_id'];
                $seats = (int)$req['seats_requested'];
                $bCourses = $db->query("SELECT course_id FROM tbl_bundle_courses WHERE bundle_id = $bId")->fetch_all(MYSQLI_ASSOC);
                foreach ($bCourses as $bc) {
                    $cId = (int)$bc['course_id'];
                    $acc = $db->prepare("
                        INSERT INTO tbl_org_course_access (org_code, course_id, is_active, granted_by, seats_purchased, purchase_request_id, granted_at)
                        VALUES (?, ?, 1, ?, ?, ?, NOW())
                        ON DUPLICATE KEY UPDATE is_active = 1, seats_purchased = seats_purchased + ?, purchase_request_id = ?, granted_by = ?, granted_at = NOW()
                    ");
                    $acc->bind_param('sisiiiis', $req['org_code'], $cId, $me, $seats, $reqId, $seats, $reqId, $me);
                    $acc->execute();
                }
            }

            // History
            $hist = $db->prepare("INSERT INTO tbl_org_request_history (request_id, actor_usr_code, action, notes, created_at) VALUES (?, ?, 'paid_and_activated', ?, NOW())");
            $histNote = "Payment confirmed and access activated." . ($payRef ? " Payment ref: $payRef" : '');
            $hist->bind_param('iss', $reqId, $me, $histNote);
            $hist->execute();

            $db->commit();

            // Notify org admins
            $orgAdmins = getOrgAdmins($db, $req['org_code']);
            notify_users(
                $db, $orgAdmins,
                'Course Access Activated',
                "Your purchase request ({$req['request_code']}) has been confirmed. Course access is now active for your organization.",
                '?view=org_purchase_requests',
                'bi-check-circle-fill', '#059669'
            );

            echo json_encode(['status' => 'success', 'message' => 'Payment confirmed and access activated']);
        } catch (Throwable $e) {
            $db->rollback();
            echo json_encode(['status' => 'error', 'message' => 'Failed: ' . $e->getMessage()]);
        }
        break;

    /* ── Reject a request ─────────────────────────────────────── */
    case 'reject_request':
        $body   = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        $reqId  = (int)($body['id'] ?? 0);
        $reason = $db->real_escape_string(trim($body['reason'] ?? ''));
        if (!$reqId)   { echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']); exit; }
        if (!$reason)  { echo json_encode(['status' => 'error', 'message' => 'Rejection reason is required']); exit; }

        $s = $db->prepare("SELECT * FROM tbl_org_purchase_requests WHERE id = ? LIMIT 1");
        $s->bind_param('i', $reqId);
        $s->execute();
        $req = $s->get_result()->fetch_assoc();
        if (!$req) { echo json_encode(['status' => 'error', 'message' => 'Request not found']); exit; }
        if (in_array($req['status'], ['active', 'rejected', 'cancelled'])) {
            echo json_encode(['status' => 'error', 'message' => 'Request cannot be rejected in its current state']); exit;
        }

        $upd = $db->prepare("UPDATE tbl_org_purchase_requests SET status = 'rejected', admin_remarks = ?, reviewed_by = ?, updated_at = NOW() WHERE id = ?");
        $upd->bind_param('ssi', $reason, $me, $reqId);
        $upd->execute();

        $hist = $db->prepare("INSERT INTO tbl_org_request_history (request_id, actor_usr_code, action, notes, created_at) VALUES (?, ?, 'rejected', ?, NOW())");
        $hist->bind_param('iss', $reqId, $me, $reason);
        $hist->execute();

        // Notify org admins
        $orgAdmins = getOrgAdmins($db, $req['org_code']);
        notify_users(
            $db, $orgAdmins,
            'Purchase Request Rejected',
            "Your purchase request ({$req['request_code']}) has been rejected. Reason: $reason",
            '?view=org_purchase_requests',
            'bi-x-circle-fill', '#dc2626'
        );

        echo json_encode(['status' => 'success', 'message' => 'Request rejected']);
        break;

    /* ── Stats for dashboard widget ───────────────────────────── */
    case 'get_stats':
        $pending  = $db->query("SELECT COUNT(*) FROM tbl_org_purchase_requests WHERE status = 'pending'")->fetch_row()[0];
        $reviewed = $db->query("SELECT COUNT(*) FROM tbl_org_purchase_requests WHERE status = 'reviewed'")->fetch_row()[0];
        $awaiting = $db->query("SELECT COUNT(*) FROM tbl_org_purchase_requests WHERE status = 'awaiting_payment'")->fetch_row()[0];
        $activeM  = $db->query("SELECT COUNT(*) FROM tbl_org_purchase_requests WHERE status = 'active' AND MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW())")->fetch_row()[0];
        $revenueM = $db->query("SELECT COALESCE(SUM(final_price),0) FROM tbl_org_purchase_requests WHERE status = 'active' AND MONTH(paid_at) = MONTH(NOW()) AND YEAR(paid_at) = YEAR(NOW())")->fetch_row()[0];

        echo json_encode([
            'status'           => 'success',
            'pending'          => (int)$pending,
            'reviewed'         => (int)$reviewed,
            'awaiting_payment' => (int)$awaiting,
            'active_this_month'=> (int)$activeM,
            'revenue_this_month'=> (float)$revenueM,
        ]);
        break;

    /* ── Get single request with full history ─────────────────── */
    case 'get_request':
        $reqId = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$reqId) { echo json_encode(['status' => 'error', 'message' => 'Invalid request ID']); exit; }

        $s = $db->prepare("
            SELECT r.*,
                   o.org_name,
                   c.title AS course_title,
                   b.bundle_name,
                   d.name AS discount_name, d.discount_percent,
                   CONCAT(u.first_name,' ',u.last_name) AS requested_by_name,
                   CONCAT(rev.first_name,' ',rev.last_name) AS reviewed_by_name
            FROM tbl_org_purchase_requests r
            LEFT JOIN tbl_organizations o ON o.org_code = r.org_code
            LEFT JOIN tbl_courses c ON c.id = r.course_id
            LEFT JOIN tbl_course_bundles b ON b.id = r.bundle_id
            LEFT JOIN tbl_discounts d ON d.id = r.discount_id
            LEFT JOIN tbl_all_users u ON u.usr_code = r.requested_by
            LEFT JOIN tbl_all_users rev ON rev.usr_code = r.reviewed_by
            WHERE r.id = ?
            LIMIT 1
        ");
        $s->bind_param('i', $reqId);
        $s->execute();
        $req = $s->get_result()->fetch_assoc();
        if (!$req) { echo json_encode(['status' => 'error', 'message' => 'Request not found']); exit; }

        // Full history with actor names
        $hs = $db->prepare("
            SELECT h.*, CONCAT(u.first_name,' ',u.last_name) AS actor_name, u.user_role AS actor_role
            FROM tbl_org_request_history h
            LEFT JOIN tbl_all_users u ON u.usr_code = h.actor_usr_code
            WHERE h.request_id = ? ORDER BY h.created_at ASC
        ");
        $hs->bind_param('i', $reqId);
        $hs->execute();
        $history = $hs->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode(['status' => 'success', 'data' => $req, 'history' => $history]);
        break;

    /* ── List discounts ───────────────────────────────────────── */
    case 'list_discounts':
        $rows = $db->query("SELECT id, name, discount_percent, description FROM tbl_discounts WHERE is_active = 1 ORDER BY discount_percent ASC")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'discounts' => $rows]);
        break;

    /* ── List all orgs (for filter dropdown) ──────────────────── */
    case 'list_orgs':
        $rows = $db->query("SELECT org_code, org_name FROM tbl_organizations WHERE deleted_at IS NULL ORDER BY org_name ASC")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['status' => 'success', 'orgs' => $rows]);
        break;

    default:
        echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
    }
}
