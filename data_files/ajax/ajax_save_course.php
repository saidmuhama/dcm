<?php
include('../config/db.php');
include('../config/dump.php');
session_start();

header('Content-Type: application/json');

// ====== CONFIG ======
$BUNNY_API_KEY = App::getBunnyNetApiKey();

// ====== GET JSON DATA ======
$data = json_decode(file_get_contents("php://input"), true);

// ====== GET VALUES ======
$title = $data['title'] ?? '';
$instructor_id = $_SESSION['usr_code'] ?? '';

// ====== VALIDATION ======
if(empty($title)){
    echo json_encode([
        "status" => "error",
        "message" => "Course title is required"
    ]);
    exit;
}

if(empty($instructor_id)){
    echo json_encode([
        "status" => "error",
        "message" => "User not logged in"
    ]);
    exit;
}

// ======================================================
// ✅ STEP 1: CREATE VIDEO LIBRARY (NEW API)
// ======================================================
$bunnyData = [
    "Name" => $title,
    "ReplicationRegions" => ["LA"],
    "PlayerVersion" => 2
];

$ch = curl_init("https://api.bunny.net/videolibrary");

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "AccessKey: $BUNNY_API_KEY",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($bunnyData));

$response = curl_exec($ch);

if(curl_errno($ch)){
    echo json_encode([
        "status" => "error",
        "message" => "Curl error: " . curl_error($ch)
    ]);
    exit;
}

curl_close($ch);

$result = json_decode($response, true);

// ======================================================
// ❌ CHECK RESPONSE
// ======================================================
if(!isset($result['Id'])){
    echo json_encode([
        "status" => "error",
        "message" => "Failed to create video library",
        "raw_response" => $response,
        "decoded" => $result
    ]);
    exit;
}

$library_id = $result['Id'];
$library_key = App::getBunnyLibraryKey($library_id, $BUNNY_API_KEY);

$create = App::createBunnyStorageFolder(
    App::getBunnyStorageZone(),
    $title,
    App::getBunnyStorageZoneAccessKey()
);

// ======================================================
// ✅ STEP 1.2: CREATE FOLDER IN STREAM (NEW API)
// ======================================================

// ======================================================
// ✅ STEP 2: INSERT INTO DATABASE
// ======================================================
$stmt = $db->prepare("INSERT INTO tbl_courses (instructor_id, title, library_id, library_key) VALUES (?, ?, ?, ?)");

if(!$stmt){
    echo json_encode([
        "status" => "error",
        "message" => "Prepare failed",
        "error" => $db->error
    ]);
    exit;
}

$stmt->bind_param("ssss", $instructor_id, $title, $library_id, $library_key);

if($stmt->execute()){
    echo json_encode([
        "status"    => "success",
        "message"   => "Course + Library created successfully",
        "course_id" => $stmt->insert_id
    ]);
}else{
    echo json_encode([
        "status" => "error",
        "message" => "Execute failed",
        "error" => $stmt->error
    ]);
}