<?php 
include('../config/db.php');
session_start();
$user = $_SESSION['usr_code'];

$data = json_decode(file_get_contents("php://input"), true);
$course_id = $data['course_id'];

$check = mysqli_query($db,"SELECT * FROM tbl_course_wishlist WHERE user_id='$user' AND course_id='$course_id'");

if(mysqli_num_rows($check)){
    mysqli_query($db,"DELETE FROM tbl_course_wishlist WHERE user_id='$user' AND course_id='$course_id'");
    echo json_encode(["message"=>"Removed from wishlist"]);
}else{
    mysqli_query($db,"INSERT INTO tbl_course_wishlist(user_id,course_id) VALUES('$user','$course_id')");
    echo json_encode(["message"=>"Added to wishlist"]);
}

?>