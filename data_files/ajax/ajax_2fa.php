<?php
session_start();
include('../config/db.php');
include('../config/totp.php');
header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

/* ── Helpers ──────────────────────────────────────────── */
function requireAuth(): void {
    if (empty($_SESSION['usr_code'])) {
        echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
        exit;
    }
}

function ensurePolicyTable($db): void {
    $db->query("CREATE TABLE IF NOT EXISTS tbl_2fa_role_policy (
        role_id     INT         NOT NULL PRIMARY KEY,
        require_2fa TINYINT(1)  NOT NULL DEFAULT 0
    )");
}

/* ── verify_login — complete the 2FA step during login ── */
if ($action === 'verify_login') {
    $code       = trim($_POST['code'] ?? '');
    $pending_id = $_SESSION['2fa_pending_id'] ?? null;

    if (!$pending_id) {
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
        exit;
    }

    $stmt = $db->prepare("SELECT * FROM tbl_all_users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $pending_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !totp_verify($user['totp_secret'] ?? '', $code)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid code. Try again or check your authenticator clock.']);
        exit;
    }

    unset($_SESSION['2fa_pending_id']);
    $_SESSION['usr_code']  = $user['usr_code'];
    $_SESSION['name']      = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = $user['user_role'];
    echo json_encode(['status' => 'success']);
    exit;
}

/* ── generate_login_secret — QR for forced setup at login ── */
if ($action === 'generate_login_secret') {
    $pending_id  = $_SESSION['2fa_pending_id'] ?? null;
    $force_setup = $_SESSION['2fa_force_setup'] ?? false;

    if (!$pending_id || !$force_setup) {
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
        exit;
    }

    $secret = totp_random_secret(20);
    $_SESSION['2fa_setup_secret'] = $secret;

    $stmt = $db->prepare("SELECT email_address FROM tbl_all_users WHERE id = ?");
    $stmt->bind_param('i', $pending_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $uri = totp_uri($secret, $row['email_address'] ?? 'user');
    echo json_encode(['status' => 'success', 'secret' => $secret, 'uri' => $uri]);
    exit;
}

/* ── setup_and_login — save 2FA + complete session during forced setup ── */
if ($action === 'setup_and_login') {
    $pending_id   = $_SESSION['2fa_pending_id'] ?? null;
    $force_setup  = $_SESSION['2fa_force_setup'] ?? false;
    $setup_secret = $_SESSION['2fa_setup_secret'] ?? '';
    $code         = trim($_POST['code'] ?? '');

    if (!$pending_id || !$force_setup) {
        echo json_encode(['status' => 'error', 'message' => 'Session expired. Please log in again.']);
        exit;
    }
    if (!$setup_secret) {
        echo json_encode(['status' => 'error', 'message' => 'Setup session expired. Please refresh.']);
        exit;
    }
    if (!totp_verify($setup_secret, $code)) {
        echo json_encode(['status' => 'error', 'message' => 'Code did not match. Make sure your device clock is synced.']);
        exit;
    }

    $stmt = $db->prepare("UPDATE tbl_all_users SET totp_secret = ?, totp_enabled = 1 WHERE id = ?");
    $stmt->bind_param('si', $setup_secret, $pending_id);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        exit;
    }

    $stmt2 = $db->prepare("SELECT * FROM tbl_all_users WHERE id = ? LIMIT 1");
    $stmt2->bind_param('i', $pending_id);
    $stmt2->execute();
    $user = $stmt2->get_result()->fetch_assoc();

    unset($_SESSION['2fa_pending_id'], $_SESSION['2fa_force_setup'], $_SESSION['2fa_setup_secret']);
    $_SESSION['usr_code']  = $user['usr_code'];
    $_SESSION['name']      = $user['first_name'] . ' ' . $user['last_name'];
    $_SESSION['user_role'] = $user['user_role'];
    echo json_encode(['status' => 'success']);
    exit;
}

/* ── status ───────────────────────────────────────────── */
if ($action === 'status') {
    requireAuth();
    $stmt = $db->prepare("SELECT totp_enabled FROM tbl_all_users WHERE usr_code = ?");
    $stmt->bind_param('s', $_SESSION['usr_code']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    echo json_encode(['status' => 'success', 'enabled' => (bool)($row['totp_enabled'] ?? false)]);
    exit;
}

/* ── generate_secret — create a new TOTP secret for setup ── */
if ($action === 'generate_secret') {
    requireAuth();
    $secret = totp_random_secret(20);
    $_SESSION['2fa_setup_secret'] = $secret;

    $stmt = $db->prepare("SELECT email_address FROM tbl_all_users WHERE usr_code = ?");
    $stmt->bind_param('s', $_SESSION['usr_code']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $uri = totp_uri($secret, $row['email_address'] ?? $_SESSION['usr_code']);
    echo json_encode(['status' => 'success', 'secret' => $secret, 'uri' => $uri]);
    exit;
}

/* ── verify_setup — confirm code then activate 2FA ───── */
if ($action === 'verify_setup') {
    requireAuth();
    $code   = trim($_POST['code'] ?? '');
    $secret = $_SESSION['2fa_setup_secret'] ?? '';

    if (!$secret) {
        echo json_encode(['status' => 'error', 'message' => 'Setup session expired. Please start again.']);
        exit;
    }
    if (!totp_verify($secret, $code)) {
        echo json_encode(['status' => 'error', 'message' => 'Code did not match. Make sure your device clock is synced.']);
        exit;
    }

    $stmt = $db->prepare("UPDATE tbl_all_users SET totp_secret = ?, totp_enabled = 1 WHERE usr_code = ?");
    $stmt->bind_param('ss', $secret, $_SESSION['usr_code']);
    if ($stmt->execute()) {
        unset($_SESSION['2fa_setup_secret']);
        echo json_encode(['status' => 'success', 'message' => '2FA enabled successfully.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

/* ── disable — turn off 2FA (requires current code) ───── */
if ($action === 'disable') {
    requireAuth();
    $code = trim($_POST['code'] ?? '');

    $stmt = $db->prepare("SELECT totp_secret, totp_enabled FROM tbl_all_users WHERE usr_code = ?");
    $stmt->bind_param('s', $_SESSION['usr_code']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (empty($row['totp_enabled'])) {
        echo json_encode(['status' => 'error', 'message' => '2FA is not currently enabled.']);
        exit;
    }
    if (!totp_verify($row['totp_secret'], $code)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid code. Enter your current authenticator code to confirm.']);
        exit;
    }

    $stmt2 = $db->prepare("UPDATE tbl_all_users SET totp_enabled = 0, totp_secret = NULL WHERE usr_code = ?");
    $stmt2->bind_param('s', $_SESSION['usr_code']);
    if ($stmt2->execute()) {
        echo json_encode(['status' => 'success', 'message' => '2FA has been disabled.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt2->error]);
    }
    exit;
}

/* ── get_role_policy — list roles with 2FA enforcement state ── */
if ($action === 'get_role_policy') {
    requireAuth();
    if ((int)$_SESSION['user_role'] !== 5) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensurePolicyTable($db);

    $result = $db->query("
        SELECT r.id, r.role_title,
               COALESCE(p.require_2fa, 0) AS require_2fa,
               COUNT(u.id) AS user_count
        FROM tbl_user_roles r
        LEFT JOIN tbl_2fa_role_policy p ON r.id = p.role_id
        LEFT JOIN tbl_all_users u ON CAST(u.user_role AS UNSIGNED) = r.id
        GROUP BY r.id, r.role_title, p.require_2fa
        ORDER BY r.id
    ");

    $roles = [];
    while ($row = $result->fetch_assoc()) {
        $roles[] = [
            'id'          => (int)$row['id'],
            'title'       => $row['role_title'],
            'require_2fa' => (bool)$row['require_2fa'],
            'user_count'  => (int)$row['user_count'],
        ];
    }
    echo json_encode(['status' => 'success', 'roles' => $roles]);
    exit;
}

/* ── set_role_policy — toggle enforcement for a role ──── */
if ($action === 'set_role_policy') {
    requireAuth();
    if ((int)$_SESSION['user_role'] !== 5) {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
    ensurePolicyTable($db);

    $role_id     = (int)($_POST['role_id'] ?? 0);
    $require_2fa = ($_POST['require_2fa'] ?? '0') === '1' ? 1 : 0;

    if (!$role_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid role.']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO tbl_2fa_role_policy (role_id, require_2fa)
                          VALUES (?, ?)
                          ON DUPLICATE KEY UPDATE require_2fa = ?");
    $stmt->bind_param('iii', $role_id, $require_2fa, $require_2fa);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
