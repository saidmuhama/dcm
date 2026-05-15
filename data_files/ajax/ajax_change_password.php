<?php
include('../config/db.php'); 
session_start();

header("Content-Type: application/json");

// GET JSON DATA
$data = json_decode(file_get_contents("php://input"), true);

$current = $data['current_password'] ?? '';
$new     = $data['new_password'] ?? '';

// SESSION USER
$user_code = $_SESSION['usr_code'] ?? '';

if(empty($user_code)){
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

// ================= GET USER =================
$stmt = $db->prepare("SELECT user_password FROM tbl_all_users WHERE usr_code=?");
$stmt->bind_param("s", $user_code);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if(!$user){
    echo json_encode([
        "status" => "error",
        "message" => "User not found"
    ]);
    exit;
}

// ================= VERIFY CURRENT PASSWORD =================
if(!password_verify($current, $user['user_password'])){
    echo json_encode([
        "status" => "error",
        "message" => "Current password is incorrect"
    ]);
    exit;
}

// ================= HASH NEW PASSWORD =================
$newHash = password_hash($new, PASSWORD_DEFAULT);

// ================= UPDATE PASSWORD =================
$stmt = $db->prepare("UPDATE tbl_all_users SET user_password=? WHERE usr_code=?");
$stmt->bind_param("ss", $newHash, $user_code);

if($stmt->execute()){
    echo json_encode([
        "status" => "success",
        "message" => "Password changed successfully"
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update password"
    ]);
}