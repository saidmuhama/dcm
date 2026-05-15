<?php
include('../config/db.php');
session_start();

$course_id = intval($_GET['course_id'] ?? 0);
$user_id   = $_SESSION['usr_code'] ?? 0;

$q = mysqli_query($db,"
    SELECT status 
    FROM tbl_courses 
    WHERE id = '$course_id'
    AND instructor_id = '$user_id'
");

if(mysqli_num_rows($q) > 0){

    $row = mysqli_fetch_assoc($q);

    echo json_encode([
        "status" => "success",
        "course_status" => $row['status']
    ]);

}else{
    echo json_encode([
        "status" => "error",
        "message" => "Course not found or unauthorized"
    ]);
}