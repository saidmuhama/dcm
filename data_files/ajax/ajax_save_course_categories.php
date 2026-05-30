<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

$me = $_SESSION['usr_code'] ?? '';
if (!$me) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$body      = json_decode(file_get_contents('php://input'), true) ?? [];
$course_id = intval($body['course_id'] ?? 0);
$ids_raw   = $body['category_ids'] ?? [];
$cat_ids   = array_values(array_unique(array_filter(array_map('intval', (array)$ids_raw))));

if (!$course_id) { echo json_encode(['status'=>'error','message'=>'Invalid course']); exit; }

/* Verify the course belongs to this instructor (or admin) */
$role = (int)($_SESSION['user_role'] ?? 0);
if ($role != 5) {
    $chk = $db->prepare("SELECT id FROM tbl_courses WHERE id=? AND instructor_id=? AND deleted_at IS NULL LIMIT 1");
    $chk->bind_param("is", $course_id, $me);
    $chk->execute();
    if (!$chk->get_result()->num_rows) {
        echo json_encode(['status'=>'error','message'=>'Course not found or access denied']);
        exit;
    }
}

/* Replace all category assignments atomically */
$db->query("DELETE FROM tbl_course_category_map WHERE course_id=$course_id");

if (!empty($cat_ids)) {
    $ins = $db->prepare("INSERT IGNORE INTO tbl_course_category_map (course_id, category_id) VALUES (?, ?)");
    foreach ($cat_ids as $cid) {
        $ins->bind_param("ii", $course_id, $cid);
        $ins->execute();
    }
}

/* Keep tbl_courses.category_id in sync with the first (primary) category */
$primary = $cat_ids[0] ?? null;
$pval    = $primary ? $primary : 'NULL';
$db->query("UPDATE tbl_courses SET category_id=$pval WHERE id=$course_id");

echo json_encode([
    'status'      => 'success',
    'message'     => 'Categories updated',
    'saved'       => count($cat_ids),
    'category_ids'=> $cat_ids,
]);
