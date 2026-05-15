<?php
session_start();
include('../config/db.php'); 

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get data
$email = mysqli_real_escape_string($db, $_POST['email']);
$password = $_POST['password'];

// Validate
if(empty($email) || empty($password)){
    echo "Please fill all fields!";
    exit;
}

// Check user
$query = mysqli_query($db, "SELECT * FROM tbl_all_users WHERE email_address='$email' LIMIT 1");

if(mysqli_num_rows($query) == 1){

    $user = mysqli_fetch_assoc($query);
    // Verify password
    if(password_verify($password, $user['user_password'])){

        // Save session
        $_SESSION['usr_code']     = $user['usr_code'];
        $_SESSION['name']         = $user['first_name'].' '.$user['last_name'];
        $_SESSION['user_role']    = $user['user_role'];
        echo "success";

    } else {
        echo "Incorrect password!";
    }

} else {
    echo "User not found!";
}
?>