<?php
include('../config/db.php');

$course_id = intval($_GET['course_id']);

$query = mysqli_query($db,"
    SELECT 
        i.*,
        u.first_name as invited_by_name

    FROM tbl_course_invitees i

    LEFT JOIN tbl_students u
    ON u.usr_code = i.invited_by

    WHERE i.course_id='$course_id'

    ORDER BY i.id DESC
");

$data = [];

while($row = mysqli_fetch_assoc($query)){

    $data[] = $row;
}

echo json_encode([
    "data"=>$data
]);