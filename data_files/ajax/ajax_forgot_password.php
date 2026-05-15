<?php
include('../config/db.php'); 

$email = mysqli_real_escape_string($db, $_POST['email']);

if(empty($email)){
    echo "Enter your email!";
    exit;
}

// Check user
$query = mysqli_query($db, "SELECT * FROM tbl_all_users WHERE email_address='$email'");

if(mysqli_num_rows($query) == 0){
    echo "Email not found!";
    exit;
}

// Generate token
$token = bin2hex(random_bytes(50));
$expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Save token
mysqli_query($db, "UPDATE tbl_all_users 
SET reset_token='$token', token_expiry='$expiry' 
WHERE email_address='$email'");

// Reset link
$reset_link = "https://app.digitalclassmedia.com/signup/reset-password.php?token=$token";

// Email headers
$subject = "Password Reset Request";
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
$headers .= "From: Your App <no-reply@yourdomain.com>";

// HTML Email Template
$message = "
<html>
<head>
<style>
body { font-family: Arial; background:#f4f4f4; padding:20px; }
.box { background:#fff; padding:20px; border-radius:10px; }
.btn { background:#007bff; color:#fff; padding:10px 20px; text-decoration:none; border-radius:5px; }
</style>
</head>
<body>
<div class='box'>
<h2>Password Reset</h2>
<p>You requested to reset your password.</p>
<p>Click the button below:</p>
<a href='$reset_link' class='btn'>Reset Password</a>
<p>This link expires in 1 hour.</p>
</div>
</body>
</html>
";

// Send email
if(mail($email, $subject, $message, $headers)){
    echo "success";
} else {
    echo "Failed to send email!";
}
?>