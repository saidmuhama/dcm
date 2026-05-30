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

/* ── Ensure extra columns exist — checked once per session ─────── */
if (empty($_SESSION['_exam_cols_ok'])) {
    $cols_needed = [
        'question_order' => 'TEXT NULL',
        'option_orders'  => 'TEXT NULL',
        'flagged'        => 'TEXT NULL',
    ];
    // Single fast query: fetch all existing column names at once
    $existing_cols = [];
    $chk = $db->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'qb_exam_sessions'");
    if ($chk) {
        while ($r = $chk->fetch_row()) $existing_cols[] = $r[0];
    }
    foreach ($cols_needed as $col => $def) {
        if (!in_array($col, $existing_cols)) {
            $db->query("ALTER TABLE `qb_exam_sessions` ADD COLUMN `$col` $def");
        }
    }
    $_SESSION['_exam_cols_ok'] = 1;
}

/* ── Routing ─────────────────────────────────────────────────── */
$action = $_GET['action'] ?? '';
$body   = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];
    $action = $body['action'] ?? $action;
}

$student_id = $_SESSION['usr_code'] ?? null;
if (!$student_id) send_json(['status' => 'error', 'message' => 'Not authenticated']);

/* ══════════════════════════════════════════════════════════════
   AVAILABLE EXAMS
══════════════════════════════════════════════════════════════ */
if ($action === 'available') {
    $sql = "SELECT e.exam_id, e.exam_title, e.exam_code, e.description,
                   e.duration_minutes, e.total_marks, e.passing_marks,
                   e.exam_type, e.show_answers_after, e.created_at,
                   s.subject_name, l.level_name,
                   COUNT(DISTINCT eq.eq_id) AS question_count,
                   MAX(es.session_id)    AS my_session_id,
                   MAX(es.status)        AS my_latest_status,
                   MAX(es.score)         AS my_best_score,
                   COUNT(DISTINCT es.session_id) AS my_attempt_count
            FROM qb_exams e
            LEFT JOIN qb_subjects s ON s.subject_id = e.subject_id
            LEFT JOIN qb_levels   l ON l.level_id   = e.level_id
            LEFT JOIN qb_exam_questions eq ON eq.exam_id = e.exam_id
            LEFT JOIN qb_exam_sessions  es ON es.exam_id = e.exam_id AND es.student_id = ?
            WHERE e.status = 'published'
            GROUP BY e.exam_id
            ORDER BY e.created_at DESC";
    $stmt = $db->prepare($sql);
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    send_json(['status' => 'success', 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

/* ══════════════════════════════════════════════════════════════
   STUDENT STATS
══════════════════════════════════════════════════════════════ */
if ($action === 'stats') {
    $stmt = $db->prepare("
        SELECT
            COUNT(*)                                                AS total_sessions,
            SUM(status IN ('submitted','graded'))                  AS completed,
            SUM(status = 'in_progress')                            AS in_progress,
            ROUND(AVG(CASE WHEN status IN ('submitted','graded')
                          THEN score / NULLIF(total_marks,0) * 100
                      END), 1)                                     AS avg_pct,
            SUM(CASE WHEN status IN ('submitted','graded')
                         AND score >= (SELECT e2.passing_marks FROM qb_exams e2
                                       WHERE e2.exam_id = qb_exam_sessions.exam_id)
                     THEN 1 ELSE 0 END)                            AS passed
        FROM qb_exam_sessions WHERE student_id = ?");
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    send_json(['status' => 'success', 'data' => $stmt->get_result()->fetch_assoc()]);
}

/* ══════════════════════════════════════════════════════════════
   START / RESUME SESSION
══════════════════════════════════════════════════════════════ */
if ($action === 'start') {
    $exam_id = (int)($body['exam_id'] ?? 0);
    if (!$exam_id) send_json(['status' => 'error', 'message' => 'exam_id required']);

    $stmt = $db->prepare("SELECT * FROM qb_exams WHERE exam_id = ? AND status = 'published'");
    $stmt->bind_param('i', $exam_id);
    $stmt->execute();
    $exam = $stmt->get_result()->fetch_assoc();
    if (!$exam) send_json(['status' => 'error', 'message' => 'Exam not found or not published']);

    // Resume in-progress session if any
    $stmt2 = $db->prepare("SELECT * FROM qb_exam_sessions WHERE exam_id = ? AND student_id = ? AND status = 'in_progress' ORDER BY started_at DESC LIMIT 1");
    $stmt2->bind_param('is', $exam_id, $student_id);
    $stmt2->execute();
    $existing = $stmt2->get_result()->fetch_assoc();
    if ($existing) {
        send_json(['status' => 'success', 'session_id' => $existing['session_id'], 'resumed' => true]);
    }

    $stmt3 = $db->prepare("INSERT INTO qb_exam_sessions (exam_id, student_id, total_marks) VALUES (?,?,?)");
    $tm = (float)$exam['total_marks'];
    $stmt3->bind_param('isd', $exam_id, $student_id, $tm);
    if ($stmt3->execute()) {
        send_json(['status' => 'success', 'session_id' => $db->insert_id, 'resumed' => false]);
    }
    send_json(['status' => 'error', 'message' => 'Could not start session: ' . $db->error]);
}

/* ══════════════════════════════════════════════════════════════
   GET QUESTIONS (with saved answers, shuffled order)
══════════════════════════════════════════════════════════════ */
if ($action === 'questions') {
    $session_id = (int)($_GET['session_id'] ?? 0);
    if (!$session_id) send_json(['status' => 'error', 'message' => 'session_id required']);

    $stmt = $db->prepare("
        SELECT es.*, e.shuffle_questions, e.shuffle_options, e.duration_minutes,
               e.show_answers_after, e.exam_title, e.exam_code, e.instructions,
               e.passing_marks
        FROM qb_exam_sessions es
        JOIN qb_exams e ON e.exam_id = es.exam_id
        WHERE es.session_id = ? AND es.student_id = ?");
    $stmt->bind_param('is', $session_id, $student_id);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    if (!$session) send_json(['status' => 'error', 'message' => 'Session not found']);
    if ($session['status'] !== 'in_progress') {
        send_json(['status' => 'error', 'message' => 'Exam already submitted', 'session_status' => $session['status']]);
    }

    // Load questions
    $stmt2 = $db->prepare("
        SELECT eq.eq_id, eq.question_id, eq.sort_order, eq.marks_override,
               q.q_uid, q.question_stem, q.question_type, q.marks,
               d.difficulty_name, c.chapter_name, st.subtopic_name
        FROM qb_exam_questions eq
        JOIN qb_questions q ON q.question_id = eq.question_id
        LEFT JOIN qb_difficulty_levels d  ON d.difficulty_id = q.difficulty_id
        LEFT JOIN qb_chapters          c  ON c.chapter_id    = q.chapter_id
        LEFT JOIN qb_subtopics         st ON st.subtopic_id  = q.subtopic_id
        WHERE eq.exam_id = ?
        ORDER BY eq.sort_order ASC, eq.eq_id ASC");
    $stmt2->bind_param('i', $session['exam_id']);
    $stmt2->execute();
    $questions = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);

    // Determine/persist question order
    $q_order = $session['question_order'] ? json_decode($session['question_order'], true) : null;
    if (!$q_order) {
        $ids = array_column($questions, 'question_id');
        if ($session['shuffle_questions']) shuffle($ids);
        $q_order = $ids;
        $qo = json_encode($q_order);
        $u = $db->prepare("UPDATE qb_exam_sessions SET question_order = ? WHERE session_id = ?");
        $u->bind_param('si', $qo, $session_id);
        $u->execute();
    }
    $qmap = array_column($questions, null, 'question_id');
    $questions = array_values(array_filter(array_map(fn($id) => $qmap[$id] ?? null, $q_order)));

    // Determine/persist option orders
    $opt_orders = $session['option_orders'] ? json_decode($session['option_orders'], true) : [];
    $opts_changed = false;

    // Load ALL options in one query (eliminates N+1)
    $all_opts = [];
    if (!empty($questions)) {
        $qids    = array_map(fn($q) => (int)$q['question_id'], $questions);
        $ph      = implode(',', $qids);
        $optRows = $db->query("SELECT question_id, option_label, option_text
                                FROM qb_question_options
                                WHERE question_id IN ($ph)
                                ORDER BY sort_order ASC, option_label ASC");
        if ($optRows) {
            while ($r = $optRows->fetch_assoc()) {
                $all_opts[(int)$r['question_id']][] = $r;
            }
        }
    }

    foreach ($questions as &$q) {
        $qid  = (int)$q['question_id'];
        $opts = $all_opts[$qid] ?? [];

        if ($session['shuffle_options'] && !isset($opt_orders[$qid]) && count($opts) > 1) {
            $labels = array_column($opts, 'option_label');
            shuffle($labels);
            $opt_orders[$qid] = $labels;
            $opts_changed = true;
        }
        if (isset($opt_orders[$qid])) {
            $omap = array_column($opts, null, 'option_label');
            $opts = array_values(array_filter(array_map(fn($l) => $omap[$l] ?? null, $opt_orders[$qid])));
        }
        $q['options'] = $opts;
        $q['effective_marks'] = (float)($q['marks_override'] ?? $q['marks'] ?? 0);
    }
    unset($q);

    if ($opts_changed) {
        $oo = json_encode($opt_orders);
        $u2 = $db->prepare("UPDATE qb_exam_sessions SET option_orders = ? WHERE session_id = ?");
        $u2->bind_param('si', $oo, $session_id);
        $u2->execute();
    }

    // Load saved answers
    $astmt = $db->prepare("SELECT question_id, answer_given FROM qb_exam_answers WHERE session_id = ?");
    $astmt->bind_param('i', $session_id);
    $astmt->execute();
    $saved = [];
    $saved_res = $astmt->get_result();
    while ($r = $saved_res->fetch_assoc()) {
        $saved[(int)$r['question_id']] = $r['answer_given'];
    }

    // Time remaining
    $elapsed   = time() - strtotime($session['started_at']);
    $remaining = max(0, $session['duration_minutes'] * 60 - $elapsed);

    send_json([
        'status'    => 'success',
        'session'   => [
            'session_id'       => (int)$session['session_id'],
            'exam_id'          => (int)$session['exam_id'],
            'exam_title'       => $session['exam_title'],
            'exam_code'        => $session['exam_code'],
            'instructions'     => $session['instructions'],
            'duration_minutes' => (int)$session['duration_minutes'],
            'passing_marks'    => (float)$session['passing_marks'],
            'started_at'       => $session['started_at'],
            'flagged'          => $session['flagged'] ? json_decode($session['flagged'], true) : [],
            'remaining_seconds'=> (int)$remaining,
        ],
        'questions'     => $questions,
        'saved_answers' => $saved,
    ]);
}

/* ══════════════════════════════════════════════════════════════
   SAVE ANSWER (auto-save, upsert)
══════════════════════════════════════════════════════════════ */
if ($action === 'save_answer') {
    $session_id  = (int)($body['session_id']  ?? 0);
    $question_id = (int)($body['question_id'] ?? 0);
    $answer      = $body['answer_given'] ?? '';

    if (!$session_id || !$question_id) send_json(['status' => 'error', 'message' => 'Missing params']);

    // Verify session
    $chk = $db->prepare("SELECT session_id FROM qb_exam_sessions WHERE session_id = ? AND student_id = ? AND status = 'in_progress'");
    $chk->bind_param('is', $session_id, $student_id);
    $chk->execute();
    if (!$chk->get_result()->fetch_assoc()) send_json(['status' => 'error', 'message' => 'Session invalid']);

    // Upsert
    $ex = $db->prepare("SELECT answer_id FROM qb_exam_answers WHERE session_id = ? AND question_id = ?");
    $ex->bind_param('ii', $session_id, $question_id);
    $ex->execute();
    if ($ex->get_result()->fetch_assoc()) {
        $upd = $db->prepare("UPDATE qb_exam_answers SET answer_given = ? WHERE session_id = ? AND question_id = ?");
        $upd->bind_param('sii', $answer, $session_id, $question_id);
        $upd->execute();
    } else {
        $ins = $db->prepare("INSERT INTO qb_exam_answers (session_id, question_id, answer_given) VALUES (?,?,?)");
        $ins->bind_param('iis', $session_id, $question_id, $answer);
        $ins->execute();
    }
    send_json(['status' => 'success']);
}

/* ══════════════════════════════════════════════════════════════
   TOGGLE FLAG
══════════════════════════════════════════════════════════════ */
if ($action === 'flag') {
    $session_id  = (int)($body['session_id']  ?? 0);
    $question_id = (int)($body['question_id'] ?? 0);

    $stmt = $db->prepare("SELECT flagged FROM qb_exam_sessions WHERE session_id = ? AND student_id = ?");
    $stmt->bind_param('is', $session_id, $student_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) send_json(['status' => 'error', 'message' => 'Session not found']);

    $flagged = $row['flagged'] ? json_decode($row['flagged'], true) : [];
    if (($k = array_search($question_id, $flagged)) !== false) {
        array_splice($flagged, $k, 1);
    } else {
        $flagged[] = $question_id;
    }
    $json = json_encode(array_values($flagged));
    $upd  = $db->prepare("UPDATE qb_exam_sessions SET flagged = ? WHERE session_id = ?");
    $upd->bind_param('si', $json, $session_id);
    $upd->execute();
    send_json(['status' => 'success', 'flagged' => $flagged]);
}

/* ══════════════════════════════════════════════════════════════
   SUBMIT & AUTO-GRADE
══════════════════════════════════════════════════════════════ */
if ($action === 'submit') {
    $session_id  = (int)($body['session_id']       ?? 0);
    $time_taken  = (int)($body['time_taken_seconds']?? 0);

    if (!$session_id) send_json(['status' => 'error', 'message' => 'session_id required']);

    $stmt = $db->prepare("
        SELECT es.*, e.passing_marks, e.show_answers_after
        FROM qb_exam_sessions es
        JOIN qb_exams e ON e.exam_id = es.exam_id
        WHERE es.session_id = ? AND es.student_id = ? AND es.status = 'in_progress'");
    $stmt->bind_param('is', $session_id, $student_id);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    if (!$session) send_json(['status' => 'error', 'message' => 'Session not found or already submitted']);

    // Load all exam questions with marks + correct answer info
    $qstmt = $db->prepare("
        SELECT eq.question_id, eq.marks_override, q.marks, q.question_type, q.correct_answer
        FROM qb_exam_questions eq
        JOIN qb_questions q ON q.question_id = eq.question_id
        WHERE eq.exam_id = ?");
    $qstmt->bind_param('i', $session['exam_id']);
    $qstmt->execute();
    $qs = $qstmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Get correct MCQ options
    $qids = array_column($qs, 'question_id');
    $correct_opts = [];
    if ($qids) {
        $ph    = implode(',', array_fill(0, count($qids), '?'));
        $types = str_repeat('i', count($qids));
        $ostmt = $db->prepare("SELECT question_id, option_label FROM qb_question_options WHERE question_id IN ($ph) AND is_correct = 1");
        $ostmt->bind_param($types, ...$qids);
        $ostmt->execute();
        $opts_res = $ostmt->get_result();
        while ($r = $opts_res->fetch_assoc()) {
            $correct_opts[(int)$r['question_id']] = strtolower(trim($r['option_label']));
        }
    }

    // Get student answers
    $astmt = $db->prepare("SELECT question_id, answer_given FROM qb_exam_answers WHERE session_id = ?");
    $astmt->bind_param('i', $session_id);
    $astmt->execute();
    $answers = [];
    $ans_res = $astmt->get_result();
    while ($r = $ans_res->fetch_assoc()) {
        $answers[(int)$r['question_id']] = strtolower(trim($r['answer_given'] ?? ''));
    }

    // Grade
    $total_score = 0.0;
    $upd = $db->prepare("UPDATE qb_exam_answers SET is_correct = ?, marks_awarded = ? WHERE session_id = ? AND question_id = ?");

    foreach ($qs as $q) {
        $qid      = (int)$q['question_id'];
        $max_marks= (float)($q['marks_override'] ?? $q['marks'] ?? 0);
        $given    = $answers[$qid] ?? null;
        if ($given === null || $given === '') continue;

        $is_correct   = 0;
        $marks_awarded= 0.0;

        switch ($q['question_type']) {
            case 'mcq':
                $correct = $correct_opts[$qid] ?? null;
                if ($correct !== null && $given === $correct) {
                    $is_correct = 1; $marks_awarded = $max_marks;
                }
                break;
            case 'true_false':
                $correct = strtolower(trim($q['correct_answer'] ?? ''));
                if ($correct !== '' && $given === $correct) {
                    $is_correct = 1; $marks_awarded = $max_marks;
                }
                break;
            case 'fill_blank':
                $correct = strtolower(trim($q['correct_answer'] ?? ''));
                if ($correct !== '' && $given === $correct) {
                    $is_correct = 1; $marks_awarded = $max_marks;
                }
                break;
        }
        $total_score += $marks_awarded;
        $upd->bind_param('idii', $is_correct, $marks_awarded, $session_id, $qid);
        $upd->execute();
    }

    // Finalise session
    $fin = $db->prepare("UPDATE qb_exam_sessions SET status='submitted', submitted_at=NOW(), score=?, time_taken_seconds=? WHERE session_id=?");
    $fin->bind_param('dii', $total_score, $time_taken, $session_id);
    $fin->execute();

    $passed = $total_score >= (float)$session['passing_marks'];
    send_json([
        'status'      => 'success',
        'score'       => $total_score,
        'total_marks' => (float)$session['total_marks'],
        'passed'      => $passed,
        'session_id'  => $session_id,
    ]);
}

/* ══════════════════════════════════════════════════════════════
   GET RESULTS
══════════════════════════════════════════════════════════════ */
if ($action === 'results') {
    $session_id = (int)($_GET['session_id'] ?? 0);
    if (!$session_id) send_json(['status' => 'error', 'message' => 'session_id required']);

    $stmt = $db->prepare("
        SELECT es.*, e.exam_title, e.exam_code, e.passing_marks, e.duration_minutes,
               e.show_answers_after, e.instructions,
               s.subject_name, l.level_name
        FROM qb_exam_sessions es
        JOIN qb_exams e ON e.exam_id = es.exam_id
        LEFT JOIN qb_subjects s ON s.subject_id = e.subject_id
        LEFT JOIN qb_levels   l ON l.level_id   = e.level_id
        WHERE es.session_id = ? AND es.student_id = ?");
    $stmt->bind_param('is', $session_id, $student_id);
    $stmt->execute();
    $session = $stmt->get_result()->fetch_assoc();
    if (!$session) send_json(['status' => 'error', 'message' => 'Results not found']);

    // Get answers with question detail
    $astmt = $db->prepare("
        SELECT ea.answer_given, ea.is_correct, ea.marks_awarded,
               q.question_id, q.question_stem, q.question_type, q.marks,
               q.correct_answer, q.solution_explanation AS explanation, q.q_uid,
               eq.marks_override, eq.sort_order,
               c.chapter_name, d.difficulty_name
        FROM qb_exam_questions eq
        JOIN qb_questions q ON q.question_id = eq.question_id
        LEFT JOIN qb_exam_answers ea ON ea.question_id = q.question_id AND ea.session_id = ?
        LEFT JOIN qb_chapters c ON c.chapter_id = q.chapter_id
        LEFT JOIN qb_difficulty_levels d ON d.difficulty_id = q.difficulty_id
        WHERE eq.exam_id = ?
        ORDER BY eq.sort_order ASC");
    $astmt->bind_param('ii', $session_id, $session['exam_id']);
    $astmt->execute();
    $answers = $astmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Include options if show_answers_after
    if ($session['show_answers_after']) {
        $qids = array_column($answers, 'question_id');
        if ($qids) {
            $ph    = implode(',', array_fill(0, count($qids), '?'));
            $types = str_repeat('i', count($qids));
            $ostmt = $db->prepare("SELECT question_id, option_label, option_text, is_correct FROM qb_question_options WHERE question_id IN ($ph) ORDER BY sort_order, option_label");
            $ostmt->bind_param($types, ...$qids);
            $ostmt->execute();
            $opts_by_q = [];
            $opts_r = $ostmt->get_result();
            while ($o = $opts_r->fetch_assoc()) {
                $opts_by_q[(int)$o['question_id']][] = $o;
            }
            foreach ($answers as &$a) {
                $a['options'] = $opts_by_q[(int)$a['question_id']] ?? [];
            }
            unset($a);
        }
    }

    send_json(['status' => 'success', 'session' => $session, 'answers' => $answers]);
}

/* ══════════════════════════════════════════════════════════════
   EXAM HISTORY
══════════════════════════════════════════════════════════════ */
if ($action === 'history') {
    $stmt = $db->prepare("
        SELECT es.session_id, es.exam_id, es.started_at, es.submitted_at,
               es.score, es.total_marks, es.status, es.time_taken_seconds,
               e.exam_title, e.exam_code, e.passing_marks, e.duration_minutes,
               s.subject_name, l.level_name,
               (SELECT COUNT(*) FROM qb_exam_answers a WHERE a.session_id = es.session_id) AS answered_count,
               (SELECT COUNT(*) FROM qb_exam_questions eq WHERE eq.exam_id = es.exam_id)   AS total_questions
        FROM qb_exam_sessions es
        JOIN qb_exams e ON e.exam_id = es.exam_id
        LEFT JOIN qb_subjects s ON s.subject_id = e.subject_id
        LEFT JOIN qb_levels   l ON l.level_id   = e.level_id
        WHERE es.student_id = ?
        ORDER BY es.started_at DESC");
    $stmt->bind_param('s', $student_id);
    $stmt->execute();
    send_json(['status' => 'success', 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

send_json(['status' => 'error', 'message' => "Unknown action: $action"]);
