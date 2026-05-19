<?php
include('../config/db.php');
session_start();
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? '') != 5) {
    echo json_encode(['status'=>'error','message'=>'Access denied']); exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

/* ── LIST ─────────────────────────────────────────────────────── */
if ($action === 'list') {
    $role_f   = trim($_GET['role']   ?? '');
    $status_f = trim($_GET['status'] ?? '');
    $q        = trim($_GET['q']      ?? '');
    $page     = max(1, (int)($_GET['page']     ?? 1));
    $per_page = min(100, max(5, (int)($_GET['per_page'] ?? 25)));
    $offset   = ($page - 1) * $per_page;

    $where = ['1=1']; $types = ''; $params = [];
    if ($role_f !== '') { $where[] = 'u.user_role = ?'; $types .= 's'; $params[] = $role_f; }
    if ($status_f !== '') { $where[] = 'u.user_status = ?'; $types .= 's'; $params[] = $status_f; }
    if ($q !== '') {
        $where[] = '(u.first_name LIKE ? OR u.last_name LIKE ? OR u.email_address LIKE ? OR u.phone_number LIKE ?)';
        $types .= 'ssss'; $params = array_merge($params, ["%$q%","%$q%","%$q%","%$q%"]);
    }
    $whereStr = implode(' AND ', $where);

    $cntStmt = $db->prepare("SELECT COUNT(*) AS cnt FROM tbl_all_users u WHERE $whereStr");
    if ($types) $cntStmt->bind_param($types, ...$params);
    $cntStmt->execute();
    $total = (int)$cntStmt->get_result()->fetch_assoc()['cnt'];

    $listStmt = $db->prepare("SELECT id, usr_code, first_name, last_name, email_address, phone_number,
        user_role, user_status, created_at FROM tbl_all_users u WHERE $whereStr ORDER BY u.created_at DESC LIMIT ? OFFSET ?");
    $allTypes  = $types . 'ii';
    $allParams = array_merge($params, [$per_page, $offset]);
    $listStmt->bind_param($allTypes, ...$allParams);
    $listStmt->execute();
    $rows = $listStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','data'=>$rows,'total'=>$total]); exit;
}

/* ── CREATE ──────────────────────────────────────────────────── */
if ($action === 'create') {
    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name']  ?? '');
    $email  = trim($_POST['email']      ?? '');
    $phone  = trim($_POST['phone']      ?? '');
    $role   = trim($_POST['role']       ?? '');
    $pw     = $_POST['password']        ?? '';

    if (!$first || !$last || !$email || !$role) {
        echo json_encode(['status'=>'error','message'=>'Required fields missing']); exit;
    }
    if (strlen($pw) < 8) {
        echo json_encode(['status'=>'error','message'=>'Password must be at least 8 characters']); exit;
    }

    // Check email uniqueness
    $chk = $db->prepare("SELECT id FROM tbl_all_users WHERE email_address = ?");
    $chk->bind_param("s", $email); $chk->execute();
    if ($chk->get_result()->fetch_assoc()) {
        echo json_encode(['status'=>'error','message'=>'Email address already in use']); exit;
    }

    $usr_code = 'usr_' . uniqid('', true);
    $hashed   = password_hash($pw, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO tbl_all_users
        (usr_code, first_name, last_name, email_address, phone_number, user_role, user_password, user_status, signup_success)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Active', 'Completed')");
    $stmt->bind_param("sssssss", $usr_code, $first, $last, $email, $phone, $role, $hashed);
    if ($stmt->execute()) {
        echo json_encode(['status'=>'success','message'=>'User created successfully']);
    } else {
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
    exit;
}

/* ── UPDATE ──────────────────────────────────────────────────── */
if ($action === 'update') {
    $id     = (int)($_POST['id'] ?? 0);
    $first  = trim($_POST['first_name'] ?? '');
    $last   = trim($_POST['last_name']  ?? '');
    $email  = trim($_POST['email']      ?? '');
    $phone  = trim($_POST['phone']      ?? '');
    $role   = trim($_POST['role']       ?? '');
    $status = trim($_POST['status']     ?? 'Active');

    if (!$id || !$first || !$last || !$email || !$role) {
        echo json_encode(['status'=>'error','message'=>'Required fields missing']); exit;
    }

    $stmt = $db->prepare("UPDATE tbl_all_users SET
        first_name=?, last_name=?, email_address=?, phone_number=?, user_role=?, user_status=?
        WHERE id=?");
    $stmt->bind_param("ssssssi", $first, $last, $email, $phone, $role, $status, $id);
    if ($stmt->execute()) {
        echo json_encode(['status'=>'success','message'=>'User updated']);
    } else {
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
    exit;
}

/* ── RESET PASSWORD ──────────────────────────────────────────── */
if ($action === 'reset_password') {
    $id = (int)($_POST['id'] ?? 0);
    $pw = $_POST['password'] ?? '';
    if (!$id || strlen($pw) < 8) {
        echo json_encode(['status'=>'error','message'=>'Invalid request']); exit;
    }
    $hashed = password_hash($pw, PASSWORD_DEFAULT);
    $stmt   = $db->prepare("UPDATE tbl_all_users SET user_password=? WHERE id=?");
    $stmt->bind_param("si", $hashed, $id);
    if ($stmt->execute()) echo json_encode(['status'=>'success','message'=>'Password reset']);
    else echo json_encode(['status'=>'error','message'=>$stmt->error]);
    exit;
}

/* ── DELETE ──────────────────────────────────────────────────── */
if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['status'=>'error','message'=>'ID missing']); exit; }
    $stmt = $db->prepare("DELETE FROM tbl_all_users WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) echo json_encode(['status'=>'success','message'=>'User deleted']);
    else echo json_encode(['status'=>'error','message'=>$stmt->error]);
    exit;
}

echo json_encode(['status'=>'error','message'=>"Unknown action: $action"]);
