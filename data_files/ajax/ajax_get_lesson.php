<?php
include('../config/db.php');

header('Content-Type: application/json');

$id = $_GET['id'] ?? '';

if(empty($id)){
    echo json_encode(["status"=>"error","message"=>"Invalid ID"]);
    exit;
}

$stmt = $db->prepare("SELECT * FROM tbl_course_chapter_lessons WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if($row = $result->fetch_assoc()){
    echo json_encode([
        "status" => "success",
        "data" => $row
    ]);
}else{
    echo json_encode([
        "status" => "error",
        "message" => "Lesson not found"
    ]);
}