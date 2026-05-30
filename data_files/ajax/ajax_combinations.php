<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me || $role != 5) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $_GET['action'] ?? ($body['action'] ?? '');
function resp(array $d): never { ob_clean(); echo json_encode($d); exit; }
function esc(mysqli $db,$v): string { return $db->real_escape_string(trim($v)); }

switch ($action) {

case 'list':
    $s = esc($db, $_GET['q'] ?? '');
    $st = esc($db, $_GET['stream'] ?? '');
    $w  = [];
    if ($s)  $w[] = "(combination_name LIKE '%$s%' OR combination_code LIKE '%$s%')";
    if ($st) $w[] = "stream_type='$st'";
    $where = $w ? 'WHERE '.implode(' AND ',$w) : '';
    $rows = $db->query("SELECT * FROM tbl_combinations $where ORDER BY stream_type, combination_code")->fetch_all(MYSQLI_ASSOC);
    resp(['status'=>'success','data'=>$rows]);

case 'get':
    $id  = (int)($_GET['id'] ?? 0);
    $row = $db->query("SELECT * FROM tbl_combinations WHERE combination_id=$id LIMIT 1")->fetch_assoc();
    resp(['status'=>$row?'success':'error','data'=>$row]);

case 'create':
    $code = strtoupper(esc($db, $body['combination_code'] ?? ''));
    $name = esc($db, $body['combination_name'] ?? '');
    $stream = in_array($body['stream_type']??'',['science','arts','business','general']) ? $body['stream_type'] : 'science';
    $subj = esc($db, $body['subjects'] ?? '');
    $desc = esc($db, $body['description'] ?? '');
    if (!$code || !$name) resp(['status'=>'error','message'=>'Code and name are required']);
    $chk = $db->query("SELECT combination_id FROM tbl_combinations WHERE combination_code='$code' LIMIT 1");
    if ($chk->num_rows) resp(['status'=>'error','message'=>'Combination code already exists']);
    $db->query("INSERT INTO tbl_combinations (combination_code,combination_name,stream_type,subjects,description,status) VALUES ('$code','$name','$stream','$subj','$desc','active')");
    resp(['status'=>'success','message'=>'Combination created','id'=>$db->insert_id]);

case 'update':
    $id   = (int)($body['id'] ?? 0);
    $code = strtoupper(esc($db, $body['combination_code'] ?? ''));
    $name = esc($db, $body['combination_name'] ?? '');
    $stream = in_array($body['stream_type']??'',['science','arts','business','general']) ? $body['stream_type'] : 'science';
    $subj = esc($db, $body['subjects'] ?? '');
    $desc = esc($db, $body['description'] ?? '');
    if (!$id || !$code || !$name) resp(['status'=>'error','message'=>'Missing required fields']);
    $chk = $db->query("SELECT combination_id FROM tbl_combinations WHERE combination_code='$code' AND combination_id<>$id LIMIT 1");
    if ($chk->num_rows) resp(['status'=>'error','message'=>'Code already used']);
    $db->query("UPDATE tbl_combinations SET combination_code='$code',combination_name='$name',stream_type='$stream',subjects='$subj',description='$desc' WHERE combination_id=$id");
    resp(['status'=>'success','message'=>'Combination updated']);

case 'toggle_status':
    $id = (int)($body['id'] ?? 0);
    if (!$id) resp(['status'=>'error','message'=>'Invalid ID']);
    $db->query("UPDATE tbl_combinations SET status=IF(status='active','inactive','active') WHERE combination_id=$id");
    $row = $db->query("SELECT status FROM tbl_combinations WHERE combination_id=$id")->fetch_assoc();
    resp(['status'=>'success','new_status'=>$row['status']]);

case 'delete':
    $id = (int)($body['id'] ?? 0);
    if (!$id) resp(['status'=>'error','message'=>'Invalid ID']);
    $used = (int)$db->query("SELECT COUNT(*) FROM tbl_student_profiles WHERE combination_id=$id")->fetch_row()[0];
    if ($used) resp(['status'=>'error','message'=>"Cannot delete — used by $used student profile(s)"]);
    $db->query("DELETE FROM tbl_combinations WHERE combination_id=$id");
    resp(['status'=>'success','message'=>'Combination deleted']);

default:
    resp(['status'=>'error','message'=>'Unknown action']);
}
