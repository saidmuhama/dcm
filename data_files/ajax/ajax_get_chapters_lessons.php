<?php
include('../config/db.php');
include('../config/cache.php');

$course_id = intval($_GET['course_id'] ?? 0);

if (!$course_id) { echo json_encode([]); exit; }

$cacheKey = 'chapters_course_' . $course_id;

$chapters = DcmCache::remember($cacheKey, 60, function() use ($db, $course_id) {
    $chapters = [];
    $sql = $db->query("SELECT * FROM tbl_course_chapters WHERE course_id='$course_id' ORDER BY `order` ASC, id ASC");
    while ($row = $sql->fetch_assoc()) {
        $chapter_id   = $row['id'];
        $lessons_sql  = $db->query("SELECT * FROM tbl_course_chapter_lessons WHERE chapter_id='$chapter_id' AND status='active' ORDER BY sort_order ASC, id ASC");
        $lessons      = [];
        while ($lesson = $lessons_sql->fetch_assoc()) {
            $lessons[] = $lesson;
        }
        $row['lessons'] = $lessons;
        $chapters[]     = $row;
    }
    return $chapters;
}, 'ccm');

echo json_encode($chapters);
