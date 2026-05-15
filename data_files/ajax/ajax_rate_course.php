<?php 
include('../config/db.php');
session_start();
$user = $_SESSION['usr_code'];

$data = json_decode(file_get_contents("php://input"), true);

$course_id = $data['course_id'];
$rating    = $data['rating'];

mysqli_query($db,"
    INSERT INTO tbl_course_ratings(user_id,course_id,rating)
    VALUES('$user','$course_id','$rating')
");

echo json_encode(["message"=>"Rating submitted"]);

?>