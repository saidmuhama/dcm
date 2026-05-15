<?php
include('../config/db.php');

session_start();

$user_id = $_SESSION['usr_code'] ?? '';
$course_id = $_POST['course_id'];

mysqli_query($db, "
DELETE FROM tbl_course_cart 
WHERE user_id='$user_id' AND course_id='$course_id'
");

echo json_encode(["status"=>"success"]);