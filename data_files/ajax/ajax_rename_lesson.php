<?php
include('../config/db.php');
include('../config/dump.php');
session_start();

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$lesson_id        = $data['lesson_id'] ?? '';
$newTitle         = $data['lesson_title'] ?? '';
$instructor_id    = $_SESSION['usr_code'] ?? '';

$sql = mysqli_query($db,"SELECT * FROM tbl_course_chapter_lessons WHERE id='$lesson_id' AND instructor_id='$instructor_id'");
$row = mysqli_fetch_assoc($sql);

$video_id   = $row['video_id'];
$course_id  = $row['course_id'];
$title      = $row['lesson_title'];
$library_id = $row['library_id'];

$apiKey     = App::getWhatFromWHere('library_key','tbl_courses','id',$course_id);

if(!isset($_SESSION['usr_code']))
{
    echo json_encode(["status"=>"error","message"=>"User not logged in"]);
    exit;
}
if(empty($lesson_id) || empty($title))
{
    echo json_encode(["status"=>"error","message"=>"Missing data"]);
    exit;
}

if(strtolower($newTitle) == strtolower($title))
{
    echo json_encode(["status"=>"error","message"=>"Title is the same"]);
    exit;
}

$res = App :: renameVideo($library_id, $video_id, $newTitle, $apiKey);
if ($res['status'] === "success") 
{
    // UPDATE DB
    $stmt = $db->prepare("UPDATE tbl_course_chapter_lessons 
            SET lesson_title=? 
            WHERE id=? AND instructor_id=?");

    $stmt->bind_param("sis", $title, $lesson_id, $instructor_id);
    if($stmt->execute())
    {
        echo json_encode([
            "status"=>"success",
            "message"=>"Lesson title updated"
        ]);
    }
    else
    {
        echo json_encode([
            "status"=>"error",
            "message"=>"Update failed"
        ]);
    }
}
else
{
    echo json_encode([
        "status"=>"error",
        "message"=>"Rename failed"
    ]);
}

