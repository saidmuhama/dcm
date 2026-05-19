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

header('Content-Type: application/json');

/* ══════════════════════════════════════════════════════════════
   AUTO-CREATE EXAM TABLES (runs on every request, harmless)
══════════════════════════════════════════════════════════════ */
$db->query("CREATE TABLE IF NOT EXISTS qb_exams (
  exam_id           BIGINT AUTO_INCREMENT PRIMARY KEY,
  exam_title        VARCHAR(255) NOT NULL,
  exam_code         VARCHAR(60) UNIQUE,
  subject_id        INT,
  level_id          INT,
  description       TEXT,
  instructions      TEXT,
  duration_minutes  INT DEFAULT 60,
  total_marks       DECIMAL(8,2) DEFAULT 0,
  passing_marks     DECIMAL(8,2) DEFAULT 0,
  exam_type         ENUM('manual','random') DEFAULT 'manual',
  status            ENUM('draft','published','archived') DEFAULT 'draft',
  shuffle_questions TINYINT(1) DEFAULT 0,
  shuffle_options   TINYINT(1) DEFAULT 0,
  show_answers_after TINYINT(1) DEFAULT 0,
  created_by        BIGINT,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS qb_exam_questions (
  eq_id          BIGINT AUTO_INCREMENT PRIMARY KEY,
  exam_id        BIGINT NOT NULL,
  question_id    BIGINT NOT NULL,
  sort_order     INT DEFAULT 0,
  marks_override DECIMAL(5,2) NULL,
  UNIQUE KEY uq_exam_question (exam_id, question_id),
  KEY idx_exam_id (exam_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS qb_exam_sessions (
  session_id          BIGINT AUTO_INCREMENT PRIMARY KEY,
  exam_id             BIGINT NOT NULL,
  student_id          VARCHAR(50),
  started_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  submitted_at        TIMESTAMP NULL,
  score               DECIMAL(8,2) DEFAULT 0,
  total_marks         DECIMAL(8,2) DEFAULT 0,
  status              ENUM('in_progress','submitted','graded') DEFAULT 'in_progress',
  time_taken_seconds  INT DEFAULT 0,
  KEY idx_exam_id (exam_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS qb_exam_answers (
  answer_id      BIGINT AUTO_INCREMENT PRIMARY KEY,
  session_id     BIGINT NOT NULL,
  question_id    BIGINT NOT NULL,
  answer_given   TEXT,
  is_correct     TINYINT(1) DEFAULT 0,
  marks_awarded  DECIMAL(5,2) DEFAULT 0,
  KEY idx_session_id (session_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* ══════════════════════════════════════════════════════════════
   ROUTING
══════════════════════════════════════════════════════════════ */
$action = $_GET['action'] ?? '';
$body   = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw    = file_get_contents('php://input');
    $body   = json_decode($raw, true) ?? [];
    $action = $body['action'] ?? $action;
}

/* ── Helper: recalculate & update total_marks for an exam ───── */
function recalcTotalMarks($db, $exam_id) {
    $exam_id = (int)$exam_id;
    $stmt = $db->prepare(
        "SELECT SUM(COALESCE(eq.marks_override, q.marks)) AS tm
         FROM qb_exam_questions eq
         JOIN qb_questions q ON q.question_id = eq.question_id
         WHERE eq.exam_id = ?"
    );
    $stmt->bind_param('i', $exam_id);
    $stmt->execute();
    $tm = (float)($stmt->get_result()->fetch_assoc()['tm'] ?? 0);
    $up = $db->prepare("UPDATE qb_exams SET total_marks = ? WHERE exam_id = ?");
    $up->bind_param('di', $tm, $exam_id);
    $up->execute();
    return $tm;
}

/* ── Helper: generate exam code ─────────────────────────────── */
function generateExamCode($db, $subject_id, $level_id) {
    // Subject code
    $stmt = $db->prepare("SELECT subject_code FROM qb_subjects WHERE subject_id = ?");
    $stmt->bind_param('i', $subject_id);
    $stmt->execute();
    $subj_row = $stmt->get_result()->fetch_assoc();
    $subj_code = strtoupper($subj_row['subject_code'] ?? 'GEN');

    // Level abbreviation
    $stmt2 = $db->prepare("SELECT level_name FROM qb_levels WHERE level_id = ?");
    $stmt2->bind_param('i', $level_id);
    $stmt2->execute();
    $lev_row = $stmt2->get_result()->fetch_assoc();
    $level_name = $lev_row['level_name'] ?? '';

    // Abbreviate level name: "Standard 7" -> STD7, "Form 2" -> F2, "Grade 4" -> G4
    $lev_abbrev = preg_replace_callback(
        '/^(Standard|Std)\s*(\d+)$/i', fn($m) => 'STD'.$m[2], $level_name
    );
    if ($lev_abbrev === $level_name) {
        $lev_abbrev = preg_replace_callback(
            '/^Form\s*(\d+)$/i', fn($m) => 'F'.$m[1], $level_name
        );
    }
    if ($lev_abbrev === $level_name) {
        $lev_abbrev = preg_replace_callback(
            '/^Grade\s*(\d+)$/i', fn($m) => 'G'.$m[1], $level_name
        );
    }
    if ($lev_abbrev === $level_name) {
        // Fallback: take capital letters + digits
        preg_match_all('/[A-Z0-9]+/i', $level_name, $m2);
        $lev_abbrev = strtoupper(implode('', array_map(fn($w) => $w[0], $m2[0])));
        if (!$lev_abbrev) $lev_abbrev = 'LVL';
    }
    $lev_abbrev = strtoupper($lev_abbrev);

    // Sequence: count exams for this subject+level
    $stmt3 = $db->prepare("SELECT COUNT(*) AS cnt FROM qb_exams WHERE subject_id = ? AND level_id = ?");
    $stmt3->bind_param('ii', $subject_id, $level_id);
    $stmt3->execute();
    $cnt = (int)($stmt3->get_result()->fetch_assoc()['cnt'] ?? 0);
    $seq = str_pad($cnt + 1, 3, '0', STR_PAD_LEFT);

    return "EXM-{$subj_code}-{$lev_abbrev}-{$seq}";
}

/* ══════════════════════════════════════════════════════════════
   ACTION: counts
══════════════════════════════════════════════════════════════ */
if ($action === 'counts') {
    $total   = (int)$db->query("SELECT COUNT(*) AS c FROM qb_exams")->fetch_assoc()['c'];
    $pub     = (int)$db->query("SELECT COUNT(*) AS c FROM qb_exams WHERE status='published'")->fetch_assoc()['c'];
    $draft   = (int)$db->query("SELECT COUNT(*) AS c FROM qb_exams WHERE status='draft'")->fetch_assoc()['c'];
    $archived= (int)$db->query("SELECT COUNT(*) AS c FROM qb_exams WHERE status='archived'")->fetch_assoc()['c'];
    $qcount  = (int)$db->query("SELECT COUNT(*) AS c FROM qb_exam_questions")->fetch_assoc()['c'];
    $sessions= (int)$db->query("SELECT COUNT(*) AS c FROM qb_exam_sessions")->fetch_assoc()['c'];

    echo json_encode(['status'=>'success','data'=>[
        'total_exams'             => $total,
        'published'               => $pub,
        'draft'                   => $draft,
        'archived'                => $archived,
        'total_questions_in_exams'=> $qcount,
        'sessions_count'          => $sessions,
    ]]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: list
══════════════════════════════════════════════════════════════ */
if ($action === 'list') {
    $status = trim($_GET['status'] ?? '');
    $where  = '1=1';
    $params = [];
    $types  = '';
    if ($status !== '') { $where .= ' AND e.status = ?'; $types .= 's'; $params[] = $status; }

    $sql = "SELECT e.exam_id, e.exam_title, e.exam_code, e.subject_id, e.level_id,
                   e.duration_minutes, e.total_marks, e.passing_marks, e.exam_type,
                   e.status, e.shuffle_questions, e.shuffle_options, e.show_answers_after,
                   e.created_at,
                   s.subject_name, l.level_name,
                   COUNT(eq.eq_id) AS question_count
            FROM qb_exams e
            LEFT JOIN qb_subjects s ON s.subject_id = e.subject_id
            LEFT JOIN qb_levels l ON l.level_id = e.level_id
            LEFT JOIN qb_exam_questions eq ON eq.exam_id = e.exam_id
            WHERE $where
            GROUP BY e.exam_id
            ORDER BY e.created_at DESC";

    $stmt = $db->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: get (single exam + questions)
══════════════════════════════════════════════════════════════ */
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) { echo json_encode(['status'=>'error','message'=>'ID missing']); exit; }

    $stmt = $db->prepare(
        "SELECT e.*, s.subject_name, l.level_name
         FROM qb_exams e
         LEFT JOIN qb_subjects s ON s.subject_id = e.subject_id
         LEFT JOIN qb_levels l ON l.level_id = e.level_id
         WHERE e.exam_id = ?"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $exam = $stmt->get_result()->fetch_assoc();
    if (!$exam) { echo json_encode(['status'=>'error','message'=>'Exam not found']); exit; }

    $stmt2 = $db->prepare(
        "SELECT eq.eq_id, eq.exam_id, eq.question_id, eq.sort_order, eq.marks_override,
                q.q_uid, q.question_stem, q.question_type, q.marks,
                d.difficulty_name, s.subject_name, l.level_name
         FROM qb_exam_questions eq
         JOIN qb_questions q ON q.question_id = eq.question_id
         LEFT JOIN qb_difficulty_levels d ON d.difficulty_id = q.difficulty_id
         LEFT JOIN qb_subjects s ON s.subject_id = q.subject_id
         LEFT JOIN qb_levels l ON l.level_id = q.level_id
         WHERE eq.exam_id = ?
         ORDER BY eq.sort_order ASC, eq.eq_id ASC"
    );
    $stmt2->bind_param('i', $id);
    $stmt2->execute();
    $exam['questions'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','data'=>$exam]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: save (create or update)
══════════════════════════════════════════════════════════════ */
if ($action === 'save') {
    $exam_id          = (int)($body['exam_id'] ?? 0);
    $exam_title       = trim($body['exam_title'] ?? '');
    $subject_id       = (int)($body['subject_id'] ?? 0);
    $level_id         = (int)($body['level_id'] ?? 0);
    $description      = trim($body['description'] ?? '');
    $instructions     = trim($body['instructions'] ?? '');
    $duration_minutes = (int)($body['duration_minutes'] ?? 60);
    $total_marks      = (float)($body['total_marks'] ?? 0);
    $passing_marks    = (float)($body['passing_marks'] ?? 0);
    $exam_type        = in_array($body['exam_type']??'', ['manual','random']) ? $body['exam_type'] : 'manual';
    $status           = in_array($body['status']??'', ['draft','published','archived']) ? $body['status'] : 'draft';
    $shuffle_questions= (int)($body['shuffle_questions'] ?? 0) ? 1 : 0;
    $shuffle_options  = (int)($body['shuffle_options']   ?? 0) ? 1 : 0;
    $show_answers_after=(int)($body['show_answers_after']?? 0) ? 1 : 0;
    $created_by       = $_SESSION['user_id'] ?? 0;

    if (!$exam_title) { echo json_encode(['status'=>'error','message'=>'Exam title is required']); exit; }

    if ($exam_id) {
        // Update
        $stmt = $db->prepare(
            "UPDATE qb_exams SET exam_title=?, subject_id=?, level_id=?, description=?,
             instructions=?, duration_minutes=?, passing_marks=?, exam_type=?, status=?,
             shuffle_questions=?, shuffle_options=?, show_answers_after=?
             WHERE exam_id=?"
        );
        $stmt->bind_param(
            'siissidssiiii',
            $exam_title, $subject_id, $level_id, $description,
            $instructions, $duration_minutes, $passing_marks, $exam_type, $status,
            $shuffle_questions, $shuffle_options, $show_answers_after,
            $exam_id
        );
        if ($stmt->execute()) {
            $tm = recalcTotalMarks($db, $exam_id);
            echo json_encode(['status'=>'success','message'=>'Exam updated','exam_id'=>$exam_id,'total_marks'=>$tm]);
        } else {
            echo json_encode(['status'=>'error','message'=>$stmt->error]);
        }
    } else {
        // Create
        $exam_code = generateExamCode($db, $subject_id, $level_id);
        $stmt = $db->prepare(
            "INSERT INTO qb_exams (exam_title, exam_code, subject_id, level_id, description,
             instructions, duration_minutes, total_marks, passing_marks, exam_type, status,
             shuffle_questions, shuffle_options, show_answers_after, created_by)
             VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
        );
        $stmt->bind_param(
            'ssiissiddssiiii',
            $exam_title, $exam_code, $subject_id, $level_id, $description,
            $instructions, $duration_minutes, $total_marks, $passing_marks, $exam_type, $status,
            $shuffle_questions, $shuffle_options, $show_answers_after, $created_by
        );
        if ($stmt->execute()) {
            $new_id = $db->insert_id;
            echo json_encode(['status'=>'success','message'=>'Exam created','exam_id'=>$new_id,'exam_code'=>$exam_code]);
        } else {
            echo json_encode(['status'=>'error','message'=>$stmt->error]);
        }
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: delete
══════════════════════════════════════════════════════════════ */
if ($action === 'delete') {
    $exam_id = (int)($body['exam_id'] ?? 0);
    if (!$exam_id) { echo json_encode(['status'=>'error','message'=>'Exam ID missing']); exit; }

    $db->begin_transaction();
    try {
        $s1 = $db->prepare("DELETE FROM qb_exam_questions WHERE exam_id = ?");
        $s1->bind_param('i', $exam_id); $s1->execute();

        $s2 = $db->prepare("DELETE FROM qb_exams WHERE exam_id = ?");
        $s2->bind_param('i', $exam_id); $s2->execute();

        $db->commit();
        echo json_encode(['status'=>'success','message'=>'Exam deleted']);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: add_question
══════════════════════════════════════════════════════════════ */
if ($action === 'add_question') {
    $exam_id     = (int)($body['exam_id']     ?? 0);
    $question_id = (int)($body['question_id'] ?? 0);
    if (!$exam_id || !$question_id) { echo json_encode(['status'=>'error','message'=>'Missing exam_id or question_id']); exit; }

    // Get next sort_order
    $stmt = $db->prepare("SELECT COALESCE(MAX(sort_order),0)+1 AS nxt FROM qb_exam_questions WHERE exam_id=?");
    $stmt->bind_param('i', $exam_id); $stmt->execute();
    $nxt = (int)$stmt->get_result()->fetch_assoc()['nxt'];

    $ins = $db->prepare("INSERT IGNORE INTO qb_exam_questions (exam_id, question_id, sort_order) VALUES (?,?,?)");
    $ins->bind_param('iii', $exam_id, $question_id, $nxt);
    if ($ins->execute()) {
        $tm = recalcTotalMarks($db, $exam_id);
        echo json_encode(['status'=>'success','message'=>'Question added','total_marks'=>$tm]);
    } else {
        echo json_encode(['status'=>'error','message'=>$ins->error]);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: remove_question
══════════════════════════════════════════════════════════════ */
if ($action === 'remove_question') {
    $eq_id   = (int)($body['eq_id']   ?? 0);
    $exam_id = (int)($body['exam_id'] ?? 0);
    if (!$eq_id) { echo json_encode(['status'=>'error','message'=>'eq_id missing']); exit; }

    $stmt = $db->prepare("DELETE FROM qb_exam_questions WHERE eq_id=?");
    $stmt->bind_param('i', $eq_id);
    if ($stmt->execute()) {
        $tm = recalcTotalMarks($db, $exam_id);
        echo json_encode(['status'=>'success','message'=>'Question removed','total_marks'=>$tm]);
    } else {
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: reorder
══════════════════════════════════════════════════════════════ */
if ($action === 'reorder') {
    $items = $body['items'] ?? [];
    if (empty($items)) { echo json_encode(['status'=>'error','message'=>'No items']); exit; }

    $stmt = $db->prepare("UPDATE qb_exam_questions SET sort_order=? WHERE eq_id=?");
    foreach ($items as $item) {
        $eq_id      = (int)($item['eq_id']      ?? 0);
        $sort_order = (int)($item['sort_order']  ?? 0);
        if ($eq_id) { $stmt->bind_param('ii', $sort_order, $eq_id); $stmt->execute(); }
    }
    echo json_encode(['status'=>'success','message'=>'Reordered']);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: update_marks_override
══════════════════════════════════════════════════════════════ */
if ($action === 'update_marks_override') {
    $eq_id          = (int)($body['eq_id']          ?? 0);
    $exam_id        = (int)($body['exam_id']         ?? 0);
    $marks_override = $body['marks_override'] !== '' ? (float)$body['marks_override'] : null;

    if (!$eq_id) { echo json_encode(['status'=>'error','message'=>'eq_id missing']); exit; }

    if ($marks_override === null) {
        $stmt = $db->prepare("UPDATE qb_exam_questions SET marks_override=NULL WHERE eq_id=?");
        $stmt->bind_param('i', $eq_id);
    } else {
        $stmt = $db->prepare("UPDATE qb_exam_questions SET marks_override=? WHERE eq_id=?");
        $stmt->bind_param('di', $marks_override, $eq_id);
    }
    if ($stmt->execute()) {
        $tm = recalcTotalMarks($db, $exam_id);
        echo json_encode(['status'=>'success','total_marks'=>$tm]);
    } else {
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: question_search
══════════════════════════════════════════════════════════════ */
if ($action === 'question_search') {
    $q            = trim($_GET['q']            ?? '');
    $subject_id   = (int)($_GET['subject_id']  ?? 0);
    $level_id     = (int)($_GET['level_id']    ?? 0);
    $chapter_id   = (int)($_GET['chapter_id']  ?? 0);
    $difficulty_id= (int)($_GET['difficulty_id']?? 0);
    $type         = trim($_GET['type']         ?? '');
    $exam_id      = (int)($_GET['exam_id']     ?? 0);
    $page         = max(1, (int)($_GET['page']     ?? 1));
    $per_page     = min(50, max(5, (int)($_GET['per_page'] ?? 15)));
    $offset       = ($page - 1) * $per_page;

    $where  = ["q.status = 'published'"];
    $types  = '';
    $params = [];

    if ($exam_id) {
        $where[] = "q.question_id NOT IN (SELECT question_id FROM qb_exam_questions WHERE exam_id = $exam_id)";
    }
    if ($subject_id)    { $where[] = 'q.subject_id = ?';    $types .= 'i'; $params[] = $subject_id; }
    if ($level_id)      { $where[] = 'q.level_id = ?';      $types .= 'i'; $params[] = $level_id; }
    if ($chapter_id)    { $where[] = 'q.chapter_id = ?';    $types .= 'i'; $params[] = $chapter_id; }
    if ($difficulty_id) { $where[] = 'q.difficulty_id = ?'; $types .= 'i'; $params[] = $difficulty_id; }
    if ($type !== '')   { $where[] = 'q.question_type = ?'; $types .= 's'; $params[] = $type; }
    if ($q !== '') {
        $where[] = '(q.q_uid LIKE ? OR q.question_stem LIKE ?)';
        $types .= 'ss'; $params[] = "%$q%"; $params[] = "%$q%";
    }

    $whereStr = implode(' AND ', $where);

    $cntSql = "SELECT COUNT(*) AS cnt FROM qb_questions q WHERE $whereStr";
    $cntStmt = $db->prepare($cntSql);
    if ($params) $cntStmt->bind_param($types, ...$params);
    $cntStmt->execute();
    $total = (int)$cntStmt->get_result()->fetch_assoc()['cnt'];

    $sql = "SELECT q.question_id, q.q_uid, q.question_stem, q.question_type, q.marks,
                   s.subject_name, l.level_name, c.chapter_name, d.difficulty_name
            FROM qb_questions q
            LEFT JOIN qb_subjects s ON s.subject_id = q.subject_id
            LEFT JOIN qb_levels l ON l.level_id = q.level_id
            LEFT JOIN qb_chapters c ON c.chapter_id = q.chapter_id
            LEFT JOIN qb_difficulty_levels d ON d.difficulty_id = q.difficulty_id
            WHERE $whereStr
            ORDER BY q.question_id DESC
            LIMIT ? OFFSET ?";

    $allTypes  = $types . 'ii';
    $allParams = array_merge($params, [$per_page, $offset]);
    $stmt = $db->prepare($sql);
    $stmt->bind_param($allTypes, ...$allParams);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','data'=>$rows,'total'=>$total,'page'=>$page,'per_page'=>$per_page]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: random_generate
══════════════════════════════════════════════════════════════ */
if ($action === 'random_generate') {
    $subject_id        = (int)($body['subject_id']   ?? 0);
    $level_id          = (int)($body['level_id']     ?? 0);
    $chapter_ids       = array_map('intval', $body['chapter_ids']  ?? []);
    $difficulty_counts = $body['difficulty_counts']  ?? [];  // {diff_id => count}
    $bloom_ids         = array_map('intval', $body['bloom_ids']    ?? []);
    $type_filter       = trim($body['type_filter']   ?? '');

    $selected = [];

    foreach ($difficulty_counts as $diff_id => $count) {
        $diff_id = (int)$diff_id;
        $count   = (int)$count;
        if ($count <= 0) continue;

        $where  = ["q.status = 'published'"];
        $types  = '';
        $params = [];

        if ($subject_id)  { $where[] = 'q.subject_id = ?';    $types .= 'i'; $params[] = $subject_id; }
        if ($level_id)    { $where[] = 'q.level_id = ?';      $types .= 'i'; $params[] = $level_id; }
        if ($diff_id)     { $where[] = 'q.difficulty_id = ?'; $types .= 'i'; $params[] = $diff_id; }
        if ($type_filter) { $where[] = 'q.question_type = ?'; $types .= 's'; $params[] = $type_filter; }

        if (!empty($chapter_ids)) {
            $ph = implode(',', array_fill(0, count($chapter_ids), '?'));
            $where[] = "q.chapter_id IN ($ph)";
            $types .= str_repeat('i', count($chapter_ids));
            $params = array_merge($params, $chapter_ids);
        }
        if (!empty($bloom_ids)) {
            $ph = implode(',', array_fill(0, count($bloom_ids), '?'));
            $where[] = "q.bloom_id IN ($ph)";
            $types .= str_repeat('i', count($bloom_ids));
            $params = array_merge($params, $bloom_ids);
        }
        if (!empty($selected)) {
            $ph = implode(',', array_fill(0, count($selected), '?'));
            $where[] = "q.question_id NOT IN ($ph)";
            $types .= str_repeat('i', count($selected));
            $params = array_merge($params, $selected);
        }

        $whereStr = implode(' AND ', $where);
        $sql = "SELECT q.question_id, q.q_uid, q.question_stem, q.question_type, q.marks,
                       d.difficulty_name, s.subject_name, l.level_name
                FROM qb_questions q
                LEFT JOIN qb_difficulty_levels d ON d.difficulty_id = q.difficulty_id
                LEFT JOIN qb_subjects s ON s.subject_id = q.subject_id
                LEFT JOIN qb_levels l ON l.level_id = q.level_id
                WHERE $whereStr
                ORDER BY RAND()
                LIMIT ?";
        $types .= 'i'; $params[] = $count;

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        foreach ($rows as $r) {
            $selected[] = (int)$r['question_id'];
        }
        // Merge rows — we need to return them
        if (!isset($allRows)) $allRows = [];
        $allRows = array_merge($allRows ?? [], $rows);
    }

    echo json_encode(['status'=>'success','data'=>$allRows ?? [],'count'=>count($selected)]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: publish (change status)
══════════════════════════════════════════════════════════════ */
if ($action === 'publish') {
    $exam_id = (int)($body['exam_id'] ?? 0);
    $status  = in_array($body['status']??'', ['draft','published','archived']) ? $body['status'] : 'published';
    if (!$exam_id) { echo json_encode(['status'=>'error','message'=>'Exam ID missing']); exit; }

    $stmt = $db->prepare("UPDATE qb_exams SET status=? WHERE exam_id=?");
    $stmt->bind_param('si', $status, $exam_id);
    if ($stmt->execute()) {
        echo json_encode(['status'=>'success','message'=>'Status updated to '.$status]);
    } else {
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: session_list
══════════════════════════════════════════════════════════════ */
if ($action === 'session_list') {
    $status_filter = trim($_GET['status'] ?? '');
    $where = '1=1';
    $params = [];
    $types  = '';
    if ($status_filter) { $where .= ' AND es.status=?'; $types .= 's'; $params[] = $status_filter; }

    $sql = "SELECT es.session_id, es.exam_id, es.student_id, es.started_at, es.submitted_at,
                   es.score, es.total_marks, es.status, es.time_taken_seconds,
                   e.exam_title, e.exam_code,
                   CONCAT(COALESCE(u.first_name,''), ' ', COALESCE(u.last_name,'')) AS student_name
            FROM qb_exam_sessions es
            JOIN qb_exams e ON e.exam_id = es.exam_id
            LEFT JOIN tbl_all_users u ON u.usr_code = es.student_id
            WHERE $where
            ORDER BY es.started_at DESC";

    $stmt = $db->prepare($sql);
    if ($params) $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: session_results
══════════════════════════════════════════════════════════════ */
if ($action === 'session_results') {
    $session_id = (int)($_GET['session_id'] ?? 0);
    if (!$session_id) { echo json_encode(['status'=>'error','message'=>'session_id missing']); exit; }

    $stmt = $db->prepare(
        "SELECT es.*, e.exam_title, e.exam_code,
                CONCAT(COALESCE(u.first_name,''),' ',COALESCE(u.last_name,'')) AS student_name
         FROM qb_exam_sessions es
         JOIN qb_exams e ON e.exam_id = es.exam_id
         LEFT JOIN tbl_all_users u ON u.usr_code = es.student_id
         WHERE es.session_id = ?"
    );
    $stmt->bind_param('i', $session_id);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    if (!$session) { echo json_encode(['status'=>'error','message'=>'Session not found']); exit; }

    $stmt2 = $db->prepare(
        "SELECT ea.*, q.question_stem, q.question_type, q.correct_answer, q.marks,
                q.q_uid,
                GROUP_CONCAT(
                    CONCAT(qo.option_label,'. ',qo.option_text,'|',qo.is_correct)
                    ORDER BY qo.sort_order SEPARATOR ';;'
                ) AS options_str
         FROM qb_exam_answers ea
         JOIN qb_questions q ON q.question_id = ea.question_id
         LEFT JOIN qb_question_options qo ON qo.question_id = q.question_id
         WHERE ea.session_id = ?
         GROUP BY ea.answer_id"
    );
    $stmt2->bind_param('i', $session_id);
    $stmt2->execute();
    $session['answers'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','data'=>$session]);
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: start_session
══════════════════════════════════════════════════════════════ */
if ($action === 'start_session') {
    $exam_id    = (int)($body['exam_id']    ?? 0);
    $student_id = trim($body['student_id']  ?? '');
    if (!$exam_id || !$student_id) { echo json_encode(['status'=>'error','message'=>'exam_id and student_id required']); exit; }

    // Get total_marks for this exam
    $row = $db->query("SELECT total_marks FROM qb_exams WHERE exam_id=$exam_id")->fetch_assoc();
    $tm  = (float)($row['total_marks'] ?? 0);

    $stmt = $db->prepare("INSERT INTO qb_exam_sessions (exam_id, student_id, total_marks) VALUES (?,?,?)");
    $stmt->bind_param('isd', $exam_id, $student_id, $tm);
    if ($stmt->execute()) {
        echo json_encode(['status'=>'success','session_id'=>$db->insert_id]);
    } else {
        echo json_encode(['status'=>'error','message'=>$stmt->error]);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: delete_session
══════════════════════════════════════════════════════════════ */
if ($action === 'delete_session') {
    $session_id = (int)($body['session_id'] ?? 0);
    if (!$session_id) { echo json_encode(['status'=>'error','message'=>'session_id missing']); exit; }

    $db->begin_transaction();
    try {
        $s1 = $db->prepare("DELETE FROM qb_exam_answers WHERE session_id=?");
        $s1->bind_param('i', $session_id); $s1->execute();
        $s2 = $db->prepare("DELETE FROM qb_exam_sessions WHERE session_id=?");
        $s2->bind_param('i', $session_id); $s2->execute();
        $db->commit();
        echo json_encode(['status'=>'success','message'=>'Session deleted']);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status'=>'error','message'=>$e->getMessage()]);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════
   ACTION: student_search (for CBT)
══════════════════════════════════════════════════════════════ */
if ($action === 'student_search') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) < 2) { echo json_encode(['status'=>'success','data'=>[]]); exit; }

    $like = "%$q%";
    $stmt = $db->prepare(
        "SELECT usr_code, first_name, last_name, email
         FROM tbl_all_users
         WHERE role = 1 AND (first_name LIKE ? OR last_name LIKE ? OR usr_code LIKE ?)
         LIMIT 20"
    );
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
    exit;
}

/* ── Fallback ─────────────────────────────────────────────── */
echo json_encode(['status'=>'error','message'=>"Unknown action: $action"]);
