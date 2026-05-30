<?php
ob_start();
include('../config/db.php');
include('../config/dump.php');
session_start();
ob_clean();

header('Content-Type: application/json');

$course_id       = intval($_POST['course_id']       ?? 0);
$title           = trim($_POST['course_name']        ?? '');
$price           = trim($_POST['course_price']       ?? '0');
$discount        = trim($_POST['course_discount']    ?? '0');
$desc            = $_POST['course_description']      ?? '';
$cert            = intval($_POST['isCertificateOffered'] ?? 0);
$qna             = intval($_POST['isQandAEnabled']   ?? 0);
$old_course_name = trim($_POST['old_course_name']    ?? '');
$library_id      = trim($_POST['library_id']         ?? '');
/* Multi-category: read JSON array from course_category_ids field */
$_rawCatIds  = $_POST['course_category_ids'] ?? '[]';
$_catIdsArr  = array_values(array_unique(array_filter(array_map('intval', json_decode($_rawCatIds, true) ?: []))));
$category_id = $_catIdsArr[0] ?? null; // primary category kept in tbl_courses for back-compat

if (!$course_id || !$title) {
    echo json_encode(['status' => 'error', 'message' => 'Course ID and name are required']);
    exit;
}

/* ── Optional thumbnail upload ─────────────────────── */
$thumbnailPath = '';
if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === 0) {
    $dir  = '../uploads/';
    $name = time() . '_' . basename($_FILES['thumbnail']['name']);
    if (!is_dir($dir)) { mkdir($dir, 0755, true); }
    if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $dir . $name)) {
        $thumbnailPath = 'uploads/' . $name;
    }
}

/* ── Rename Bunny library if title changed ─────────── */
if (strtolower($old_course_name) !== strtolower($title) && !empty($library_id)) {
    try {
        $res = App::renameVideoLibrary($library_id, $title, App::getBunnyNetApiKey());
        if (($res['status'] ?? '') !== 'success') {
            echo json_encode(['status' => 'error', 'message' => 'Failed to rename video library: ' . ($res['message'] ?? 'unknown error')]);
            exit;
        }
    } catch (Throwable $e) {
        /* non-fatal — library rename failed but we still update the DB */
    }
}

/* ── Build UPDATE with prepared statement ──────────── */
$haThumb = $thumbnailPath !== '';
if ($haThumb) {
    $stmt = $db->prepare("UPDATE tbl_courses
        SET title=?, price=?, discount=?, description=?, thumbnail=?, certificate=?, qna=?, category_id=?
        WHERE id=?");
    if (!$stmt) { echo json_encode(['status'=>'error','message'=>'DB prepare failed: '.$db->error]); exit; }
    $stmt->bind_param('sssssiiii', $title, $price, $discount, $desc, $thumbnailPath, $cert, $qna, $category_id, $course_id);
} else {
    $stmt = $db->prepare("UPDATE tbl_courses
        SET title=?, price=?, discount=?, description=?, certificate=?, qna=?, category_id=?
        WHERE id=?");
    if (!$stmt) { echo json_encode(['status'=>'error','message'=>'DB prepare failed: '.$db->error]); exit; }
    $stmt->bind_param('ssssiiii', $title, $price, $discount, $desc, $cert, $qna, $category_id, $course_id);
}

if (!$stmt->execute()) {
    echo json_encode(['status' => 'error', 'message' => 'DB update failed: ' . $stmt->error]);
    exit;
}

/* ── Sync category map ──────────────────────────────────── */
if (!empty($_catIdsArr)) {
    /* Replace all category assignments for this course */
    $db->query("DELETE FROM tbl_course_category_map WHERE course_id=$course_id");
    $mapStmt = $db->prepare("INSERT IGNORE INTO tbl_course_category_map (course_id, category_id) VALUES (?, ?)");
    foreach ($_catIdsArr as $cid) {
        $mapStmt->bind_param("ii", $course_id, $cid);
        $mapStmt->execute();
    }
}

echo json_encode(['status' => 'success', 'message' => 'Course updated']);
