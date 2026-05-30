<?php
session_start();
include('../config/db.php');
include('../config/totp.php');
header('Content-Type: application/json');

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill all fields.']);
    exit;
}

$stmt = $db->prepare("SELECT * FROM tbl_all_users WHERE email_address = ? LIMIT 1");
$stmt->bind_param('s', $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User not found.']);
    exit;
}

if (!password_verify($password, $user['user_password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Incorrect password.']);
    exit;
}

// Check role-level 2FA policy
$role_id = (int)$user['user_role'];
$db->query("CREATE TABLE IF NOT EXISTS tbl_2fa_role_policy (
    role_id INT NOT NULL PRIMARY KEY, require_2fa TINYINT(1) NOT NULL DEFAULT 0
)");
$pstmt = $db->prepare("SELECT require_2fa FROM tbl_2fa_role_policy WHERE role_id = ?");
$pstmt->bind_param('i', $role_id);
$pstmt->execute();
$policy = $pstmt->get_result()->fetch_assoc();
$role_requires_2fa = !empty($policy['require_2fa']);

$user_has_2fa = !empty($user['totp_enabled']) && !empty($user['totp_secret']);

if ($user_has_2fa) {
    // User enrolled — require code verification
    $_SESSION['2fa_pending_id'] = $user['id'];
    echo json_encode(['status' => '2fa_required']);
    exit;
}

if ($role_requires_2fa) {
    // Role enforces 2FA but user hasn't enrolled — force setup
    $_SESSION['2fa_pending_id']  = $user['id'];
    $_SESSION['2fa_force_setup'] = true;
    echo json_encode(['status' => '2fa_setup_required']);
    exit;
}

// No 2FA needed — complete login
$_SESSION['usr_code']  = $user['usr_code'];
$_SESSION['name']      = $user['first_name'] . ' ' . $user['last_name'];
$_SESSION['user_role'] = $user['user_role'];
if (!empty($user['force_pw_change'])) {
    $_SESSION['force_pw_change'] = true;
}
echo json_encode(['status' => 'success']);
