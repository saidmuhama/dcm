<?php
session_start();
include('../config/db.php');
include('../config/dump.php');
include('../config/url_crypt_config.php');
header('Content-Type: application/json');

if (($_SESSION['user_role'] ?? 0) != 5) {
    echo json_encode(['status'=>'error','message'=>'Access denied']); exit;
}

$me          = $_SESSION['usr_code'] ?? '';
$_raw_body   = file_get_contents('php://input');
$_json_body  = json_decode($_raw_body, true) ?? [];
$action      = $_POST['action'] ?? $_GET['action'] ?? ($_json_body['action'] ?? '');

// ── Helpers ──────────────────────────────────────────────────────
function genOrgCode(): string {
    return 'ORG' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
}

function genUsrCode(): string {
    return 'USR' . time() . rand(100, 999);
}

function orgLog(mysqli $db, string $oc, string $actor, string $action, string $tt = '', string $tid = '', array $d = []): void {
    $det = $d ? json_encode($d) : null;
    $ip  = $_SERVER['REMOTE_ADDR'] ?? null;
    $s   = $db->prepare("INSERT INTO tbl_org_activity (org_code,actor_usr_code,action,target_type,target_id,details,ip_address) VALUES (?,?,?,?,?,?,?)");
    $s->bind_param('sssssss', $oc, $actor, $action, $tt, $tid, $det, $ip);
    $s->execute();
}

function orgTypeBadge(string $t): string {
    $m = ['school'=>'School','college'=>'College','company'=>'Company','institution'=>'Institution','training_center'=>'Training Center'];
    return $m[$t] ?? ucfirst($t);
}

// ── Switch ───────────────────────────────────────────────────────
switch ($action) {

/* ── Stats ────────────────────────────────────────────────────── */
case 'stats':
    $oidParam = $_GET['oid'] ?? '';
    if ($oidParam) {
        // Per-org stats (for admin_org_detail page)
        $oid = decryptURLId($oidParam, ctx: 'org');
        if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }
        $orgC = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
        if (!$orgC) { echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
        $oc = $db->real_escape_string($orgC['org_code']);
        $total_members  = $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE org_code='$oc'")->fetch_row()[0];
        $active_members = $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE org_code='$oc' AND status='active'")->fetch_row()[0];
        $total_courses  = $db->query("SELECT COUNT(*) FROM tbl_org_course_access WHERE org_code='$oc' AND is_active=1")->fetch_row()[0];
        $total_depts    = $db->query("SELECT COUNT(*) FROM tbl_org_departments WHERE org_code='$oc' AND status='active'")->fetch_row()[0];
        echo json_encode(['status'=>'success','total_members'=>$total_members,'active_members'=>$active_members,'total_courses'=>$total_courses,'total_depts'=>$total_depts]);
    } else {
        // Global stats for admin_organizations list page
        $total     = $db->query("SELECT COUNT(*) FROM tbl_organizations WHERE deleted_at IS NULL")->fetch_row()[0];
        $active    = $db->query("SELECT COUNT(*) FROM tbl_organizations WHERE status='active' AND deleted_at IS NULL")->fetch_row()[0];
        $suspended = $db->query("SELECT COUNT(*) FROM tbl_organizations WHERE status='suspended' AND deleted_at IS NULL")->fetch_row()[0];
        $expiring  = $db->query("SELECT COUNT(*) FROM tbl_organizations WHERE license_expires_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(),INTERVAL 30 DAY) AND deleted_at IS NULL")->fetch_row()[0];
        $members   = $db->query("SELECT COUNT(*) FROM tbl_org_members WHERE status='active'")->fetch_row()[0];
        echo json_encode(['status'=>'success','data'=>compact('total','active','suspended','expiring','members')]);
    }
    break;

/* ── List ─────────────────────────────────────────────────────── */
case 'list':
    $search  = '%' . $db->real_escape_string($_POST['search'] ?? '') . '%';
    $type    = $db->real_escape_string($_POST['type']   ?? '');
    $stat    = $db->real_escape_string($_POST['status'] ?? '');
    $plan    = (int)($_POST['plan'] ?? 0);
    $page    = max(1, (int)($_POST['page'] ?? 1));
    $per     = 20;
    $offset  = ($page - 1) * $per;

    $where = "WHERE o.deleted_at IS NULL AND (o.org_name LIKE '$search' OR o.email LIKE '$search' OR o.org_code LIKE '$search')";
    if ($type) $where .= " AND o.org_type='$type'";
    if ($stat) $where .= " AND o.status='$stat'";
    if ($plan) $where .= " AND o.plan_id=$plan";

    $total = $db->query("SELECT COUNT(*) FROM tbl_organizations o $where")->fetch_row()[0];
    $rows  = $db->query("
        SELECT o.id, o.org_code, o.org_name, o.org_type, o.logo, o.email, o.phone,
               o.address, o.country, o.domain, o.plan_id, o.notes,
               o.status, o.license_expires_at, o.max_users, o.admin_usr_code,
               o.created_at,
               p.plan_name,
               CONCAT(u.first_name,' ',u.last_name) AS admin_name,
               (SELECT COUNT(*) FROM tbl_org_members m WHERE m.org_code=o.org_code AND m.status='active') AS member_count,
               (SELECT COUNT(*) FROM tbl_org_course_access ca WHERE ca.org_code=o.org_code AND ca.is_active=1) AS course_count
        FROM tbl_organizations o
        LEFT JOIN tbl_org_plans p ON p.id=o.plan_id
        LEFT JOIN tbl_all_users u ON u.usr_code=o.admin_usr_code
        $where
        ORDER BY o.created_at DESC
        LIMIT $per OFFSET $offset
    ")->fetch_all(MYSQLI_ASSOC);

    foreach ($rows as &$r) {
        $r['oid_token'] = encryptURLId((int)$r['id'], ctx: 'org');
    }
    unset($r);
    echo json_encode(['status'=>'success','data'=>$rows,'total'=>$total,'page'=>$page,'per'=>$per]);
    break;

/* ── Plans list ───────────────────────────────────────────────── */
case 'plans':
    $plans = $db->query("SELECT * FROM tbl_org_plans WHERE is_active=1 ORDER BY max_users ASC")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','data'=>$plans]);
    break;

/* ── Create ───────────────────────────────────────────────────── */
case 'create':
    $body    = $_json_body ?: $_POST;
    $name    = trim($body['org_name']  ?? '');
    $type    = $body['org_type']       ?? 'school';
    $email   = trim($body['email']     ?? '');
    $phone   = trim($body['phone']     ?? '');
    $address = trim($body['address']   ?? '');
    $country = trim($body['country']   ?? '');
    $planId  = (int)($body['plan_id']  ?? 1);
    $license = $body['license_expires_at'] ?? null;
    $notes   = trim($body['notes']     ?? '');
    // Admin account details
    $adFname = trim($body['admin_first_name'] ?? '');
    $adLname = trim($body['admin_last_name']  ?? '');
    $adEmail = trim($body['admin_email']      ?? '');
    $adPhone = trim($body['admin_phone']      ?? '');
    $adPass  = $body['admin_password']        ?? '';

    if (!$name) { echo json_encode(['status'=>'error','message'=>'Organization name is required']); exit; }
    if (!$adFname || !$adEmail || !$adPass) { echo json_encode(['status'=>'error','message'=>'Admin first name, email, and password are required']); exit; }

    // Check admin email not already used
    $chk = $db->prepare("SELECT id FROM tbl_all_users WHERE email_address=? LIMIT 1");
    $chk->bind_param('s', $adEmail);
    $chk->execute();
    if ($chk->get_result()->num_rows > 0) { echo json_encode(['status'=>'error','message'=>'Admin email is already registered']); exit; }

    // Get plan limits
    $plan = $db->query("SELECT * FROM tbl_org_plans WHERE id=$planId LIMIT 1")->fetch_assoc();
    $maxUsers   = $plan ? $plan['max_users']      : 50;
    $maxStorage = $plan ? $plan['max_storage_gb']  : 10;

    $db->begin_transaction();
    try {
        // Generate unique org code
        do { $orgCode = genOrgCode(); } while ($db->query("SELECT id FROM tbl_organizations WHERE org_code='$orgCode'")->num_rows > 0);

        // Create admin user
        $usrCode  = genUsrCode();
        $pwHash   = password_hash($adPass, PASSWORD_BCRYPT);
        $userRole = 4;
        $us = $db->prepare("INSERT INTO tbl_all_users (usr_code,first_name,last_name,email_address,phone_number,user_role,user_password,user_status,signup_success) VALUES (?,?,?,?,?,?,?,'Active','Completed')");
        $us->bind_param('sssssss', $usrCode, $adFname, $adLname, $adEmail, $adPhone, $userRole, $pwHash);
        $us->execute();

        // Create organization
        $licVal = $license ?: null;
        $os = $db->prepare("INSERT INTO tbl_organizations (org_code,org_name,org_type,email,phone,address,country,plan_id,status,license_expires_at,max_users,storage_limit_gb,admin_usr_code,created_by,notes) VALUES (?,?,?,?,?,?,?,?,'active',?,?,?,?,?,?)");
        $os->bind_param('sssssssissiiiss', $orgCode,$name,$type,$email,$phone,$address,$country,$planId,$licVal,$maxUsers,$maxStorage,$usrCode,$me,$notes);
        $os->execute();
        $orgId = $db->insert_id;

        // Add admin as org member
        $ms2 = $db->prepare("INSERT INTO tbl_org_members (org_code,usr_code,org_role,status,invited_by) VALUES (?,?,?,?,?)");
        $role = 'admin'; $stat = 'active';
        $ms2->bind_param('sssss', $orgCode, $usrCode, $role, $stat, $me);
        $ms2->execute();

        $db->commit();
        orgLog($db, $orgCode, $me, 'org_created', 'organization', $orgCode, ['name'=>$name]);
        echo json_encode(['status'=>'success','message'=>'Organization created successfully','org_code'=>$orgCode,'org_id'=>$orgId]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status'=>'error','message'=>'Failed to create: '.$e->getMessage()]);
    }
    break;

/* ── Get single ───────────────────────────────────────────────── */
case 'get':
    $oid = decryptURLId($_GET['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }

    $org = $db->query("
        SELECT o.*, p.plan_name, p.plan_code, p.features AS plan_features,
               CONCAT(u.first_name,' ',u.last_name) AS admin_name, u.email_address AS admin_email,
               (SELECT COUNT(*) FROM tbl_org_members m WHERE m.org_code=o.org_code AND m.status='active') AS member_count,
               (SELECT COUNT(*) FROM tbl_org_departments d WHERE d.org_code=o.org_code AND d.status='active') AS dept_count,
               (SELECT COUNT(*) FROM tbl_org_course_access ca WHERE ca.org_code=o.org_code AND ca.is_active=1) AS course_count
        FROM tbl_organizations o
        LEFT JOIN tbl_org_plans p ON p.id=o.plan_id
        LEFT JOIN tbl_all_users u ON u.usr_code=o.admin_usr_code
        WHERE o.id=$oid AND o.deleted_at IS NULL LIMIT 1
    ")->fetch_assoc();

    if (!$org) { echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
    echo json_encode(['status'=>'success','data'=>$org]);
    break;

/* ── Update ───────────────────────────────────────────────────── */
case 'update':
    $body    = $_json_body ?: $_POST;
    $oid     = decryptURLId($body['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }

    $name    = $db->real_escape_string(trim($body['org_name']  ?? ''));
    $type    = $db->real_escape_string($body['org_type']       ?? 'school');
    $email   = $db->real_escape_string(trim($body['email']     ?? ''));
    $phone   = $db->real_escape_string(trim($body['phone']     ?? ''));
    $address = $db->real_escape_string(trim($body['address']   ?? ''));
    $country = $db->real_escape_string(trim($body['country']   ?? ''));
    $domain  = $db->real_escape_string(trim($body['domain']    ?? ''));
    $planId  = (int)($body['plan_id']  ?? 0);
    $license = $db->real_escape_string($body['license_expires_at'] ?? '');
    $maxU    = (int)($body['max_users']        ?? 50);
    $maxS    = (int)($body['storage_limit_gb'] ?? 10);
    $status  = $db->real_escape_string($body['status'] ?? 'active');
    $notes   = $db->real_escape_string(trim($body['notes'] ?? ''));
    $licSql  = $license ? "'$license'" : 'NULL';
    $planSql = $planId ? $planId : 'NULL';

    $db->query("UPDATE tbl_organizations SET
        org_name='$name', org_type='$type', email='$email', phone='$phone',
        address='$address', country='$country', domain='$domain',
        plan_id=$planSql, status='$status', license_expires_at=$licSql,
        max_users=$maxU, storage_limit_gb=$maxS, notes='$notes'
        WHERE id=$oid AND deleted_at IS NULL");

    $org = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid LIMIT 1")->fetch_assoc();
    if ($org) orgLog($db, $org['org_code'], $me, 'org_updated', 'organization', $org['org_code']);
    echo json_encode(['status'=>'success','message'=>'Organization updated']);
    break;

/* ── Toggle status ────────────────────────────────────────────── */
case 'toggle_status':
    $body   = $_json_body ?: $_POST;
    $oid    = decryptURLId($body['oid'] ?? '', ctx: 'org');
    $newSt  = $db->real_escape_string($body['status'] ?? 'suspended');
    if (!$oid || !in_array($newSt, ['active','suspended','expired'])) {
        echo json_encode(['status'=>'error','message'=>'Invalid request']); exit;
    }
    $db->query("UPDATE tbl_organizations SET status='$newSt' WHERE id=$oid AND deleted_at IS NULL");
    $org = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid LIMIT 1")->fetch_assoc();
    if ($org) orgLog($db, $org['org_code'], $me, 'status_changed', 'organization', $org['org_code'], ['new_status'=>$newSt]);
    echo json_encode(['status'=>'success','message'=>'Status updated to '.$newSt]);
    break;

/* ── Delete (soft) ────────────────────────────────────────────── */
case 'delete':
    $body = $_json_body ?: $_POST;
    $oid  = decryptURLId($body['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }
    $db->query("UPDATE tbl_organizations SET deleted_at=NOW() WHERE id=$oid AND deleted_at IS NULL");
    echo json_encode(['status'=>'success','message'=>'Organization removed']);
    break;

/* ── Grant course access ──────────────────────────────────────── */
case 'grant_course': {
    $body    = $_json_body ?: $_POST;
    $oid     = decryptURLId($body['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid org']); exit; }
    $orgRow  = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
    if (!$orgRow) { echo json_encode(['status'=>'error','message'=>'Org not found']); exit; }
    $oc      = $db->real_escape_string($orgRow['org_code']);
    $ids     = array_map('intval', (array)($body['course_ids'] ?? ($body['course_ids[]'] ?? [])));
    // Handle FormData which may come as course_ids[]
    if (empty($ids) && isset($_POST['course_ids'])) $ids = array_map('intval', (array)$_POST['course_ids']);
    if (empty($ids)) { echo json_encode(['status'=>'error','message'=>'No courses selected']); exit; }
    $granted = 0;
    foreach ($ids as $cid) {
        if (!$cid) continue;
        $db->query("INSERT INTO tbl_org_course_access (org_code,course_id,granted_by) VALUES ('$oc',$cid,'$me')
                    ON DUPLICATE KEY UPDATE is_active=1, granted_by='$me'");
        $granted++;
    }
    orgLog($db, $oc, $me, 'course_granted', 'course', implode(',',$ids));
    echo json_encode(['status'=>'success','message'=>"$granted course(s) granted"]);
    break;
}

/* ── Revoke course access ─────────────────────────────────────── */
case 'revoke_course': {
    $body     = $_json_body ?: $_POST;
    $oid      = decryptURLId($body['oid'] ?? '', ctx: 'org');
    $courseId = (int)($body['course_id'] ?? 0);
    if (!$oid || !$courseId) { echo json_encode(['status'=>'error','message'=>'Missing data']); exit; }
    $orgRow   = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
    if (!$orgRow) { echo json_encode(['status'=>'error','message'=>'Org not found']); exit; }
    $oc = $db->real_escape_string($orgRow['org_code']);
    $db->query("UPDATE tbl_org_course_access SET is_active=0 WHERE org_code='$oc' AND course_id=$courseId");
    orgLog($db, $oc, $me, 'course_revoked', 'course', (string)$courseId);
    echo json_encode(['status'=>'success','message'=>'Course access revoked']);
    break;
}

/* ── List org courses (for super admin detail) ────────────────── */
case 'list_courses': {
    $oid = decryptURLId($_GET['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }
    $orgRow = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
    if (!$orgRow) { echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
    $oc   = $db->real_escape_string($orgRow['org_code']);
    $rows = $db->query("
        SELECT ca.course_id, ca.granted_at, ca.expires_at, ca.is_active,
               c.title, c.status AS course_status,
               CONCAT(gb.first_name,' ',gb.last_name) AS granted_by_name
        FROM tbl_org_course_access ca
        JOIN tbl_courses c ON c.id=ca.course_id
        LEFT JOIN tbl_all_users gb ON gb.usr_code=ca.granted_by
        WHERE ca.org_code='$oc'
        ORDER BY ca.granted_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','courses'=>$rows]);
    break;
}

/* ── List org members (super admin view) ──────────────────────── */
case 'list_members': {
    $oid    = decryptURLId($_GET['oid'] ?? '', ctx: 'org');
    $search = '%' . $db->real_escape_string($_GET['search'] ?? '') . '%';
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }
    $orgRow = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
    if (!$orgRow) { echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
    $oc   = $db->real_escape_string($orgRow['org_code']);
    $rows = $db->query("
        SELECT m.usr_code, m.org_role, m.dept_id, m.employee_id, m.status, m.joined_at,
               CONCAT(u.first_name,' ',u.last_name) AS full_name, u.email_address AS email,
               d.dept_name
        FROM tbl_org_members m
        JOIN tbl_all_users u ON u.usr_code=m.usr_code
        LEFT JOIN tbl_org_departments d ON d.id=m.dept_id
        WHERE m.org_code='$oc'
          AND (u.first_name LIKE '$search' OR u.last_name LIKE '$search' OR u.email_address LIKE '$search')
        ORDER BY m.org_role ASC, m.joined_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','members'=>$rows]);
    break;
}

/* ── Remove member (super admin) ──────────────────────────────── */
case 'remove_member': {
    $body    = $_json_body ?: $_POST;
    $oid     = decryptURLId($body['oid'] ?? '', ctx: 'org');
    $usrCode = $db->real_escape_string($body['usr_code'] ?? '');
    if (!$oid || !$usrCode) { echo json_encode(['status'=>'error','message'=>'Missing data']); exit; }
    $orgRow  = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
    if (!$orgRow) { echo json_encode(['status'=>'error','message'=>'Org not found']); exit; }
    $oc = $db->real_escape_string($orgRow['org_code']);
    $db->query("DELETE FROM tbl_org_members WHERE org_code='$oc' AND usr_code='$usrCode'");
    orgLog($db, $oc, $me, 'member_removed', 'member', $usrCode);
    echo json_encode(['status'=>'success','message'=>'Member removed']);
    break;
}

/* ── List departments (super admin view) ──────────────────────── */
case 'list_depts': {
    $oid = decryptURLId($_GET['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }
    $orgRow = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
    if (!$orgRow) { echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
    $oc   = $db->real_escape_string($orgRow['org_code']);
    $rows = $db->query("
        SELECT d.*,
               (SELECT COUNT(*) FROM tbl_org_members m WHERE m.dept_id=d.id) AS member_count
        FROM tbl_org_departments d
        WHERE d.org_code='$oc'
        ORDER BY d.dept_name ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','departments'=>$rows]);
    break;
}

/* ── Activity log ─────────────────────────────────────────────── */
case 'activity_log': {
    $oid = decryptURLId($_GET['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid ID']); exit; }
    $orgRow = $db->query("SELECT org_code FROM tbl_organizations WHERE id=$oid AND deleted_at IS NULL LIMIT 1")->fetch_assoc();
    if (!$orgRow) { echo json_encode(['status'=>'error','message'=>'Not found']); exit; }
    $oc   = $db->real_escape_string($orgRow['org_code']);
    $logs = $db->query("
        SELECT a.*, CONCAT(u.first_name,' ',u.last_name) AS actor_name
        FROM tbl_org_activity a
        LEFT JOIN tbl_all_users u ON u.usr_code=a.actor_usr_code
        WHERE a.org_code='$oc'
        ORDER BY a.created_at DESC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','logs'=>$logs]);
    break;
}

/* ── All courses available for grant ──────────────────────────── */
case 'all_courses':
    $rows = $db->query("
        SELECT c.id, c.title, c.status, CONCAT(u.first_name,' ',u.last_name) AS instructor_name
        FROM tbl_courses c
        LEFT JOIN tbl_all_users u ON u.usr_code=c.instructor_id
        WHERE c.deleted_at IS NULL AND c.status='active'
        ORDER BY c.title ASC
    ")->fetch_all(MYSQLI_ASSOC);
    echo json_encode(['status'=>'success','courses'=>$rows]);
    break;

/* ── Reset org-admin password (super admin) ───────────────────── */
case 'reset_admin_password': {
    $body   = $_json_body ?: $_POST;
    $oid    = decryptURLId($body['oid'] ?? '', ctx: 'org');
    if (!$oid) { echo json_encode(['status'=>'error','message'=>'Invalid org ID']); exit; }

    $org = $db->query("
        SELECT o.org_name, o.org_code, o.admin_usr_code,
               u.first_name, u.last_name, u.phone_number, u.email_address
        FROM tbl_organizations o
        LEFT JOIN tbl_all_users u ON u.usr_code = o.admin_usr_code
        WHERE o.id = $oid AND o.deleted_at IS NULL LIMIT 1
    ")->fetch_assoc();
    if (!$org)                   { echo json_encode(['status'=>'error','message'=>'Organization not found']); exit; }
    if (!$org['admin_usr_code']) { echo json_encode(['status'=>'error','message'=>'No admin account is linked to this organization']); exit; }
    if (!$org['first_name'])     { echo json_encode(['status'=>'error','message'=>'Admin account not found in the system']); exit; }

    $usrCode   = $org['admin_usr_code'];
    $phone     = $org['phone_number'] ?? '';
    $orgName   = $org['org_name'];
    $orgCode   = $org['org_code'];
    $adminName = trim($org['first_name'] . ' ' . $org['last_name']);

    // Alphanumeric-only temp password (no special characters)
    $tempPass = strtoupper(bin2hex(random_bytes(4))) . rand(10, 99);
    $tempHash = password_hash($tempPass, PASSWORD_BCRYPT);

    $esc = $db->real_escape_string($usrCode);
    $db->query("UPDATE tbl_all_users SET user_password='$tempHash', force_pw_change=1 WHERE usr_code='$esc'");

    orgLog($db, $orgCode, $me, 'admin_password_reset', 'user', $usrCode);

    $smsSent = false;
    if (!empty($phone)) {
        $smsMsg  = "Hi $adminName, your $orgName admin password has been reset.\nNew Password: $tempPass\nPlease log in and change it immediately.";
        $smsResult = App::sendSMS($phone, $smsMsg);
        $smsSent   = !empty($smsResult['status']);
    }

    echo json_encode([
        'status'        => 'success',
        'message'       => 'Password reset successfully',
        'temp_password' => $tempPass,
        'sms_sent'      => $smsSent,
        'admin_name'    => $adminName,
        'phone'         => $phone,
        'email'         => $org['email_address'],
    ]);
    break;
}

default:
    echo json_encode(['status'=>'error','message'=>'Unknown action']);
}
