<?php
include('../config/db.php');

header('Content-Type: application/json');

$search = $_GET['search'] ?? '';
$price  = $_GET['price'] ?? '';

$where = "WHERE 1";

if(!empty($search)){
    $where .= " AND c.title LIKE '%$search%'";
}

if($price == "free"){
    $where .= " AND c.price = 0";
}

if($price == "paid"){
    $where .= " AND c.price > 0";
}

$q = mysqli_query($db, "
    SELECT 
        c.id,
        c.title,
        c.price,
        c.discount,
        c.thumbnail,
        IFNULL(AVG(r.rating),0) as avg_rating,
        COUNT(r.id) as total_reviews
    FROM tbl_courses c
    LEFT JOIN tbl_course_ratings r ON r.course_id = c.id
    $where AND c.status='active'
    GROUP BY c.id
    ORDER BY c.id DESC
");

$data = [];

while($row = mysqli_fetch_assoc($q)){

    // ✅ FIX thumbnail fallback
    if(empty($row['thumbnail'])){
        $row['thumbnail'] = "uploads/course_default.png";
    }

    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);