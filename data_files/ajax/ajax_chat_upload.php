<?php
session_start();
header('Content-Type: application/json');

$me = $_SESSION['usr_code'] ?? '';
if (!$me) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit; }

if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $err = $_FILES['file']['error'] ?? 'no_file';
    echo json_encode(['status' => 'error', 'message' => "Upload error: $err"]);
    exit;
}

$file    = $_FILES['file'];
$maxSize = 25 * 1024 * 1024; // 25 MB

if ($file['size'] > $maxSize) {
    echo json_encode(['status' => 'error', 'message' => 'File too large (max 25 MB)']);
    exit;
}

$mime = mime_content_type($file['tmp_name']);

$allowedMimes = [
    // Images
    'image/jpeg'  => ['ext' => 'jpg',  'type' => 'image'],
    'image/png'   => ['ext' => 'png',  'type' => 'image'],
    'image/gif'   => ['ext' => 'gif',  'type' => 'image'],
    'image/webp'  => ['ext' => 'webp', 'type' => 'image'],
    // Documents
    'application/pdf'  => ['ext' => 'pdf',  'type' => 'file'],
    'application/msword' => ['ext' => 'doc', 'type' => 'file'],
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['ext' => 'docx', 'type' => 'file'],
    'application/vnd.ms-excel' => ['ext' => 'xls', 'type' => 'file'],
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['ext' => 'xlsx', 'type' => 'file'],
    'application/zip'             => ['ext' => 'zip',  'type' => 'file'],
    'application/x-zip-compressed'=> ['ext' => 'zip',  'type' => 'file'],
    'text/plain'  => ['ext' => 'txt',  'type' => 'file'],
    // Audio
    'audio/mpeg'  => ['ext' => 'mp3',  'type' => 'audio'],
    'audio/ogg'   => ['ext' => 'ogg',  'type' => 'audio'],
    'audio/wav'   => ['ext' => 'wav',  'type' => 'audio'],
    'audio/webm'  => ['ext' => 'webm', 'type' => 'audio'],
    'audio/mp4'   => ['ext' => 'm4a',  'type' => 'audio'],
    'video/webm'  => ['ext' => 'webm', 'type' => 'audio'], // voice notes recorded as video/webm
    // Video
    'video/mp4'   => ['ext' => 'mp4',  'type' => 'video'],
    'video/ogg'   => ['ext' => 'ogv',  'type' => 'video'],
    'video/quicktime' => ['ext' => 'mov', 'type' => 'video'],
];

if (!isset($allowedMimes[$mime])) {
    echo json_encode(['status' => 'error', 'message' => "File type not allowed ($mime)"]);
    exit;
}

$meta    = $allowedMimes[$mime];
$ext     = $meta['ext'];
$msgType = $meta['type'];

// Determine if this is a voice note (sent as blob from MediaRecorder)
$origName  = $file['name'];
$isVoice   = str_contains(strtolower($origName), 'voice') || $origName === 'blob';
if ($isVoice && $msgType === 'audio') {
    $ext     = 'ogg';
    $origName = 'voice_note.'.$ext;
}

$safeName  = preg_replace('/[^a-zA-Z0-9\-_.]/', '_', pathinfo($origName, PATHINFO_FILENAME));
$safeName  = substr($safeName, 0, 50);
$newName   = time().'_'.substr(bin2hex(random_bytes(4)), 0, 8).'_'.$safeName.'.'.$ext;

$uploadDir = dirname(__DIR__).'/uploads/chat/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$dest = $uploadDir.$newName;
if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['status' => 'error', 'message' => 'Failed to save file']);
    exit;
}

echo json_encode([
    'status'    => 'success',
    'file_path' => 'uploads/chat/'.$newName,
    'file_name' => $isVoice ? 'Voice Note' : $file['name'],
    'file_size' => $file['size'],
    'type'      => $msgType,
    'mime'      => $mime,
]);
