<?php
include('../config/db.php');
session_start();
$discussion_id = $_POST['discussion_id'];
$user_id = $_SESSION['usr_code'];

mysqli_query($db, "
INSERT INTO tbl_course_discussion_likes(discussion_id,user_id)
VALUES('$discussion_id','$user_id')
");

echo json_encode(["status"=>"success"]);