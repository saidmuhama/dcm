<?php
include('../config/db.php');
include('../config/cache.php');

session_start();

header('Content-Type: application/json');

// GET JSON DATA
$data = json_decode(file_get_contents("php://input"), true);

// GET VALUES
$title         = $data['title'] ?? '';
$course_id     = $data['course_id'] ?? '';
$instructor_id = $_SESSION['usr_code'] ?? ''; // logged in user

// VALIDATION
if(empty($title)){
    echo json_encode([
        "status" => "error",
        "message" => "Course title is required"
    ]);
    exit;
}

if(empty($instructor_id)){
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

// INSERT
$stmt = $db->prepare("INSERT INTO tbl_course_chapters (instructor_id, chapter_title,course_id) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $instructor_id, $title, $course_id);

if($stmt->execute()){
    DcmCache::delete('chapters_course_' . $course_id, 'ccm');
    echo json_encode([
        "status" => "success",
        "message" => "Chapter added successfully"
    ]);
}else{
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save Chapter"
    ]);
}
