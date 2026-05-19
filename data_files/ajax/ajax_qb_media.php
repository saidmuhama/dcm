<?php
include('../config/db.php');
session_start();
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';

$body = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_FILES)) {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action'] ?? $action;
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? $action;
}

$UPLOAD_DIR     = __DIR__ . '/../uploads/qb_media/';
$UPLOAD_URL_BASE= '../uploads/qb_media/';

/* ── STATS ───────────────────────────────────────────────── */
if ($action === 'stats') {
    $res = $db->query("SELECT media_type, COUNT(*) AS cnt FROM qb_question_media GROUP BY media_type");
    $stats = ['total'=>0,'image'=>0,'audio'=>0,'video'=>0,'document'=>0];
    while ($row = $res->fetch_assoc()) {
        $stats[$row['media_type']] = (int)$row['cnt'];
        $stats['total'] += (int)$row['cnt'];
    }
    echo json_encode(['status'=>'success','data'=>$stats]);
    exit;
}

/* ── SEARCH QUESTIONS ────────────────────────────────────── */
if ($action === 'search') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) { echo json_encode(['status'=>'success','data'=>[]]); exit; }

    $like = "%$q%";
    $stmt = $db->prepare("
        SELECT q.question_id, q.q_uid, q.question_stem,
               s.subject_name, l.level_name, c.chapter_name
        FROM   qb_questions q
        JOIN   qb_subjects s  ON s.subject_id = q.subject_id
        JOIN   qb_levels   l  ON l.level_id   = q.level_id
        JOIN   qb_chapters c  ON c.chapter_id  = q.chapter_id
        WHERE  q.q_uid LIKE ? OR q.question_stem LIKE ?
        ORDER  BY q.question_id DESC
        LIMIT  20
    ");
    $stmt->bind_param('ss', $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Strip HTML from stem for plain display
    foreach ($rows as &$row) {
        $row['question_stem_plain'] = mb_substr(strip_tags($row['question_stem']), 0, 120);
    }

    echo json_encode(['status'=>'success','data'=>$rows]);
    exit;
}

/* ── LIST MEDIA FOR QUESTION ─────────────────────────────── */
if ($action === 'list') {
    $question_id = (int)($_GET['question_id'] ?? 0);
    if (!$question_id) { echo json_encode(['status'=>'error','message'=>'question_id required']); exit; }

    $stmt = $db->prepare("SELECT media_id, media_type, media_path, created_at FROM qb_question_media WHERE question_id = ? ORDER BY media_id DESC");
    $stmt->bind_param('i', $question_id);
    $stmt->execute();
    echo json_encode(['status'=>'success','data'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    exit;
}

/* ── UPLOAD ──────────────────────────────────────────────── */
if ($action === 'upload' && !empty($_FILES['media_file'])) {
    $question_id = (int)($_POST['question_id'] ?? 0);
    $media_type  = trim($_POST['media_type'] ?? 'image');

    if (!$question_id) { echo json_encode(['status'=>'error','message'=>'question_id required']); exit; }

    $allowed_types = ['image','audio','video','document'];
    if (!in_array($media_type, $allowed_types)) $media_type = 'image';

    $file = $_FILES['media_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status'=>'error','message'=>'Upload error: '.$file['error']]);
        exit;
    }

    if ($file['size'] > 20 * 1024 * 1024) {
        echo json_encode(['status'=>'error','message'=>'File too large. Max 20 MB.']);
        exit;
    }

    // Validate MIME
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $allowed_mimes = [
        'image'    => ['image/jpeg','image/png','image/gif','image/webp'],
        'audio'    => ['audio/mpeg','audio/wav','audio/ogg','audio/mp4'],
        'video'    => ['video/mp4','video/quicktime','video/x-msvideo'],
        'document' => ['application/pdf','application/msword',
                       'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    ];
    if (!in_array($mime, $allowed_mimes[$media_type] ?? [])) {
        echo json_encode(['status'=>'error','message'=>"File type ($mime) not allowed for $media_type"]);
        exit;
    }

    // Build safe filename
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'qbm_'.$question_id.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$ext;
    $dest     = $UPLOAD_DIR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        echo json_encode(['status'=>'error','message'=>'Failed to save file']);
        exit;
    }

    $media_path = $UPLOAD_URL_BASE . $filename;

    $stmt = $db->prepare("INSERT INTO qb_question_media (question_id, media_type, media_path) VALUES (?,?,?)");
    $stmt->bind_param('iss', $question_id, $media_type, $media_path);
    if ($stmt->execute()) {
        echo json_encode(['status'=>'success','message'=>'Media uploaded','media_id'=>$db->insert_id,'media_path'=>$media_path]);
    } else {
        unlink($dest); // rollback file
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
    exit;
}

/* ── DELETE ──────────────────────────────────────────────── */
if ($action === 'delete') {
    $media_id = (int)($body['media_id'] ?? 0);
    if (!$media_id) { echo json_encode(['status'=>'error','message'=>'media_id required']); exit; }

    $stmt = $db->prepare("SELECT media_path FROM qb_question_media WHERE media_id = ?");
    $stmt->bind_param('i', $media_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) { echo json_encode(['status'=>'error','message'=>'Media not found']); exit; }

    // Delete file if local
    $path = __DIR__ . '/../' . ltrim($row['media_path'],'./');
    if (file_exists($path) && strpos(realpath($path), realpath($UPLOAD_DIR)) === 0) {
        @unlink($path);
    }

    $del = $db->prepare("DELETE FROM qb_question_media WHERE media_id = ?");
    $del->bind_param('i', $media_id);
    if ($del->execute()) {
        echo json_encode(['status'=>'success','message'=>'Media deleted']);
    } else {
        echo json_encode(['status'=>'error','message'=>$del->error]);
    }
    exit;
}

echo json_encode(['status'=>'error','message'=>"Unknown action: $action"]);
