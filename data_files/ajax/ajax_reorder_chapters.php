<?php
session_start();
include('../config/db.php');
include('../config/cache.php');

header('Content-Type: application/json');

$usr_code = $_SESSION['usr_code'] ?? '';
if (empty($usr_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$data       = json_decode(file_get_contents('php://input'), true);
$course_id  = intval($data['course_id']   ?? 0);
$chapterIds = $data['chapter_ids'] ?? [];

if (!$course_id || empty($chapterIds) || !is_array($chapterIds)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

// Verify this course belongs to the requesting instructor
$ownerCheck = mysqli_query($db,
    "SELECT id FROM tbl_courses WHERE id = $course_id AND instructor_id = '$usr_code' LIMIT 1"
);
if (mysqli_num_rows($ownerCheck) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Update `order` for each chapter in the submitted sequence
foreach ($chapterIds as $index => $chapterId) {
    $chapterId = intval($chapterId);
    if (!$chapterId) continue;
    $newOrder = $index + 1;
    mysqli_query($db,
        "UPDATE tbl_course_chapters SET `order` = $newOrder
         WHERE id = $chapterId AND course_id = $course_id"
    );
    if (mysqli_errno($db)) {
        echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
        exit;
    }
}

DcmCache::delete('chapters_course_' . $course_id, 'ccm');

echo json_encode(['status' => 'success', 'message' => 'Chapters reordered']);
