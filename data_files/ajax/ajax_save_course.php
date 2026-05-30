<?php
include('../config/db.php');
include('../config/dump.php');
include('../config/url_crypt_config.php');
session_start();

header('Content-Type: application/json');

// ====== CONFIG ======
$BUNNY_API_KEY = App::getBunnyNetApiKey();

// ====== GET JSON DATA ======
$data = json_decode(file_get_contents("php://input"), true);

// ====== GET VALUES ======
$title         = $data['title'] ?? '';
$instructor_id = $_SESSION['usr_code'] ?? '';
/* Support both legacy single category_id and new category_ids array */
$category_ids_raw = $data['category_ids'] ?? (isset($data['category_id']) ? [$data['category_id']] : []);
$category_ids     = array_values(array_unique(array_filter(array_map('intval', (array)$category_ids_raw))));
$primary_cat      = $category_ids[0] ?? null; // first selected = primary (kept in tbl_courses for backwards compat)

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
// ✅ STEP 2: INSERT COURSE INTO DATABASE
// ======================================================
$stmt = $db->prepare("INSERT INTO tbl_courses (instructor_id, title, library_id, library_key, category_id) VALUES (?, ?, ?, ?, ?)");
if (!$stmt) {
    echo json_encode(["status"=>"error","message"=>"Prepare failed","error"=>$db->error]);
    exit;
}
$stmt->bind_param("ssssi", $instructor_id, $title, $library_id, $library_key, $primary_cat);

if (!$stmt->execute()) {
    echo json_encode(["status"=>"error","message"=>"Execute failed","error"=>$stmt->error]);
    exit;
}

$newId = $stmt->insert_id;

// ======================================================
// ✅ STEP 3: SAVE CATEGORY MAP (many-to-many)
// ======================================================
if (!empty($category_ids)) {
    $mapStmt = $db->prepare("INSERT IGNORE INTO tbl_course_category_map (course_id, category_id) VALUES (?, ?)");
    foreach ($category_ids as $cid) {
        $mapStmt->bind_param("ii", $newId, $cid);
        $mapStmt->execute();
    }
}

echo json_encode([
    "status"        => "success",
    "message"       => "Course created successfully",
    "course_id"     => $newId,
    "course_token"  => encryptURLId((int)$newId, ctx: 'course'),
    "category_ids"  => $category_ids,
]);