<?php
include('../config/db.php');

$course_id = $_GET['course_id'] ?? 0;

$sql = "
SELECT d.*, u.first_name, u.last_name,
(SELECT COUNT(*) FROM tbl_course_discussion_answers a WHERE a.discussion_id = d.id) as total_answers,
(SELECT COUNT(*) FROM tbl_course_discussion_likes l WHERE l.discussion_id = d.id) as total_likes
FROM tbl_course_discussions d
LEFT JOIN tbl_all_users u ON u.usr_code = d.user_id
WHERE d.course_id = '$course_id'
ORDER BY d.id DESC
";

$result = mysqli_query($db, $sql);

$data = [];

while($row = mysqli_fetch_assoc($result)){

    // fetch answers
    $answers = [];
    $q2 = mysqli_query($db, "
        SELECT a.*, u.first_name, u.last_name 
        FROM  tbl_course_discussion_answers a
        LEFT JOIN tbl_all_users u ON u.usr_code = a.user_id
        WHERE a.discussion_id = {$row['id']}
    ");

    while($a = mysqli_fetch_assoc($q2)){
        $answers[] = $a;
    }

    $row['answers'] = $answers;

    $data[] = $row;
}

echo json_encode([
    "status" => "success",
    "data" => $data
]);