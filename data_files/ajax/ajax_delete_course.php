<?php
include('../config/db.php');
include('../config/dump.php');
session_start();

header('Content-Type: application/json');
$instructor_id = $_SESSION['usr_code'];
if(!$instructor_id) {
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$course_id   = $_POST['course_id'];
$library_id  = App::getWhatFromWHere('library_id','tbl_courses','id',$course_id);
$library_key = App::getWhatFromWHere('library_key','tbl_courses','id',$course_id);

$res = App::deleteVideoLibrary($library_id, App::getBunnyNetApiKey());

if ($res['status'] === "success") {
    
    $sql = mysqli_query($db, "DELETE FROM tbl_courses 
            WHERE id='$course_id' AND instructor_id='$instructor_id'");
    
    $sql2 = mysqli_query($db, "DELETE FROM tbl_course_chapter_lessons 
            WHERE course_id='$course_id' AND instructor_id='$instructor_id'");
            
    $sql3 = mysqli_query($db, "DELETE FROM tbl_course_wishlist WHERE course_id='$course_id'");

    echo json_encode(["status"=>"success","message"=>"Course deleted"]);

} 
else 
{
    echo json_encode(["status"=>"error","message"=>"Failed to delete Course/Library: ID ".$library_id." AccountAPI ".App::getBunnyNetApiKey().' '. json_encode($res)]);
}
