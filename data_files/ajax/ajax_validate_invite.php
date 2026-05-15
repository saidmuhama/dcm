<?php

header('Content-Type: application/json');

include('../config/db.php');

$code = $_POST['invitation_code'] ?? '';

if (empty($code)) {
    echo json_encode([
        "status" => "error",
        "message" => "Invitation code required"
    ]);
    exit;
}

// CHECK IF CODE EXISTS AND NOT USED
$stmt = $db->prepare("
    SELECT phone, first_name, last_name, status
    FROM tbl_course_invitees
    WHERE invitation_code = ?
    LIMIT 1
");

$stmt->bind_param("s", $code);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid invitation code"
    ]);
    exit;
}

$row = $result->fetch_assoc();

// CHECK IF ALREADY USED
if ($row['status'] == 1) {

    echo json_encode([
        "status" => "error",
        "message" => "This invitation code has already been used"
    ]);
    exit;
}

// SUCCESS
echo json_encode([
    "status" => "success",
    "data" => [
        "first_name" => $row['first_name'],
        "last_name"  => $row['last_name'],
        "phone"      => $row['phone']
    ]
]);

exit;