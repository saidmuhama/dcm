<?php
include('../config/db.php');
session_start();

header('Content-Type: application/json');

// ── INPUT ────────────────────────────────────────────────────
$question_id   = (int)($_POST['question_id']   ?? 0);
$subject_id    = (int)($_POST['subject_id']    ?? 0);
$level_id      = (int)($_POST['level_id']      ?? 0);
$chapter_id    = (int)($_POST['chapter_id']    ?? 0);
$subtopic_id   = (int)($_POST['subtopic_id']   ?? 0);
$difficulty_id = (int)($_POST['difficulty_id'] ?? 0);
$bloom_id      = (int)($_POST['bloom_id']      ?? 0) ?: null;
$section_id    = (int)($_POST['section_id']    ?? 0) ?: null;

$question_stem  = trim($_POST['question_stem']           ?? '');
$correct_answer = trim($_POST['correct_answer']          ?? '');
$solution       = trim($_POST['solution_explanation']    ?? '');
$swahili_hint   = trim($_POST['swahili_hint']            ?? '');
$question_type  = $_POST['question_type']                ?? 'mcq';
$marks          = (float)($_POST['marks']                ?? 1.00);
$est_time       = (int)($_POST['estimated_time_seconds'] ?? 60);
$year           = (int)($_POST['year_year']              ?? 0) ?: null;
$question_num   = trim($_POST['question_number']         ?? '') ?: null;
$save_status    = in_array($_POST['save_status'] ?? '', ['draft','review','approved','published']) ? $_POST['save_status'] : 'draft';

$options = $_POST['options'] ?? [];
$matching_pairs_raw = $_POST['matching_pairs'] ?? '[]';
$matching_pairs = json_decode($matching_pairs_raw, true) ?: [];

// ── VALIDATION ───────────────────────────────────────────────
$errors = [];

if (!$question_id)   $errors[] = "Question ID is required";
if (!$subject_id)    $errors[] = "Subject is required";
if (!$level_id)      $errors[] = "Level is required";
if (!$chapter_id)    $errors[] = "Chapter is required";
if (!$subtopic_id)   $errors[] = "Subtopic is required";
if (!$difficulty_id) $errors[] = "Difficulty is required";
if (empty($question_stem)) $errors[] = "Question stem is required";

if ($question_type === 'mcq') {
    if (count($options) < 2)    $errors[] = "MCQ needs at least 2 options";
    if (empty($correct_answer)) $errors[] = "Correct answer is required for MCQ";
}
if ($question_type === 'true_false' && empty($correct_answer)) {
    $errors[] = "Correct answer is required for True/False";
}
if ($question_type === 'matching' && count($matching_pairs) < 2) {
    $errors[] = "Matching questions need at least 2 pairs";
}

if (!empty($errors)) {
    echo json_encode(["status" => "error", "message" => implode(', ', $errors)]);
    exit;
}

// ── VERIFY OWNERSHIP ────────────────────────────────────────
$me = $_SESSION['usr_code'] ?? '';
$own = $db->prepare("SELECT q_uid FROM qb_questions WHERE question_id = ? AND created_by = ?");
$own->bind_param("is", $question_id, $me);
$own->execute();
$row = $own->get_result()->fetch_assoc();

if (!$row) {
    echo json_encode(["status" => "error", "message" => "Question not found or access denied"]);
    exit;
}
$q_uid = $row['q_uid'];

// ── UPDATE ───────────────────────────────────────────────────
$db->begin_transaction();

try {

    $stmt = $db->prepare("
        UPDATE qb_questions SET
            subject_id    = ?, level_id       = ?, chapter_id    = ?, subtopic_id   = ?,
            difficulty_id = ?, bloom_id        = ?, section_id    = ?,
            question_stem = ?, correct_answer  = ?, solution_explanation = ?,
            swahili_hint  = ?, question_type  = ?, marks          = ?,
            estimated_time_seconds = ?, year_year = ?, question_number = ?,
            status        = ?, updated_at      = NOW()
        WHERE question_id = ?
    ");

    $stmt->bind_param(
        "iiiiiiisssssdiissi",
        $subject_id, $level_id, $chapter_id, $subtopic_id,
        $difficulty_id, $bloom_id, $section_id,
        $question_stem, $correct_answer, $solution,
        $swahili_hint, $question_type, $marks,
        $est_time, $year, $question_num,
        $save_status, $question_id
    );

    if (!$stmt->execute()) {
        throw new RuntimeException("Question update failed: " . $stmt->error);
    }
    $stmt->close();

    // Delete existing options
    $del = $db->prepare("DELETE FROM qb_question_options WHERE question_id = ?");
    $del->bind_param("i", $question_id);
    $del->execute();
    $del->close();

    // Re-insert MCQ options
    if ($question_type === 'mcq' && !empty($options)) {
        $optStmt = $db->prepare("
            INSERT INTO qb_question_options (question_id, option_label, option_text, is_correct, sort_order)
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

    // Re-insert matching pairs
    if ($question_type === 'matching' && !empty($matching_pairs)) {
        $matchStmt = $db->prepare("
            INSERT INTO qb_question_options (question_id, option_label, option_text, is_correct, sort_order)
            VALUES (?, ?, ?, 1, ?)
        ");
        foreach ($matching_pairs as $i => $pair) {
            $label     = (string)($i + 1);
            $pair_json = json_encode(['left' => trim($pair['left'] ?? ''), 'right' => trim($pair['right'] ?? '')], JSON_UNESCAPED_UNICODE);
            $sort      = $i;
            $matchStmt->bind_param("issi", $question_id, $label, $pair_json, $sort);
            if (!$matchStmt->execute()) {
                throw new RuntimeException("Matching pair insert failed: " . $matchStmt->error);
            }
        }
        $matchStmt->close();
    }

    $db->commit();

    $status_label = $save_status === 'review' ? 'submitted for review' : 'saved as draft';
    echo json_encode([
        "status"      => "success",
        "message"     => "Question {$status_label}",
        "save_status" => $save_status,
        "question_id" => $question_id,
        "q_uid"       => $q_uid
    ]);

} catch (RuntimeException $e) {
    $db->rollback();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
