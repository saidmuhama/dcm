<?php
session_start();
include('../config/db.php');
include('../config/url_crypt_config.php');

$instructor_id = $_SESSION['usr_code'] ?? 0;

if(!$instructor_id){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$query = mysqli_query($db,"
    SELECT c.*,
    (SELECT COUNT(*) FROM tbl_course_chapters WHERE course_id = c.id) as total_chapters
    FROM tbl_courses c
    WHERE c.instructor_id = '$instructor_id'
    ORDER BY c.id DESC
");

$data = [];

while($row = mysqli_fetch_assoc($query)){
    $row['course_token'] = encryptURLId((int)$row['id'], ctx: 'course');
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);