<?php
include('../config/db.php');
session_start();
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? '') != 5) {
    echo json_encode(['status'=>'error','message'=>'Access denied']); exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'create') {
    $title = trim($_POST['title'] ?? '');
    if (!$title) { echo json_encode(['status'=>'error','message'=>'Title required']); exit; }
    $stmt = $db->prepare("INSERT INTO tbl_user_roles (role_title) VALUES (?)");
    $stmt->bind_param("s", $title);
    if ($stmt->execute()) echo json_encode(['status'=>'success','id'=>$db->insert_id]);
    else echo json_encode(['status'=>'error','message'=>$stmt->error]);
    exit;
}

if ($action === 'update') {
    $id    = (int)($_POST['id']    ?? 0);
    $title = trim($_POST['title']  ?? '');
    if (!$id || !$title) { echo json_encode(['status'=>'error','message'=>'ID and title required']); exit; }
    $stmt = $db->prepare("UPDATE tbl_user_roles SET role_title=? WHERE id=?");
    $stmt->bind_param("si", $title, $id);
    if ($stmt->execute()) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error','message'=>$stmt->error]);
    exit;
}

if ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { echo json_encode(['status'=>'error','message'=>'ID required']); exit; }

    // Refuse if users exist with this role
    $chk = $db->prepare("SELECT COUNT(*) AS c FROM tbl_all_users WHERE user_role = ?");
    $chk->bind_param("i", $id); $chk->execute();
    if ((int)$chk->get_result()->fetch_assoc()['c'] > 0) {
        echo json_encode(['status'=>'error','message'=>'Cannot delete: users with this role still exist']); exit;
    }

    $db->query("DELETE FROM tbl_module_permissions WHERE role_id = $id");
    $stmt = $db->prepare("DELETE FROM tbl_user_roles WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error','message'=>$stmt->error]);
    exit;
}

echo json_encode(['status'=>'error','message'=>"Unknown action"]);
