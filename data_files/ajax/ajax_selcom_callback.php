<?php
/* Selcom webhook — called by Selcom when payment is confirmed */
ini_set('display_errors', 0);
include('../config/db.php');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true) ?? [];

$orderRef = trim($data['order_id'] ?? '');
$result   = trim($data['result']   ?? '');
$transId  = trim($data['transid']  ?? '');

/* ── Log incoming callback ───────────────────────────────────── */
file_put_contents(__DIR__ . '/../logs/selcom_callback.log',
    date('Y-m-d H:i:s') . ' ' . $raw . PHP_EOL, FILE_APPEND | LOCK_EX);

if (!$orderRef) { http_response_code(400); echo 'Bad request'; exit; }

/* ── Find the pending checkout ───────────────────────────────── */
$po = $db->query("SELECT * FROM tbl_payment_order WHERE order_id='{$db->escape_string($orderRef)}' LIMIT 1")->fetch_assoc();
if (!$po) { http_response_code(404); echo 'Order not found'; exit; }

/* ── Only process confirmed payments ─────────────────────────── */
if ($result !== '000') {
    $db->query("UPDATE tbl_payment_order SET order_status='Failed' WHERE order_id='{$db->escape_string($orderRef)}'");
    echo 'OK'; exit;
}

/* ── Already processed? ─────────────────────────────────────── */
if ($po['order_status'] === 'Paid') { echo 'OK'; exit; }

/* ── Mark payment order as paid ──────────────────────────────── */
$db->query("UPDATE tbl_payment_order
    SET order_status='Paid', is_alerted='Yes'
    WHERE order_id='{$db->escape_string($orderRef)}'");

/* ── Create course orders + grant access ─────────────────────── */
$usr          = $po['username'];
$details      = json_decode($po['order_details'] ?? '{}', true);
$courses      = $details['courses'] ?? [];
$payType      = $po['pay_type'] ?? 'ONLINE';
$amount       = (float)($po['amount'] ?? 0);
$perCourse    = count($courses) > 0 ? round($amount / count($courses), 2) : 0;

// Numeric user id for tbl_course_enrollments
$numUserId = (int)($db->query("SELECT id FROM tbl_all_users WHERE usr_code='{$db->escape_string($usr)}' LIMIT 1")->fetch_assoc()['id'] ?? 0);

foreach ($courses as $course) {
    $cid          = (int)$course['id'];
    $instructorId = $db->escape_string($course['instructor_id'] ?? '');
    $invoice      = 'INV-' . $orderRef . '-' . $cid;

    /* ── tbl_orders ─── */
    $db->query("INSERT IGNORE INTO tbl_orders
        (invoice_id, instructor_id, user_id, payment_method, payment_status,
         payable_amount, paid_amount, transaction_id, commission_rate, created_at)
        VALUES ('{$invoice}', '{$instructorId}', '{$usr}', '{$payType}', 'paid',
                {$perCourse}, {$perCourse}, '{$db->escape_string($orderRef)}', 10, NOW())");
    $orderId = (int)$db->insert_id;
    if (!$orderId) continue;

    /* ── tbl_order_items ─── */
    $db->query("INSERT IGNORE INTO tbl_order_items
        (order_id, qty, price, item_type, course_id, commission_rate, created_at)
        VALUES ({$orderId}, 1, {$perCourse}, 'course', {$cid}, 10, NOW())");

    /* ── tbl_course_enrollments ─── */
    if ($numUserId) {
        $db->query("INSERT IGNORE INTO tbl_course_enrollments
            (order_id, user_id, course_id, has_access, created_at)
            VALUES ({$orderId}, {$numUserId}, {$cid}, 1, NOW())");
    }

    /* ── Remove from cart ─── */
    $db->query("DELETE FROM tbl_course_cart WHERE user_id='{$usr}' AND course_id={$cid}");
}

echo 'OK';
