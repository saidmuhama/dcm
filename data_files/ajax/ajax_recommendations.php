<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

/* Action can come from the URL (?action=recommended) OR from a POST body (FormData / JSON) */
$_jsonBody = json_decode(file_get_contents('php://input'), true) ?? [];
$action    = $_GET['action'] ?? $_POST['action'] ?? $_jsonBody['action'] ?? 'recommended';
function resp(array $d): never { ob_clean(); echo json_encode($d); exit; }

/* ═══════════════════════════════════════════════════════════════════
   STUDENT PROFILE HELPERS
═══════════════════════════════════════════════════════════════════ */

/** Interest category IDs from tbl_student_interests */
function getInterestIds(mysqli $db, string $usr): array {
    $r = $db->query("SELECT category_id FROM tbl_student_interests WHERE student_id='".$db->real_escape_string($usr)."'");
    return array_column($r->fetch_all(MYSQLI_ASSOC), 'category_id');
}

/** Academic / stream profile from tbl_student_profiles (onboarding) */
function getProfile(mysqli $db, string $usr): ?array {
    return $db->query("SELECT * FROM tbl_student_profiles WHERE student_id='"
        .$db->real_escape_string($usr)."' LIMIT 1")->fetch_assoc() ?: null;
}

/**
 * Resolve the student's education_level string.
 * Priority:
 *   1. tbl_student_profiles.education_level  (onboarding wizard)
 *   2. tbl_students.main_academic_level → tbl_main_academic_levels.level_title (legacy)
 *   3. NULL (unknown — no level filtering applied)
 */
function getEducationLevel(mysqli $db, string $usr): ?string {
    // 1. onboarding profile
    $profile = getProfile($db, $usr);
    if (!empty($profile['education_level'])) return $profile['education_level'];

    // 2. legacy student record
    $r = $db->query("
        SELECT mal.level_title
        FROM tbl_students s
        JOIN tbl_main_academic_levels mal ON mal.id = s.main_academic_level
        WHERE s.usr_code='" . $db->real_escape_string($usr) . "'
        LIMIT 1
    ")->fetch_assoc();
    if ($r) return normaliseLevelTitle($r['level_title']);

    return null;
}

/** Map level_title strings (from tbl_main_academic_levels) to our internal keys */
function normaliseLevelTitle(string $t): string {
    $t = strtolower(trim($t));
    if (str_contains($t,'pre'))         return 'pre_school';
    if (str_contains($t,'primary'))     return 'primary';
    if (str_contains($t,'secondary')
     || str_contains($t,'o-level')
     || str_contains($t,'o level'))     return 'o_level';
    if (str_contains($t,'high school')
     || str_contains($t,'advanced')
     || str_contains($t,'a-level')
     || str_contains($t,'a level'))     return 'a_level';
    if (str_contains($t,'university')
     || str_contains($t,'degree')
     || str_contains($t,'undergrad'))   return 'university';
    if (str_contains($t,'professional')
     || str_contains($t,'certif')
     || str_contains($t,'courses'))     return 'professional';
    return 'other';
}

/* ═══════════════════════════════════════════════════════════════════
   LEVEL-AWARE QUERY FRAGMENTS
   Returns an array:
     'filter' — SQL AND clause that excludes courses inappropriate
                for the student's level
     'boost'  — SQL CASE expression giving higher sort-weight to
                high-priority categories for this level
     'level'  — resolved level string (or null)
     'high'   — array of high-priority category codes for this level
═══════════════════════════════════════════════════════════════════ */
function levelFragments(mysqli $db, string $usr): array {
    $level = getEducationLevel($db, $usr);

    if (!$level || $level === 'other') {
        // No level set → show everything, no boosting
        return ['filter'=>'', 'boost'=>'0', 'level'=>$level, 'high'=>[]];
    }

    $lesc = $db->real_escape_string($level);

    /* Fetch priorities for this level */
    $rows = $db->query("
        SELECT category_code, priority
        FROM tbl_level_category_map
        WHERE education_level = '$lesc'
    ")->fetch_all(MYSQLI_ASSOC);

    $excluded = [];
    $high     = [];
    $medium   = [];

    foreach ($rows as $row) {
        $code = $row['category_code'];
        switch ($row['priority']) {
            case 'excluded': $excluded[] = $code; break;
            case 'high':     $high[]     = $code; break;
            case 'medium':   $medium[]   = $code; break;
        }
    }

    /* ── Filter: remove courses where ALL categories are excluded ── */
    $filter = '';
    if (!empty($excluded)) {
        $exIn = "'" . implode("','", array_map([$db,'real_escape_string'], $excluded)) . "'";
        $filter = "
            AND (
                /* Course has at least one non-excluded category */
                c.id IN (
                    SELECT DISTINCT ccm.course_id
                    FROM tbl_course_category_map ccm
                    JOIN tbl_course_categories cat ON cat.id = ccm.category_id
                    WHERE cat.category_code NOT IN ($exIn)
                      AND cat.category_code IS NOT NULL
                )
                /* OR course is uncategorised (don't hide it) */
                OR c.id NOT IN (SELECT DISTINCT course_id FROM tbl_course_category_map)
            )";
    }

    /* ── Boost: courses with high-priority categories sort first ── */
    $boost = '0';
    if (!empty($high)) {
        $hiIn  = "'" . implode("','", array_map([$db,'real_escape_string'], $high)) . "'";
        $meIn  = !empty($medium) ? "'" . implode("','", array_map([$db,'real_escape_string'], $medium)) . "'" : null;
        $boost = "CASE
            WHEN c.id IN (
                SELECT DISTINCT ccm.course_id FROM tbl_course_category_map ccm
                JOIN tbl_course_categories cat ON cat.id = ccm.category_id
                WHERE cat.category_code IN ($hiIn)
            ) THEN 2"
            . ($meIn ? "
            WHEN c.id IN (
                SELECT DISTINCT ccm.course_id FROM tbl_course_category_map ccm
                JOIN tbl_course_categories cat ON cat.id = ccm.category_id
                WHERE cat.category_code IN ($meIn)
            ) THEN 1" : '')
            . " ELSE 0 END";
    }

    return ['filter'=>$filter, 'boost'=>$boost, 'level'=>$level, 'high'=>$high, 'excluded'=>$excluded];
}

/* ═══════════════════════════════════════════════════════════════════
   COMBINATION → CATEGORY CODES  (stream/combination tuning for A-level)
═══════════════════════════════════════════════════════════════════ */
function comboToCatCodes(mysqli $db, int $comboId): array {
    $row = $db->query("SELECT subjects FROM tbl_combinations WHERE combination_id=$comboId LIMIT 1")->fetch_assoc();
    if (!$row) return [];
    $map = [
        'Physics'     =>'PHY',  'Chemistry'=>'CHEM', 'Biology'    =>'BIO',
        'Mathematics' =>'MATH', 'History'  =>'ART',  'Geography'  =>'MATH',
        'Economics'   =>'ECO',  'Commerce' =>'BUS',  'Accounting' =>'ACC',
        'Kiswahili'   =>'LANG', 'Literature'=>'LIT', 'Book-keeping'=>'ACC',
        'Business'    =>'BUS',
    ];
    $codes = [];
    foreach (array_map('trim', explode(',', $row['subjects'])) as $s) {
        foreach ($map as $key => $code) {
            if (stripos($s, $key) !== false) $codes[] = $code;
        }
    }
    return array_unique($codes);
}

/* ═══════════════════════════════════════════════════════════════════
   BASE COURSE SELECT  (shared by all recommendation queries)
═══════════════════════════════════════════════════════════════════ */
function courseSelect(): string {
    return "
        SELECT c.id, c.title, c.thumbnail, c.price, c.discount,
               c.status, c.is_approved, c.created_at,
               cat.category_title, cat.icon AS cat_icon, cat.category_code,
               COALESCE(u.first_name,'') AS instructor_fname,
               COALESCE(u.last_name,'')  AS instructor_lname,
               (SELECT COUNT(*) FROM tbl_orders o
                JOIN tbl_order_items oi ON oi.order_id=o.id
                WHERE oi.course_id=c.id AND o.payment_status='paid') AS enroll_count,
               (SELECT ROUND(AVG(rating),1) FROM tbl_course_ratings WHERE course_id=c.id) AS avg_rating
        FROM tbl_courses c
        LEFT JOIN tbl_course_categories cat ON cat.id = c.category_id
        LEFT JOIN tbl_all_users u ON u.usr_code = c.instructor_id
        WHERE c.status='active' AND c.is_approved='approved' AND c.deleted_at IS NULL
    ";
}

$limit = min(20, (int)($_GET['limit'] ?? 8));

/* ═══════════════════════════════════════════════════════════════════
   ACTIONS
═══════════════════════════════════════════════════════════════════ */
switch ($action) {

/* ── RECOMMENDED FOR YOU ──────────────────────────────────────── */
case 'recommended':
    $catIds  = getInterestIds($db, $me);
    $profile = getProfile($db, $me);
    $lf      = levelFragments($db, $me);

    // Add combination-derived categories (A-level stream accuracy)
    if ($profile && !empty($profile['combination_id'])) {
        $codes = comboToCatCodes($db, (int)$profile['combination_id']);
        if ($codes) {
            $escaped  = implode("','", array_map(fn($c)=>$db->real_escape_string($c), $codes));
            $res2     = $db->query("SELECT id FROM tbl_course_categories WHERE category_code IN ('$escaped')");
            if ($res2) $catIds = array_unique(array_merge($catIds, array_column($res2->fetch_all(MYSQLI_ASSOC), 'id')));
        }
    }

    // Seed from high-priority level categories when no interests set
    if (empty($catIds) && !empty($lf['high'])) {
        $hiEsc  = implode("','", array_map(fn($c)=>$db->real_escape_string($c), $lf['high']));
        $hiRes  = $db->query("SELECT id FROM tbl_course_categories WHERE category_code IN ('$hiEsc')");
        if ($hiRes) $catIds = array_column($hiRes->fetch_all(MYSQLI_ASSOC), 'id');
    }

    // Try interest-filtered query first
    $rows = [];
    if (!empty($catIds)) {
        $inList = implode(',', array_map('intval', $catIds));
        $sql = courseSelect() . $lf['filter']
             . " AND c.id IN (
                    SELECT DISTINCT course_id FROM tbl_course_category_map WHERE category_id IN ($inList)
                )
                ORDER BY ({$lf['boost']}) DESC, c.created_at DESC LIMIT $limit";
        $res = $db->query($sql);
        if ($res) $rows = $res->fetch_all(MYSQLI_ASSOC);
    }

    // Fallback: if no interest-matched courses exist, return all active courses
    if (empty($rows)) {
        $sql  = courseSelect() . $lf['filter']
              . " ORDER BY ({$lf['boost']}) DESC, c.created_at DESC LIMIT $limit";
        $res  = $db->query($sql);
        $rows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    resp([
        'status'       => 'success',
        'data'         => $rows,
        'has_interests'=> !empty($catIds),
        'level'        => $lf['level'],
    ]);

/* ── POPULAR IN YOUR INTERESTS ────────────────────────────────── */
case 'popular':
    $catIds = getInterestIds($db, $me);
    $lf     = levelFragments($db, $me);

    if (empty($catIds)) {
        $sql = courseSelect() . $lf['filter']
             . " ORDER BY ({$lf['boost']}) DESC, enroll_count DESC LIMIT $limit";
    } else {
        $inList = implode(',', array_map('intval', $catIds));
        $sql = courseSelect() . $lf['filter']
             . " AND c.id IN (
                    SELECT DISTINCT course_id FROM tbl_course_category_map WHERE category_id IN ($inList)
                )
                ORDER BY ({$lf['boost']}) DESC, enroll_count DESC LIMIT $limit";
    }
    $popRes = $db->query($sql);
    resp(['status'=>'success','data'=> $popRes ? $popRes->fetch_all(MYSQLI_ASSOC) : []]);

/* ── EXPLORE (outside current interests, still level-filtered) ── */
case 'explore':
    $catIds = getInterestIds($db, $me);
    $lf     = levelFragments($db, $me);

    $notIn = '';
    if (!empty($catIds)) {
        $inList = implode(',', array_map('intval', $catIds));
        $notIn  = " AND c.id NOT IN (
                        SELECT DISTINCT course_id FROM tbl_course_category_map WHERE category_id IN ($inList)
                    )";
    }
    $sql  = courseSelect() . $lf['filter'] . $notIn
          . " ORDER BY ({$lf['boost']}) DESC, RAND() LIMIT $limit";
    resp(['status'=>'success','data'=>$db->query($sql)->fetch_all(MYSQLI_ASSOC)]);

/* ── TRENDING (last 30 days, level-filtered) ──────────────────── */
case 'trending':
    $lf  = levelFragments($db, $me);
    $sql = courseSelect() . $lf['filter']
         . " AND c.id IN (
                SELECT oi.course_id FROM tbl_order_items oi
                JOIN tbl_orders o ON o.id=oi.order_id
                WHERE o.payment_status='paid'
                  AND o.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY oi.course_id ORDER BY COUNT(*) DESC LIMIT 50
            )
            ORDER BY ({$lf['boost']}) DESC, enroll_count DESC LIMIT $limit";
    $rows = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
    if (empty($rows)) {
        // Fallback: newest level-appropriate courses
        $rows = $db->query(
            courseSelect() . $lf['filter']
            . " ORDER BY ({$lf['boost']}) DESC, c.created_at DESC LIMIT $limit"
        )->fetch_all(MYSQLI_ASSOC);
    }
    resp(['status'=>'success','data'=>$rows]);

/* ── LEVEL-MATCHED (courses specifically suited to this level) ── */
case 'level_matched':
    $lf = levelFragments($db, $me);
    if (empty($lf['high'])) {
        // No level set — return newest
        resp(['status'=>'success','data'=>$db->query(
            courseSelect() . " ORDER BY c.created_at DESC LIMIT $limit"
        )->fetch_all(MYSQLI_ASSOC), 'level'=>null]);
    }
    $hiEsc = implode("','", array_map(fn($c)=>$db->real_escape_string($c), $lf['high']));
    $sql = courseSelect() . $lf['filter']
         . " AND c.id IN (
                SELECT DISTINCT ccm.course_id FROM tbl_course_category_map ccm
                JOIN tbl_course_categories cat ON cat.id = ccm.category_id
                WHERE cat.category_code IN ('$hiEsc')
            )
            ORDER BY ({$lf['boost']}) DESC, enroll_count DESC, c.created_at DESC
            LIMIT $limit";
    resp(['status'=>'success','data'=>$db->query($sql)->fetch_all(MYSQLI_ASSOC),'level'=>$lf['level']]);

/* ── SAVE INTERESTS ───────────────────────────────────────────── */
case 'save_interests':
    /* Accept category_ids from multipart FormData (category_ids[]) OR JSON body */
    $rawIds = $_POST['category_ids'] ?? $_jsonBody['category_ids'] ?? [];
    $ids    = array_map('intval', (array)$rawIds);
    $db->query("DELETE FROM tbl_student_interests WHERE student_id='".$db->real_escape_string($me)."'");
    foreach ($ids as $cid) {
        if ($cid > 0) $db->query("INSERT IGNORE INTO tbl_student_interests (student_id,category_id) VALUES ('".$db->real_escape_string($me)."',$cid)");
    }
    resp(['status'=>'success','message'=>'Interests saved','count'=>count($ids)]);

/* ── GET MY INTERESTS ─────────────────────────────────────────── */
case 'my_interests':
    $rows = $db->query("
        SELECT c.id, c.category_title, c.icon, c.category_code
        FROM tbl_student_interests si
        JOIN tbl_course_categories c ON c.id = si.category_id
        WHERE si.student_id='".$db->real_escape_string($me)."'
        ORDER BY c.sort_order
    ")->fetch_all(MYSQLI_ASSOC);
    resp(['status'=>'success','data'=>$rows]);

/* ── SAVE ACADEMIC PROFILE ────────────────────────────────────── */
case 'save_profile':
    /* Read from FormData or JSON body */
    $pData   = !empty($_POST) ? $_POST : $_jsonBody;
    $level   = in_array($pData['education_level']??'',['primary','o_level','a_level','university','professional','other'])
               ? $pData['education_level'] : null;
    $stream  = in_array($pData['stream']??'',['science','arts','business','general']) ? $pData['stream'] : null;
    $comboId = (int)($pData['combination_id'] ?? 0) ?: null;

    $existing = $db->query("SELECT profile_id FROM tbl_student_profiles WHERE student_id='".$db->real_escape_string($me)."' LIMIT 1")->fetch_assoc();
    $lv = $level   ? "'".$db->real_escape_string($level)."'"  : 'NULL';
    $sv = $stream  ? "'".$db->real_escape_string($stream)."'" : 'NULL';
    $cv = $comboId ?? 'NULL';

    if ($existing) {
        $db->query("UPDATE tbl_student_profiles SET education_level=$lv, stream=$sv, combination_id=$cv, updated_at=NOW() WHERE student_id='".$db->real_escape_string($me)."'");
    } else {
        $db->query("INSERT INTO tbl_student_profiles (student_id,education_level,stream,combination_id) VALUES ('".$db->real_escape_string($me)."',$lv,$sv,$cv)");
    }
    resp(['status'=>'success','message'=>'Profile saved']);

/* ── ALL CATEGORIES (for interest/onboarding picker) ─────────── */
case 'categories':
    $lf    = levelFragments($db, $me);
    $rows  = $db->query("
        SELECT id, category_title, icon, category_code,
               COALESCE(
                   (SELECT priority FROM tbl_level_category_map
                    WHERE education_level='" . $db->real_escape_string($lf['level'] ?? 'other') . "'
                    AND category_code=tbl_course_categories.category_code LIMIT 1),
                   'medium'
               ) AS level_priority
        FROM tbl_course_categories
        WHERE status=1
        ORDER BY
            CASE WHEN category_code IN (
                SELECT category_code FROM tbl_level_category_map
                WHERE education_level='" . $db->real_escape_string($lf['level'] ?? 'other') . "'
                  AND priority='high'
            ) THEN 0 ELSE 1 END,
            sort_order, id
    ")->fetch_all(MYSQLI_ASSOC);
    $myIds = getInterestIds($db, $me);
    foreach ($rows as &$r) {
        $r['selected']  = in_array($r['id'], $myIds);
        $r['excluded']  = $r['level_priority'] === 'excluded';
    }
    resp(['status'=>'success','data'=>$rows,'level'=>$lf['level']]);

default:
    resp(['status'=>'error','message'=>'Unknown action']);
}
