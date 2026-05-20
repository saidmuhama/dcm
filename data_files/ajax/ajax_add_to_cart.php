<?php
ini_set('display_errors', 0);
ob_start();
include('../config/db.php');
session_start();
header('Content-Type: application/json');

function _j($d) { ob_clean(); echo json_encode($d); exit; }

if (!isset($_SESSION['usr_code'])) _j(['status' => 'error', 'message' => 'Not authenticated']);

$data      = json_decode(file_get_contents('php://input'), true) ?? [];
$course_id = (int)($data['course_id'] ?? 0);
$usr       = $_SESSION['usr_code'];

if (!$course_id) _j(['status' => 'error', 'message' => 'Invalid course']);

$course = $db->query("SELECT id, price FROM tbl_courses WHERE id={$course_id} AND status='active' AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
if (!$course) _j(['status' => 'error', 'message' => 'Course not found']);

/* ── Already enrolled? ─────────────────────────────────────── */
$enrolled = $db->query("
    SELECT COUNT(*) AS cnt FROM tbl_orders o
    JOIN tbl_order_items oi ON oi.order_id = o.id
    WHERE o.user_id = '{$db->escape_string($usr)}' AND o.payment_status = 'paid' AND oi.course_id = {$course_id}
")->fetch_assoc()['cnt'] ?? 0;

if ($enrolled > 0) _j(['status' => 'enrolled', 'message' => 'You are already enrolled in this course']);

/* ── Already in cart? ──────────────────────────────────────── */
$inCart = $db->query("SELECT id FROM tbl_course_cart WHERE user_id='{$db->escape_string($usr)}' AND course_id={$course_id} LIMIT 1")->fetch_assoc();
if ($inCart) _j(['status' => 'already', 'message' => 'Already in your cart']);

$db->query("INSERT INTO tbl_course_cart (user_id, course_id) VALUES ('{$db->escape_string($usr)}', {$course_id})");
_j(['status' => 'success', 'message' => 'Added to cart']);
