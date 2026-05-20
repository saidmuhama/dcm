<?php
ini_set('display_errors', 0);
ob_start();
include('../config/db.php');
session_start();

function _sjs(array $d): never { ob_clean(); header('Content-Type: application/json'); echo json_encode($d); exit; }

if (!isset($_SESSION['usr_code'])) _sjs(['status'=>'error','message'=>'Not authenticated']);

$data = json_decode(file_get_contents('php://input'), true) ?? [];
$usr  = $_SESSION['usr_code'];

/* ── Image upload ─────────────────────────────────────────── */
$imagePath = '';
if (!empty($data['image'])) {
    $folder = '../uploads/';
    if (!is_dir($folder)) mkdir($folder, 0755, true);
    $img = $data['image'];
    if (str_contains($img, 'base64,')) $img = explode('base64,', $img)[1];
    $decoded = base64_decode(str_replace(' ', '+', $img), true);
    if (!$decoded) _sjs(['status'=>'error','message'=>'Invalid image data']);
    $fileName = time() . '_' . bin2hex(random_bytes(4)) . '.png';
    if (!file_put_contents($folder . $fileName, $decoded)) _sjs(['status'=>'error','message'=>'Could not save image']);
    $imagePath = 'uploads/' . $fileName;
}

/* ── Bind values ──────────────────────────────────────────── */
$fn  = trim($data['first_name']         ?? '');
$mn  = trim($data['middle_name']         ?? '');
$ln  = trim($data['last_name']           ?? '');
$dob = trim($data['dob']                 ?? '');
$dsc = trim($data['description']         ?? '');
$sk  = trim($data['skill']               ?? '');
$pn  = trim($data['parent_name']         ?? '');
$ph  = trim($data['phone']               ?? '');
$em  = trim($data['email']               ?? '');
$sc  = trim($data['school']              ?? '');
$co  = trim($data['course']              ?? '');
$mal = (int)($data['main_academic_level']?? 0);
$sal = (int)($data['sub_academic_level'] ?? 0);
$sy  = (int)($data['start_year']         ?? date('Y'));
$ey  = trim($data['end_year']            ?? 'Continuing');

/* ── Upsert ───────────────────────────────────────────────── */
$chk = $db->prepare("SELECT id FROM tbl_students WHERE usr_code = ?");
$chk->bind_param('s', $usr);
$chk->execute();
$exists = (bool)$chk->get_result()->fetch_assoc();

if ($exists) {
    if ($imagePath) {
        // UPDATE with image — 17 params: 11×s + 3×i + 3×s
        $stmt = $db->prepare(
            "UPDATE tbl_students SET first_name=?,middle_name=?,last_name=?,dob=?,description=?,skill=?,
             parent_name=?,phone=?,email=?,school=?,course=?,
             main_academic_level=?,sub_academic_level=?,start_year=?,end_year=?,image=?
             WHERE usr_code=?"
        );
        $stmt->bind_param('sssssssssssiiisss',
            $fn,$mn,$ln,$dob,$dsc,$sk,$pn,$ph,$em,$sc,$co,$mal,$sal,$sy,$ey,$imagePath,$usr);
    } else {
        // UPDATE without image — 16 params: 11×s + 3×i + 2×s
        $stmt = $db->prepare(
            "UPDATE tbl_students SET first_name=?,middle_name=?,last_name=?,dob=?,description=?,skill=?,
             parent_name=?,phone=?,email=?,school=?,course=?,
             main_academic_level=?,sub_academic_level=?,start_year=?,end_year=?
             WHERE usr_code=?"
        );
        $stmt->bind_param('sssssssssssiiiss',
            $fn,$mn,$ln,$dob,$dsc,$sk,$pn,$ph,$em,$sc,$co,$mal,$sal,$sy,$ey,$usr);
    }
} else {
    $img = $imagePath ?: '';
    // INSERT — 17 params: 12×s + 3×i + 2×s
    $stmt = $db->prepare(
        "INSERT INTO tbl_students
         (usr_code,first_name,middle_name,last_name,dob,description,skill,
          parent_name,phone,email,school,course,main_academic_level,sub_academic_level,start_year,end_year,image)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param('ssssssssssssiiiss',
        $usr,$fn,$mn,$ln,$dob,$dsc,$sk,$pn,$ph,$em,$sc,$co,$mal,$sal,$sy,$ey,$img);
}

if (!$stmt || !$stmt->execute()) _sjs(['status'=>'error','message'=>$db->error ?: 'Database error']);

_sjs(['status'=>'success','message'=>'Profile saved successfully']);
