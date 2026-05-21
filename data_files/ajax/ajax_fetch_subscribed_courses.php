<?php
include "../config/db.php";
include "../config/url_crypt_config.php";
session_start();

$user_id = $_SESSION['usr_code'];

$data          = [];
$seenCourseIds = [];

/* ── Helper: build course stats row ───────────────────────────── */
function buildCourseRow($db, $user_id, $course_id, $title, $thumbnail) {
    $totalLessons = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(*) as total FROM tbl_course_chapter_lessons
         WHERE course_id = '$course_id'"
    ))['total'];

    $watchedLessons = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(DISTINCT lesson_id) as done
         FROM tbl_course_progress
         WHERE user_id = '$user_id' AND course_id = '$course_id' AND watched = 1"
    ))['done'];

    $totalChapters = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(*) as total FROM tbl_course_chapters WHERE course_id = '$course_id'"
    ))['total'];

    $progress = ($totalLessons > 0) ? round(($watchedLessons / $totalLessons) * 100) : 0;

    if ($progress == 100)     $status = "Completed";
    elseif ($progress > 0)    $status = "In-Progress";
    else                      $status = "Not Started";

    $discussions = mysqli_fetch_assoc(mysqli_query($db,
        "SELECT COUNT(*) as total FROM tbl_course_discussions WHERE course_id = '$course_id'"
    ))['total'] ?? 0;

    return [
        "course_id"      => $course_id,
        "course_token"   => encryptURLId((int)$course_id, ctx: 'course'),
        "title"          => $title,
        "thumbnail"      => $thumbnail,
        "total_lessons"  => $totalLessons,
        "watched_lessons"=> $watchedLessons,
        "total_chapters" => $totalChapters,
        "progress"       => $progress,
        "status"         => $status,
        "discussions"    => $discussions,
        "upcoming"       => date("d-m-Y", strtotime("+7 days")),
        "via_org"        => false,
    ];
}

/* ── 1. Paid / purchased courses ──────────────────────────────── */
$result = mysqli_query($db,
    "SELECT DISTINCT oi.course_id, c.title, c.thumbnail
     FROM tbl_orders o
     JOIN tbl_order_items oi ON o.id = oi.order_id
     JOIN tbl_courses c ON c.id = oi.course_id
     WHERE o.user_id = '$user_id' AND o.payment_status = 'paid'
     ORDER BY o.id DESC"
);

while ($row = mysqli_fetch_assoc($result)) {
    $cid = $row['course_id'];
    $seenCourseIds[$cid] = true;
    $data[] = buildCourseRow($db, $user_id, $cid, $row['title'], $row['thumbnail']);
}

/* ── 2. Org-subscribed courses (member only, active, not expired) */
$orgResult = mysqli_query($db,
    "SELECT DISTINCT oca.course_id, c.title, c.thumbnail
     FROM tbl_org_course_access oca
     INNER JOIN tbl_org_members m ON m.org_code = oca.org_code
     JOIN tbl_courses c ON c.id = oca.course_id
     WHERE m.usr_code = '$user_id'
       AND m.status = 'active'
       AND oca.is_active = 1
       AND c.status = 'active'
       AND c.is_approved = 'approved'
       AND c.deleted_at IS NULL
       AND (oca.expires_at IS NULL OR oca.expires_at >= CURDATE())
     ORDER BY oca.granted_at DESC"
);

while ($row = mysqli_fetch_assoc($orgResult)) {
    $cid = $row['course_id'];
    if (isset($seenCourseIds[$cid])) continue; // already included via purchase
    $seenCourseIds[$cid] = true;
    $entry = buildCourseRow($db, $user_id, $cid, $row['title'], $row['thumbnail']);
    $entry['via_org'] = true;
    $data[] = $entry;
}

echo json_encode([
    "status" => "success",
    "data"   => $data,
]);
