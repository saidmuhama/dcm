<?php
ini_set('display_errors', 0);
ob_start();
include('../config/db.php');
session_start();
header('Content-Type: application/json');

function _j(array $d): never { ob_clean(); echo json_encode($d); exit; }

if (!isset($_SESSION['usr_code'])) _j(['status' => 'error', 'message' => 'Not authenticated']);

$data      = json_decode(file_get_contents('php://input'), true) ?? [];
$course_id = (int)($data['course_id'] ?? 0);
$usr       = $_SESSION['usr_code'];

if (!$course_id) _j(['status' => 'error', 'message' => 'Invalid course']);

/* ── Verify course is active, free, and not deleted ─────────── */
$cs = $db->prepare("SELECT id, title, price, instructor_id FROM tbl_courses WHERE id = ? AND status = 'active' AND deleted_at IS NULL");
$cs->bind_param('i', $course_id);
$cs->execute();
$course = $cs->get_result()->fetch_assoc();

if (!$course) _j(['status' => 'error', 'message' => 'Course not available']);
if ((float)$course['price'] > 0) _j(['status' => 'error', 'message' => 'This is a paid course. Please use checkout.']);

/* ── Check not already enrolled ─────────────────────────────── */
$chk = $db->prepare("
    SELECT COUNT(*) AS cnt
    FROM tbl_orders o
    JOIN tbl_order_items oi ON oi.order_id = o.id
    WHERE o.user_id = ? AND o.payment_status = 'paid' AND oi.course_id = ?
");
$chk->bind_param('si', $usr, $course_id);
$chk->execute();
$already = (int)($chk->get_result()->fetch_assoc()['cnt'] ?? 0);
if ($already > 0) _j(['status' => 'already', 'message' => 'Already enrolled']);

/* ── Create free order ───────────────────────────────────────── */
$invoice = 'FREE-' . strtoupper(bin2hex(random_bytes(5))) . '-' . time();

$ins = $db->prepare("
    INSERT INTO tbl_orders
        (invoice_id, instructor_id, user_id, payment_method, payment_status, payable_amount, paid_amount, created_at)
    VALUES (?, ?, ?, 'free', 'paid', 0, 0, NOW())
");
$ins->bind_param('sss', $invoice, $course['instructor_id'], $usr);
$ins->execute();
$order_id = $db->insert_id;

if (!$order_id) _j(['status' => 'error', 'message' => 'Could not create enrolment']);

/* ── Create order item ───────────────────────────────────────── */
$oi = $db->prepare("
    INSERT INTO tbl_order_items (order_id, qty, price, item_type, course_id, created_at)
    VALUES (?, 1, 0, 'course', ?, NOW())
");
$oi->bind_param('ii', $order_id, $course_id);
$oi->execute();

/* ── Grant access in enrollments table ───────────────────────── */
$enr = $db->prepare("
    INSERT IGNORE INTO tbl_course_enrollments (order_id, user_id, course_id, has_access, created_at)
    SELECT ?, u.id, ?, 1, NOW()
    FROM tbl_all_users u WHERE u.usr_code = ?
");
$enr->bind_param('iis', $order_id, $course_id, $usr);
$enr->execute();

/* ── Auto-join course community chat ─────────────────────────────────── */
try {
    $enroll_usr = $db->real_escape_string($usr);
    $enroll_cid = $course_id;
    $commRow = $db->query("SELECT id FROM tbl_chat_conversations WHERE linked_type='course' AND linked_id=$enroll_cid AND auto_managed=1 LIMIT 1")->fetch_assoc();
    if (!$commRow) {
        $cTitle = $db->real_escape_string(($db->query("SELECT title FROM tbl_courses WHERE id=$enroll_cid LIMIT 1")->fetch_row()[0] ?? 'Course') . ' Community');
        $db->query("INSERT INTO tbl_chat_conversations (type,name,linked_type,linked_id,auto_managed,created_by,last_message_at) VALUES ('group','$cTitle','course',$enroll_cid,1,'system',NOW())");
        $commId = (int)$db->insert_id;
    } else {
        $commId = (int)$commRow['id'];
    }
    if ($commId) {
        $db->query("INSERT IGNORE INTO tbl_chat_participants (conv_id,usr_code,role,joined_at) VALUES ($commId,'$enroll_usr','member',NOW())");
    }
} catch (Throwable $ce) { /* non-fatal — enrollment still succeeds */ }

_j(['status' => 'success', 'message' => 'Enrolled successfully! Start learning now.']);
