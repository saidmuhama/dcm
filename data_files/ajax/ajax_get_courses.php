<?php
include('../config/db.php');
session_start();

header('Content-Type: application/json');

$instructor_id = $_SESSION['usr_code'] ?? '';

if(empty($instructor_id)){
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

// FETCH COURSES
$stmt = $db->prepare("SELECT id, title, thumbnail, instructor_id FROM tbl_courses WHERE instructor_id = ?");
$stmt->bind_param("s", $instructor_id);
$stmt->execute();

$result = $stmt->get_result();

$courses = [];

while($row = $result->fetch_assoc()){
    $courses[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $courses
]);