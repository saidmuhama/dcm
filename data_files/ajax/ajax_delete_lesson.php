<?php

include('../config/db.php');
include('../config/dump.php');

session_start();

header('Content-Type: application/json');

// =========================
// GET JSON INPUT
// =========================

$lesson_id     = $_POST['lesson_id'] ?? '';
$instructor_id = $_SESSION['usr_code'] ?? '';


// =========================
// AUTH CHECK
// =========================
if (!isset($_SESSION['usr_code'])) {

    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}


// =========================
// VALIDATION
// =========================
if (empty($lesson_id)) {

    echo json_encode([
        "status" => "error",
        "message" => "Missing lesson id"
    ]);
    exit;
}


// =========================
// FETCH LESSON
// =========================
$stmt = $db->prepare("
    SELECT *
    FROM tbl_course_chapter_lessons
    WHERE id=? AND instructor_id=?
");

$stmt->bind_param("is", $lesson_id, $instructor_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    echo json_encode([
        "status" => "error",
        "message" => "Lesson not found"
    ]);
    exit;
}

$row = $result->fetch_assoc();

// =========================
// LESSON DATA
// =========================
$video_id     = $row['video_id'];
$course_id    = $row['course_id'];
$title        = $row['lesson_title'];
$library_id   = $row['library_id'];
$content_type = $row['content_type'];
$file_path    = $row['file_path'];


// =========================
// GET API KEYS
// =========================
$libraryKey = App::getWhatFromWHere(
    'library_key',
    'tbl_courses',
    'id',
    $course_id
);

$storageZone = App::getBunnyStorageZone();
$storageKey  = App::getBunnyStorageZoneAccessKey();


// =========================
// DELETE RESOURCE
// =========================
if($content_type === "video")
{
    // =========================
    // DELETE VIDEO FROM STREAM
    // =========================
    $res = App::deleteVideo(
        $library_id,
        $video_id,
        $libraryKey
    );
}
else
{
    // =========================
    // EXTRACT STORAGE PATH
    // from:
    // https://dcmbank.b-cdn.net/DCM/file.pdf
    // to:
    // DCM/file.pdf
    // =========================

    $parsed = parse_url($file_path);

    $storageFilePath = ltrim($parsed['path'], "/");

    // =========================
    // DELETE FROM STORAGE
    // =========================
    $res = App::deleteBunnyStorageFile(
        $storageZone,
        $storageFilePath,
        $storageKey
    );
}


// normalize
$res = is_string($res)
    ? json_decode($res, true)
    : $res;


// =========================
// CHECK DELETE RESPONSE
// =========================
if(isset($res['status']) && $res['status'] === "success")
{
    // =========================
    // DELETE DB RECORD
    // =========================
    $deleteStmt = $db->prepare("
        DELETE FROM tbl_course_chapter_lessons
        WHERE id=? AND instructor_id=?
    ");

    $deleteStmt->bind_param(
        "is",
        $lesson_id,
        $instructor_id
    );

    if($deleteStmt->execute())
    {
        echo json_encode([
            "status"  => "success",
            "message" => "Lesson deleted successfully"
        ]);
    }
    else
    {
        echo json_encode([
            "status"   => "error",
            "message"  => "DB deletion failed",
            "db_error" => $deleteStmt->error
        ]);
    }
}
else
{
    echo json_encode([
        "status"  => "error",
        "message" => "Resource deletion failed",
        "response"=> $res
    ]);
}