<?php
include "../config/db.php";
session_start();

$user_id = $_SESSION['usr_code'];

$data = [];

// ✅ Get PAID courses
$sql = "SELECT DISTINCT oi.course_id, c.title
        FROM tbl_orders o
        JOIN tbl_order_items oi ON o.id = oi.order_id
        JOIN tbl_courses c ON c.id = oi.course_id
        WHERE o.user_id = '$user_id'
        AND o.payment_status = 'paid'";

$result = mysqli_query($db, $sql);

while($row = mysqli_fetch_assoc($result)){

    $course_id = $row['course_id'];

    // ✅ TOTAL LESSONS
    $totalLessonsQuery = mysqli_query($db,
        "SELECT COUNT(*) as total 
         FROM tbl_course_chapter_lessons 
         WHERE course_id = '$course_id'"
    );
    $totalLessons = mysqli_fetch_assoc($totalLessonsQuery)['total'];

    // ✅ WATCHED LESSONS
    $watchedQuery = mysqli_query($db,
        "SELECT COUNT(DISTINCT lesson_id) as done
         FROM tbl_course_progress
         WHERE user_id = '$user_id'
         AND course_id = '$course_id'
         AND watched = 1"
    );
    $watchedLessons = mysqli_fetch_assoc($watchedQuery)['done'];

    // ✅ TOTAL CHAPTERS
    $chaptersQuery = mysqli_query($db,
        "SELECT COUNT(*) as total 
         FROM tbl_course_chapters 
         WHERE course_id = '$course_id'"
    );
    $totalChapters = mysqli_fetch_assoc($chaptersQuery)['total'];

    // ✅ COMPLETED CHAPTERS (all lessons in chapter watched)
    $completedChaptersQuery = mysqli_query($db,
        "SELECT COUNT(*) as completed FROM (
            SELECT ch.id
            FROM tbl_course_chapters ch
            JOIN tbl_course_chapter_lessons l 
                ON ch.id = l.chapter_id
            LEFT JOIN tbl_course_progress p 
                ON p.lesson_id = l.id 
                AND p.user_id = '$user_id'
                AND p.watched = 1
            WHERE ch.course_id = '$course_id'
            GROUP BY ch.id
            HAVING COUNT(l.id) = COUNT(p.lesson_id)
        ) as completed_chapters"
    );

    $completedChapters = mysqli_fetch_assoc($completedChaptersQuery)['completed'];

    // ✅ PROGRESS %
    $progress = ($totalLessons > 0)
        ? round(($watchedLessons / $totalLessons) * 100)
        : 0;

    $data[] = [
        "course_id" => $course_id,
        "title" => $row['title'],
        "total_lessons" => $totalLessons,
        "watched_lessons" => $watchedLessons,
        "total_chapters" => $totalChapters,
        "completed_chapters" => $completedChapters,
        "progress" => $progress
    ];
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);