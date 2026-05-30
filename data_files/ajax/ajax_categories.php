<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me || $role != 5) { echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit; }

$action = $_GET['action'] ?? ($_POST['action'] ?? (json_decode(file_get_contents('php://input'),true)['action'] ?? ''));
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

/* ── helpers ── */
function resp(array $d): never { ob_clean(); echo json_encode($d); exit; }
function esc(mysqli $db, $v): string { return $db->real_escape_string(trim($v)); }
function qi($v): int { return (int)$v; }

switch ($action) {

/* ═══ LIST ═══════════════════════════════════════════════════════ */
case 'list':
    $search = esc($db, $_GET['q'] ?? '');
    $where  = $search ? "AND (category_title LIKE '%$search%' OR category_code LIKE '%$search%')" : '';
    $rows = $db->query("
        SELECT c.*,
               (SELECT COUNT(DISTINCT m.course_id)
                FROM tbl_course_category_map m
                JOIN tbl_courses cs ON cs.id = m.course_id AND cs.deleted_at IS NULL
                WHERE m.category_id = c.id) AS course_count
        FROM tbl_course_categories c
        WHERE 1=1 $where
        ORDER BY c.sort_order ASC, c.id ASC
    ")->fetch_all(MYSQLI_ASSOC);
    resp(['status'=>'success','data'=>$rows]);

/* ═══ STATS ══════════════════════════════════════════════════════ */
case 'stats':
    $r = $db->query("
        SELECT
          COUNT(*) AS total,
          SUM(status=1) AS active,
          SUM(status=0) AS inactive,
          (SELECT COUNT(*) FROM tbl_courses WHERE category_id IS NOT NULL AND deleted_at IS NULL) AS categorised_courses,
          (SELECT category_title FROM tbl_course_categories c2
           LEFT JOIN tbl_courses cs2 ON cs2.category_id=c2.id AND cs2.deleted_at IS NULL
           GROUP BY c2.id ORDER BY COUNT(cs2.id) DESC LIMIT 1) AS top_category
        FROM tbl_course_categories
    ")->fetch_assoc();
    resp(['status'=>'success','data'=>$r]);

/* ═══ CREATE ═════════════════════════════════════════════════════ */
case 'create':
    $name = esc($db, $body['category_title'] ?? '');
    $code = strtoupper(esc($db, $body['category_code'] ?? ''));
    $desc = esc($db, $body['category_description'] ?? '');
    $icon = esc($db, $body['icon'] ?? 'bi-grid');
    $ord  = qi($body['sort_order'] ?? 0);

    if (!$name || !$code) resp(['status'=>'error','message'=>'Name and code are required']);

    $chk = $db->query("SELECT id FROM tbl_course_categories WHERE category_code='$code' LIMIT 1");
    if ($chk->num_rows) resp(['status'=>'error','message'=>'Category code already exists']);

    $stmt = $db->prepare("INSERT INTO tbl_course_categories (category_title,category_code,category_description,icon,status,sort_order,created_by) VALUES (?,?,?,?,1,?,?)");
    $stmt->bind_param('ssssii', $name, $code, $desc, $icon, $ord, $me);
    // fix: sort_order is int, created_by is string
    $stmt = $db->prepare("INSERT INTO tbl_course_categories (category_title,category_code,category_description,icon,status,sort_order,created_by) VALUES (?,?,?,?,1,?,?)");
    $stmt->bind_param('sssssi', $name, $code, $desc, $icon, $ord, $me);
    $stmt->execute();
    resp(['status'=>'success','message'=>'Category created','id'=>$db->insert_id]);

/* ═══ UPDATE ═════════════════════════════════════════════════════ */
case 'update':
    $id   = qi($body['id'] ?? 0);
    $name = esc($db, $body['category_title'] ?? '');
    $code = strtoupper(esc($db, $body['category_code'] ?? ''));
    $desc = esc($db, $body['category_description'] ?? '');
    $icon = esc($db, $body['icon'] ?? 'bi-grid');
    $ord  = qi($body['sort_order'] ?? 0);

    if (!$id || !$name || !$code) resp(['status'=>'error','message'=>'Missing required fields']);

    $chk = $db->query("SELECT id FROM tbl_course_categories WHERE category_code='$code' AND id<>$id LIMIT 1");
    if ($chk->num_rows) resp(['status'=>'error','message'=>'Category code already used by another category']);

    $db->query("UPDATE tbl_course_categories SET category_title='$name',category_code='$code',category_description='$desc',icon='$icon',sort_order=$ord,updated_at=NOW() WHERE id=$id");
    resp(['status'=>'success','message'=>'Category updated']);

/* ═══ TOGGLE STATUS ══════════════════════════════════════════════ */
case 'toggle_status':
    $id = qi($body['id'] ?? 0);
    if (!$id) resp(['status'=>'error','message'=>'Invalid ID']);
    $db->query("UPDATE tbl_course_categories SET status = IF(status=1,0,1), updated_at=NOW() WHERE id=$id");
    $row = $db->query("SELECT status FROM tbl_course_categories WHERE id=$id")->fetch_assoc();
    resp(['status'=>'success','new_status'=>(int)$row['status']]);

/* ═══ DELETE ═════════════════════════════════════════════════════ */
case 'delete':
    $id = qi($body['id'] ?? 0);
    if (!$id) resp(['status'=>'error','message'=>'Invalid ID']);
    $cc = (int)$db->query("SELECT COUNT(*) c FROM tbl_courses WHERE category_id=$id AND deleted_at IS NULL")->fetch_row()[0];
    if ($cc) resp(['status'=>'error','message'=>"Cannot delete — $cc course(s) are assigned to this category"]);
    $db->query("DELETE FROM tbl_course_categories WHERE id=$id");
    resp(['status'=>'success','message'=>'Category deleted']);

/* ═══ GET ONE ════════════════════════════════════════════════════ */
case 'get':
    $id  = qi($_GET['id'] ?? 0);
    $row = $db->query("SELECT * FROM tbl_course_categories WHERE id=$id LIMIT 1")->fetch_assoc();
    resp(['status'=> $row ? 'success' : 'error', 'data'=>$row]);

default:
    resp(['status'=>'error','message'=>'Unknown action']);
}
