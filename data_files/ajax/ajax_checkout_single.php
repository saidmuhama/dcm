<?php
include('../config/db.php');

session_start();
$user_id = $_SESSION['usr_code'] ?? 0;
$course_id = $_POST['course_id'] ?? 0;

// 🔹 GET COURSE
$q = mysqli_query($db, "
SELECT price, discount FROM tbl_courses WHERE id='$course_id'
");

if(mysqli_num_rows($q) == 0){
    echo json_encode(["status"=>"error","msg"=>"Course not found"]);
    exit;
}

$row = mysqli_fetch_assoc($q);

$price = $row['price'];
$discount = $row['discount'];

$final = $price - ($price * $discount / 100);

// 🔹 CREATE ORDER
mysqli_query($db, "
INSERT INTO tbl_orders(user_id,total_amount,payment_method,payment_status)
VALUES('$user_id','$final','card','pending')
");

$order_id = mysqli_insert_id($db);

// 🔹 INSERT ITEM
mysqli_query($db, "
INSERT INTO tbl_order_items(order_id,course_id,price)
VALUES('$order_id','$course_id','$final')
");

// 🔹 OPTIONAL: REMOVE FROM CART
mysqli_query($db, "
DELETE FROM tbl_course_cart 
WHERE user_id='$user_id' AND course_id='$course_id'
");

echo json_encode([
    "status" => "success",
    "order_id" => $order_id
]);