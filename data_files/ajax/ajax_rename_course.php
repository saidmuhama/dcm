<?php
session_start();
include('../config/db.php');
include('../config/dump.php');

header('Content-Type: application/json');

// Allow extra time for large storage folders (many files)
set_time_limit(300);
ini_set('memory_limit', '256M');

$user_id = $_SESSION['usr_code'] ?? '';
if (!$user_id) {
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$data      = json_decode(file_get_contents("php://input"), true);
$course_id = intval($data['course_id'] ?? 0);
$newTitle  = trim($data['title'] ?? '');

if (!$course_id || $newTitle === '') {
    echo json_encode(["status"=>"error","message"=>"Course ID and title are required"]);
    exit;
}
if (strlen($newTitle) < 3 || strlen($newTitle) > 120) {
    echo json_encode(["status"=>"error","message"=>"Title must be between 3 and 120 characters"]);
    exit;
}

// Verify instructor owns this course and fetch current title + library_id
$stmt = $db->prepare(
    "SELECT id, title, library_id FROM tbl_courses
     WHERE id = ? AND instructor_id = ? AND deleted_at IS NULL LIMIT 1"
);
$stmt->bind_param("is", $course_id, $user_id);
$stmt->execute();
$course = $stmt->get_result()->fetch_assoc();

if (!$course) {
    echo json_encode(["status"=>"error","message"=>"Course not found or access denied"]);
    exit;
}

// Storage folder slugs — must match ajax_update_lesson.php pattern exactly
$oldFolder = preg_replace('/[^a-zA-Z0-9\-]/', '_', trim($course['title'] ?: 'DCM'));
$newFolder = preg_replace('/[^a-zA-Z0-9\-]/', '_', trim($newTitle ?: 'DCM'));

// ── 1. Update database title ───────────────────────────────────────────────
$upd = $db->prepare("UPDATE tbl_courses SET title = ? WHERE id = ?");
$upd->bind_param("si", $newTitle, $course_id);
if (!$upd->execute()) {
    echo json_encode(["status"=>"error","message"=>"Failed to update database: ".$db->error]);
    exit;
}

// ── 2. Rename Bunny.net Stream library ────────────────────────────────────
$streamUpdated = false;
$streamMessage = '';
if (!empty($course['library_id'])) {
    $apiKey        = App::getBunnyNetApiKey();
    $streamResult  = App::renameVideoLibrary($course['library_id'], $newTitle, $apiKey);
    $streamUpdated = ($streamResult['status'] === 'success');
    if (!$streamUpdated) {
        $streamMessage = $streamResult['message'] ?? 'Unknown stream error';
    }
}

// ── 3. Rename Bunny.net Storage folder ────────────────────────────────────
$storageUpdated = false;
$storageMessage = '';
$storageDetails = [];

if ($oldFolder !== $newFolder) {
    $storageZone   = App::getBunnyStorageZone();
    $storageKey    = App::getBunnyStorageZoneAccessKey();
    $storageResult = App::renameBunnyStorageFolder($storageZone, $oldFolder, $newFolder, $storageKey);

    $storageUpdated = in_array($storageResult['status'], ['success', 'partial']);
    $storageMessage = $storageResult['message'] ?? '';
    if (!empty($storageResult['errors'])) {
        $storageDetails = $storageResult['errors'];
    }

    // ── 4. Update file_path CDN URLs in lesson records ────────────────────
    if ($storageUpdated && ($storageResult['moved'] ?? 0) > 0) {
        $cdnBase      = "https://" . $storageZone . ".b-cdn.net/";
        $oldUrl       = $cdnBase . $oldFolder . "/";
        $newUrl       = $cdnBase . $newFolder . "/";
        $oldUrlEsc    = $db->real_escape_string($oldUrl);
        $newUrlEsc    = $db->real_escape_string($newUrl);
        $db->query(
            "UPDATE tbl_course_chapter_lessons
             SET file_path = REPLACE(file_path, '{$oldUrlEsc}', '{$newUrlEsc}')
             WHERE course_id = " . intval($course_id)
        );
    }
} else {
    $storageUpdated = true;
    $storageMessage = 'Folder slug unchanged — no migration needed';
}

// ── Response ──────────────────────────────────────────────────────────────
echo json_encode([
    "status"          => "success",
    "message"         => "Course renamed successfully",
    "bunny_updated"   => $streamUpdated && $storageUpdated,
    "stream_updated"  => $streamUpdated,
    "stream_message"  => $streamMessage,
    "storage_updated" => $storageUpdated,
    "storage_message" => $storageMessage,
    "storage_errors"  => $storageDetails,
]);
