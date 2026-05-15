<?php
include('../config/db.php');
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$course_id = intval($data['course_id'] ?? 0);
$status    = $data['status'] ?? '';
$user_id   = $_SESSION['usr_code'] ?? 0;

$allowed = ['active','inactive','is_draft'];

if(!$course_id || !$user_id || !in_array($status, $allowed)){
    echo json_encode([
        "status"=>"error",
        "message"=>"Invalid data"
    ]);
    exit;
}

/* ✅ VERIFY COURSE BELONGS TO INSTRUCTOR */
$check = mysqli_query($db,"
    SELECT id 
    FROM tbl_courses 
    WHERE id = '$course_id'
    AND instructor_id = '$user_id'
");

if(mysqli_num_rows($check) == 0){
    echo json_encode([
        "status"=>"error",
        "message"=>"Unauthorized action"
    ]);
    exit;
}

/* ✅ UPDATE */
$update = mysqli_query($db,"
    UPDATE tbl_courses 
    SET status = '$status', updated_at = NOW()
    WHERE id = '$course_id'
    AND instructor_id = '$user_id'
");

if($update){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode([
        "status"=>"error",
        "message"=>"Update failed"
    ]);
}