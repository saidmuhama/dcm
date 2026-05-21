<?php
ini_set('display_errors', 0);
ob_start();
include('../config/db.php');
include('../config/url_crypt_config.php');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usr_code'])) {
    ob_clean();
    echo json_encode(['status' => 'error', 'data' => [], 'total' => 0]);
    exit;
}

$usr    = $_SESSION['usr_code'];
$search = trim($_GET['search']   ?? '');
$cat    = (int)($_GET['category'] ?? 0);
$price  = trim($_GET['price']    ?? '');
$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 24;
$offset = ($page - 1) * $limit;

/* ── Build WHERE ────────────────────────────────────────────── */
$where  = ["c.status = 'active'", "c.deleted_at IS NULL"];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = "(c.title LIKE ? OR c.seo_description LIKE ?)";
    $like     = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $types   .= 'ss';
}
if ($cat > 0) {
    $where[]  = "c.category_id = ?";
    $params[] = $cat;
    $types   .= 'i';
}
if ($price === 'free') {
    $where[] = "c.price = 0";
} elseif ($price === 'paid') {
    $where[] = "c.price > 0";
}

$whereSQL = implode(' AND ', $where);

/*
 * Enrollment is stored in tbl_orders (user_id = usr_code varchar) +
 * tbl_order_items.  We detect is_enrolled via a correlated subquery so
 * it doesn't fan-out the GROUP BY aggregates.
 */
$sql = "
    SELECT
        c.id, c.title, c.thumbnail, c.price, c.discount, c.type,
        c.description, c.created_at, c.instructor_id,
        cat.category_title,
        COUNT(DISTINCT ch.id)    AS total_chapters,
        COUNT(DISTINCT l.id)     AS total_lessons,
        COUNT(DISTINCT oi2.id)   AS enrolled_count,
        ROUND(AVG(r.rating), 1)  AS avg_rating,
        COUNT(DISTINCT r.id)     AS total_reviews,
        (
            SELECT COUNT(*)
            FROM tbl_orders o2
            JOIN tbl_order_items oi3 ON oi3.order_id = o2.id
            WHERE o2.user_id = ?
              AND o2.payment_status = 'paid'
              AND oi3.course_id = c.id
            LIMIT 1
        ) AS is_enrolled
    FROM tbl_courses c
    LEFT JOIN tbl_course_categories cat    ON cat.id        = c.category_id
    LEFT JOIN tbl_course_chapters ch       ON ch.course_id  = c.id AND ch.status = 'active'
    LEFT JOIN tbl_course_chapter_lessons l ON l.chapter_id  = ch.id AND l.status = 'active'
    LEFT JOIN tbl_order_items oi2          ON oi2.course_id = c.id
    LEFT JOIN tbl_course_ratings r         ON r.course_id   = c.id
    WHERE $whereSQL
    GROUP BY c.id
    ORDER BY enrolled_count DESC, c.created_at DESC
    LIMIT ? OFFSET ?
";

array_unshift($params, $usr);
$types = 's' . $types . 'ii';
$params[] = $limit;
$params[] = $offset;

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$courses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ── Total count ────────────────────────────────────────────── */
$countSQL    = "SELECT COUNT(DISTINCT c.id) AS total FROM tbl_courses c WHERE $whereSQL";
$countParams = array_slice($params, 1, count($params) - 3);
$countTypes  = substr($types, 1, strlen($types) - 3);
$cStmt = $db->prepare($countSQL);
if ($countParams) {
    $cStmt->bind_param($countTypes, ...$countParams);
}
$cStmt->execute();
$total = (int)($cStmt->get_result()->fetch_assoc()['total'] ?? 0);

foreach ($courses as &$c) {
    $c['course_token'] = encryptURLId((int)$c['id'], ctx: 'course');
}
unset($c);

ob_clean();
echo json_encode([
    'status' => 'success',
    'data'   => $courses,
    'total'  => $total,
    'page'   => $page,
    'pages'  => (int)ceil($total / $limit),
]);
