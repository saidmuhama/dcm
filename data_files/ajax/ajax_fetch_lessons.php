<?php
include('../config/db.php');

$course_id = intval($_GET['course_id'] ?? 0);

if(!$course_id){
    echo json_encode(["status"=>"error","message"=>"Invalid course"]);
    exit;
}

/* FETCH CHAPTERS */
$chapters_q = mysqli_query($db,"
    SELECT id, chapter_title
    FROM tbl_course_chapters
    WHERE course_id = '$course_id'
    AND id IN(SELECT chapter_id FROM tbl_course_chapter_lessons)
    ORDER BY id ASC
");

$data = [];

while($chapter = mysqli_fetch_assoc($chapters_q)){

    /* FETCH LESSONS FOR THIS CHAPTER */
    $lessons_q = mysqli_query($db,"
        SELECT id, lesson_title, description, file_path,
               video_duration, isFreePreviewLesson
        FROM tbl_course_chapter_lessons
        WHERE chapter_id = '{$chapter['id']}'
        AND course_id = '$course_id'
        ORDER BY id ASC
    ");

    $lessons = [];

    while($lesson = mysqli_fetch_assoc($lessons_q)){
        $lessons[] = $lesson;
    }

    $chapter['lessons'] = $lessons;

    $data[] = $chapter;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);