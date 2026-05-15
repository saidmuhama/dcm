<?php
include('../config/db.php');
session_start();

$course_id      = $_POST['course_id'] ?? 0;
$title          = $_POST['title'];
$description    = $_POST['description'];
$user_id        = $_SESSION['usr_code']; // session user

if(!$course_id || !$title){
    echo json_encode([
        "status" => "error",
        "message" => "Missing data"
    ]);
    exit;
}

mysqli_query($db, "
INSERT INTO tbl_course_discussions(course_id,user_id,title,description)
VALUES('$course_id','$user_id','$title','$description')
");

echo json_encode(["status"=>"success"]);