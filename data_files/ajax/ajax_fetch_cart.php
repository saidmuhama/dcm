<?php
include('../config/db.php');

session_start();

$user_id = $_SESSION['usr_code'] ?? ''; // logged in user

$query = mysqli_query($db, "
    SELECT 
        c.id,
        c.title,
        c.thumbnail,
        c.price,
        c.discount
    FROM tbl_course_cart cart
    INNER JOIN tbl_courses c ON c.id = cart.course_id
    WHERE cart.user_id = '$user_id'
");

$data = [];

while($row = mysqli_fetch_assoc($query)){

    $price = $row['price'];
    $discount = $row['discount'];

    $final_price = $price - ($price * $discount / 100);

    $row['final_price'] = $final_price;

    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);