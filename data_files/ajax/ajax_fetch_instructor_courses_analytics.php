<?php
session_start();
include('../config/db.php');

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

        COUNT(DISTINCT oi.id) as total_sales,
        COUNT(DISTINCT o.user_id) as students,

        SUM(oi.price) as gross_earnings,
        SUM(oi.price * (oi.commission_rate / 100)) as commission,
        SUM(oi.price - (oi.price * (oi.commission_rate / 100))) as net_earnings

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
    $row['gross_earnings'] = $row['gross_earnings'] ?? 0;
    $row['net_earnings']   = $row['net_earnings'] ?? 0;
    $row['students']       = $row['students'] ?? 0;
    $row['total_sales']    = $row['total_sales'] ?? 0;

    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);