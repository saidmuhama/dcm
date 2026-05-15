<?php
session_start();
include('../config/db.php');

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $_SESSION['usr_code'] ?? 0;
$lesson_id = intval($data['lesson_id'] ?? 0);

if(!$user_id || !$lesson_id){
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit;
}

/* GET COURSE + CHAPTER */
$q = mysqli_query($db,"
    SELECT course_id, chapter_id 
    FROM tbl_course_chapter_lessons 
    WHERE id = '$lesson_id'
");

$row = mysqli_fetch_assoc($q);

$course_id  = $row['course_id'];
$chapter_id = $row['chapter_id'];

/* CHECK IF EXISTS */
$check = mysqli_query($db,"
    SELECT id FROM tbl_course_progress
    WHERE user_id='$user_id'
    AND lesson_id='$lesson_id'
");

if(mysqli_num_rows($check) == 0){

    mysqli_query($db,"
        INSERT INTO tbl_course_progress
        (user_id, course_id, chapter_id, lesson_id, watched, type, created_at)
        VALUES
        ('$user_id','$course_id','$chapter_id','$lesson_id',1,'lesson',NOW())
    ");
}

echo json_encode(["status"=>"success"]);