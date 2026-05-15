<?php
session_start();
include('../config/db.php');

$data = json_decode(file_get_contents("php://input"), true);

$course_id = intval($data['course_id']);
$status = $data['status'];

$instructor_id = $_SESSION['usr_code'];

$check = mysqli_query($db,"
    SELECT id FROM tbl_courses 
    WHERE id='$course_id' AND instructor_id='$instructor_id'
");

if(mysqli_num_rows($check) == 0){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

mysqli_query($db,"
    UPDATE tbl_courses 
    SET status='$status', updated_at=NOW()
    WHERE id='$course_id'
");

echo json_encode(["status"=>"success"]);