<?php
session_start();
include('../config/db.php');
include('../config/dump.php');

header('Content-Type: application/json');

$me   = $_SESSION['usr_code'] ?? '';
$role = (int)($_SESSION['user_role'] ?? 0);
if (!$me) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit; }

/* ── Bootstrap tables on first run ─────────────────────────────────────── */
$db->query("CREATE TABLE IF NOT EXISTS `tbl_chat_conversations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `type` ENUM('direct','group') NOT NULL DEFAULT 'direct',
    `name` VARCHAR(255) DEFAULT NULL,
    `avatar` VARCHAR(500) DEFAULT NULL,
    `created_by` VARCHAR(50) NOT NULL,
    `last_message` TEXT DEFAULT NULL,
    `last_msg_type` VARCHAR(20) DEFAULT 'text',
    `last_message_at` TIMESTAMP NULL DEFAULT NULL,
    `last_message_by` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`), KEY `idx_lm` (`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS `tbl_chat_participants` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conv_id` BIGINT UNSIGNED NOT NULL,
    `usr_code` VARCHAR(50) NOT NULL,
    `role` ENUM('member','admin') DEFAULT 'member',
    `last_read_at` TIMESTAMP NULL DEFAULT NULL,
    `last_read_msg_id` BIGINT UNSIGNED DEFAULT 0,
    `is_muted` TINYINT(1) DEFAULT 0,
    `joined_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `left_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`), UNIQUE KEY `uniq_cp` (`conv_id`,`usr_code`), KEY `idx_usr` (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS `tbl_chat_messages` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `conv_id` BIGINT UNSIGNED NOT NULL,
    `sender_code` VARCHAR(50) NOT NULL,
    `type` ENUM('text','image','file','audio','video','system') DEFAULT 'text',
    `body` TEXT DEFAULT NULL,
    `file_path` VARCHAR(500) DEFAULT NULL,
    `file_name` VARCHAR(255) DEFAULT NULL,
    `file_size` INT UNSIGNED DEFAULT NULL,
    `reply_to` BIGINT UNSIGNED DEFAULT NULL,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`), KEY `idx_conv` (`conv_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$db->query("CREATE TABLE IF NOT EXISTS `tbl_chat_presence` (
    `usr_code` VARCHAR(50) NOT NULL,
    `status` ENUM('online','offline') DEFAULT 'offline',
    `typing_in` BIGINT UNSIGNED DEFAULT NULL,
    `last_seen` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`usr_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

/* ── Helpers ────────────────────────────────────────────────────────────── */
function ce(mysqli $db, $v): string { return $db->real_escape_string((string)$v); }

function avatarInitials(string $name): string {
    $p = explode(' ', trim($name));
    return strtoupper(substr($p[0], 0, 1) . (isset($p[1]) ? substr($p[1], 0, 1) : ''));
}

function avatarColor(string $str): string {
    $c = ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#3b82f6','#f97316','#14b8a6','#0ea5e9','#a855f7'];
    return $c[abs(crc32($str)) % count($c)];
}

function roleLabel(int $role): string {
    return match($role) { 1=>'Student', 3=>'Teacher', 4=>'Org Admin', 5=>'Admin', default=>'User' };
}

function userInfo(mysqli $db, string $usr_code): array {
    $e = ce($db, $usr_code);
    $r = $db->query("SELECT first_name,last_name,user_role FROM tbl_all_users WHERE usr_code='$e' LIMIT 1")->fetch_assoc();
    if (!$r) return ['name'=>'Unknown','initials'=>'?','color'=>'#999','role'=>0,'role_label'=>'User'];
    $name = trim($r['first_name'].' '.$r['last_name']);
    return ['name'=>$name,'initials'=>avatarInitials($name),'color'=>avatarColor($usr_code),'role'=>(int)$r['user_role'],'role_label'=>roleLabel((int)$r['user_role'])];
}

function isParticipant(mysqli $db, int $convId, string $usr): bool {
    $e = ce($db, $usr);
    $r = $db->query("SELECT id FROM tbl_chat_participants WHERE conv_id=$convId AND usr_code='$e' AND left_at IS NULL LIMIT 1");
    return $r && $r->num_rows > 0;
}

function isOnline(?string $lastSeen): bool {
    return $lastSeen && strtotime($lastSeen) > time() - 35;
}

function fmtMsg(array $m, string $me): array {
    return [
        'id'          => (int)$m['id'],
        'conv_id'     => (int)$m['conv_id'],
        'sender_code' => $m['sender_code'],
        'sender_name' => isset($m['first_name']) ? trim($m['first_name'].' '.$m['last_name']) : '',
        'type'        => $m['type'],
        'body'        => $m['body'],
        'file_path'   => $m['file_path'],
        'file_name'   => $m['file_name'],
        'file_size'   => isset($m['file_size']) ? (int)$m['file_size'] : null,
        'reply_to'    => $m['reply_to'] ? (int)$m['reply_to'] : null,
        'is_mine'     => $m['sender_code'] === $me,
        'created_at'  => $m['created_at'],
        'deleted'     => !empty($m['deleted_at']),
    ];
}

/* ── Update my presence (every request = heartbeat) ────────────────────── */
$esc_me = ce($db, $me);
$db->query("INSERT INTO tbl_chat_presence (usr_code,status,last_seen) VALUES ('$esc_me','online',NOW())
    ON DUPLICATE KEY UPDATE status='online', last_seen=NOW(), updated_at=NOW()");

/* ── Route ──────────────────────────────────────────────────────────────── */
$method = $_SERVER['REQUEST_METHOD'];
$body   = $method === 'POST' ? (json_decode(file_get_contents('php://input'), true) ?? []) : [];
$action = $method === 'GET'  ? ($_GET['action'] ?? '') : ($body['action'] ?? '');

/* ══════════════════════════════════════════════════════════════════════════
   GET_CONVERSATIONS
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_conversations') {
    $rows = $db->query("
        SELECT c.*, cp.last_read_msg_id, cp.is_muted,
            (SELECT COUNT(*) FROM tbl_chat_messages m2
             WHERE m2.conv_id=c.id AND m2.id>COALESCE(cp.last_read_msg_id,0)
               AND m2.sender_code!='$esc_me' AND m2.deleted_at IS NULL) AS unread_count
        FROM tbl_chat_conversations c
        JOIN tbl_chat_participants cp ON cp.conv_id=c.id AND cp.usr_code='$esc_me' AND cp.left_at IS NULL
        ORDER BY COALESCE(c.last_message_at,c.created_at) DESC
        LIMIT 150
    ")->fetch_all(MYSQLI_ASSOC);

    $out = [];
    foreach ($rows as $r) {
        $cid = (int)$r['id'];
        $item = [
            'id'             => $cid,
            'type'           => $r['type'],
            'name'           => $r['name'],
            'last_message'   => $r['last_message'],
            'last_msg_type'  => $r['last_msg_type'] ?? 'text',
            'last_message_at'=> $r['last_message_at'],
            'last_message_by'=> $r['last_message_by'],
            'unread_count'   => (int)$r['unread_count'],
            'is_muted'       => (bool)$r['is_muted'],
        ];
        if ($r['type'] === 'direct') {
            $other = $db->query("SELECT u.usr_code,u.first_name,u.last_name,u.user_role,pr.status AS ps,pr.last_seen
                FROM tbl_chat_participants cp2
                JOIN tbl_all_users u ON u.usr_code=cp2.usr_code
                LEFT JOIN tbl_chat_presence pr ON pr.usr_code=cp2.usr_code
                WHERE cp2.conv_id=$cid AND cp2.usr_code!='$esc_me' AND cp2.left_at IS NULL LIMIT 1")->fetch_assoc();
            if ($other) {
                $name = trim($other['first_name'].' '.$other['last_name']);
                $item['with_user']  = $other['usr_code'];
                $item['name']       = $name;
                $item['initials']   = avatarInitials($name);
                $item['color']      = avatarColor($other['usr_code']);
                $item['user_role']  = (int)$other['user_role'];
                $item['role_label'] = roleLabel((int)$other['user_role']);
                $item['online']     = $other['ps']==='online' && isOnline($other['last_seen']);
                $item['last_seen']  = $other['last_seen'];
            }
        } else {
            $cnt = (int)$db->query("SELECT COUNT(*) FROM tbl_chat_participants WHERE conv_id=$cid AND left_at IS NULL")->fetch_row()[0];
            $item['member_count'] = $cnt;
            $item['initials']     = strtoupper(substr($r['name']??'GR',0,2));
            $item['color']        = avatarColor($r['name']??'group'.$cid);
        }
        $out[] = $item;
    }
    echo json_encode(['status'=>'success','data'=>$out]);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   GET_OR_CREATE_DIRECT
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_or_create_direct') {
    $with = trim($_GET['with'] ?? '');
    if (!$with || $with === $me) { echo json_encode(['status'=>'error','message'=>'Invalid user']); exit; }
    $ew = ce($db, $with);

    $ex = $db->query("
        SELECT c.id FROM tbl_chat_conversations c
        JOIN tbl_chat_participants p1 ON p1.conv_id=c.id AND p1.usr_code='$esc_me' AND p1.left_at IS NULL
        JOIN tbl_chat_participants p2 ON p2.conv_id=c.id AND p2.usr_code='$ew' AND p2.left_at IS NULL
        WHERE c.type='direct' LIMIT 1
    ")->fetch_assoc();

    if ($ex) { echo json_encode(['status'=>'success','conv_id'=>(int)$ex['id']]); exit; }

    $db->begin_transaction();
    try {
        $db->query("INSERT INTO tbl_chat_conversations (type,created_by) VALUES ('direct','$esc_me')");
        $cid = $db->insert_id;
        $db->query("INSERT INTO tbl_chat_participants (conv_id,usr_code,role) VALUES ($cid,'$esc_me','admin')");
        $db->query("INSERT INTO tbl_chat_participants (conv_id,usr_code,role) VALUES ($cid,'$ew','admin')");
        $db->commit();
        echo json_encode(['status'=>'success','conv_id'=>$cid]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status'=>'error','message'=>'Could not create conversation']);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   CREATE_GROUP
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'create_group') {
    $name    = trim($body['name'] ?? '');
    $members = array_filter(array_unique($body['members'] ?? []));
    if (!$name) { echo json_encode(['status'=>'error','message'=>'Group name required']); exit; }

    $en = ce($db, $name);
    $db->begin_transaction();
    try {
        $db->query("INSERT INTO tbl_chat_conversations (type,name,created_by) VALUES ('group','$en','$esc_me')");
        $cid = $db->insert_id;
        $db->query("INSERT INTO tbl_chat_participants (conv_id,usr_code,role) VALUES ($cid,'$esc_me','admin')");
        foreach ($members as $m) {
            $em = ce($db, $m);
            if ($em && $em !== $esc_me) {
                $db->query("INSERT IGNORE INTO tbl_chat_participants (conv_id,usr_code) VALUES ($cid,'$em')");
            }
        }
        $myInfo = userInfo($db, $me);
        $sysMsg = ce($db, $myInfo['name'].' created the group');
        $db->query("INSERT INTO tbl_chat_messages (conv_id,sender_code,type,body) VALUES ($cid,'$esc_me','system','$sysMsg')");
        $db->commit();
        echo json_encode(['status'=>'success','conv_id'=>$cid]);
    } catch (Exception $e) {
        $db->rollback();
        echo json_encode(['status'=>'error','message'=>'Failed to create group']);
    }
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   GET_MESSAGES  (initial load + older messages)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_messages') {
    $cid      = intval($_GET['conv_id'] ?? 0);
    $beforeId = intval($_GET['before_id'] ?? 0);
    if (!$cid) { echo json_encode(['status'=>'error','message'=>'Invalid conv']); exit; }
    if (!isParticipant($db, $cid, $me)) { echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }

    $bc  = $beforeId ? "AND m.id < $beforeId" : '';
    $msgs = $db->query("
        SELECT m.*, u.first_name, u.last_name
        FROM tbl_chat_messages m
        LEFT JOIN tbl_all_users u ON u.usr_code=m.sender_code
        WHERE m.conv_id=$cid $bc
        ORDER BY m.id DESC LIMIT 40
    ")->fetch_all(MYSQLI_ASSOC);
    $msgs = array_reverse($msgs);

    $readBy = $db->query("SELECT usr_code,last_read_msg_id FROM tbl_chat_participants
        WHERE conv_id=$cid AND usr_code!='$esc_me' AND left_at IS NULL")->fetch_all(MYSQLI_ASSOC);

    $out = array_map(fn($m) => fmtMsg($m, $me), $msgs);
    echo json_encode(['status'=>'success','data'=>$out,'read_by'=>$readBy,'has_more'=>count($msgs)===40]);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   POLL  (new messages + typing + read receipts)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'poll') {
    $cid    = intval($_GET['conv_id'] ?? 0);
    $lastId = intval($_GET['last_id'] ?? 0);
    if (!$cid) { echo json_encode(['status'=>'error','message'=>'Invalid conv']); exit; }
    if (!isParticipant($db, $cid, $me)) { echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }

    $msgs = $db->query("
        SELECT m.*, u.first_name, u.last_name
        FROM tbl_chat_messages m
        LEFT JOIN tbl_all_users u ON u.usr_code=m.sender_code
        WHERE m.conv_id=$cid AND m.id > $lastId
        ORDER BY m.id ASC LIMIT 50
    ")->fetch_all(MYSQLI_ASSOC);

    $typing = $db->query("
        SELECT cp.usr_code, u.first_name
        FROM tbl_chat_presence cp
        JOIN tbl_all_users u ON u.usr_code=cp.usr_code
        WHERE cp.typing_in=$cid AND cp.usr_code!='$esc_me'
          AND cp.updated_at >= DATE_SUB(NOW(), INTERVAL 6 SECOND)
        LIMIT 5
    ")->fetch_all(MYSQLI_ASSOC);

    $readBy = $db->query("SELECT usr_code,last_read_msg_id FROM tbl_chat_participants
        WHERE conv_id=$cid AND usr_code!='$esc_me' AND left_at IS NULL")->fetch_all(MYSQLI_ASSOC);

    // Total unread across all convs for badge
    $totalUnread = (int)$db->query("
        SELECT COALESCE(SUM(
            (SELECT COUNT(*) FROM tbl_chat_messages m2
             WHERE m2.conv_id=c.id AND m2.id>COALESCE(cp.last_read_msg_id,0)
               AND m2.sender_code!='$esc_me' AND m2.deleted_at IS NULL)
        ),0)
        FROM tbl_chat_conversations c
        JOIN tbl_chat_participants cp ON cp.conv_id=c.id AND cp.usr_code='$esc_me' AND cp.left_at IS NULL
    ")->fetch_row()[0];

    echo json_encode([
        'status'       => 'success',
        'messages'     => array_map(fn($m) => fmtMsg($m, $me), $msgs),
        'typing'       => array_column($typing, 'first_name'),
        'read_by'      => $readBy,
        'total_unread' => $totalUnread,
    ]);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   SEND_MESSAGE
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'send_message') {
    $cid      = intval($body['conv_id'] ?? 0);
    $msgBody  = trim($body['body'] ?? '');
    $type     = in_array($body['type']??'', ['text','image','file','audio','video']) ? ($body['type']??'text') : 'text';
    $replyTo  = intval($body['reply_to'] ?? 0);
    $filePath = isset($body['file_path']) ? trim($body['file_path']) : null;
    $fileName = isset($body['file_name']) ? trim($body['file_name']) : null;
    $fileSize = intval($body['file_size'] ?? 0);

    if (!$cid) { echo json_encode(['status'=>'error','message'=>'Invalid conv']); exit; }
    if (!isParticipant($db, $cid, $me)) { echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }
    if ($type === 'text' && !$msgBody) { echo json_encode(['status'=>'error','message'=>'Empty message']); exit; }

    $eb = ce($db, $msgBody);
    $et = ce($db, $type);
    $ef = $filePath ? "'".ce($db,$filePath)."'" : 'NULL';
    $en = $fileName ? "'".ce($db,$fileName)."'" : 'NULL';
    $es = $fileSize ?: 'NULL';
    $er = $replyTo  ?: 'NULL';

    $db->query("INSERT INTO tbl_chat_messages (conv_id,sender_code,type,body,file_path,file_name,file_size,reply_to)
        VALUES ($cid,'$esc_me','$et','$eb',$ef,$en,$es,$er)");
    $msgId = $db->insert_id;

    $preview = $type==='text' ? (mb_strlen($msgBody)>90 ? mb_substr($msgBody,0,90).'…' : $msgBody) : ucfirst($type);
    $ep = ce($db, $preview);
    $db->query("UPDATE tbl_chat_conversations SET last_message='$ep',last_msg_type='$et',last_message_at=NOW(),last_message_by='$esc_me' WHERE id=$cid");
    $db->query("UPDATE tbl_chat_participants SET last_read_msg_id=$msgId,last_read_at=NOW() WHERE conv_id=$cid AND usr_code='$esc_me'");
    $db->query("UPDATE tbl_chat_presence SET typing_in=NULL WHERE usr_code='$esc_me'");

    $row = $db->query("SELECT m.*,u.first_name,u.last_name FROM tbl_chat_messages m
        LEFT JOIN tbl_all_users u ON u.usr_code=m.sender_code WHERE m.id=$msgId")->fetch_assoc();

    echo json_encode(['status'=>'success','message'=>fmtMsg($row, $me)]);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   MARK_READ
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'mark_read') {
    $cid = intval($body['conv_id'] ?? 0);
    if (!$cid) { echo json_encode(['status'=>'error']); exit; }
    $maxId = (int)($db->query("SELECT MAX(id) FROM tbl_chat_messages WHERE conv_id=$cid")->fetch_row()[0] ?? 0);
    if ($maxId) {
        $db->query("UPDATE tbl_chat_participants SET last_read_msg_id=$maxId,last_read_at=NOW() WHERE conv_id=$cid AND usr_code='$esc_me'");
    }
    echo json_encode(['status'=>'success']);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   HEARTBEAT  (presence + typing)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'heartbeat') {
    $typingIn = isset($body['typing_in']) && $body['typing_in'] ? intval($body['typing_in']) : 'NULL';
    $db->query("INSERT INTO tbl_chat_presence (usr_code,status,typing_in,last_seen)
        VALUES ('$esc_me','online',$typingIn,NOW())
        ON DUPLICATE KEY UPDATE status='online',typing_in=$typingIn,last_seen=NOW(),updated_at=NOW()");
    echo json_encode(['status'=>'success']);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   GET_USERS  (search for starting a new chat or adding to group)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_users') {
    $q    = trim($_GET['q'] ?? '');
    $excl = intval($_GET['exclude_conv'] ?? 0); // exclude existing members of this conv

    $eq = '%'.ce($db,$q).'%';
    $excludeSql = '';
    if ($excl > 0) {
        $mems = $db->query("SELECT usr_code FROM tbl_chat_participants WHERE conv_id=$excl AND left_at IS NULL")->fetch_all(MYSQLI_ASSOC);
        $codes = array_map(fn($r)=>"'".ce($db,$r['usr_code'])."'", $mems);
        if ($codes) $excludeSql = 'AND u.usr_code NOT IN ('.implode(',',$codes).')';
    }

    $users = $db->query("
        SELECT u.usr_code,u.first_name,u.last_name,u.user_role,
               COALESCE(pr.status,'offline') AS ps, pr.last_seen
        FROM tbl_all_users u
        LEFT JOIN tbl_chat_presence pr ON pr.usr_code=u.usr_code
        WHERE u.usr_code!='$esc_me' AND u.user_status='Active'
          AND (u.first_name LIKE '$eq' OR u.last_name LIKE '$eq'
               OR CONCAT(u.first_name,' ',u.last_name) LIKE '$eq'
               OR u.email_address LIKE '$eq')
          $excludeSql
        ORDER BY u.first_name ASC LIMIT 25
    ")->fetch_all(MYSQLI_ASSOC);

    $out = [];
    foreach ($users as $u) {
        $name = trim($u['first_name'].' '.$u['last_name']);
        $out[] = [
            'usr_code'   => $u['usr_code'],
            'name'       => $name,
            'initials'   => avatarInitials($name),
            'color'      => avatarColor($u['usr_code']),
            'user_role'  => (int)$u['user_role'],
            'role_label' => roleLabel((int)$u['user_role']),
            'online'     => $u['ps']==='online' && isOnline($u['last_seen']),
        ];
    }
    echo json_encode(['status'=>'success','data'=>$out]);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   GET_GROUP_INFO
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'get_group_info') {
    $cid = intval($_GET['conv_id'] ?? 0);
    if (!$cid || !isParticipant($db,$cid,$me)) { echo json_encode(['status'=>'error','message'=>'Forbidden']); exit; }

    $conv = $db->query("SELECT * FROM tbl_chat_conversations WHERE id=$cid")->fetch_assoc();
    $members = $db->query("
        SELECT cp.usr_code,cp.role AS conv_role,u.first_name,u.last_name,u.user_role,
               COALESCE(pr.status,'offline') AS ps,pr.last_seen
        FROM tbl_chat_participants cp
        JOIN tbl_all_users u ON u.usr_code=cp.usr_code
        LEFT JOIN tbl_chat_presence pr ON pr.usr_code=cp.usr_code
        WHERE cp.conv_id=$cid AND cp.left_at IS NULL
        ORDER BY FIELD(cp.role,'admin','member'), u.first_name ASC
    ")->fetch_all(MYSQLI_ASSOC);

    // My role in this group
    $myConvRole = $db->query("SELECT role FROM tbl_chat_participants WHERE conv_id=$cid AND usr_code='$esc_me' LIMIT 1")->fetch_row()[0] ?? 'member';

    $out = [];
    foreach ($members as $m) {
        $name = trim($m['first_name'].' '.$m['last_name']);
        $out[] = [
            'usr_code'  => $m['usr_code'],
            'name'      => $name,
            'initials'  => avatarInitials($name),
            'color'     => avatarColor($m['usr_code']),
            'conv_role' => $m['conv_role'],
            'user_role' => (int)$m['user_role'],
            'role_label'=> roleLabel((int)$m['user_role']),
            'is_me'     => $m['usr_code']===$me,
            'online'    => $m['ps']==='online' && isOnline($m['last_seen']),
        ];
    }
    echo json_encode(['status'=>'success','conv'=>$conv,'members'=>$out,'my_role'=>$myConvRole]);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   ADD_PARTICIPANT
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'add_participant') {
    $cid     = intval($body['conv_id'] ?? 0);
    $addUser = trim($body['usr_code'] ?? '');
    if (!$cid || !$addUser) { echo json_encode(['status'=>'error','message'=>'Invalid params']); exit; }

    $myRole = $db->query("SELECT role FROM tbl_chat_participants WHERE conv_id=$cid AND usr_code='$esc_me' LIMIT 1")->fetch_row()[0] ?? '';
    if ($myRole !== 'admin') { echo json_encode(['status'=>'error','message'=>'Only group admins can add members']); exit; }

    $ea = ce($db,$addUser);
    // Re-add if they left, otherwise insert
    $existing = $db->query("SELECT id,left_at FROM tbl_chat_participants WHERE conv_id=$cid AND usr_code='$ea' LIMIT 1")->fetch_assoc();
    if ($existing) {
        $db->query("UPDATE tbl_chat_participants SET left_at=NULL,joined_at=NOW() WHERE conv_id=$cid AND usr_code='$ea'");
    } else {
        $db->query("INSERT IGNORE INTO tbl_chat_participants (conv_id,usr_code) VALUES ($cid,'$ea')");
    }
    $info  = userInfo($db, $addUser);
    $smsg  = ce($db, $info['name'].' was added to the group');
    $db->query("INSERT INTO tbl_chat_messages (conv_id,sender_code,type,body) VALUES ($cid,'$esc_me','system','$smsg')");
    echo json_encode(['status'=>'success']);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   REMOVE_PARTICIPANT / LEAVE_GROUP
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'remove_participant' || $action === 'leave_group') {
    $cid     = intval($body['conv_id'] ?? 0);
    $remUser = $action==='leave_group' ? $me : trim($body['usr_code'] ?? '');
    if (!$cid || !$remUser) { echo json_encode(['status'=>'error','message'=>'Invalid params']); exit; }

    $myRole = $db->query("SELECT role FROM tbl_chat_participants WHERE conv_id=$cid AND usr_code='$esc_me' LIMIT 1")->fetch_row()[0] ?? '';
    if ($action==='remove_participant' && $myRole!=='admin') {
        echo json_encode(['status'=>'error','message'=>'Only admins can remove members']); exit;
    }

    $er   = ce($db,$remUser);
    $db->query("UPDATE tbl_chat_participants SET left_at=NOW() WHERE conv_id=$cid AND usr_code='$er'");
    $info = userInfo($db,$remUser);
    $verb = $remUser===$me ? 'left' : 'was removed from';
    $smsg = ce($db,$info['name'].' '.$verb.' the group');
    $db->query("INSERT INTO tbl_chat_messages (conv_id,sender_code,type,body) VALUES ($cid,'$esc_me','system','$smsg')");
    echo json_encode(['status'=>'success']);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   UPDATE_GROUP (rename)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'update_group') {
    $cid  = intval($body['conv_id'] ?? 0);
    $name = trim($body['name'] ?? '');
    if (!$cid || !$name) { echo json_encode(['status'=>'error','message'=>'Invalid params']); exit; }

    $myRole = $db->query("SELECT role FROM tbl_chat_participants WHERE conv_id=$cid AND usr_code='$esc_me' LIMIT 1")->fetch_row()[0] ?? '';
    if ($myRole !== 'admin') { echo json_encode(['status'=>'error','message'=>'Only admins can rename the group']); exit; }

    $en = ce($db,$name);
    $db->query("UPDATE tbl_chat_conversations SET name='$en' WHERE id=$cid AND type='group'");
    echo json_encode(['status'=>'success']);
    exit;
}

/* ══════════════════════════════════════════════════════════════════════════
   DELETE_MESSAGE  (soft delete — only own messages)
══════════════════════════════════════════════════════════════════════════ */
if ($action === 'delete_message') {
    $msgId = intval($body['msg_id'] ?? 0);
    if (!$msgId) { echo json_encode(['status'=>'error','message'=>'Invalid']); exit; }
    $db->query("UPDATE tbl_chat_messages SET deleted_at=NOW() WHERE id=$msgId AND sender_code='$esc_me'");
    echo json_encode(['status'=>'success','affected'=>$db->affected_rows]);
    exit;
}

echo json_encode(['status'=>'error','message'=>'Unknown action']);
