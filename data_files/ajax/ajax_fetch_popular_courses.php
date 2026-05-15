<?php
include('../config/db.php');

$sql = "
SELECT c.*, 
       COUNT(w.id) as total_likes
FROM tbl_courses c
LEFT JOIN tbl_course_wishlist w ON w.course_id = c.id
GROUP BY c.id
ORDER BY total_likes DESC
LIMIT 10
";

$res = mysqli_query($db, $sql);

$data = [];

while($row = mysqli_fetch_assoc($res)){
    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);