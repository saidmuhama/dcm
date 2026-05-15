<?php
include('../config/db.php'); 
// Get POST data safely
$first_name = mysqli_real_escape_string($db, $_POST['first_name']);
$last_name  = mysqli_real_escape_string($db, $_POST['last_name']);
$email      = mysqli_real_escape_string($db, $_POST['email']);
$phone      = mysqli_real_escape_string($db, $_POST['phone']);
$user_role  = mysqli_real_escape_string($db, $_POST['user_role']);
$password   = $_POST['password'];
$confirm    = $_POST['confirm_password'];

// Validation
if(empty($first_name) || empty($email) || empty($password)){
    echo "All fields are required!";
    exit;
}

if($password !== $confirm)
{
    echo "Passwords do not match!";
    exit;
}

// Check if email exists
$check = mysqli_query($db, "SELECT * FROM tbl_all_users WHERE email_address='$email'");
if(mysqli_num_rows($check) > 0){
    echo "Email already exists!";
    exit;
}

// Generate unique user code
$usr_code = 'USR' . time();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$query = "INSERT INTO tbl_all_users 
(usr_code, first_name, last_name, email_address, phone_number, user_role, user_password, created_at, user_status)
VALUES 
('$usr_code','$first_name','$last_name','$email','$phone','$user_role','$hashed_password',NOW(),'Active')";

if(mysqli_query($db, $query)){
    echo "success";
} else {
    echo "Error: " . mysqli_error($db);
}
?>