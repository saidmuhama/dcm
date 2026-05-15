<?php
include('../config/db.php'); 

$token    = $_POST['token'];
$password = $_POST['password'];

if(empty($token) || empty($password)){
    echo "Invalid request!";
    exit;
}

// Check token
$query = mysqli_query($db, "SELECT * FROM tbl_all_users 
WHERE reset_token='$token' AND token_expiry > NOW()");

if(mysqli_num_rows($query) == 0){
    echo "Invalid or expired token!";
    exit;
}

// Hash password
$hashed = password_hash($password, PASSWORD_DEFAULT);

// Update
mysqli_query($db, "UPDATE tbl_all_users 
SET user_password='$hashed', reset_token=NULL, token_expiry=NULL 
WHERE reset_token='$token'");

echo "success";
?>