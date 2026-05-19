<?php
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();
session_start();
include('../config/db.php');
include('../config/xlsx_reader.php');

function send_json(array $data): never {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['usr_code'])) {
    send_json(['status' => 'error', 'message' => 'Not authenticated']);
}

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

/* ════════════════════════════════════════════════════════════
   TEMPLATE DOWNLOAD  (GET ?action=template)
   ════════════════════════════════════════════════════════════ */
if ($action === 'template') {
    ob_clean();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="dcm_qbank_template.csv"');
    $fp = fopen('php://output', 'w');
    fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM so Excel opens cleanly
    fputcsv($fp, [
        'Q_ID','Year','Section','Q#','Chapter#','Chapter Name','Sub-topic',
        'Difficulty',"Bloom's Level",'Question Stem',
        'Option A','Option B','Option C','Option D','Option E',
        'Correct Ans','Solution/Explanation','Swahili Hint','Est.Time(s)','Marks','CIRA Flag'
    ]);
    fputcsv($fp, [
        'PP_2025_A01','2025','Section A (MCQ)','1','1','Numbers & Place Value',
        'Whole Numbers & Place Value','Easy','Remembering',
        '56 + 467 + 1,307 = ?',
        '1,710','1,730','1,824','1,829','1,830',
        'E','56+467+1307=1830','Panga nambari','45','1','No'
    ]);
    fclose($fp);
    exit;
}

/* ════════════════════════════════════════════════════════════
   IMPORT  (POST action=import)
   ════════════════════════════════════════════════════════════ */
if ($action !== 'import') {
    send_json(['status' => 'error', 'message' => 'Unknown action']);
}

set_time_limit(120);

/* ── File validation ─────────────────────────────────────── */
$fileKey = isset($_FILES['file']) ? 'file' : (isset($_FILES['csv_file']) ? 'csv_file' : '');
if (!$fileKey || empty($_FILES[$fileKey]['tmp_name']) || $_FILES[$fileKey]['error'] !== UPLOAD_ERR_OK) {
    $errCodes = [1=>'File exceeds server upload limit',2=>'File too large',
                 3=>'Partial upload',4=>'No file uploaded'];
    $code = $_FILES[$fileKey]['error'] ?? 4;
    send_json(['status'=>'error','message'=>$errCodes[$code] ?? 'Upload error']);
}

$tmpPath  = $_FILES[$fileKey]['tmp_name'];
$origName = $_FILES[$fileKey]['name'];
$ext      = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

if (!in_array($ext, ['xlsx','csv'], true)) {
    send_json(['status'=>'error','message'=>'Only .xlsx and .csv files are accepted.']);
}
if ($_FILES[$fileKey]['size'] > 10 * 1024 * 1024) {
    send_json(['status'=>'error','message'=>'File exceeds the 10 MB limit.']);
}

/* ── Settings ────────────────────────────────────────────── */
$subjectId    = (int)($_POST['subject_id']      ?? 0);
$levelName    = trim($_POST['level_name']        ?? '');
$importStatus = in_array($_POST['status'] ?? '', ['draft','review','approved','published'])
                    ? $_POST['status'] : 'draft';
$dupAction    = (($_POST['duplicate_action'] ?? 'skip') === 'overwrite') ? 'overwrite' : 'skip';

if (!$subjectId) {
    send_json(['status'=>'error','message'=>'Please select a subject.']);
}
if ($levelName === '') {
    send_json(['status'=>'error','message'=>'Please enter or select a level / grade.']);
}

/* ── Resolve / auto-create level ─────────────────────────── */
$lvStmt = $db->prepare("SELECT level_id FROM qb_levels WHERE LOWER(level_name) = LOWER(?) LIMIT 1");
$lvStmt->bind_param('s', $levelName);
$lvStmt->execute();
$lvRow = $lvStmt->get_result()->fetch_assoc();

if ($lvRow) {
    $levelId = (int)$lvRow['level_id'];
} else {
    $lvIns = $db->prepare("INSERT INTO qb_levels (level_name) VALUES (?)");
    $lvIns->bind_param('s', $levelName);
    $lvIns->execute();
    $levelId = (int)$db->insert_id;
}

/* ── Build taxonomy lookup caches ────────────────────────── */

// Bloom: normalise British "Analysing" → "Analyzing"
$bloomMap = [];
foreach ($db->query("SELECT bloom_id, bloom_name FROM qb_bloom_levels")->fetch_all(MYSQLI_ASSOC) as $r) {
    $key = strtolower($r['bloom_name']);
    $bloomMap[$key] = (int)$r['bloom_id'];
}
$bloomMap['analysing']  = $bloomMap['analyzing']   ?? null;
$bloomMap['analyse']    = $bloomMap['analyzing']   ?? null;
$bloomMap['remembering']= $bloomMap['remembering'] ?? null;

// Difficulty: alias "medium" → "medium / moderate"
$diffMap = [];
foreach ($db->query("SELECT difficulty_id, difficulty_name FROM qb_difficulty_levels")->fetch_all(MYSQLI_ASSOC) as $r) {
    $key = strtolower($r['difficulty_name']);
    $diffMap[$key] = (int)$r['difficulty_id'];
    if (str_contains($key, 'medium')) $diffMap['medium'] = (int)$r['difficulty_id'];
}

// Section: "Section A (MCQ)" → extract letter 'a' → section_id
$sectionMap = [];
foreach ($db->query("SELECT section_id, section_name FROM qb_sections")->fetch_all(MYSQLI_ASSOC) as $r) {
    if (preg_match('/section\s+([a-z])/i', $r['section_name'], $m)) {
        $sectionMap[strtolower($m[1])] = (int)$r['section_id'];
    }
}

// Chapter + subtopic caches (built lazily per row)
$chapterCache  = [];   // lowercase chapter_name → chapter_id
$subtopicCache = [];   // "chapter_id:lc_subtopic" → subtopic_id

/* ── Parse file into rows ────────────────────────────────── */
// We look for the sheet/section with these headers
$EXPECTED = ['Q_ID', 'Question Stem', 'Option A', 'Correct Ans'];

if ($ext === 'xlsx') {
    $reader = new SimpleXlsxReader();
    if (!$reader->open($tmpPath)) {
        echo json_encode(['status'=>'error','message'=>'Could not open the XLSX file. Is it a valid .xlsx?']);
        exit;
    }
    // Try to find the right sheet by expected headers
    $rows = $reader->findSheetByHeaders($EXPECTED);
    if ($rows === null) {
        // Fallback: take the first sheet with more than 1 row
        foreach ($reader->getSheetNames() as $sn) {
            $r = $reader->getRows($sn, false);
            if ($r && count($r) > 1) { $rows = array_slice($r, 1); break; }
        }
    }
    $reader->close();
} else {
    // CSV
    $rows = [];
    if (($fp = fopen($tmpPath, 'r')) === false) {
        echo json_encode(['status'=>'error','message'=>'Cannot read the uploaded CSV file.']);
        exit;
    }
    $bom = fread($fp, 3);
    if ($bom !== "\xEF\xBB\xBF") rewind($fp);
    fgetcsv($fp); // skip header row
    while (($r = fgetcsv($fp)) !== false) $rows[] = $r;
    fclose($fp);
}

if (empty($rows)) {
    send_json(['status'=>'error','message'=>'No data rows found. Check that the file has the correct sheet / format.']);
}

/* ── Look up the uploader's user id ─────────────────────── */
$createdBy  = null;
$cuStmt = $db->prepare("SELECT id FROM tbl_all_users WHERE usr_code = ? LIMIT 1");
$cuStmt->bind_param('s', $_SESSION['usr_code']);
$cuStmt->execute();
$cuRow = $cuStmt->get_result()->fetch_assoc();
if ($cuRow) $createdBy = (int)$cuRow['id'];

/* ── Process rows ────────────────────────────────────────── */
$imported = 0;
$skipped  = 0;
$errors   = [];
$uids     = [];

foreach ($rows as $rowIdx => $row) {
    $dataRow = $rowIdx + 2;   // row 1 = header, data starts at row 2

    /* Map columns (pad with nulls if row is shorter than expected) */
    $row      = array_pad((array)$row, 21, null);
    $qUid        = trim((string)($row[0]  ?? ''));
    $year        = ($row[1] !== null && $row[1] !== '') ? (int)$row[1] : null;
    $sectionStr  = trim((string)($row[2]  ?? ''));
    $qNum        = trim((string)($row[3]  ?? ''));
    $chapterNum  = trim((string)($row[4]  ?? ''));
    $chapterName = trim((string)($row[5]  ?? ''));
    $subtopicName= trim((string)($row[6]  ?? ''));
    $diffName    = strtolower(trim((string)($row[7]  ?? '')));
    $bloomName   = strtolower(trim((string)($row[8]  ?? '')));
    $stem        = trim((string)($row[9]  ?? ''));
    $optA        = ($row[10] !== null && $row[10] !== '') ? trim((string)$row[10]) : null;
    $optB        = ($row[11] !== null && $row[11] !== '') ? trim((string)$row[11]) : null;
    $optC        = ($row[12] !== null && $row[12] !== '') ? trim((string)$row[12]) : null;
    $optD        = ($row[13] !== null && $row[13] !== '') ? trim((string)$row[13]) : null;
    $optE        = ($row[14] !== null && $row[14] !== '') ? trim((string)$row[14]) : null;
    $correctAns  = strtoupper(trim((string)($row[15] ?? '')));
    $solution    = trim((string)($row[16] ?? ''));
    $swahiliHint = trim((string)($row[17] ?? ''));
    $estTime     = max(10, (int)($row[18] !== null && $row[18] !== '' ? $row[18] : 60));
    $marks       = max(0,  (float)($row[19] !== null && $row[19] !== '' ? $row[19] : 1));
    $ciraFlag    = strtolower(trim((string)($row[20] ?? ''))) === 'yes' ? 1 : 0;

    // Skip entirely blank rows
    if ($qUid === '' && $stem === '') continue;

    /* ── Required field checks ── */
    $rowErrors = [];
    if ($stem        === '') $rowErrors[] = 'Question Stem is empty';
    if ($qUid        === '') $rowErrors[] = 'Q_ID is empty';
    if ($chapterName === '') $rowErrors[] = 'Chapter Name is empty';

    if ($rowErrors) {
        $errors[] = ['row'=>$dataRow,'stem'=>$qUid ?: substr($stem,0,40),'error'=>implode('; ',$rowErrors)];
        continue;
    }

    /* ── Duplicate check ── */
    $ckStmt = $db->prepare("SELECT question_id FROM qb_questions WHERE q_uid = ? LIMIT 1");
    $ckStmt->bind_param('s', $qUid);
    $ckStmt->execute();
    $existingRow = $ckStmt->get_result()->fetch_assoc();

    if ($existingRow && $dupAction === 'skip') {
        $skipped++;
        continue;
    }

    /* ── Resolve taxonomy IDs ── */
    $diffId    = $diffMap[$diffName]   ?? null;
    $bloomId   = $bloomMap[$bloomName] ?? null;

    // Section from string e.g. "Section A (MCQ)"
    $sectionId = null;
    if (preg_match('/section\s+([a-z])/i', $sectionStr, $sm)) {
        $sectionId = $sectionMap[strtolower($sm[1])] ?? null;
    }

    // Question type: Section A → mcq, Section B → essay
    $qType = 'mcq';
    if (stripos($sectionStr, 'section b') !== false || stripos($sectionStr, 'short') !== false) {
        $qType = 'essay';
    }

    /* ── Chapter: find or auto-create under chosen subject+level ── */
    $chapterKey = strtolower($chapterName);
    if (!isset($chapterCache[$chapterKey])) {
        $csStmt = $db->prepare("SELECT chapter_id FROM qb_chapters WHERE subject_id=? AND level_id=? AND LOWER(chapter_name)=? LIMIT 1");
        $csStmt->bind_param('iis', $subjectId, $levelId, $chapterKey);
        $csStmt->execute();
        $cRow = $csStmt->get_result()->fetch_assoc();
        if ($cRow) {
            $chapterCache[$chapterKey] = (int)$cRow['chapter_id'];
        } else {
            $ciStmt = $db->prepare("INSERT INTO qb_chapters (subject_id, level_id, chapter_number, chapter_name) VALUES (?,?,?,?)");
            $ciStmt->bind_param('iiss', $subjectId, $levelId, $chapterNum, $chapterName);
            $ciStmt->execute();
            $chapterCache[$chapterKey] = (int)$db->insert_id;
        }
    }
    $chapterId = $chapterCache[$chapterKey];

    /* ── Subtopic: find or auto-create under chapter ── */
    $subtopicId = null;
    if ($subtopicName !== '') {
        $stKey = $chapterId . ':' . strtolower($subtopicName);
        if (!isset($subtopicCache[$stKey])) {
            $stN   = strtolower($subtopicName);
            $ssStmt= $db->prepare("SELECT subtopic_id FROM qb_subtopics WHERE chapter_id=? AND LOWER(subtopic_name)=? LIMIT 1");
            $ssStmt->bind_param('is', $chapterId, $stN);
            $ssStmt->execute();
            $sRow  = $ssStmt->get_result()->fetch_assoc();
            if ($sRow) {
                $subtopicCache[$stKey] = (int)$sRow['subtopic_id'];
            } else {
                $siStmt = $db->prepare("INSERT INTO qb_subtopics (chapter_id, subtopic_name) VALUES (?,?)");
                $siStmt->bind_param('is', $chapterId, $subtopicName);
                $siStmt->execute();
                $subtopicCache[$stKey] = (int)$db->insert_id;
            }
        }
        $subtopicId = $subtopicCache[$stKey];
    }

    /* ── Correct answer storage ── */
    // MCQ: store the letter (A–E); essay: store the full answer text
    $dbCorrectAnswer = ($qType === 'mcq')
        ? (in_array($correctAns, ['A','B','C','D','E']) ? $correctAns : null)
        : ($correctAns !== '' ? $correctAns : $solution);

    /* ── Insert or overwrite question ── */
    try {
        if ($existingRow && $dupAction === 'overwrite') {
            $qId   = (int)$existingRow['question_id'];
            $uStmt = $db->prepare("UPDATE qb_questions SET
                year_year=?, section_id=?, question_number=?, subject_id=?, level_id=?,
                chapter_id=?, subtopic_id=?, difficulty_id=?, bloom_id=?,
                question_stem=?, correct_answer=?, solution_explanation=?,
                swahili_hint=?, estimated_time_seconds=?, marks=?, cira_flag=?,
                question_type=?, status=?, updated_at=NOW()
                WHERE question_id=?");
            $uStmt->bind_param('iisiiiiiissssidissi',
                $year, $sectionId, $qNum, $subjectId, $levelId,
                $chapterId, $subtopicId, $diffId, $bloomId,
                $stem, $dbCorrectAnswer, $solution,
                $swahiliHint, $estTime, $marks, $ciraFlag,
                $qType, $importStatus, $qId);
            if (!$uStmt->execute()) throw new RuntimeException($uStmt->error);

            // Delete old options
            $delStmt = $db->prepare("DELETE FROM qb_question_options WHERE question_id=?");
            $delStmt->bind_param('i', $qId);
            $delStmt->execute();
        } else {
            $insStmt = $db->prepare("INSERT INTO qb_questions
                (q_uid, year_year, section_id, question_number, subject_id, level_id,
                 chapter_id, subtopic_id, difficulty_id, bloom_id,
                 question_stem, correct_answer, solution_explanation,
                 swahili_hint, estimated_time_seconds, marks, cira_flag,
                 question_type, status, created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $insStmt->bind_param('siisiiiiiissssidissi',
                $qUid, $year, $sectionId, $qNum, $subjectId, $levelId,
                $chapterId, $subtopicId, $diffId, $bloomId,
                $stem, $dbCorrectAnswer, $solution,
                $swahiliHint, $estTime, $marks, $ciraFlag,
                $qType, $importStatus, $createdBy);
            if (!$insStmt->execute()) throw new RuntimeException($insStmt->error);
            $qId = (int)$db->insert_id;
        }

        /* ── Options (MCQ only) ── */
        if ($qType === 'mcq') {
            $optLabels = ['A'=>$optA,'B'=>$optB,'C'=>$optC,'D'=>$optD,'E'=>$optE];
            $optIns    = $db->prepare("INSERT INTO qb_question_options
                (question_id, option_label, option_text, is_correct, sort_order) VALUES (?,?,?,?,?)");
            $sort = 0;
            foreach ($optLabels as $label => $text) {
                if ($text === null || $text === '') continue;
                $isCorrect = ($label === $dbCorrectAnswer) ? 1 : 0;
                $optIns->bind_param('issii', $qId, $label, $text, $isCorrect, $sort);
                $optIns->execute();
                $sort++;
            }
        }

        $imported++;
        $uids[] = $qUid;

    } catch (RuntimeException $e) {
        $errors[] = ['row'=>$dataRow, 'stem'=>$qUid, 'error'=>$e->getMessage()];
    }
}

/* ── Summary ─────────────────────────────────────────────── */
$totalInBank = (int)$db->query("SELECT COUNT(*) AS c FROM qb_questions")->fetch_assoc()['c'];

send_json([
    'status'        => 'success',
    'total_rows'    => count($rows),
    'imported'      => $imported,
    'skipped'       => $skipped,
    'errors'        => $errors,
    'uids'          => $uids,
    'total_in_bank' => $totalInBank,
]);
