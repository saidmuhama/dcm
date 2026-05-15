<?php

include('../config/db.php');
session_start();

header('Content-Type: application/json');

// ================= GET VALUES =================
$title = $_POST['lesson_title'] ?? '';
$video = $_POST['video_url'] ?? '';
$description = $_POST['description'] ?? '';
$course_id = $_POST['course_id'] ?? '';
$chapter_id = $_POST['chapter_id'] ?? '';
$content_type = $_POST['content_type'] ?? '';

$isFree = $_POST['isFreePreviewLesson'] ?? 0;
$discussion = $_POST['enableDiscussions'] ?? 0;
$downloadable = $_POST['isDownloadable'] ?? 0;

$instructor_id = $_SESSION['usr_code'] ?? '';

// ================= VALIDATION =================
if(empty($title)){
    echo json_encode(["status"=>"error","message"=>"Lesson title required"]);
    exit;
}

if(empty($instructor_id)){
    echo json_encode(["status"=>"error","message"=>"User not logged in"]);
    exit;
}

// ================= FILE / VIDEO HANDLING =================
$file_path = "";

if($content_type === "Video"){

    if(empty($video)){
        echo json_encode(["status"=>"error","message"=>"Video URL required"]);
        exit;
    }

    $file_path = $video;

} else {

    if(!isset($_FILES['file']) || $_FILES['file']['error'] != 0){
        echo json_encode(["status"=>"error","message"=>"File upload failed"]);
        exit;
    }

    $uploadDir = "../uploads/lessons/";

    if(!is_dir($uploadDir)){
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["file"]["name"]);
    $targetPath = $uploadDir . $fileName;

    if(move_uploaded_file($_FILES["file"]["tmp_name"], $targetPath)){
        $file_path = "uploads/lessons/" . $fileName; // save relative path
    } else {
        echo json_encode(["status"=>"error","message"=>"Failed to upload file"]);
        exit;
    }
}

// ================= INSERT =================
$stmt = $db->prepare("INSERT INTO tbl_course_chapter_lessons 
(lesson_title, description, instructor_id, course_id, chapter_id, file_path, isFreePreviewLesson, enableDiscussions, isDownloadable, created_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

$stmt->bind_param(
    "ssssssiii",
    $title,
    $description,
    $instructor_id,
    $course_id,
    $chapter_id,
    $file_path,
    $isFree,
    $discussion,
    $downloadable
);

if($stmt->execute()){
    echo json_encode([
        "status"=>"success",
        "message"=>"Lesson saved successfully"
    ]);
}else{
    echo json_encode([
        "status"=>"error",
        "message"=>"Failed to save lesson"
    ]);
}