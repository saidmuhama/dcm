<?php
include('../config/db.php');
include('../config/dump.php');
include('../config/qb_uid.php');
session_start();

header('Content-Type: application/json');

// ── INPUT ────────────────────────────────────────────────────
$subject_id    = (int)($_POST['subject_id']    ?? 0);
$level_id      = (int)($_POST['level_id']      ?? 0);
$chapter_id    = (int)($_POST['chapter_id']    ?? 0);
$subtopic_id   = (int)($_POST['subtopic_id']   ?? 0);
$difficulty_id = (int)($_POST['difficulty_id'] ?? 0);
$bloom_id      = (int)($_POST['bloom_id']      ?? 0) ?: null;
$section_id    = (int)($_POST['section_id']    ?? 0) ?: null;

$question_stem  = trim($_POST['question_stem']          ?? '');
$correct_answer = trim($_POST['correct_answer']         ?? '');
$solution       = trim($_POST['solution_explanation']   ?? '');
$swahili_hint   = trim($_POST['swahili_hint']           ?? '');
$question_type  = $_POST['question_type']               ?? 'mcq';
$marks          = (float)($_POST['marks']               ?? 1.00);
$est_time       = (int)($_POST['estimated_time_seconds']?? 60);
$year           = (int)($_POST['year_year']             ?? 0) ?: null;
$question_num   = trim($_POST['question_number']        ?? '') ?: null;
$created_by     = $_SESSION['usr_code']                 ?? null;

// Options array for MCQ — ['A text', 'B text', 'C text', 'D text']
$options = $_POST['options'] ?? [];

// ── VALIDATION ───────────────────────────────────────────────
$errors = [];

if (!$subject_id)    $errors[] = "Subject is required";
if (!$level_id)      $errors[] = "Level is required";
if (!$chapter_id)    $errors[] = "Chapter is required";
if (!$subtopic_id)   $errors[] = "Subtopic is required";
if (!$difficulty_id) $errors[] = "Difficulty is required";
if (empty($question_stem)) $errors[] = "Question stem is required";

if ($question_type === 'mcq') {
    if (count($options) < 2)         $errors[] = "MCQ needs at least 2 options";
    if (empty($correct_answer))      $errors[] = "Correct answer is required for MCQ";
}

if (!empty($errors)) {
    echo json_encode(["status" => "error", "message" => implode(', ', $errors)]);
    exit;
}

// ── GENERATE q_uid ───────────────────────────────────────────
try {
    $q_uid = generateQuestionUID(
        $db,
        $subject_id,
        $level_id,
        $chapter_id,
        $subtopic_id,
        $difficulty_id
    );
} catch (RuntimeException $e) {
    echo json_encode(["status" => "error", "message" => "ID generation failed: " . $e->getMessage()]);
    exit;
}

// ── INSERT QUESTION ──────────────────────────────────────────
$db->begin_transaction();

try {

    $stmt = $db->prepare("
        INSERT INTO qb_questions
            (q_uid, year_year, question_number, section_id,
             subject_id, level_id, chapter_id, subtopic_id,
             difficulty_id, bloom_id,
             question_stem, correct_answer, solution_explanation,
             swahili_hint, question_type, marks,
             estimated_time_seconds, status, created_by)
        VALUES
            (?, ?, ?, ?,
             ?, ?, ?, ?,
             ?, ?,
             ?, ?, ?,
             ?, ?, ?,
             ?, 'draft', ?)
    ");

    $stmt->bind_param(
        "sisiiiiiiisssssdis",
        $q_uid,
        $year,
        $question_num,
        $section_id,
        $subject_id,
        $level_id,
        $chapter_id,
        $subtopic_id,
        $difficulty_id,
        $bloom_id,
        $question_stem,
        $correct_answer,
        $solution,
        $swahili_hint,
        $question_type,
        $marks,
        $est_time,
        $created_by
    );

    if (!$stmt->execute()) {
        throw new RuntimeException("Question insert failed: " . $stmt->error);
    }

    $question_id = $db->insert_id;
    $stmt->close();

    // ── INSERT MCQ OPTIONS ───────────────────────────────────
    if ($question_type === 'mcq' && !empty($options)) {

        $optStmt = $db->prepare("
            INSERT INTO qb_question_options
                (question_id, option_label, option_text, is_correct, sort_order)
            VALUES (?, ?, ?, ?, ?)
        ");

        $labels = ['A', 'B', 'C', 'D', 'E'];

        foreach ($options as $i => $opt_text) {
            $label      = $labels[$i] ?? chr(65 + $i);
            $opt_text   = trim($opt_text);
            $is_correct = (strtoupper($correct_answer) === $label) ? 1 : 0;
            $sort       = $i;

            $optStmt->bind_param("issii", $question_id, $label, $opt_text, $is_correct, $sort);

            if (!$optStmt->execute()) {
                throw new RuntimeException("Option insert failed: " . $optStmt->error);
            }
        }

        $optStmt->close();
    }

    $db->commit();

    echo json_encode([
        "status"      => "success",
        "message"     => "Question saved as draft",
        "question_id" => $question_id,
        "q_uid"       => $q_uid
    ]);

} catch (RuntimeException $e) {
    $db->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
