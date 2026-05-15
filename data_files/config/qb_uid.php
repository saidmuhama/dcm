<?php
/**
 * Question Bank — Auto Question ID (q_uid) Generator
 *
 * Format: Q_{SUBJECT}_{LEVEL}_{CHAPTER}_{SUBTOPIC}_{DIFFICULTY}_{SEQ}
 * Example: Q_MATH_STD7_CH09_SC03_MD_015
 */

// ─────────────────────────────────────────────────────────────
// PUBLIC ENTRY POINT
// ─────────────────────────────────────────────────────────────

function generateQuestionUID($db, $subject_id, $level_id, $chapter_id, $subtopic_id, $difficulty_id)
{
    $subject    = _qb_getSubjectCode($db, $subject_id);
    $level      = _qb_getLevelCode($db, $level_id);
    $chapter    = _qb_getChapterCode($db, $chapter_id);
    $subtopic   = _qb_getSubtopicCode($db, $subtopic_id, $chapter_id);
    $difficulty = _qb_getDifficultyCode($db, $difficulty_id);
    $seq        = _qb_getNextSeq($db, $subject_id, $level_id, $chapter_id, $subtopic_id, $difficulty_id);

    return "Q_{$subject}_{$level}_{$chapter}_{$subtopic}_{$difficulty}_{$seq}";
}


// ─────────────────────────────────────────────────────────────
// STEP 1 — SUBJECT CODE
// Source: qb_subjects.subject_code  (stored uppercase, e.g. MATH)
// ─────────────────────────────────────────────────────────────

function _qb_getSubjectCode($db, $subject_id)
{
    $stmt = $db->prepare("SELECT subject_code FROM qb_subjects WHERE subject_id = ?");
    $stmt->bind_param("i", $subject_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($row['subject_code'])) {
        throw new RuntimeException("Subject not found or subject_code is empty (id=$subject_id)");
    }

    return strtoupper(trim($row['subject_code']));
}


// ─────────────────────────────────────────────────────────────
// STEP 2 — LEVEL CODE
// Source: qb_levels.level_name  → abbreviated
//   "Standard 7"  →  STD7
//   "Form 2"      →  F2
//   "Grade 4"     →  G4
// ─────────────────────────────────────────────────────────────

function _qb_getLevelCode($db, $level_id)
{
    $stmt = $db->prepare("SELECT level_name FROM qb_levels WHERE level_id = ?");
    $stmt->bind_param("i", $level_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($row['level_name'])) {
        throw new RuntimeException("Level not found (id=$level_id)");
    }

    return _qb_abbreviateLevel($row['level_name']);
}

function _qb_abbreviateLevel($name)
{
    $name = trim($name);

    $replacements = [
        '/Standard\s+/i' => 'STD',
        '/Form\s+/i'     => 'F',
        '/Grade\s+/i'    => 'G',
        '/Class\s+/i'    => 'CL',
        '/Year\s+/i'     => 'Y',
    ];

    foreach ($replacements as $pattern => $replacement) {
        $result = preg_replace($pattern, $replacement, $name);
        if ($result !== $name) {
            return strtoupper(preg_replace('/\s+/', '', $result));
        }
    }

    // Fallback: strip spaces, uppercase
    return strtoupper(preg_replace('/\s+/', '', $name));
}


// ─────────────────────────────────────────────────────────────
// STEP 3 — CHAPTER CODE
// Source: qb_chapters.chapter_number  zero-padded to 2 digits
//   chapter_number = "9"  →  CH09
//   chapter_number = "12" →  CH12
// ─────────────────────────────────────────────────────────────

function _qb_getChapterCode($db, $chapter_id)
{
    $stmt = $db->prepare("SELECT chapter_number FROM qb_chapters WHERE chapter_id = ?");
    $stmt->bind_param("i", $chapter_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row === null) {
        throw new RuntimeException("Chapter not found (id=$chapter_id)");
    }

    $num = (int)($row['chapter_number'] ?? 0);
    return 'CH' . str_pad($num, 2, '0', STR_PAD_LEFT);
}


// ─────────────────────────────────────────────────────────────
// STEP 4 — SUBTOPIC CODE
// Source: positional rank of subtopic_id within its chapter
//   (not the raw subtopic_id — keeps codes short and stable per chapter)
//   1st subtopic in the chapter  →  SC01
//   3rd subtopic in the chapter  →  SC03
// ─────────────────────────────────────────────────────────────

function _qb_getSubtopicCode($db, $subtopic_id, $chapter_id)
{
    $stmt = $db->prepare("
        SELECT COUNT(*) AS pos
        FROM   qb_subtopics
        WHERE  chapter_id  = ?
          AND  subtopic_id <= ?
    ");
    $stmt->bind_param("ii", $chapter_id, $subtopic_id);
    $stmt->execute();
    $pos = (int)$stmt->get_result()->fetch_assoc()['pos'];
    $stmt->close();

    if ($pos === 0) {
        throw new RuntimeException("Subtopic not found in chapter (subtopic_id=$subtopic_id, chapter_id=$chapter_id)");
    }

    return 'SC' . str_pad($pos, 2, '0', STR_PAD_LEFT);
}


// ─────────────────────────────────────────────────────────────
// STEP 5 — DIFFICULTY CODE
// Source: qb_difficulty_levels.difficulty_name  → 2-letter abbreviation
//   Easy      → ES
//   Medium    → MD
//   Hard      → HD
//   Very Hard → VH
//   (others)  → first 2 uppercase chars
// ─────────────────────────────────────────────────────────────

function _qb_getDifficultyCode($db, $difficulty_id)
{
    if (!$difficulty_id) return 'ND';   // no difficulty selected

    $stmt = $db->prepare("SELECT difficulty_name FROM qb_difficulty_levels WHERE difficulty_id = ?");
    $stmt->bind_param("i", $difficulty_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (empty($row['difficulty_name'])) return 'ND';

    return _qb_abbreviateDifficulty($row['difficulty_name']);
}

function _qb_abbreviateDifficulty($name)
{
    $map = [
        'easy'      => 'ES',
        'medium'    => 'MD',
        'hard'      => 'HD',
        'very hard' => 'VH',
        'very easy' => 'VE',
    ];

    $key = strtolower(trim($name));
    return $map[$key] ?? strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 2));
}


// ─────────────────────────────────────────────────────────────
// STEP 6 — SEQUENTIAL NUMBER
// Scope: questions sharing the same subject + level + chapter + subtopic + difficulty
// Rule:  COUNT existing questions in that bucket + 1, zero-padded to 3 digits
//   0 existing  → 001
//   14 existing → 015
// ─────────────────────────────────────────────────────────────

function _qb_getNextSeq($db, $subject_id, $level_id, $chapter_id, $subtopic_id, $difficulty_id)
{
    $stmt = $db->prepare("
        SELECT COUNT(*) AS cnt
        FROM   qb_questions
        WHERE  subject_id    = ?
          AND  level_id      = ?
          AND  chapter_id    = ?
          AND  subtopic_id   = ?
          AND  difficulty_id = ?
    ");
    $stmt->bind_param("iiiii", $subject_id, $level_id, $chapter_id, $subtopic_id, $difficulty_id);
    $stmt->execute();
    $cnt = (int)$stmt->get_result()->fetch_assoc()['cnt'];
    $stmt->close();

    return str_pad($cnt + 1, 3, '0', STR_PAD_LEFT);
}
