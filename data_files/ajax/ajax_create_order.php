<?php
include('../config/db.php');

session_start();

$data = json_decode(file_get_contents("php://input"), true);

$user_id   = $_SESSION['usr_code'] ?? 0;
$course_id = intval($data['course_id']);

if(!$user_id || !$course_id){
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit;
}

/* ✅ GET COURSE */
$sql = "SELECT id, instructor_id, price, discount 
        FROM tbl_courses 
        WHERE id='$course_id'";

$res = mysqli_query($db, $sql);
$course = mysqli_fetch_assoc($res);

if(!$course){
    echo json_encode(["status"=>"error","message"=>"Course not found"]);
    exit;
}

/* ✅ CALCULATE PRICE */
$price = $course['price'];
$discount = $course['discount'];
$final_price = $price - ($price * $discount / 100);

/* ✅ GENERATE INVOICE */
$invoice_id = "INV-" . time();

/* ✅ INSERT ORDER */
mysqli_query($db, "
INSERT INTO tbl_orders (
    invoice_id,
    instructor_id,
    user_id,
    payment_method,
    payment_status,
    payable_amount,
    paid_amount,
    commission_rate,
    created_at
) VALUES (
    '$invoice_id',
    '{$course['instructor_id']}',
    '$user_id',
    'ONLINE',
    'PAID',
    '$final_price',
    '$final_price',
    '10',
    NOW()
)");

$order_id = mysqli_insert_id($db);

/* ✅ INSERT ORDER ITEM */
mysqli_query($db, "
INSERT INTO tbl_order_items (
    order_id,
    qty,
    price,
    item_type,
    course_id,
    commission_rate,
    created_at
) VALUES (
    '$order_id',
    1,
    '$final_price',
    'course',
    '$course_id',
    '10',
    NOW()
)");

/* ✅ REMOVE FROM CART */
mysqli_query($db, "
DELETE FROM tbl_course_cart 
WHERE user_id='$user_id' 
AND course_id='$course_id'
");

echo json_encode([
    "status"=>"success",
    "message"=>"Order created successfully"
]);