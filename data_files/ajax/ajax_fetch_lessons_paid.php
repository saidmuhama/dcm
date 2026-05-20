<?php
session_start();
include('../config/db.php');

header('Content-Type: application/json');

$course_id = intval($_GET['course_id'] ?? 0);
$user_id   = $_SESSION['usr_code'] ?? 0;

if(!$course_id){
    echo json_encode([
        "status" => "error",
        "message" => "Invalid course"
    ]);
    exit;
}

/* =========================
   ✅ CHECK IF USER PAID
========================= */
$is_paid = false;

if($user_id){

    $paid_q = mysqli_query($db,"
        SELECT o.id 
        FROM tbl_orders o
        INNER JOIN tbl_order_items oi 
            ON oi.order_id = o.id
        WHERE o.user_id = '$user_id'
        AND o.payment_status = 'paid'
        AND oi.course_id = '$course_id'
        LIMIT 1
    ");

    if(mysqli_num_rows($paid_q) > 0){
        $is_paid = true;
    }
}

/* =========================
   ✅ FETCH CHAPTERS
========================= */
$chapters_q = mysqli_query($db,"
    SELECT id, chapter_title
    FROM tbl_course_chapters
    WHERE course_id = '$course_id'
    ORDER BY id ASC
");

$data = [];

while($chapter = mysqli_fetch_assoc($chapters_q)){

    /* =========================
       ✅ FETCH LESSONS
    ========================= */
    $lessons_q = mysqli_query($db,"
        SELECT id, lesson_title, description, file_path,
               video_duration, isFreePreviewLesson,
               storage, content_type, video_id, library_id, sort_order,
               lesson_thumbnail
        FROM tbl_course_chapter_lessons
        WHERE chapter_id = '{$chapter['id']}'
        AND course_id = '$course_id'
        ORDER BY sort_order ASC, id ASC
    ");

    $lessons = [];

    while($lesson = mysqli_fetch_assoc($lessons_q)){
        $lessons[] = $lesson;
    }

    // OPTIONAL: skip empty chapters (better UX)
    if(count($lessons) > 0){
        $chapter['lessons'] = $lessons;
        $data[] = $chapter;
    }
}

/* =========================
   ✅ FETCH COMPLETED LESSONS
========================= */
$completed_lessons = [];

if($user_id){
    $progress_q = mysqli_query($db,"
        SELECT lesson_id 
        FROM tbl_course_progress
        WHERE user_id='$user_id'
        AND course_id='$course_id'
        AND watched=1
    ");

    while($p = mysqli_fetch_assoc($progress_q)){
        $completed_lessons[] = (int)$p['lesson_id'];
    }
}

/* =========================
   ✅ FINAL RESPONSE
========================= */

echo json_encode([
    "status" => "success",
    "is_paid" => $is_paid,
    "completed_lessons" => $completed_lessons,
    "data" => $data
]);