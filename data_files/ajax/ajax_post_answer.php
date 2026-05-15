<?php
include('../config/db.php');
session_start();
$discussion_id  = $_POST['discussion_id'];
$user_id        = $_SESSION['usr_code']; // session user
$answer         = $_POST['answer'];

mysqli_query($db, "
INSERT INTO tbl_course_discussion_answers(discussion_id,user_id,answer)
VALUES('$discussion_id','$user_id','$answer')
");

echo json_encode(["status"=>"success"]);