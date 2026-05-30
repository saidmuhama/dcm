<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
include('../config/url_crypt_config.php');
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? 0) != 4) {
    echo json_encode(['status'=>'error','message'=>'Access denied']); exit;
}

$me     = $_SESSION['usr_code'] ?? '';
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// ── Get org for current admin ─────────────────────────────────────
function getMyOrg(mysqli $db, string $usrCode): ?array {
    $s = $db->prepare("
        SELECT o.* FROM tbl_organizations o
        JOIN tbl_org_members m ON m.org_code=o.org_code
        WHERE m.usr_code=? AND m.org_role='admin' AND m.status='active' AND o.deleted_at IS NULL
        LIMIT 1
    ");
    $s->bind_param('s', $usrCode);
    $s->execute();
    return $s->get_result()->fetch_assoc();
}

function genUsrCode(): string { return 'USR' . time() . rand(100,999); }

function orgLog(mysqli $db, string $oc, string $actor, string $action, string $tt='', string $tid='', array $d=[]): void {
    $det = $d ? json_encode($d) : null;
    $ip  = $_SERVER['REMOTE_ADDR'] ?? null;
    $s   = $db->prepare("INSERT INTO tbl_org_activity (org_code,actor_usr_code,action,target_type,target_id,details,ip_address) VALUES (?,?,?,?,?,?,?)");
    $s->bind_param('sssssss', $oc, $actor, $action, $tt, $tid, $det, $ip);
    $s->execute();
}

$myOrg = getMyOrg($db, $me);

// get_org allowed without existing org check
if ($action === 'get_org') {
    echo json_encode(['status'=>'success','data'=>$myOrg]);
    exit;
}

if (!$myOrg) { echo json_encode(['status'=>'error','message'=>'No active organization found for your account']); exit; }

$orgCode = $myOrg['org_code'];

// ── Switch ───────────────────────────────────────────────────────
switch ($action) {

/* ── Dashboard stats ──────────────────────────────────────────── */
case 'dashboard':
    $totalMembers = $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE org_code='$orgCode'")->fetch_row()[0];
    $activeMembers= $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE org_code='$orgCode' AND status='active'")->fetch_row()[0];
    $totalDepts   = $db->query("SELECT COUNT(*) FROM tbl_org_departments WHERE org_code='$orgCode' AND status='active'")->fetch_row()[0];
    $totalCourses = $db->query("SELECT COUNT(*) FROM tbl_org_course_access WHERE org_code='$orgCode' AND is_active=1")->fetch_row()[0];

    // Members enrolled in at least one org course
    $enrolled = $db->query("
        SELECT COUNT(DISTINCT m.usr_code) FROM tbl_org_members m
        JOIN tbl_all_users u ON u.usr_code=m.usr_code
        JOIN tbl_course_enrollments e ON e.user_id=u.id
        JOIN tbl_org_course_access ca ON ca.course_id=e.course_id AND ca.org_code='$orgCode' AND ca.is_active=1
        WHERE m.org_code='$orgCode'
    ")->fetch_row()[0];

    // Average completion rate
    $avgCompletion = $db->query("
        SELECT ROUND(AVG(pct),1) FROM (
            SELECT m.usr_code,
                ROUND(100 * COUNT(DISTINCT CASE WHEN p.watched=1 THEN p.lesson_id END) / GREATEST(COUNT(DISTINCT l.id),1), 1) AS pct
            FROM tbl_org_members m
            JOIN tbl_org_course_access ca ON ca.org_code='$orgCode' AND ca.is_active=1
            JOIN tbl_course_chapter_lessons l ON l.course_id=ca.course_id AND l.status='active'
            LEFT JOIN tbl_course_progress p ON p.lesson_id=l.id AND p.user_id=m.usr_code
            WHERE m.org_code='$orgCode' AND m.status='active'
            GROUP BY m.usr_code
        ) t
    ")->fetch_row()[0] ?? 0;

    // Role breakdown
    $roleBreakdown = $db->query("SELECT org_role, COUNT(*) AS cnt FROM tbl_org_members WHERE org_code='$orgCode' AND status='active' GROUP BY org_role ORDER BY cnt DESC")->fetch_all(MYSQLI_ASSOC);

    // Recent activity (last 10)
    $recentAct = $db->query("
        SELECT a.created_at, a.action, a.actor_usr_code,
               CONCAT(u.first_name,' ',u.last_name) AS actor_name
        FROM tbl_org_activity a
        LEFT JOIN tbl_all_users u ON u.usr_code=a.actor_usr_code
        WHERE a.org_code='$orgCode'
        ORDER BY a.created_at DESC LIMIT 10
    ")->fetch_all(MYSQLI_ASSOC);

    // Top learners (top 5 by lessons done)
    $topLearners = $db->query("
        SELECT u.first_name, u.last_name, u.email_address AS email,
               COUNT(DISTINCT oca.course_id) AS enrolled_courses,
               ROUND(100 * COUNT(DISTINCT CASE WHEN p.watched=1 THEN p.lesson_id END) / GREATEST(COUNT(DISTINCT l.id),1),1) AS avg_completion
        FROM tbl_org_members m
        JOIN tbl_all_users u ON u.usr_code=m.usr_code
        LEFT JOIN tbl_org_course_access oca ON oca.org_code='$orgCode' AND oca.is_active=1
        LEFT JOIN tbl_course_chapter_lessons l ON l.course_id=oca.course_id AND l.status='active'
        LEFT JOIN tbl_course_progress p ON p.lesson_id=l.id AND p.user_id=m.usr_code
        WHERE m.org_code='$orgCode' AND m.status='active'
        GROUP BY m.usr_code
        ORDER BY avg_completion DESC, enrolled_courses DESC LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','data'=>[
        'total_members'   => (int)$totalMembers,
        'active_members'  => (int)$activeMembers,
        'total_depts'     => (int)$totalDepts,
        'total_courses'   => (int)$totalCourses,
        'enrolled'        => (int)$enrolled,
        'avg_completion'  => (float)$avgCompletion,
        'role_breakdown'  => $roleBreakdown,
        'recent_activity' => $recentAct,
        'top_learners'    => $topLearners,
        'org'             => $myOrg,
    ]]);
    break;

/* ── List members ─────────────────────────────────────────────── */
case 'list_members':
    $search  = '%' . $db->real_escape_string($_GET['q'] ?? $_POST['search'] ?? '') . '%';
    $role    = $db->real_escape_string($_GET['role']   ?? $_POST['role']   ?? '');
    $dept    = (int)($_GET['dept']   ?? $_POST['dept_id'] ?? 0);
    $stat    = $db->real_escape_string($_GET['status'] ?? $_POST['status'] ?? '');
    $page    = max(1, (int)($_GET['page'] ?? $_POST['page'] ?? 1));
    $per     = 20;
    $offset  = ($page - 1) * $per;

    $where = "WHERE m.org_code='$orgCode'
              AND (u.first_name LIKE '$search' OR u.last_name LIKE '$search' OR u.email_address LIKE '$search' OR m.employee_id LIKE '$search')";
    if ($role) $where .= " AND m.org_role='$role'";
    if ($dept) $where .= " AND m.dept_id=$dept";
    if ($stat) $where .= " AND m.status='$stat'";

    $total = $db->query("SELECT COUNT(*) FROM tbl_org_members m JOIN tbl_all_users u ON u.usr_code=m.usr_code $where")->fetch_row()[0];
    $active     = $db->query("SELECT COUNT(*) FROM tbl_org_members m JOIN tbl_all_users u ON u.usr_code=m.usr_code $where AND m.status='active'")->fetch_row()[0];
    $instructors= $db->query("SELECT COUNT(*) FROM tbl_org_members m JOIN tbl_all_users u ON u.usr_code=m.usr_code $where AND m.org_role='instructor'")->fetch_row()[0];
    $students   = $db->query("SELECT COUNT(*) FROM tbl_org_members m JOIN tbl_all_users u ON u.usr_code=m.usr_code $where AND m.org_role='student'")->fetch_row()[0];

    $rows = $db->query("
        SELECT m.id AS member_id, m.usr_code, m.org_role, m.dept_id, m.employee_id, m.status, m.joined_at,
               u.first_name, u.last_name, u.email_address AS email, u.phone_number, u.user_status, u.user_role,
               d.dept_name
        FROM tbl_org_members m
        JOIN tbl_all_users u ON u.usr_code=m.usr_code
        LEFT JOIN tbl_org_departments d ON d.id=m.dept_id
        $where
        ORDER BY m.org_role ASC, m.joined_at DESC
        LIMIT $per OFFSET $offset
    ")->fetch_all(MYSQLI_ASSOC);

    echo json_encode(['status'=>'success','members'=>$rows,'total'=>$total,'page'=>$page,'per'=>$per,
        'counts'=>['total'=>(int)$total,'active'=>(int)$active,'instructors'=>(int)$instructors,'students'=>(int)$students]]);
    break;

/* ── Add existing user to org ─────────────────────────────────── */
case 'add_member':
    $body    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $email   = trim($body['email'] ?? '');
    $role    = $db->real_escape_string($body['org_role'] ?? 'student');
    $deptId  = (int)($body['dept_id'] ?? 0);
    $empId   = $db->real_escape_string(trim($body['employee_id'] ?? ''));

    if (!$email) { echo json_encode(['status'=>'error','message'=>'Email is required']); exit; }

    // Check member limit
    $memberCount = $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE org_code='$orgCode' AND status='active'")->fetch_row()[0];
    if ($myOrg['max_users'] > 0 && $memberCount >= $myOrg['max_users']) {
        echo json_encode(['status'=>'error','message'=>'Member limit reached for your plan']); exit;
    }

    $esc = $db->real_escape_string($email);
    $user = $db->query("SELECT usr_code FROM tbl_all_users WHERE email_address='$esc' LIMIT 1")->fetch_assoc();
    if (!$user) { echo json_encode(['status'=>'error','message'=>'No user found with that email address']); exit; }

    $usrCode = $user['usr_code'];
    $deptSql = $deptId ? $deptId : 'NULL';
    $empSql  = $empId  ? "'$empId'" : 'NULL';
    $stat    = 'active';
    $result  = $db->query("INSERT INTO tbl_org_members (org_code,usr_code,org_role,dept_id,employee_id,status,invited_by)
                            VALUES ('$orgCode','$usrCode','$role',$deptSql,$empSql,'$stat','$me')
                            ON DUPLICATE KEY UPDATE org_role='$role', dept_id=$deptSql, status='active'");
    if (!$result) { echo json_encode(['status'=>'error','message'=>'Failed to add member']); exit; }

    orgLog($db, $orgCode, $me, 'member_added', 'user', $usrCode, ['role'=>$role]);
    echo json_encode(['status'=>'success','message'=>'Member added successfully']);
    break;

/* ── Create new user + add to org ────────────────────────────── */
case 'create_member':
    $body    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $fname   = trim($body['first_name']  ?? '');
    $lname   = trim($body['last_name']   ?? '');
    $email   = trim($body['email']       ?? '');
    $phone   = trim($body['phone']       ?? '');
    $pass    = !empty($body['password']) ? $body['password'] : 'DigitalClass@123';
    $role    = $db->real_escape_string($body['org_role'] ?? 'student');
    $deptId  = (int)($body['dept_id']   ?? 0);
    $empId   = $db->real_escape_string(trim($body['employee_id'] ?? ''));

    if (!$fname || !$email) { echo json_encode(['status'=>'error','message'=>'First name and email are required']); exit; }

    // Member limit check
    $memberCount = $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE org_code='$orgCode' AND status='active'")->fetch_row()[0];
    if ($myOrg['max_users'] > 0 && $memberCount >= $myOrg['max_users']) {
        echo json_encode(['status'=>'error','message'=>'Member limit reached']); exit;
    }

    $esc = $db->real_escape_string($email);
    if ($db->query("SELECT id FROM tbl_all_users WHERE email_address='$esc' LIMIT 1")->num_rows > 0) {
        echo json_encode(['status'=>'error','message'=>'Email already registered — use Add Existing Member instead']); exit;
    }

    // Map org_role to system user_role
    $sysRole = match($role) { 'instructor' => 3, 'admin','coordinator' => 4, default => 1 };
    $usrCode = genUsrCode();
    $pwHash  = password_hash($pass, PASSWORD_BCRYPT);
    $deptSql = $deptId ? $deptId : 'NULL';
    $empSql  = $empId  ? "'$empId'" : 'NULL';
    $stat    = 'active';

    $db->begin_transaction();
    try {
        $us = $db->prepare("INSERT INTO tbl_all_users (usr_code,first_name,last_name,email_address,phone_number,user_role,user_password,user_status,signup_success,force_pw_change) VALUES (?,?,?,?,?,?,?,'Active','Completed',1)");
        $us->bind_param('sssssss', $usrCode,$fname,$lname,$email,$phone,$sysRole,$pwHash);
        $us->execute();

        $ms = $db->query("INSERT INTO tbl_org_members (org_code,usr_code,org_role,dept_id,employee_id,status,invited_by)
                          VALUES ('$orgCode','$usrCode','$role',$deptSql,$empSql,'$stat','$me')");

        $db->commit();
        orgLog($db, $orgCode, $me, 'member_created', 'user', $usrCode, ['name'=>"$fname $lname", 'role'=>$role]);

        $smsSent = false;
        if (!empty($phone)) {
            $orgName = $myOrg['org_name'] ?? 'DigitalClass';
            $smsMsg  = "Welcome to $orgName!\nEmail: $email\nPassword: $pass\nYou will be required to change your password on first login.";
            $smsResult = App::sendSMS($phone, $smsMsg);
            $smsSent   = !empty($smsResult['status']);
        }

        echo json_encode([
            'status'      => 'success',
            'message'     => 'Member account created successfully',
            'sms_sent'    => $smsSent,
            'credentials' => [
                'name'     => trim("$fname $lname"),
                'email'    => $email,
                'password' => $pass,
                'phone'    => $phone,
                'usr_code' => $usrCode,
            ]
        ]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status'=>'error','message'=>'Failed: '.$e->getMessage()]);
    }
    break;

/* ── Update member ────────────────────────────────────────────── */
case 'update_member':
    $body    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $usrCode = $db->real_escape_string($body['usr_code'] ?? '');
    $role    = $db->real_escape_string($body['org_role'] ?? 'student');
    $deptId  = !empty($body['dept_id']) ? (int)$body['dept_id'] : null;
    $empId   = $db->real_escape_string(trim($body['employee_id'] ?? ''));
    $stat    = $db->real_escape_string($body['status'] ?? 'active');
    $empSql  = $empId ? "'$empId'" : 'NULL';
    $deptSql = $deptId ? $deptId : 'NULL';
    if (!$usrCode) { echo json_encode(['status'=>'error','message'=>'Missing usr_code']); exit; }

    $db->query("UPDATE tbl_org_members SET org_role='$role', dept_id=$deptSql, employee_id=$empSql, status='$stat'
                WHERE usr_code='$usrCode' AND org_code='$orgCode'");
    orgLog($db, $orgCode, $me, 'member_updated', 'user', $usrCode);
    echo json_encode(['status'=>'success','message'=>'Member updated']);
    break;

/* ── Remove member ────────────────────────────────────────────── */
case 'remove_member':
    $body    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $usrCode = $db->real_escape_string($body['usr_code'] ?? '');
    if (!$usrCode) { echo json_encode(['status'=>'error','message'=>'Invalid member']); exit; }
    $db->query("DELETE FROM tbl_org_members WHERE usr_code='$usrCode' AND org_code='$orgCode'");
    orgLog($db, $orgCode, $me, 'member_removed', 'user', $usrCode);
    echo json_encode(['status'=>'success','message'=>'Member removed']);
    break;

/* ── Reset member password ────────────────────────────────────── */
case 'reset_password':
    $body    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $usrCode = $db->real_escape_string($body['usr_code'] ?? '');
    if (!$usrCode) { echo json_encode(['status'=>'error','message'=>'Invalid member']); exit; }

    // Verify member belongs to this org
    $check = $db->query("SELECT id FROM tbl_org_members WHERE org_code='$orgCode' AND usr_code='$usrCode' LIMIT 1");
    if ($check->num_rows === 0) { echo json_encode(['status'=>'error','message'=>'Member not found in your organization']); exit; }

    $hash = password_hash('DigitalClass@123', PASSWORD_BCRYPT);
    $db->query("UPDATE tbl_all_users SET user_password='$hash', force_pw_change=1 WHERE usr_code='$usrCode'");
    orgLog($db, $orgCode, $me, 'password_reset', 'user', $usrCode);
    echo json_encode(['status'=>'success','message'=>'Password reset to default (DigitalClass@123)']);
    break;

/* ── Import members via CSV ───────────────────────────────────── */
case 'import_members':
    $csvFile = $_FILES['csv_file']['tmp_name'] ?? $_FILES['csv']['tmp_name'] ?? '';
    if (empty($csvFile)) { echo json_encode(['status'=>'error','message'=>'No file uploaded']); exit; }
    $_FILES['csv']['tmp_name'] = $csvFile; // normalize below
    if (empty($_FILES['csv']['tmp_name'])) { echo json_encode(['status'=>'error','message'=>'No file uploaded']); exit; }

    $memberCount = $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE org_code='$orgCode' AND status='active'")->fetch_row()[0];
    $remaining   = $myOrg['max_users'] > 0 ? ($myOrg['max_users'] - $memberCount) : PHP_INT_MAX;

    $handle  = fopen($csvFile, 'r');
    $header  = fgetcsv($handle); // skip header row
    $created = 0; $skipped = 0; $errors = [];

    while (($row = fgetcsv($handle)) !== false) {
        if (count($row) < 3) { $skipped++; continue; }
        [$fname, $lname, $email, $role, $deptCode, $empId] = array_pad($row, 6, '');
        $fname = trim($fname); $lname = trim($lname); $email = trim($email);
        $role  = in_array(trim($role), ['admin','coordinator','instructor','student','staff']) ? trim($role) : 'student';
        $empId = trim($empId);

        if (!$fname || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }
        if ($remaining <= 0) { $skipped++; $errors[] = "$email: member limit reached"; continue; }

        $esc = $db->real_escape_string($email);
        $existing = $db->query("SELECT usr_code FROM tbl_all_users WHERE email_address='$esc' LIMIT 1")->fetch_assoc();

        if ($existing) {
            $usrCode = $existing['usr_code'];
        } else {
            $usrCode = genUsrCode();
            $defPass = password_hash('DigitalClass@123', PASSWORD_BCRYPT);
            $sysRole = match($role) { 'instructor' => 3, 'admin','coordinator' => 4, default => 1 };
            $fn      = $db->real_escape_string($fname);
            $ln      = $db->real_escape_string($lname);
            $ph      = '';
            $db->query("INSERT INTO tbl_all_users (usr_code,first_name,last_name,email_address,phone_number,user_role,user_password,user_status,signup_success)
                        VALUES ('$usrCode','$fn','$ln','$esc','$ph',$sysRole,'$defPass','Active','Completed')");
        }

        $empSql = $empId ? "'".$db->real_escape_string($empId)."'" : 'NULL';
        $rl     = $db->real_escape_string($role);
        $db->query("INSERT INTO tbl_org_members (org_code,usr_code,org_role,employee_id,status,invited_by)
                    VALUES ('$orgCode','$usrCode','$rl',$empSql,'active','$me')
                    ON DUPLICATE KEY UPDATE org_role='$rl', status='active'");
        $created++;
        $remaining--;
    }
    fclose($handle);

    orgLog($db, $orgCode, $me, 'bulk_import', 'members', '', ['imported'=>$created,'skipped'=>$skipped]);
    echo json_encode(['status'=>'success','imported'=>$created,'skipped'=>$skipped,'message'=>"Imported $created, skipped $skipped",'errors'=>$errors]);
    break;

/* ── List departments ─────────────────────────────────────────── */
case 'list_departments':
    $rows = $db->query("
        SELECT d.*,
               CONCAT(u.first_name,' ',u.last_name) AS head_name,
               (SELECT COUNT(*) FROM tbl_org_members m WHERE m.dept_id=d.id AND m.status='active') AS member_count
        FROM tbl_org_departments d
        LEFT JOIN tbl_all_users u ON u.usr_code=d.head_usr_code
        WHERE d.org_code='$orgCode'
        ORDER BY d.status ASC, d.dept_name ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','departments'=>$rows]);
    break;

/* ── Create department ────────────────────────────────────────── */
case 'create_dept':
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $name = $db->real_escape_string(trim($body['dept_name'] ?? ''));
    $code = $db->real_escape_string(trim($body['dept_code'] ?? ''));
    $desc = $db->real_escape_string(trim($body['description'] ?? ''));
    $head = $db->real_escape_string(trim($body['head_usr_code'] ?? ''));
    if (!$name) { echo json_encode(['status'=>'error','message'=>'Department name is required']); exit; }
    $codeSql = $code ? "'$code'" : 'NULL';
    $headSql = $head ? "'$head'" : 'NULL';
    $descSql = $desc ? "'$desc'" : 'NULL';
    $db->query("INSERT INTO tbl_org_departments (org_code,dept_name,dept_code,description,head_usr_code) VALUES ('$orgCode','$name',$codeSql,$descSql,$headSql)");
    orgLog($db, $orgCode, $me, 'dept_created', 'department', $name);
    echo json_encode(['status'=>'success','message'=>'Department created','dept_id'=>$db->insert_id]);
    break;

/* ── Update department ────────────────────────────────────────── */
case 'update_dept':
    $body   = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $did    = (int)($body['dept_id'] ?? 0);
    $name   = $db->real_escape_string(trim($body['dept_name'] ?? ''));
    $code   = $db->real_escape_string(trim($body['dept_code'] ?? ''));
    $desc   = $db->real_escape_string(trim($body['description'] ?? ''));
    $head   = $db->real_escape_string(trim($body['head_usr_code'] ?? ''));
    $stat   = $db->real_escape_string($body['status'] ?? 'active');
    if (!$did || !$name) { echo json_encode(['status'=>'error','message'=>'Missing data']); exit; }
    $codeSql = $code ? "'$code'" : 'NULL';
    $headSql = $head ? "'$head'" : 'NULL';
    $db->query("UPDATE tbl_org_departments SET dept_name='$name', dept_code=$codeSql, description='$desc', head_usr_code=$headSql, status='$stat'
                WHERE id=$did AND org_code='$orgCode'");
    orgLog($db, $orgCode, $me, 'dept_updated', 'department', (string)$did);
    echo json_encode(['status'=>'success','message'=>'Department updated']);
    break;

/* ── Delete department ────────────────────────────────────────── */
case 'delete_dept':
    $body = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $did  = (int)($body['dept_id'] ?? 0);
    if (!$did) { echo json_encode(['status'=>'error','message'=>'Invalid department']); exit; }
    // Unassign members first
    $db->query("UPDATE tbl_org_members SET dept_id=NULL WHERE dept_id=$did AND org_code='$orgCode'");
    $db->query("DELETE FROM tbl_org_departments WHERE id=$did AND org_code='$orgCode'");
    orgLog($db, $orgCode, $me, 'dept_deleted', 'department', (string)$did);
    echo json_encode(['status'=>'success','message'=>'Department deleted']);
    break;

/* ── List accessible courses ──────────────────────────────────── */
case 'list_courses':
    $rows = $db->query("
        SELECT ca.*, c.title, c.thumbnail, c.status AS course_status, c.price,
               CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
               (SELECT COUNT(DISTINCT e.user_id) FROM tbl_course_enrollments e
                JOIN tbl_all_users eu ON eu.id=e.user_id
                JOIN tbl_org_members m ON m.usr_code=eu.usr_code AND m.org_code='$orgCode'
                WHERE e.course_id=ca.course_id) AS enrolled_members
        FROM tbl_org_course_access ca
        JOIN tbl_courses c ON c.id=ca.course_id
        LEFT JOIN tbl_all_users u ON u.usr_code=c.instructor_id
        WHERE ca.org_code='$orgCode' AND ca.is_active=1
        ORDER BY ca.granted_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$rows]);
    break;

/* ── Progress reports ─────────────────────────────────────────── */
case 'get_reports':
    $deptId  = (int)($_GET['dept']   ?? 0);
    $period  = max(0, (int)($_GET['period'] ?? 30));
    $deptWhere = $deptId ? "AND m.dept_id=$deptId" : '';
    $dateWhere = $period > 0 ? "AND p.created_at >= DATE_SUB(NOW(), INTERVAL $period DAY)" : '';

    $activeLearners = $db->query("
        SELECT COUNT(DISTINCT m.usr_code) FROM tbl_org_members m
        JOIN tbl_course_progress p ON p.user_id=m.usr_code
        JOIN tbl_course_chapter_lessons lp ON lp.id=p.lesson_id AND lp.status='active'
        JOIN tbl_course_chapters chp ON chp.id=lp.chapter_id
        JOIN tbl_org_course_access ocap ON ocap.course_id=chp.course_id AND ocap.org_code='$orgCode' AND ocap.is_active=1
        WHERE m.org_code='$orgCode' AND m.status='active' $deptWhere $dateWhere
    ")->fetch_row()[0];
    // Count distinct (member, course) pairs where member has watched at least one lesson
    $totalEnrolled = $db->query("
        SELECT COUNT(DISTINCT CONCAT(m.usr_code,'|',chp.course_id))
        FROM tbl_org_members m
        JOIN tbl_course_progress p ON p.user_id=m.usr_code AND p.watched=1
        JOIN tbl_course_chapter_lessons lp ON lp.id=p.lesson_id AND lp.status='active'
        JOIN tbl_course_chapters chp ON chp.id=lp.chapter_id
        JOIN tbl_org_course_access oca ON oca.course_id=chp.course_id AND oca.org_code='$orgCode' AND oca.is_active=1
        WHERE m.org_code='$orgCode' $deptWhere
    ")->fetch_row()[0];

    // All member counts (enrolled, completed, avg_progress, last_active) are progress-based,
    // so members who access org courses without a formal enrollment record are counted correctly.
    $members = $db->query("
        SELECT m.usr_code, m.org_role,
               u.first_name, u.last_name, u.email_address AS email,
               d.dept_name,
               (
                   SELECT COUNT(DISTINCT oca2.course_id)
                   FROM tbl_org_course_access oca2
                   JOIN tbl_course_chapter_lessons l2 ON l2.course_id=oca2.course_id AND l2.status='active'
                   JOIN tbl_course_progress p2 ON p2.lesson_id=l2.id AND p2.user_id=m.usr_code AND p2.watched=1
                   WHERE oca2.org_code='$orgCode' AND oca2.is_active=1
               ) AS enrolled_courses,
               (
                   SELECT COUNT(DISTINCT oca2.course_id)
                   FROM tbl_org_course_access oca2
                   WHERE oca2.org_code='$orgCode' AND oca2.is_active=1
                   AND (
                       SELECT ROUND(100 * SUM(CASE WHEN p2.watched=1 THEN 1 ELSE 0 END) / GREATEST(COUNT(*),1), 1)
                       FROM tbl_course_chapter_lessons l2
                       LEFT JOIN tbl_course_progress p2 ON p2.lesson_id=l2.id AND p2.user_id=m.usr_code
                       WHERE l2.course_id=oca2.course_id AND l2.status='active'
                   ) >= 100
               ) AS completed_courses,
               COALESCE((
                   SELECT ROUND(100 * COUNT(DISTINCT CASE WHEN p2.watched=1 THEN p2.lesson_id END) / GREATEST(COUNT(DISTINCT l2.id),1), 1)
                   FROM tbl_org_course_access oca2
                   JOIN tbl_course_chapter_lessons l2 ON l2.course_id=oca2.course_id AND l2.status='active'
                   LEFT JOIN tbl_course_progress p2 ON p2.lesson_id=l2.id AND p2.user_id=m.usr_code
                   WHERE oca2.org_code='$orgCode' AND oca2.is_active=1
               ), 0) AS avg_progress,
               (
                   SELECT MAX(p3.created_at)
                   FROM tbl_course_progress p3
                   JOIN tbl_course_chapter_lessons l3 ON l3.id=p3.lesson_id
                   JOIN tbl_course_chapters ch3 ON ch3.id=l3.chapter_id
                   JOIN tbl_org_course_access oca3 ON oca3.course_id=ch3.course_id AND oca3.org_code='$orgCode' AND oca3.is_active=1
                   WHERE p3.user_id=m.usr_code
               ) AS last_active
        FROM tbl_org_members m
        JOIN tbl_all_users u ON u.usr_code=m.usr_code
        LEFT JOIN tbl_org_departments d ON d.id=m.dept_id
        WHERE m.org_code='$orgCode' AND m.status='active' $deptWhere
        GROUP BY m.usr_code
        ORDER BY avg_progress DESC, enrolled_courses DESC
    ")->fetch_all(MYSQLI_ASSOC);

    $totalCompleted = array_sum(array_column($members, 'completed_courses'));
    $avgCompletion  = count($members) > 0 ? round(array_sum(array_column($members,'avg_progress')) / count($members), 1) : 0;

    // Course engagement
    $courses = $db->query("
        SELECT c.id, c.title,
               COUNT(DISTINCT e.user_id) AS enrolled,
               COUNT(DISTINCT CASE WHEN prog_pct.pct >= 100 THEN e.user_id END) AS completed,
               NULL AS avg_score
        FROM tbl_org_course_access oca
        JOIN tbl_courses c ON c.id=oca.course_id
        LEFT JOIN tbl_course_enrollments e ON e.course_id=oca.course_id
        LEFT JOIN tbl_all_users eu ON eu.id=e.user_id
        LEFT JOIN tbl_org_members m ON m.usr_code=eu.usr_code AND m.org_code='$orgCode' $deptWhere
        LEFT JOIN (
            SELECT p2.user_id, p2.course_id,
                   ROUND(100*SUM(p2.watched)/GREATEST(COUNT(*),1),1) AS pct
            FROM tbl_course_progress p2 GROUP BY p2.user_id, p2.course_id
        ) prog_pct ON prog_pct.user_id=eu.usr_code AND prog_pct.course_id=e.course_id
        WHERE oca.org_code='$orgCode' AND oca.is_active=1
        GROUP BY c.id
        ORDER BY enrolled DESC
    ")->fetch_all(MYSQLI_ASSOC);

    // Department summary
    $departments = $db->query("
        SELECT d.dept_name,
               COUNT(DISTINCT m.usr_code) AS member_count,
               COUNT(DISTINCT e.course_id) AS enrolled,
               COUNT(DISTINCT CASE WHEN dprog.pct >= 100 THEN CONCAT(m.usr_code,'-',e.course_id) END) AS completed,
               ROUND(AVG(COALESCE(dprog.pct,0)),1) AS avg_completion
        FROM tbl_org_departments d
        LEFT JOIN tbl_org_members m ON m.dept_id=d.id AND m.status='active'
        LEFT JOIN tbl_all_users eu ON eu.usr_code=m.usr_code
        LEFT JOIN tbl_course_enrollments e ON e.user_id=eu.id
        LEFT JOIN tbl_org_course_access oca ON oca.course_id=e.course_id AND oca.org_code='$orgCode' AND oca.is_active=1
        LEFT JOIN tbl_course_chapter_lessons l ON l.course_id=oca.course_id AND l.status='active'
        LEFT JOIN (
            SELECT p2.user_id, p2.course_id,
                   ROUND(100*SUM(p2.watched)/GREATEST(COUNT(*),1),1) AS pct
            FROM tbl_course_progress p2 GROUP BY p2.user_id, p2.course_id
        ) dprog ON dprog.user_id=m.usr_code AND dprog.course_id=e.course_id
        WHERE d.org_code='$orgCode' AND d.status='active'
        GROUP BY d.id
        ORDER BY d.dept_name ASC
    ")->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        'status'          => 'success',
        'active_learners' => (int)$activeLearners,
        'total_completed' => (int)$totalCompleted,
        'avg_completion'  => (float)$avgCompletion,
        'total_enrolled'  => (int)$totalEnrolled,
        'members'         => $members,
        'courses'         => $courses,
        'departments'     => $departments,
    ]);
    break;

/* ── Org members list for dept head select ────────────────────── */
case 'list_instructors':
    $rows = $db->query("
        SELECT m.usr_code, u.first_name, u.last_name, u.email_address
        FROM tbl_org_members m
        JOIN tbl_all_users u ON u.usr_code=m.usr_code
        WHERE m.org_code='$orgCode' AND m.org_role IN ('admin','coordinator','instructor') AND m.status='active'
        ORDER BY u.first_name ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','instructors'=>$rows]);
    break;

/* ── Browse all published courses with org subscription status ─── */
case 'browse_courses':
    $q       = $db->real_escape_string(trim($_GET['q']   ?? ''));
    $catId   = (int)($_GET['cat']  ?? 0);
    $subOnly = (int)($_GET['subscribed'] ?? 0);
    $qWhere  = $q      ? "AND c.title LIKE '%$q%'" : '';
    $cWhere  = $catId  ? "AND c.category_id=$catId" : '';
    $sWhere  = $subOnly ? "AND oca.id IS NOT NULL AND oca.is_active=1" : '';
    $rows = $db->query("
        SELECT c.id, c.title, c.thumbnail, c.price, c.discount, c.description,
               CONCAT(u.first_name,' ',u.last_name) AS instructor_name,
               u.usr_code AS instructor_code,
               cat.category_title AS category_name,
               (SELECT COUNT(*) FROM tbl_course_chapters ch WHERE ch.course_id=c.id AND ch.status='active') AS chapters,
               (SELECT COUNT(*) FROM tbl_course_chapter_lessons l
                JOIN tbl_course_chapters ch2 ON ch2.id=l.chapter_id
                WHERE ch2.course_id=c.id AND l.status='active') AS lessons,
               oca.id AS access_id,
               oca.is_active AS is_subscribed,
               oca.granted_at,
               oca.expires_at,
               (SELECT COUNT(DISTINCT e.user_id) FROM tbl_course_enrollments e
                JOIN tbl_all_users eu ON eu.id=e.user_id
                JOIN tbl_org_members m ON m.usr_code=eu.usr_code
                WHERE e.course_id=c.id AND m.org_code='$orgCode') AS enrolled_members
        FROM tbl_courses c
        LEFT JOIN tbl_all_users u ON u.usr_code=c.instructor_id
        LEFT JOIN tbl_course_categories cat ON cat.id=c.category_id
        LEFT JOIN tbl_org_course_access oca ON oca.course_id=c.id AND oca.org_code='$orgCode'
        WHERE c.status='active' AND c.is_approved='approved' AND c.deleted_at IS NULL
          $qWhere $cWhere $sWhere
        ORDER BY c.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','courses'=>$rows]);
    break;

/* ── Subscribe org to a course ───────────────────────────────── */
case 'subscribe_course':
    $body     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $courseId = (int)($body['course_id'] ?? 0);
    $expires  = $db->real_escape_string(trim($body['expires_at'] ?? ''));
    if (!$courseId) { echo json_encode(['status'=>'error','message'=>'Invalid course']); exit; }
    // Check course is published
    $chk = $db->query("SELECT id FROM tbl_courses WHERE id=$courseId AND status='active' AND is_approved='approved' LIMIT 1")->fetch_assoc();
    if (!$chk) { echo json_encode(['status'=>'error','message'=>'Course not available']); exit; }
    // Upsert
    $expSql = $expires ? "'$expires'" : 'NULL';
    $db->query("INSERT INTO tbl_org_course_access (org_code, course_id, is_active, granted_by, expires_at)
                VALUES ('$orgCode', $courseId, 1, '$me', $expSql)
                ON DUPLICATE KEY UPDATE is_active=1, granted_by='$me', expires_at=$expSql, granted_at=NOW()");
    orgLog($db, $orgCode, $me, 'course_subscribed', 'course', (string)$courseId);
    echo json_encode(['status'=>'success','message'=>'Course subscribed']);
    break;

/* ── Unsubscribe org from a course ───────────────────────────── */
case 'unsubscribe_course':
    $body     = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $courseId = (int)($body['course_id'] ?? 0);
    if (!$courseId) { echo json_encode(['status'=>'error','message'=>'Invalid course']); exit; }
    $db->query("UPDATE tbl_org_course_access SET is_active=0 WHERE org_code='$orgCode' AND course_id=$courseId");
    orgLog($db, $orgCode, $me, 'course_unsubscribed', 'course', (string)$courseId);
    echo json_encode(['status'=>'success','message'=>'Course unsubscribed']);
    break;

/* ── List course categories ───────────────────────────────────── */
case 'list_categories':
    $rows = $db->query("
        SELECT id, category_title AS title FROM tbl_course_categories
        WHERE status=1 ORDER BY category_title ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','categories'=>$rows]);
    break;

/* ── Org subscribed courses (lightweight, for member create preview) ── */
case 'org_course_list':
    $rows = $db->query("
        SELECT c.id, c.title, c.thumbnail
        FROM tbl_org_course_access ca
        JOIN tbl_courses c ON c.id = ca.course_id
        WHERE ca.org_code = '$orgCode' AND ca.is_active = 1
          AND (ca.expires_at IS NULL OR ca.expires_at >= CURDATE())
        ORDER BY c.title ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','courses'=>$rows]);
    break;

/* ── Send login credentials via SMS ──────────────────────────── */
case 'send_credentials':
    $body    = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $usrCode = $db->real_escape_string($body['usr_code'] ?? '');
    if (!$usrCode) { echo json_encode(['status'=>'error','message'=>'Invalid member']); exit; }

    $chk = $db->query("SELECT id FROM tbl_org_members WHERE org_code='$orgCode' AND usr_code='$usrCode' AND status='active' LIMIT 1");
    if ($chk->num_rows === 0) { echo json_encode(['status'=>'error','message'=>'Member not in your organization']); exit; }

    $u = $db->query("SELECT first_name, phone_number, email_address FROM tbl_all_users WHERE usr_code='$usrCode' LIMIT 1")->fetch_assoc();
    if (!$u)                       { echo json_encode(['status'=>'error','message'=>'Member not found']); exit; }
    if (empty($u['phone_number'])) { echo json_encode(['status'=>'error','message'=>'No phone number on record for this member']); exit; }

    /* generate a readable temp password and reset it in DB */
    $tempPass = strtoupper(bin2hex(random_bytes(4))) . rand(10, 99);
    $tempHash = password_hash($tempPass, PASSWORD_BCRYPT);
    $db->query("UPDATE tbl_all_users SET user_password='$tempHash', force_pw_change=1 WHERE usr_code='$usrCode'");

    $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $loginUrl = $scheme . '://' . $_SERVER['HTTP_HOST'];
    $orgName  = $myOrg['org_name'];

    $msg = "Hello {$u['first_name']}, you have been added to {$orgName}.\n"
         . "Login at: {$loginUrl}\n"
         . "Email: {$u['email_address']}\n"
         . "Password: {$tempPass}\n"
         . "Please change your password after first login.";

    try {
        App::sendSMS($u['phone_number'], $msg);
        orgLog($db, $orgCode, $me, 'credentials_sent', 'user', $usrCode, ['via'=>'sms']);
        echo json_encode(['status'=>'success','message'=>'Credentials sent via SMS to '.$u['phone_number'],'temp_password'=>$tempPass]);
    } catch (Throwable $e) {
        echo json_encode(['status'=>'error','message'=>'SMS failed: '.$e->getMessage()]);
    }
    break;

default:
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
