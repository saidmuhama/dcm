<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? 0) != 3) {
    echo json_encode(['status' => 'error', 'message' => 'Access denied']); exit;
}

$me     = $_SESSION['usr_code'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

/* ── Instructor dashboard stats ─────────────────────────────────────── */
case 'get_stats':
    // Total enrollments across instructor's courses
    $total_enrollments = (int)$db->query("
        SELECT COUNT(ce.id)
        FROM tbl_course_enrollments ce
        JOIN tbl_courses c ON c.id = ce.course_id
        WHERE c.instructor_id = '$me'
    ")->fetch_row()[0];

    // Org learners — enrolled members who are in tbl_org_members
    $org_learners = (int)$db->query("
        SELECT COUNT(DISTINCT ce.user_id)
        FROM tbl_course_enrollments ce
        JOIN tbl_courses c ON c.id = ce.course_id
        JOIN tbl_org_members om ON om.usr_code = ce.user_id
        WHERE c.instructor_id = '$me'
    ")->fetch_row()[0];

    // Active discussions in instructor's courses
    $active_discussions = 0;
    $discRes = $db->query("SHOW TABLES LIKE 'tbl_course_discussions'");
    if ($discRes && $discRes->num_rows > 0) {
        $active_discussions = (int)$db->query("
            SELECT COUNT(cd.id)
            FROM tbl_course_discussions cd
            JOIN tbl_courses c ON c.id = cd.course_id
            WHERE c.instructor_id = '$me' AND cd.status = 'open'
        ")->fetch_row()[0];
    }

    // Courses with learner progress in last 7 days
    $coursesActivityRes = $db->query("
        SELECT c.id, c.title, COUNT(DISTINCT ce.user_id) AS active_learners
        FROM tbl_courses c
        JOIN tbl_course_enrollments ce ON ce.course_id = c.id
        WHERE c.instructor_id = '$me'
          AND ce.last_accessed_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY c.id, c.title
        ORDER BY active_learners DESC
    ");
    $courses_with_activity = [];
    if ($coursesActivityRes) {
        while ($row = $coursesActivityRes->fetch_assoc()) {
            $courses_with_activity[] = [
                'id'             => (int)$row['id'],
                'title'         => $row['title'],
                'active_learners' => (int)$row['active_learners'],
            ];
        }
    }

    echo json_encode([
        'status'                => 'success',
        'total_enrollments'     => $total_enrollments,
        'org_learners'          => $org_learners,
        'active_discussions'    => $active_discussions,
        'unread_announcements'  => 0,
        'courses_with_activity' => $courses_with_activity,
    ]);
    break;

/* ── Enrollment chart — per course bar chart data ───────────────────── */
case 'get_enrollment_chart':
    $rows = $db->query("
        SELECT c.title, COUNT(ce.id) AS enrollments
        FROM tbl_courses c
        LEFT JOIN tbl_course_enrollments ce ON ce.course_id = c.id
        WHERE c.instructor_id = '$me'
        GROUP BY c.id, c.title
        ORDER BY enrollments DESC
        LIMIT 15
    ");

    $chart = [];
    if ($rows) {
        while ($r = $rows->fetch_assoc()) {
            $chart[] = [
                'title'       => $r['title'],
                'enrollments' => (int)$r['enrollments'],
            ];
        }
    }
    echo json_encode(['status' => 'success', 'chart' => $chart]);
    break;

default:
    echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
}
