<?php
include('../config/db.php');
include('../config/dump.php');
session_start();

header('Content-Type: application/json');


// ================= GET VALUES =================
$title          = trim($_POST['lesson_title'] ?? '');
$course_id      = $_POST['course_id'] ?? '';
$chapter_id     = $_POST['chapter_id'] ?? '';
$instructor_id  = $_SESSION['usr_code'] ?? '';
$content_type   = $_POST['content_type'] ?? '';
$video_id = null;
$library_id = null;
if($content_type != 'video')
{
    // ======================================================
        // ✅ STEP 3: INSERT LESSON WITH VIDEO ID
        // ======================================================
        $stmt = $db->prepare("INSERT INTO tbl_course_chapter_lessons 
        (lesson_title, instructor_id, content_type, course_id, chapter_id, video_id,library_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            echo json_encode([
                "status" => "error",
                "message" => "DB prepare failed",
                "error" => $db->error
            ]);
            exit;
        }

        $stmt->bind_param(
            "sssssss",
            $title,
            $instructor_id,
            $content_type,
            $course_id,
            $chapter_id,
            $video_id,
            $library_id
        );


        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Lesson created successfully",
                "video_id" => $video_id
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "DB insert failed",
                "db_error" => $stmt->error
            ]);
        }
}
else 
{
        // ===== CONFIG =====
        $BUNNY_API_KEY   = App::getWhatFromWHere('library_key', 'tbl_courses','id',$course_id);

        // ================= VALIDATION =================
        if (empty($title)) {
            echo json_encode(["status" => "error", "message" => "Lesson title required"]);
            exit;
        }

        if (empty($instructor_id)) {
            echo json_encode(["status" => "error", "message" => "User not logged in"]);
            exit;
        }

        // ======================================================
        // ✅ STEP 1: GET LIBRARY_ID FROM COURSE
        // ======================================================
        $stmtCourse = $db->prepare("SELECT library_id FROM tbl_courses WHERE id = ?");
        $stmtCourse->bind_param("i", $course_id);
        $stmtCourse->execute();
        $res = $stmtCourse->get_result();

        if ($res->num_rows == 0) {
            echo json_encode(["status" => "error", "message" => "Course not found"]);
            exit;
        }

        $course = $res->fetch_assoc();
        $library_id = $course['library_id'];

        if (empty($library_id)) {
            echo json_encode(["status" => "error", "message" => "Course has no Bunny library ID"]);
            exit;
        }

        // ======================================================
        // ✅ STEP 2: CREATE VIDEO IN BUNNY
        // ======================================================
        $url = "https://video.bunnycdn.com/library/$library_id/videos";

        $payload = [
            "title" => $title
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                "AccessKey: $BUNNY_API_KEY",
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS => json_encode($payload)
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo json_encode([
                "status" => "error",
                "message" => "Curl error: " . curl_error($ch)
            ]);
            exit;
        }

        curl_close($ch);

        // decode safely
        $result = json_decode($response, true);

        if (!is_array($result)) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid Bunny response",
                "raw_response" => $response
            ]);
            exit;
        }

        // ======================================================
        // ❌ CHECK VIDEO CREATED
        // ======================================================
        if (empty($result['guid'])) {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to create video on Bunny",
                "bunny_response" => $result,
                "library_id" => $library_id
            ]);
            exit;
        }

        $video_id = $result['guid'];

        // ======================================================
        // ✅ STEP 3: INSERT LESSON WITH VIDEO ID
        // ======================================================
        $stmt = $db->prepare("INSERT INTO tbl_course_chapter_lessons 
        (lesson_title, instructor_id, content_type, course_id, chapter_id, video_id,library_id) 
        VALUES (?, ?, ?, ?, ?, ?, ?)");

        if (!$stmt) {
            echo json_encode([
                "status" => "error",
                "message" => "DB prepare failed",
                "error" => $db->error
            ]);
            exit;
        }

        $stmt->bind_param(
            "sssssss",
            $title,
            $instructor_id,
            $content_type,
            $course_id,
            $chapter_id,
            $video_id,
            $library_id
        );


        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Lesson + Video created successfully",
                "video_id" => $video_id
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "DB insert failed",
                "db_error" => $stmt->error
            ]);
        }
}
