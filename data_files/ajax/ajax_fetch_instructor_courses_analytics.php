<?php
session_start();
include('../config/db.php');
include('../config/url_crypt_config.php');

$user_id = $_SESSION['usr_code'] ?? 0;

if(!$user_id){
    echo json_encode(["status"=>"error","message"=>"Unauthorized"]);
    exit;
}

$query = mysqli_query($db,"
    SELECT
        c.id,
        c.title,
        c.thumbnail,
        c.price,
        c.discount,
        c.status,
        c.is_approved,
        c.created_at,

        COUNT(DISTINCT oi.id)      AS total_sales,
        COUNT(DISTINCT o.user_id)  AS students,

        SUM(oi.price)                                              AS gross_earnings,
        SUM(oi.price * (oi.commission_rate / 100))                 AS commission,
        SUM(oi.price - (oi.price * (oi.commission_rate / 100)))    AS net_earnings,

        (SELECT COUNT(*) FROM tbl_course_chapters ch
         WHERE ch.course_id = c.id AND ch.status = 'active')      AS total_chapters,

        (SELECT COUNT(*) FROM tbl_course_chapter_lessons l
         JOIN tbl_course_chapters ch2 ON ch2.id = l.chapter_id
         WHERE ch2.course_id = c.id AND l.status = 'active')      AS total_lessons,

        (SELECT COUNT(*) FROM tbl_course_enrollments en
         WHERE en.course_id = c.id)                                AS total_enrollments

    FROM tbl_courses c

    LEFT JOIN tbl_order_items oi
        ON oi.course_id = c.id

    LEFT JOIN tbl_orders o
        ON o.id = oi.order_id
        AND o.payment_status = 'paid'

    WHERE c.instructor_id = '$user_id'

    GROUP BY c.id
    ORDER BY c.id DESC
");

$data = [];

while($row = mysqli_fetch_assoc($query)){
    $row['gross_earnings']    = $row['gross_earnings']    ?? 0;
    $row['net_earnings']      = $row['net_earnings']      ?? 0;
    $row['students']          = $row['students']          ?? 0;
    $row['total_sales']       = $row['total_sales']       ?? 0;
    $row['total_chapters']    = $row['total_chapters']    ?? 0;
    $row['total_lessons']     = $row['total_lessons']     ?? 0;
    $row['total_enrollments'] = $row['total_enrollments'] ?? 0;
    $row['course_token']      = encryptURLId((int)$row['id'], ctx: 'course');

    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);