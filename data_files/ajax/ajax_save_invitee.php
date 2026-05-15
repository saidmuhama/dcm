<?php

header('Content-Type: application/json');

ini_set('display_errors', 1);
error_reporting(E_ALL);

include('../config/db.php');
include('../config/dump.php');
session_start();

try {

    // ======================
    // GET DATA SAFELY
    // ======================
    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $course_id  = $_POST['course_id'] ?? '';
    $code       = $_POST['invitation_code'] ?? '';
    $invited_by = $_SESSION['usr_code'];

    // ======================
    // VALIDATION
    // ======================
    if (empty($first_name) || empty($phone) || empty($course_id) || empty($code)) {

        echo json_encode([
            "status" => "error",
            "message" => "Missing required fields"
        ]);
        exit;
    }

    // ======================
    // CHECK DUPLICATE PHONE PER COURSE
    // ======================
    $check = $db->prepare("
        SELECT id 
        FROM tbl_course_invitees 
        WHERE phone = ? AND course_id = ?
        LIMIT 1
    ");

    $check->bind_param("si", $phone, $course_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {

        echo json_encode([
            "status" => "error",
            "message" => "This phone number is already used in this course"
        ]);
        exit;
    }

    // ======================
    // INSERT INVITEE
    // ======================
    $stmt = $db->prepare("
        INSERT INTO tbl_course_invitees 
        (invited_by,first_name, last_name, phone, course_id, invitation_code, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 0, NOW())
    ");

    if (!$stmt) {
        echo json_encode([
            "status" => "error",
            "message" => "Prepare failed: " . $db->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "ssssis",
        $invited_by,
        $first_name,
        $last_name,
        $phone,
        $course_id,
        $code
    );

    // ======================
    // EXECUTE INSERT
    // ======================
    if ($stmt->execute()) {

        // Get course name
        $course_name = App::getWhatFromWHere(
            "title",
            "tbl_courses",
            "id",
            $course_id
        );

        // Send SMS AFTER SUCCESS
        App::sendSMS(
            $phone,
            "You have been invited to join our course {$course_name}. Your invitation code is {$code}. Please visit our website digitalcoursemedia.com to register."
        );

        echo json_encode([
            "status" => "success",
            "message" => "Invitation saved successfully"
        ]);

    } else {

        echo json_encode([
            "status" => "error",
            "message" => $stmt->error
        ]);
    }

    $stmt->close();

} catch (Exception $e) {

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}

exit;