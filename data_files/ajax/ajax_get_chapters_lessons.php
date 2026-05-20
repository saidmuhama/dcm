<?php
include('../config/db.php');

$course_id = $_GET['course_id'] ?? '';

if(empty($course_id)){
    echo json_encode([]);
    exit;
}

$chapters = [];

// GET CHAPTERS
$sql = mysqli_query($db, "SELECT * FROM tbl_course_chapters WHERE course_id='$course_id' ORDER BY `order` ASC, id ASC");

while($row = mysqli_fetch_assoc($sql)){

    $chapter_id = $row['id'];

    // GET LESSONS PER CHAPTER
    $lessons_sql = mysqli_query($db, "SELECT * FROM tbl_course_chapter_lessons WHERE chapter_id='$chapter_id' AND status='active' ORDER BY sort_order ASC, id ASC");
    
    $lessons = [];
    while($lesson = mysqli_fetch_assoc($lessons_sql)){
        $lessons[] = $lesson;
    }

    $row['lessons'] = $lessons;
    $chapters[] = $row;
}

echo json_encode($chapters);