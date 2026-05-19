<?php
include('../config/db.php');
include('../config/modules.php');
session_start();
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? '') != 5) {
    echo json_encode(['status'=>'error','message'=>'Access denied']); exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'seed_defaults') {
    // Insert is_enabled=1 for any module/role combo that has no row yet
    $all_roles = $db->query("SELECT id FROM tbl_user_roles WHERE id != 5")->fetch_all(MYSQLI_ASSOC);
    $seeded = 0;
    foreach (array_keys(APP_MODULES) as $mk) {
        foreach ($all_roles as $r) {
            $rid = (int)$r['id'];
            $enabled = ($rid == 1 && $mk !== 'student_exams') ? 0 : 1;
            $chk = $db->query("SELECT 1 FROM tbl_module_permissions WHERE module_key='$mk' AND role_id=$rid LIMIT 1");
            if ($chk && $chk->num_rows === 0) {
                $db->query("INSERT INTO tbl_module_permissions (module_key, role_id, is_enabled) VALUES ('$mk', $rid, $enabled)");
                $seeded++;
            }
        }
    }
    echo json_encode(['status'=>'success','seeded'=>$seeded]); exit;
}

if ($action === 'toggle') {
    $module    = trim($_POST['module_key'] ?? '');
    $role_id   = (int)($_POST['role_id']   ?? 0);
    $is_enabled = (int)($_POST['is_enabled'] ?? 0);

    $valid_modules = array_keys(APP_MODULES);
    if (!in_array($module, $valid_modules) || !$role_id) {
        echo json_encode(['status'=>'error','message'=>'Invalid module or role']); exit;
    }

    $stmt = $db->prepare("INSERT INTO tbl_module_permissions (module_key, role_id, is_enabled)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE is_enabled = VALUES(is_enabled)");
    $stmt->bind_param("sii", $module, $role_id, $is_enabled);

    if ($stmt->execute()) echo json_encode(['status'=>'success']);
    else echo json_encode(['status'=>'error','message'=>$stmt->error]);
    exit;
}

echo json_encode(['status'=>'error','message'=>"Unknown action"]);
