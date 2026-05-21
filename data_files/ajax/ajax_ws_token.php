<?php
session_start();
header('Content-Type: application/json');

$user_code = $_SESSION['usr_code']   ?? '';
$user_role = $_SESSION['user_role']  ?? 0;

if (!$user_code) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Token = timestamp.HMAC — validated by ws_server.php using the same key
$secret = 'dcm_ws_hmac_secret_k2026';
$ts     = time();
$sig    = hash_hmac('sha256', $ts . ':' . $user_code, $secret);

echo json_encode([
    'status'    => 'success',
    'token'     => $ts . '.' . $sig,
    'user_code' => $user_code,
    'role'      => intval($user_role),
    'expires'   => $ts + 300,
]);
