<?php
session_start();
include('../config/db.php');

$user_id = $_SESSION['usr_code'] ?? 0;

if(!$user_id){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$query = mysqli_query($db,"
    SELECT id, title, thumbnail, duration, created_at
    FROM tbl_courses
    WHERE instructor_id = '$user_id'
    AND status = 'active'
    ORDER BY id DESC
    LIMIT 3
");

$data = [];

while($row = mysqli_fetch_assoc($query)){
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);