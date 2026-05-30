<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? 0) != 5) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function jsonErr(string $msg): void {
    echo json_encode(['status' => 'error', 'message' => $msg]); exit;
}
function jsonOk($data = [], string $msg = 'Success'): void {
    echo json_encode(['status' => 'success', 'message' => $msg, 'data' => $data]); exit;
}

switch ($action) {

// ── GET PRICING ───────────────────────────────────────────────────────────────
case 'get_pricing': {
    $courseId = (int)($_GET['course_id'] ?? 0);
    if (!$courseId) jsonErr('course_id required');

    $s = $db->prepare("
        SELECT id, course_title, price, discount, org_price, org_discount,
               min_seats, max_seats, pricing_notes, status
        FROM tbl_courses WHERE id = ? LIMIT 1
    ");
    $s->bind_param('i', $courseId);
    $s->execute();
    $course = $s->get_result()->fetch_assoc();
    if (!$course) jsonErr('Course not found');

    $t = $db->prepare("
        SELECT id, tier_label, min_seats, max_seats, price_per_seat, valid_from, valid_to
        FROM tbl_course_pricing_tiers
        WHERE course_id = ? AND status = 'active'
        ORDER BY min_seats ASC
    ");
    $t->bind_param('i', $courseId);
    $t->execute();
    $tiers = $t->get_result()->fetch_all(MYSQLI_ASSOC);

    $d = $db->prepare("
        SELECT d.id, d.code, d.name, d.discount_type, d.discount_value,
               d.valid_from, d.valid_to, d.status,
               COUNT(DISTINCT u.id) AS usage_count
        FROM tbl_discounts d
        LEFT JOIN tbl_discount_usage u ON u.discount_id = d.id
        WHERE d.status = 'active'
          AND (d.applies_to = 'all'
               OR (d.applies_to = 'course' AND d.target_id = ?)
               OR d.applies_to = 'category')
        GROUP BY d.id
        ORDER BY d.created_at DESC
        LIMIT 20
    ");
    $d->bind_param('i', $courseId);
    $d->execute();
    $discounts = $d->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status'    => 'success',
        'message'   => 'OK',
        'course'    => $course,
        'tiers'     => $tiers,
        'discounts' => $discounts,
    ]);
    exit;
}

// ── SAVE INDIVIDUAL PRICE ─────────────────────────────────────────────────────
case 'save_individual_price': {
    $input       = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $courseId    = (int)($input['course_id']      ?? 0);
    $price       = (float)($input['price']        ?? 0);
    $orgPrice    = (float)($input['org_price']    ?? 0);
    $orgDiscount = (float)($input['org_discount'] ?? 0);
    $minSeats    = (int)($input['min_seats']      ?? 0);
    $maxSeats    = (int)($input['max_seats']      ?? 0);
    $notes       = trim($input['pricing_notes']   ?? '');

    if (!$courseId) jsonErr('course_id required');
    if ($price < 0) jsonErr('Price cannot be negative');
    if ($orgDiscount < 0 || $orgDiscount > 100) jsonErr('Org discount must be between 0 and 100');

    $s = $db->prepare("
        UPDATE tbl_courses
        SET price = ?, org_price = ?, org_discount = ?,
            min_seats = ?, max_seats = ?, pricing_notes = ?
        WHERE id = ?
    ");
    $s->bind_param('dddiiisi', $price, $orgPrice, $orgDiscount, $minSeats, $maxSeats, $notes, $courseId);
    if (!$s->execute()) jsonErr('Update failed: ' . $s->error);
    jsonOk([], 'Pricing saved successfully');
}

// ── SAVE TIERS ────────────────────────────────────────────────────────────────
case 'save_tiers': {
    $input    = json_decode(file_get_contents('php://input'), true) ?? [];
    $courseId = (int)($input['course_id'] ?? 0);
    $tiers    = $input['tiers']           ?? [];

    if (!$courseId) jsonErr('course_id required');
    if (!is_array($tiers)) jsonErr('tiers must be an array');

    $del = $db->prepare("DELETE FROM tbl_course_pricing_tiers WHERE course_id = ?");
    $del->bind_param('i', $courseId);
    if (!$del->execute()) jsonErr('Failed to clear existing tiers');

    if (!empty($tiers)) {
        $ins = $db->prepare("
            INSERT INTO tbl_course_pricing_tiers
                (course_id, tier_label, min_seats, max_seats, price_per_seat, valid_from, valid_to, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        foreach ($tiers as $tier) {
            $label     = trim($tier['tier_label']        ?? '');
            $minS      = (int)($tier['min_seats']        ?? 0);
            $maxS      = (int)($tier['max_seats']        ?? 0);
            $pps       = (float)($tier['price_per_seat'] ?? 0);
            $validFrom = !empty($tier['valid_from']) ? $tier['valid_from'] : null;
            $validTo   = !empty($tier['valid_to'])   ? $tier['valid_to']   : null;
            if ($minS < 1 || $pps < 0) continue;
            $ins->bind_param('issidss', $courseId, $label, $minS, $maxS, $pps, $validFrom, $validTo);
            $ins->execute();
        }
    }

    jsonOk([], 'Tiers saved successfully');
}

// ── LIST BUNDLES ──────────────────────────────────────────────────────────────
case 'list_bundles': {
    $res = $db->query("
        SELECT b.id, b.bundle_name, b.bundle_type, b.description,
               b.target_level, b.org_price, b.individual_price,
               b.status, b.created_at,
               COUNT(bc.course_id) AS course_count
        FROM tbl_course_bundles b
        LEFT JOIN tbl_bundle_courses bc ON bc.bundle_id = b.id
        WHERE b.status != 'deleted'
        GROUP BY b.id
        ORDER BY b.created_at DESC
    ");
    $bundles = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    jsonOk($bundles);
}

// ── GET BUNDLE ────────────────────────────────────────────────────────────────
case 'get_bundle': {
    $bundleId = (int)($_GET['id'] ?? 0);
    if (!$bundleId) jsonErr('id required');

    $s = $db->prepare("SELECT * FROM tbl_course_bundles WHERE id = ? LIMIT 1");
    $s->bind_param('i', $bundleId);
    $s->execute();
    $bundle = $s->get_result()->fetch_assoc();
    if (!$bundle) jsonErr('Bundle not found');

    $bc = $db->prepare("
        SELECT bc.course_id, c.course_title
        FROM tbl_bundle_courses bc
        JOIN tbl_courses c ON c.id = bc.course_id
        WHERE bc.bundle_id = ?
    ");
    $bc->bind_param('i', $bundleId);
    $bc->execute();
    $bundle['courses'] = $bc->get_result()->fetch_all(MYSQLI_ASSOC);

    jsonOk($bundle);
}

// ── SAVE BUNDLE ───────────────────────────────────────────────────────────────
case 'save_bundle': {
    $input     = json_decode(file_get_contents('php://input'), true) ?? [];
    $bundleId  = (int)($input['id']                 ?? 0);
    $name      = trim($input['bundle_name']          ?? '');
    $type      = trim($input['bundle_type']          ?? 'subject');
    $desc      = trim($input['description']          ?? '');
    $targetLvl = trim($input['target_level']         ?? '');
    $orgPrice  = (float)($input['org_price']         ?? 0);
    $indPrice  = (float)($input['individual_price']  ?? 0);
    $courses   = $input['course_ids']                ?? [];

    if (!$name) jsonErr('Bundle name is required');
    if (!in_array($type, ['subject', 'institutional', 'promotional'])) $type = 'subject';

    if ($bundleId) {
        $s = $db->prepare("
            UPDATE tbl_course_bundles
            SET bundle_name = ?, bundle_type = ?, description = ?,
                target_level = ?, org_price = ?, individual_price = ?
            WHERE id = ?
        ");
        $s->bind_param('ssssddi', $name, $type, $desc, $targetLvl, $orgPrice, $indPrice, $bundleId); // s=4 d=2 i=1 => ssssddi
        if (!$s->execute()) jsonErr('Update failed: ' . $s->error);
    } else {
        $s = $db->prepare("
            INSERT INTO tbl_course_bundles
                (bundle_name, bundle_type, description, target_level,
                 org_price, individual_price, status)
            VALUES (?, ?, ?, ?, ?, ?, 'active')
        ");
        $s->bind_param('ssssdd', $name, $type, $desc, $targetLvl, $orgPrice, $indPrice);
        if (!$s->execute()) jsonErr('Insert failed: ' . $s->error);
        $bundleId = (int)$s->insert_id;
    }

    $dc = $db->prepare("DELETE FROM tbl_bundle_courses WHERE bundle_id = ?");
    $dc->bind_param('i', $bundleId);
    $dc->execute();

    if (!empty($courses)) {
        $ic = $db->prepare("INSERT IGNORE INTO tbl_bundle_courses (bundle_id, course_id) VALUES (?, ?)");
        foreach ($courses as $cid) {
            $cid = (int)$cid;
            if ($cid > 0) {
                $ic->bind_param('ii', $bundleId, $cid);
                $ic->execute();
            }
        }
    }

    jsonOk(['id' => $bundleId], 'Bundle saved');
}

// ── DELETE BUNDLE (soft) ──────────────────────────────────────────────────────
case 'delete_bundle': {
    $input    = json_decode(file_get_contents('php://input'), true) ?? [];
    $bundleId = (int)($input['id'] ?? $_GET['id'] ?? 0);
    if (!$bundleId) jsonErr('Bundle id required');

    $s = $db->prepare("UPDATE tbl_course_bundles SET status = 'inactive' WHERE id = ?");
    $s->bind_param('i', $bundleId);
    if (!$s->execute()) jsonErr('Delete failed');
    jsonOk([], 'Bundle removed');
}

// ── LIST DISCOUNTS ────────────────────────────────────────────────────────────
case 'list_discounts': {
    $res = $db->query("
        SELECT d.id, d.code, d.name, d.discount_type, d.discount_value,
               d.applies_to, d.target_id, d.valid_from, d.valid_to,
               d.usage_limit, d.status, d.created_at,
               COUNT(DISTINCT u.id) AS usage_count
        FROM tbl_discounts d
        LEFT JOIN tbl_discount_usage u ON u.discount_id = d.id
        GROUP BY d.id
        ORDER BY d.created_at DESC
    ");
    $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    jsonOk($rows);
}

// ── SAVE DISCOUNT ─────────────────────────────────────────────────────────────
case 'save_discount': {
    $input     = json_decode(file_get_contents('php://input'), true) ?? [];
    $discId    = (int)($input['id']              ?? 0);
    $code      = strtoupper(trim($input['code']  ?? ''));
    $dname     = trim($input['name']             ?? '');
    $dtype     = trim($input['discount_type']    ?? 'percentage');
    $dvalue    = (float)($input['discount_value'] ?? 0);
    $appliesTo = trim($input['applies_to']        ?? 'all');
    $targetId  = (int)($input['target_id']        ?? 0);
    $validFrom = !empty($input['valid_from']) ? $input['valid_from'] : null;
    $validTo   = !empty($input['valid_to'])   ? $input['valid_to']   : null;
    $usageLim  = (int)($input['usage_limit']      ?? 0);

    if (!$code || !$dname) jsonErr('Code and name are required');
    if (!in_array($dtype, ['percentage', 'fixed'])) $dtype = 'percentage';
    if ($dvalue <= 0) jsonErr('Discount value must be greater than 0');
    if ($dtype === 'percentage' && $dvalue > 100) jsonErr('Percentage cannot exceed 100');

    if ($discId) {
        $s = $db->prepare("
            UPDATE tbl_discounts
            SET code = ?, name = ?, discount_type = ?, discount_value = ?,
                applies_to = ?, target_id = ?, valid_from = ?, valid_to = ?, usage_limit = ?
            WHERE id = ?
        ");
        $s->bind_param('sssdsissii', $code, $dname, $dtype, $dvalue, $appliesTo, $targetId, $validFrom, $validTo, $usageLim, $discId);
        if (!$s->execute()) jsonErr('Update failed: ' . $s->error);
        jsonOk(['id' => $discId], 'Discount updated');
    } else {
        $chk = $db->prepare("SELECT id FROM tbl_discounts WHERE code = ? LIMIT 1");
        $chk->bind_param('s', $code);
        $chk->execute();
        if ($chk->get_result()->fetch_assoc()) jsonErr('Discount code already exists');

        $s = $db->prepare("
            INSERT INTO tbl_discounts
                (code, name, discount_type, discount_value, applies_to, target_id,
                 valid_from, valid_to, usage_limit, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')
        ");
        $s->bind_param('sssdsissi', $code, $dname, $dtype, $dvalue, $appliesTo, $targetId, $validFrom, $validTo, $usageLim);
        if (!$s->execute()) jsonErr('Insert failed: ' . $s->error);
        jsonOk(['id' => $s->insert_id], 'Discount created');
    }
}

// ── TOGGLE DISCOUNT ───────────────────────────────────────────────────────────
case 'toggle_discount': {
    $input  = json_decode(file_get_contents('php://input'), true) ?? [];
    $discId = (int)($input['id'] ?? $_GET['id'] ?? 0);
    if (!$discId) jsonErr('id required');

    $cur = $db->prepare("SELECT status FROM tbl_discounts WHERE id = ? LIMIT 1");
    $cur->bind_param('i', $discId);
    $cur->execute();
    $row = $cur->get_result()->fetch_assoc();
    if (!$row) jsonErr('Discount not found');

    $newStatus = ($row['status'] === 'active') ? 'inactive' : 'active';
    $upd = $db->prepare("UPDATE tbl_discounts SET status = ? WHERE id = ?");
    $upd->bind_param('si', $newStatus, $discId);
    if (!$upd->execute()) jsonErr('Toggle failed');
    jsonOk(['status' => $newStatus], 'Status updated to ' . $newStatus);
}

// ── LIST COURSES (for bundle builder) ─────────────────────────────────────────
case 'list_courses': {
    $q = trim($_GET['q'] ?? '');
    if ($q !== '') {
        $like = '%' . $db->real_escape_string($q) . '%';
        $s = $db->prepare("
            SELECT id, course_title, price, status
            FROM tbl_courses
            WHERE course_title LIKE ?
              AND status != 'deleted'
            ORDER BY course_title ASC
            LIMIT 50
        ");
        $s->bind_param('s', $like);
        $s->execute();
        $rows = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $res  = $db->query("
            SELECT id, course_title, price, status
            FROM tbl_courses
            WHERE status != 'deleted'
            ORDER BY course_title ASC
            LIMIT 100
        ");
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    jsonOk($rows);
}

default:
    jsonErr('Unknown action');
}
