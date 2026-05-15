<?php
include "../config/db.php";
session_start();

$user_id = $_SESSION['usr_code'];

$data = [];

// ✅ GET PAID COURSES
$sql = "SELECT DISTINCT oi.course_id, c.title, c.thumbnail, c.instructor_id
        FROM tbl_orders o
        JOIN tbl_order_items oi ON o.id = oi.order_id
        JOIN tbl_courses c ON c.id = oi.course_id
        WHERE o.user_id = '$user_id'
        AND o.payment_status = 'paid'
        ORDER BY o.id DESC";

$result = mysqli_query($db, $sql);

while($row = mysqli_fetch_assoc($result)){

    $course_id = $row['course_id'];

    // ✅ TOTAL LESSONS
    $totalLessons = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(*) as total FROM tbl_course_chapter_lessons 
         WHERE course_id = '$course_id'"
    ))['total'];

    // ✅ WATCHED LESSONS
    $watchedLessons = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(DISTINCT lesson_id) as done 
         FROM tbl_course_progress
         WHERE user_id = '$user_id'
         AND course_id = '$course_id'
         AND watched = 1"
    ))['done'];

    // ✅ TOTAL CHAPTERS
    $totalChapters = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(*) as total 
         FROM tbl_course_chapters 
         WHERE course_id = '$course_id'"
    ))['total'];

    // ✅ PROGRESS %
    $progress = ($totalLessons > 0)
        ? round(($watchedLessons / $totalLessons) * 100)
        : 0;

    // ✅ STATUS
    if($progress == 100){
        $status = "Completed";
    } elseif($progress > 0){
        $status = "In-Progress";
    } else {
        $status = "Not Started";
    }

    // ✅ DISCUSSIONS COUNT
    $discussions = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(*) as total 
         FROM tbl_course_discussions 
         WHERE course_id = '$course_id'"
    ))['total'] ?? 0;

    // ✅ UPCOMING (optional mock)
    $upcoming = date("d-m-Y", strtotime("+7 days"));

    $data[] = [
        "course_id" => $course_id,
        "title" => $row['title'],
        "thumbnail" => $row['thumbnail'],
        "total_lessons" => $totalLessons,
        "watched_lessons" => $watchedLessons,
        "total_chapters" => $totalChapters,
        "progress" => $progress,
        "status" => $status,
        "discussions" => $discussions,
        "upcoming" => $upcoming
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);