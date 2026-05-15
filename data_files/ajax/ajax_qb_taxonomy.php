<?php
include('../config/db.php');
session_start();
header('Content-Type: application/json');

/* ── Entity definitions ────────────────────────────────────── */
$entities = [
    'subjects' => [
        'table'    => 'qb_subjects',
        'pk'       => 'subject_id',
        'writable' => ['subject_code', 'subject_name'],
        'list_sql' => "SELECT subject_id, subject_code, subject_name, created_at FROM qb_subjects ORDER BY subject_name",
    ],
    'levels' => [
        'table'    => 'qb_levels',
        'pk'       => 'level_id',
        'writable' => ['level_name', 'sort_order'],
        'list_sql' => "SELECT level_id, level_name, sort_order FROM qb_levels ORDER BY sort_order, level_name",
    ],
    'chapters' => [
        'table'    => 'qb_chapters',
        'pk'       => 'chapter_id',
        'writable' => ['subject_id', 'level_id', 'chapter_number', 'chapter_name'],
        'list_sql' => "SELECT c.chapter_id, c.chapter_number, c.chapter_name,
                              s.subject_name, l.level_name,
                              c.subject_id, c.level_id
                       FROM qb_chapters c
                       JOIN qb_subjects s ON s.subject_id = c.subject_id
                       JOIN qb_levels   l ON l.level_id   = c.level_id
                       ORDER BY s.subject_name, l.sort_order, c.chapter_number + 0",
    ],
    'subtopics' => [
        'table'    => 'qb_subtopics',
        'pk'       => 'subtopic_id',
        'writable' => ['chapter_id', 'subtopic_name'],
        'list_sql' => "SELECT st.subtopic_id, st.subtopic_name, c.chapter_name, st.chapter_id
                       FROM qb_subtopics st
                       JOIN qb_chapters c ON c.chapter_id = st.chapter_id
                       ORDER BY c.chapter_name, st.subtopic_name",
    ],
    'bloom_levels' => [
        'table'    => 'qb_bloom_levels',
        'pk'       => 'bloom_id',
        'writable' => ['bloom_name', 'description'],
        'list_sql' => "SELECT bloom_id, bloom_name, description FROM qb_bloom_levels ORDER BY bloom_id",
    ],
    'difficulty_levels' => [
        'table'    => 'qb_difficulty_levels',
        'pk'       => 'difficulty_id',
        'writable' => ['difficulty_name'],
        'list_sql' => "SELECT difficulty_id, difficulty_name FROM qb_difficulty_levels ORDER BY difficulty_id",
    ],
    'sections' => [
        'table'    => 'qb_sections',
        'pk'       => 'section_id',
        'writable' => ['section_name'],
        'list_sql' => "SELECT section_id, section_name FROM qb_sections ORDER BY section_name",
    ],
];

/* ── Route ─────────────────────────────────────────────────── */
$entity_key = $_GET['entity'] ?? ($_POST['entity'] ?? (json_decode(file_get_contents('php://input'), true)['entity'] ?? ''));
$action     = $_GET['action'] ?? '';

// Read JSON body for POST requests
$body = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? [];
    $action = $body['action'] ?? $action;
    $entity_key = $body['entity'] ?? $entity_key;
}

if (!isset($entities[$entity_key])) {
    echo json_encode(['status' => 'error', 'message' => "Unknown entity: $entity_key"]);
    exit;
}

$cfg   = $entities[$entity_key];
$table = $cfg['table'];
$pk    = $cfg['pk'];

/* ── LIST ───────────────────────────────────────────────────── */
if ($action === 'list') {
    $res = $db->query($cfg['list_sql']);
    if (!$res) {
        echo json_encode(['status' => 'error', 'message' => $db->error]);
        exit;
    }
    echo json_encode(['status' => 'success', 'data' => $res->fetch_all(MYSQLI_ASSOC)]);
    exit;
}

/* ── CREATE ─────────────────────────────────────────────────── */
if ($action === 'create') {
    $cols = $vals = $types = [];
    $params = [];

    foreach ($cfg['writable'] as $col) {
        $v = isset($body[$col]) ? trim($body[$col]) : null;
        if ($v === '' || $v === null) continue;
        $cols[]   = "`$col`";
        $vals[]   = '?';
        $types[]  = is_numeric($v) ? 'i' : 's';
        $params[] = is_numeric($v) ? (int)$v : $v;
    }

    if (empty($cols)) {
        echo json_encode(['status' => 'error', 'message' => 'No data provided']);
        exit;
    }

    $sql  = "INSERT INTO $table (" . implode(',', $cols) . ") VALUES (" . implode(',', $vals) . ")";
    $stmt = $db->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => $db->error]);
        exit;
    }
    $stmt->bind_param(implode('', $types), ...$params);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record created', 'id' => $db->insert_id]);
    } else {
        $msg = str_contains($stmt->error, 'Duplicate') ? 'This record already exists.' : $stmt->error;
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
    exit;
}

/* ── UPDATE ─────────────────────────────────────────────────── */
if ($action === 'update') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { echo json_encode(['status'=>'error','message'=>'ID missing']); exit; }

    $sets = $types = [];
    $params = [];

    foreach ($cfg['writable'] as $col) {
        $v = isset($body[$col]) ? trim($body[$col]) : null;
        if ($v === null) continue;
        $sets[]   = "`$col` = ?";
        $types[]  = is_numeric($v) ? 'i' : 's';
        $params[] = is_numeric($v) ? (int)$v : $v;
    }

    if (empty($sets)) {
        echo json_encode(['status' => 'error', 'message' => 'Nothing to update']);
        exit;
    }

    $params[] = $id;
    $types[]  = 'i';

    $stmt = $db->prepare("UPDATE $table SET " . implode(',', $sets) . " WHERE $pk = ?");
    $stmt->bind_param(implode('', $types), ...$params);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record updated']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

/* ── DELETE ─────────────────────────────────────────────────── */
if ($action === 'delete') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) { echo json_encode(['status'=>'error','message'=>'ID missing']); exit; }

    $stmt = $db->prepare("DELETE FROM $table WHERE $pk = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Record deleted']);
    } else {
        $msg = str_contains($stmt->error, 'foreign key') ? 'Cannot delete — this record is used by questions or other data.' : $stmt->error;
        echo json_encode(['status' => 'error', 'message' => $msg]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => "Unknown action: $action"]);
