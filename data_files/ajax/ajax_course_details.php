<?php
include('../config/db.php');

$course_id = intval($_GET['course_id']);

/* FETCH COURSE + RATINGS */
$q = mysqli_query($db,"
    SELECT c.*,
           IFNULL(AVG(r.rating),0) as avg_rating,
           COUNT(r.id) as total_reviews
    FROM tbl_courses c
    LEFT JOIN course_ratings r ON r.course_id = c.id
    WHERE c.id = '$course_id'
    GROUP BY c.id
");

if(mysqli_num_rows($q) == 0){
    echo json_encode(["status"=>"error"]);
    exit;
}

$data = mysqli_fetch_assoc($q);

echo json_encode([
    "status" => "success",
    "data" => $data
]);