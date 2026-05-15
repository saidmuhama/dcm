<?php
include('../config/db.php');
session_start();

$user_id   = $_SESSION['usr_code'] ?? 0;

if(!$user_id){
    echo json_encode(["status"=>"error","message"=>"Not logged in"]);
    exit;
}

/* ✅ FETCH STUDENT */
$student_q = mysqli_query($db,"
    SELECT * 
    FROM tbl_students 
    WHERE usr_code = '$user_id'
    
");

$student = mysqli_fetch_assoc($student_q);

/* ✅ TOTAL COURSES (PAID) */
$courses_q = mysqli_query($db,"
    SELECT COUNT(DISTINCT course_id) as total_courses
    FROM tbl_order_items oi
    JOIN tbl_orders o ON o.id = oi.order_id
    WHERE o.user_id = '$user_id'
    AND o.payment_status = 'paid'
");
$courses = mysqli_fetch_assoc($courses_q)['total_courses'] ?? 0;

/* ✅ COMPLETED COURSES */
$completed_q = mysqli_query($db,"
    SELECT COUNT(*) as completed
    FROM (
        SELECT course_id,
        SUM(CASE WHEN watched=1 THEN 1 ELSE 0 END) as done,
        COUNT(*) as total
        FROM tbl_course_progress
        WHERE user_id = '$user_id'
        GROUP BY course_id
        HAVING done = total
    ) t
");
$completed = mysqli_fetch_assoc($completed_q)['completed'] ?? 0;

/* ✅ TOTAL TASKS (LESSONS) */
$tasks_q = mysqli_query($db,"
    SELECT COUNT(*) as total_tasks
    FROM tbl_course_progress
    WHERE user_id = '$user_id'
");
$tasks = mysqli_fetch_assoc($tasks_q)['total_tasks'] ?? 0;

/* ✅ SKILL BREAKDOWN (FROM STUDENT SKILL FIELD) */
$skills = explode(',', $student['skill'] ?? '');

echo json_encode([
    "status"=>"success",
    "student"=>$student,
    "stats"=>[
        "courses"=>$courses,
        "completed"=>$completed,
        "tasks"=>$tasks
    ],
    "skills"=>$skills
]);