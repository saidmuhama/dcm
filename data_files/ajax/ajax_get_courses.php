<?php
include('../config/db.php');
include('../config/url_crypt_config.php');
session_start();

header('Content-Type: application/json');

$instructor_id = $_SESSION['usr_code'] ?? '';

if (empty($instructor_id)) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$stmt = $db->prepare("
    SELECT
        c.id,
        c.title,
        c.thumbnail,
        c.status,
        c.price,
        c.created_at,
        COUNT(DISTINCT ch.id)                                   AS chapters,
        COUNT(DISTINCT l.id)                                    AS lessons,
        COUNT(DISTINCT CASE WHEN e.has_access = 1 THEN e.id END) AS enrolled,
        ROUND(AVG(r.rating), 1)                                 AS avg_rating,
        COUNT(DISTINCT r.id)                                    AS rating_count,
        COUNT(DISTINCT sn.id)                                   AS study_notes
    FROM tbl_courses c
    LEFT JOIN tbl_course_chapters ch         ON ch.course_id = c.id
    LEFT JOIN tbl_course_chapter_lessons l   ON l.chapter_id  = ch.id
    LEFT JOIN tbl_course_enrollments e       ON e.course_id   = c.id
    LEFT JOIN tbl_course_ratings r           ON r.course_id   = c.id
    LEFT JOIN study_notes sn                 ON sn.course_id  = c.id
    WHERE c.instructor_id = ? AND c.deleted_at IS NULL
    GROUP BY c.id
    ORDER BY c.created_at DESC
");
$stmt->bind_param("s", $instructor_id);
$stmt->execute();

$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* Attach assigned category names for each course */
$catStmt = $db->prepare("
    SELECT m.course_id, cat.id AS category_id, cat.category_title, cat.icon, cat.category_code
    FROM tbl_course_category_map m
    JOIN tbl_course_categories cat ON cat.id = m.category_id
    WHERE m.course_id IN (" . implode(',', array_column($courses, 'id') ?: [0]) . ")
    ORDER BY cat.sort_order, cat.id
");
$catStmt->execute();
$catRows   = $catStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$catByCourse = [];
foreach ($catRows as $row) {
    $catByCourse[$row['course_id']][] = $row;
}

foreach ($courses as &$c) {
    $c['course_token'] = encryptURLId((int)$c['id'], ctx: 'course');
    $c['categories']   = $catByCourse[$c['id']] ?? [];
}
unset($c);

echo json_encode(['status' => 'success', 'data' => $courses]);
