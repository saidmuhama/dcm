<?php
session_start();
include('../config/db.php');
include('../config/cache.php');

header('Content-Type: application/json');

$data       = json_decode(file_get_contents('php://input'), true);
$lesson_id  = intval($data['lesson_id']  ?? 0);
$chapter_id = intval($data['chapter_id'] ?? 0);

if (!$lesson_id || !$chapter_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid parameters']);
    exit;
}

$usr_code = $_SESSION['usr_code'] ?? '';
if (empty($usr_code)) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Verify lesson belongs to a course owned by this instructor
$check = mysqli_query($db, "
    SELECT l.id
    FROM tbl_course_chapter_lessons l
    JOIN tbl_course_chapters ch ON ch.id = l.chapter_id
    JOIN tbl_courses c ON c.id = ch.course_id
    WHERE l.id = $lesson_id
      AND c.instructor_id = '$usr_code'
    LIMIT 1
");

if (mysqli_num_rows($check) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Verify the target chapter belongs to the same course
$courseCheck = mysqli_query($db, "
    SELECT ch.id
    FROM tbl_course_chapters ch
    JOIN tbl_courses c ON c.id = ch.course_id
    JOIN tbl_course_chapter_lessons l ON l.chapter_id = (
        SELECT chapter_id FROM tbl_course_chapter_lessons WHERE id = $lesson_id LIMIT 1
    )
    WHERE ch.id = $chapter_id
      AND c.instructor_id = '$usr_code'
    LIMIT 1
");

if (mysqli_num_rows($courseCheck) === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Target chapter not found in this course']);
    exit;
}

$result = mysqli_query($db, "UPDATE tbl_course_chapter_lessons SET chapter_id = $chapter_id WHERE id = $lesson_id");

if ($result) {
    // Invalidate cache for this course
    $cRow = mysqli_fetch_assoc(mysqli_query($db, "SELECT course_id FROM tbl_course_chapter_lessons WHERE id = $lesson_id LIMIT 1"));
    if ($cRow) DcmCache::delete('chapters_course_' . $cRow['course_id'], 'ccm');
    echo json_encode(['status' => 'success', 'message' => 'Lesson moved successfully']);
} else {
    echo json_encode(['status' => 'error', 'message' => mysqli_error($db)]);
}
