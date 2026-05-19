<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();
include('../config/db.php');
session_start();

function send_json(array $data): never {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

$q_stats = "COUNT(DISTINCT CASE WHEN q.status='published' THEN q.question_id END) AS published_count,
            COUNT(DISTINCT CASE WHEN q.status='draft'     THEN q.question_id END) AS draft_count,
            COUNT(DISTINCT CASE WHEN q.status='review'    THEN q.question_id END) AS review_count";

$entities = [
    'subjects' => [
        'table'    => 'qb_subjects',
        'pk'       => 'subject_id',
        'writable' => ['subject_code', 'subject_name'],
        'list_sql' => "SELECT s.subject_id, s.subject_code, s.subject_name, s.created_at,
                              COUNT(DISTINCT c.chapter_id)   AS chapter_count,
                              COUNT(DISTINCT st.subtopic_id) AS subtopic_count,
                              COUNT(DISTINCT q.question_id)  AS question_count,
                              $q_stats
                       FROM qb_subjects s
                       LEFT JOIN qb_chapters  c  ON c.subject_id  = s.subject_id
                       LEFT JOIN qb_subtopics st ON st.chapter_id = c.chapter_id
                       LEFT JOIN qb_questions q  ON q.subject_id  = s.subject_id
                       GROUP BY s.subject_id ORDER BY question_count DESC, s.subject_name",
    ],
    'levels' => [
        'table'    => 'qb_levels',
        'pk'       => 'level_id',
        'writable' => ['level_name', 'sort_order'],
        'list_sql' => "SELECT l.level_id, l.level_name, l.sort_order,
                              COUNT(DISTINCT c.chapter_id)  AS chapter_count,
                              COUNT(DISTINCT q.question_id) AS question_count,
                              $q_stats
                       FROM qb_levels l
                       LEFT JOIN qb_chapters  c ON c.level_id  = l.level_id
                       LEFT JOIN qb_questions q ON q.level_id  = l.level_id
                       GROUP BY l.level_id ORDER BY l.sort_order, l.level_name",
    ],
    'chapters' => [
        'table'    => 'qb_chapters',
        'pk'       => 'chapter_id',
        'writable' => ['subject_id', 'level_id', 'chapter_number', 'chapter_name'],
        'list_sql' => "SELECT c.chapter_id, c.chapter_number, c.chapter_name,
                              s.subject_name, l.level_name, c.subject_id, c.level_id,
                              COUNT(DISTINCT st.subtopic_id) AS subtopic_count,
                              COUNT(DISTINCT q.question_id)  AS question_count,
                              $q_stats
                       FROM qb_chapters c
                       JOIN qb_subjects s  ON s.subject_id = c.subject_id
                       JOIN qb_levels   l  ON l.level_id   = c.level_id
                       LEFT JOIN qb_subtopics st ON st.chapter_id = c.chapter_id
                       LEFT JOIN qb_questions  q ON q.chapter_id  = c.chapter_id
                       GROUP BY c.chapter_id ORDER BY s.subject_name, l.sort_order, c.chapter_number+0",
    ],
    'subtopics' => [
        'table'    => 'qb_subtopics',
        'pk'       => 'subtopic_id',
        'writable' => ['chapter_id', 'subtopic_name'],
        'list_sql' => "SELECT st.subtopic_id, st.subtopic_name, c.chapter_name, st.chapter_id,
                              COUNT(DISTINCT q.question_id) AS question_count,
                              $q_stats
                       FROM qb_subtopics st
                       JOIN qb_chapters  c ON c.chapter_id  = st.chapter_id
                       LEFT JOIN qb_questions q ON q.subtopic_id = st.subtopic_id
                       GROUP BY st.subtopic_id ORDER BY c.chapter_name, st.subtopic_name",
    ],
    'bloom_levels' => [
        'table'    => 'qb_bloom_levels',
        'pk'       => 'bloom_id',
        'writable' => ['bloom_name', 'description'],
        'list_sql' => "SELECT bl.bloom_id, bl.bloom_name, bl.description,
                              COUNT(DISTINCT q.question_id) AS question_count,
                              $q_stats
                       FROM qb_bloom_levels bl
                       LEFT JOIN qb_questions q ON q.bloom_id = bl.bloom_id
                       GROUP BY bl.bloom_id ORDER BY bl.bloom_id",
    ],
    'difficulty_levels' => [
        'table'    => 'qb_difficulty_levels',
        'pk'       => 'difficulty_id',
        'writable' => ['difficulty_name'],
        'list_sql' => "SELECT dl.difficulty_id, dl.difficulty_name,
                              COUNT(DISTINCT q.question_id) AS question_count,
                              $q_stats
                       FROM qb_difficulty_levels dl
                       LEFT JOIN qb_questions q ON q.difficulty_id = dl.difficulty_id
                       GROUP BY dl.difficulty_id ORDER BY dl.difficulty_id",
    ],
    'sections' => [
        'table'    => 'qb_sections',
        'pk'       => 'section_id',
        'writable' => ['section_name'],
        'list_sql' => "SELECT s.section_id, s.section_name,
                              COUNT(DISTINCT q.question_id) AS question_count,
                              $q_stats
                       FROM qb_sections s
                       LEFT JOIN qb_questions q ON q.section_id = s.section_id
                       GROUP BY s.section_id ORDER BY s.section_name",
    ],
];

/* ── Route ─────────────────────────────────────────────────── */
$entity_key = $_GET['entity'] ?? '';
$action     = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body       = json_decode(file_get_contents('php://input'), true) ?? [];
    $action     = $body['action']     ?? $action;
    $entity_key = $body['entity']     ?? $entity_key;
} else {
    $body = [];
}

if (!isset($entities[$entity_key])) {
    send_json(['status' => 'error', 'message' => "Unknown entity: $entity_key"]);
}

$cfg   = $entities[$entity_key];
$table = $cfg['table'];
$pk    = $cfg['pk'];

/* ── LIST ───────────────────────────────────────────────────── */
if ($action === 'list') {
    $res = $db->query($cfg['list_sql']);
    if (!$res) send_json(['status'=>'error','message'=>$db->error]);
    send_json(['status'=>'success','data'=>$res->fetch_all(MYSQLI_ASSOC)]);
}

/* ── CREATE ─────────────────────────────────────────────────── */
if ($action === 'create') {
    $cols = $vals = $types = $params = [];
    foreach ($cfg['writable'] as $col) {
        $v = isset($body[$col]) ? trim($body[$col]) : null;
        if ($v === '' || $v === null) continue;
        $cols[] = "`$col`"; $vals[] = '?';
        $types[] = is_numeric($v) ? 'i' : 's';
        $params[] = is_numeric($v) ? (int)$v : $v;
    }
    if (empty($cols)) send_json(['status'=>'error','message'=>'No data provided']);
    $stmt = $db->prepare("INSERT INTO $table (".implode(',',$cols).") VALUES (".implode(',',$vals).")");
    if (!$stmt) send_json(['status'=>'error','message'=>$db->error]);
    $stmt->bind_param(implode('',$types), ...$params);
    if ($stmt->execute()) {
        send_json(['status'=>'success','message'=>'Record created','id'=>$db->insert_id]);
    } else {
        $msg = str_contains($stmt->error,'Duplicate') ? 'This record already exists.' : $stmt->error;
        send_json(['status'=>'error','message'=>$msg]);
    }
}

/* ── UPDATE ─────────────────────────────────────────────────── */
if ($action === 'update') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) send_json(['status'=>'error','message'=>'ID missing']);
    $sets = $types = $params = [];
    foreach ($cfg['writable'] as $col) {
        $v = isset($body[$col]) ? trim($body[$col]) : null;
        if ($v === null) continue;
        $sets[] = "`$col` = ?";
        $types[] = is_numeric($v) ? 'i' : 's';
        $params[] = is_numeric($v) ? (int)$v : $v;
    }
    if (empty($sets)) send_json(['status'=>'error','message'=>'Nothing to update']);
    $params[] = $id; $types[] = 'i';
    $stmt = $db->prepare("UPDATE $table SET ".implode(',',$sets)." WHERE $pk = ?");
    $stmt->bind_param(implode('',$types), ...$params);
    if ($stmt->execute()) {
        send_json(['status'=>'success','message'=>'Record updated']);
    } else {
        send_json(['status'=>'error','message'=>$stmt->error]);
    }
}

/* ── DELETE ─────────────────────────────────────────────────── */
if ($action === 'delete') {
    $id = (int)($body['id'] ?? 0);
    if (!$id) send_json(['status'=>'error','message'=>'ID missing']);
    $stmt = $db->prepare("DELETE FROM $table WHERE $pk = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        send_json(['status'=>'success','message'=>'Record deleted']);
    } else {
        $msg = str_contains($stmt->error,'foreign key') ? 'Cannot delete — this record is used by questions or other data.' : $stmt->error;
        send_json(['status'=>'error','message'=>$msg]);
    }
}

send_json(['status'=>'error','message'=>"Unknown action: $action"]);
