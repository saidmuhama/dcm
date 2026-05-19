<?php
include('../config/db.php');
include('../config/dump.php');
session_start();

header('Content-Type: application/json');

// ===== CONFIG =====
// $API_KEY = App::getBunnyNetApiKey();

// ===== GET VALUES =====
$lesson_id     = $_POST['lesson_id'] ?? '';
$title         = $_POST['lesson_title'] ?? '';
$description   = $_POST['description'] ?? '';
$chapter_id    = $_POST['chapter_id'] ?? '';
$instructor_id = $_SESSION['usr_code'] ?? '';
$content_type  = $_POST['content_type'] ?? '';

$isDownloadable = $_POST['isDownloadable'] ?? 0;
$enableDiscussions = $_POST['enableDiscussions'] ?? 0;
$isFreePreviewLesson = $_POST['isFreePreviewLesson'] ?? 0;

if(empty($lesson_id))
{
    echo json_encode(["status"=>"error","message"=>"Lesson ID missing"]);
    exit;
}

// ── AUDIO THUMBNAIL (handled before main file logic) ────────
$thumbnail_set_sql = '';
$thumbnail_url_val = null;

if (!empty($_FILES['lesson_thumbnail']['tmp_name'])) {
    $tfile     = $_FILES['lesson_thumbnail'];
    $tExt      = strtolower(pathinfo($tfile['name'], PATHINFO_EXTENSION));
    $allowedImg = ['jpg','jpeg','png','webp','gif'];
    if (in_array($tExt, $allowedImg) && $tfile['size'] < 5 * 1024 * 1024) {
        $tName = 'thumb_' . $lesson_id . '_' . time() . '.' . $tExt;
        $tDir  = __DIR__ . '/../uploads/lessons/';
        if (!is_dir($tDir)) mkdir($tDir, 0755, true);
        if (move_uploaded_file($tfile['tmp_name'], $tDir . $tName)) {
            $thumbnail_url_val = 'uploads/lessons/' . $tName;
            $thumbnail_set_sql = ', lesson_thumbnail = ?';
        }
    }
} elseif (!empty($_POST['remove_thumbnail'])) {
    $thumbnail_url_val = '';
    $thumbnail_set_sql = ', lesson_thumbnail = ?';
}

if($content_type == "video")
{
    // ===== GET LESSON + COURSE =====
    $stmt = $db->prepare("
        SELECT l.video_id, c.library_id, c.library_key
        FROM tbl_course_chapter_lessons l
        JOIN tbl_courses c ON l.course_id = c.id
        WHERE l.id = ?
    ");
    $stmt->bind_param("i", $lesson_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if($res->num_rows == 0){
        echo json_encode(["status"=>"error","message"=>"Lesson not found"]);
        exit;
    }

    $row = $res->fetch_assoc();

    $video_id    = $row['video_id'];
    $library_id  = $row['library_id'];
    $library_key = $row['library_key'];

    // ===== CHECK FILE =====
    if(!isset($_FILES['file'])){
        echo json_encode(["status"=>"error","message"=>"No file uploaded"]);
        exit;
    }

    $filePath = $_FILES['file']['tmp_name'];

    // ===== UPLOAD TO BUNNY =====
    $url = "https://video.bunnycdn.com/library/$library_id/videos/$video_id";

    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "AccessKey: $library_key",
        "Content-Type: application/octet-stream"
    ]);

    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($filePath));

    $response = curl_exec($ch);

    if(curl_errno($ch)){
        echo json_encode([
            "status"=>"error",
            "message"=>"Upload error: ".curl_error($ch)
        ]);
        exit;
    }

    curl_close($ch);

    // ===== GENERATE PLAYER URL =====
    $video_url = "https://iframe.mediadelivery.net/embed/$library_id/$video_id";

    // ===== UPDATE DB =====
    $stmt = $db->prepare("UPDATE tbl_course_chapter_lessons
    SET lesson_title=?, description=?, file_path=?, isDownloadable=?, enableDiscussions=?, isFreePreviewLesson=? {$thumbnail_set_sql}
    WHERE id=? AND instructor_id=?");

    if ($thumbnail_url_val !== null) {
        $stmt->bind_param("sssiiisis",
            $title, $description, $video_url, $isDownloadable, $enableDiscussions, $isFreePreviewLesson,
            $thumbnail_url_val, $lesson_id, $instructor_id);
    } else {
        $stmt->bind_param("sssiiiis",
            $title, $description, $video_url, $isDownloadable, $enableDiscussions, $isFreePreviewLesson,
            $lesson_id, $instructor_id);
    }
    if($stmt->execute()){
        echo json_encode([
            "status"=>"success",
            "message"=>"Video uploaded & lesson updated"
        ]);
    }else{
        echo json_encode([
            "status"=>"error",
            "message"=>"DB update failed",
            "error"=>$stmt->error
        ]);
    }
}
else 
{
    // ===== CHECK FILE =====
    if(!isset($_FILES['file'])){
        echo json_encode([
            "status"=>"error",
            "message"=>"No file uploaded"
        ]);
        exit;
    }

    $file = $_FILES['file'];

    // ===== ORIGINAL FILE =====
    $originalName = basename($file['name']);

    // sanitize
    $originalName = preg_replace("/[^a-zA-Z0-9\.\-_]/", "", $originalName);

    // unique file name
    $fileName = time() . "_" . $originalName;

    $tmpPath = $file['tmp_name'];

    // ===== VALIDATE EXTENSION =====
    $allowed = ['pdf','ppt','pptx','mp3','mp4'];

    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if(!in_array($ext, $allowed)){
        echo json_encode([
            "status"=>"error",
            "message"=>"Only PDF, PPT, PPTX, MP3, MP4 allowed"
        ]);
        exit;
    }

    // ===== BUNNY STORAGE CONFIG =====
    $storageZoneName = App::getBunnyStorageZone();
    $apiKey          = App::getBunnyStorageZoneAccessKey();

    // folder inside bunny storage
    $remotePath = "DCM";
    $course_id  = App::getWhatFromWHere('course_id','tbl_course_chapter_lessons','id',$lesson_id);
    $remotePath = App::getWhatFromWHere('title','tbl_courses','id',$course_id);

    $upload = App::uploadFileToBunnyStorage(
        $storageZoneName,
        $tmpPath,
        $remotePath,
        $apiKey,
        $fileName // ✅ IMPORTANT
    );

    // ===== CHECK UPLOAD =====
    if($upload['status'] !== "success"){

        echo json_encode([
            "status"=>"error",
            "message"=>"Failed to upload to Bunny Storage",
            "response"=>$upload
        ]);

        exit;
    }

    // pullzone
    $cdnBase = "https://dcmbank.b-cdn.net";

    // BETTER:
    $file_url = $cdnBase . "/" . $remotePath . "/" . $fileName;

    // ===== UPDATE DB =====
    $stmt = $db->prepare("
        UPDATE tbl_course_chapter_lessons
        SET lesson_title=?, description=?, file_path=?,
            isDownloadable=?, enableDiscussions=?, isFreePreviewLesson=?
            {$thumbnail_set_sql}
        WHERE id=? AND instructor_id=?
    ");

    if ($thumbnail_url_val !== null) {
        $stmt->bind_param("sssiiisis",
            $title, $description, $file_url, $isDownloadable, $enableDiscussions, $isFreePreviewLesson,
            $thumbnail_url_val, $lesson_id, $instructor_id);
    } else {
        $stmt->bind_param("sssiiiis",
            $title, $description, $file_url, $isDownloadable, $enableDiscussions, $isFreePreviewLesson,
            $lesson_id, $instructor_id);
    }

    if($stmt->execute()){
        echo json_encode([
            "status"=>"success",
            "message"=>"File uploaded & lesson updated",
            "file_url"=>$file_url
        ]);
    }else{
        echo json_encode([
            "status"=>"error",
            "message"=>"DB update failed",
            "error"=>$stmt->error
        ]);
    }
}