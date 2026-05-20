<?php
ini_set('display_errors', 0);
include('../config/db.php');
session_start();
header('Content-Type: application/json');

function _j(array $d): never { echo json_encode($d); exit; }

if (($_SESSION['user_role'] ?? '') != 5) _j(['status' => 'error', 'message' => 'Access denied']);

/* ── Require fresh 2FA stamp (20 min TTL) for write actions ── */
function requireReauth(): void {
    $ts = $_SESSION['reauth_payment_settings'] ?? 0;
    if (!$ts || (time() - $ts) >= 1200) {
        echo json_encode(['status' => 'reauth_required',
            'message' => 'Authentication required. Please verify with your authenticator app.']);
        exit;
    }
}

$action = $_GET['action'] ?? $_POST['action']
    ?? (json_decode(file_get_contents('php://input'), true)['action'] ?? '');

/* ── GET — masked read, no reauth needed ──────────────────── */
if ($action === 'get') {
    $row = $db->query("SELECT * FROM tbl_payment_settings WHERE gateway='selcom' LIMIT 1")->fetch_assoc();
    if (!$row) _j(['status' => 'ok', 'data' => null]);
    $row['api_secret_masked'] = $row['api_secret']
        ? str_repeat('•', max(0, strlen($row['api_secret']) - 4)) . substr($row['api_secret'], -4) : '';
    unset($row['api_secret']);
    _j(['status' => 'ok', 'data' => $row]);
}

/* ── SAVE — requires fresh 2FA stamp ─────────────────────── */
if ($action === 'save') {
    requireReauth();
    $input = json_decode(file_get_contents('php://input'), true) ?? [];

    $apiKey     = trim($input['api_key']    ?? '');
    $apiSecret  = trim($input['api_secret'] ?? '');
    $vendor     = trim($input['vendor']     ?? '');
    $baseUrl    = rtrim(trim($input['base_url'] ?? 'https://apigw.selcommobile.com'), '/');
    $webhookUrl = trim($input['webhook_url'] ?? '');
    $isActive   = isset($input['is_active']) ? (int)(bool)$input['is_active'] : 1;
    $by         = $_SESSION['usr_code'] ?? 'admin';

    if (!$apiKey)    _j(['status' => 'error', 'message' => 'API Key is required']);
    if (!$vendor)    _j(['status' => 'error', 'message' => 'Vendor / Till number is required']);
    if (!$webhookUrl) _j(['status' => 'error', 'message' => 'Webhook URL is required']);
    if (!filter_var($webhookUrl, FILTER_VALIDATE_URL))
        _j(['status' => 'error', 'message' => 'Webhook URL must be a valid URL']);
    if (str_contains($webhookUrl, 'localhost') || str_contains($webhookUrl, '127.0.0.1'))
        _j(['status' => 'error', 'message' => 'Webhook URL must be a public internet URL, not localhost']);

    $existing = $db->query("SELECT api_secret FROM tbl_payment_settings WHERE gateway='selcom' LIMIT 1")->fetch_assoc();

    if ($existing) {
        $secretToSave = $apiSecret ?: $existing['api_secret'];
        $stmt = $db->prepare("UPDATE tbl_payment_settings
            SET api_key=?, api_secret=?, vendor=?, base_url=?, webhook_url=?,
                is_active=?, updated_at=NOW(), updated_by=?
            WHERE gateway='selcom'");
        $stmt->bind_param('sssssss', $apiKey, $secretToSave, $vendor, $baseUrl, $webhookUrl, $isActive, $by);
    } else {
        if (!$apiSecret) _j(['status' => 'error', 'message' => 'API Secret is required for new configuration']);
        $gateway = 'selcom';
        $stmt = $db->prepare("INSERT INTO tbl_payment_settings
            (gateway, api_key, api_secret, vendor, base_url, webhook_url, is_active, updated_at, updated_by)
            VALUES (?,?,?,?,?,?,?,NOW(),?)");
        $stmt->bind_param('ssssssss', $gateway, $apiKey, $apiSecret, $vendor, $baseUrl, $webhookUrl, $isActive, $by);
    }

    if (!$stmt->execute()) _j(['status' => 'error', 'message' => 'DB error: ' . $stmt->error]);
    _j(['status' => 'ok', 'message' => 'Settings saved successfully']);
}

/* ── TEST CONNECTION — requires fresh 2FA stamp ───────────── */
if ($action === 'test') {
    requireReauth();
    $row = $db->query("SELECT * FROM tbl_payment_settings WHERE gateway='selcom' AND is_active=1 LIMIT 1")->fetch_assoc();
    if (!$row) _j(['status' => 'error', 'message' => 'No active Selcom configuration found']);

    $path      = '/v1/checkout/list-orders';
    $payload   = ['vendor' => $row['vendor']];
    $timestamp = gmdate('D, d M Y H:i:s T');
    $secret    = $row['api_secret'];
    $dataStr   = base64_encode(json_encode($payload));
    $sigStr    = "timestamp:{$timestamp}\nmethod:POST\npath:{$path}\ndigest:{$dataStr}";
    $sig       = base64_encode(hash_hmac('sha256', $sigStr, $secret, true));

    $ch = curl_init($row['base_url'] . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: SELCOM ' . $row['api_key'],
            'Digest: '     . $dataStr,
            'Signature: '  . $sig,
            'Signed-Fields: timestamp method path digest',
            'Timestamp: '  . $timestamp,
        ],
        CURLOPT_TIMEOUT => 10,
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($err) _j(['status' => 'error', 'message' => 'Connection failed: ' . $err]);
    $data = json_decode($resp ?: '{}', true);
    if (($data['result'] ?? '') === '000' || isset($data['data']))
        _j(['status' => 'ok', 'message' => 'Connection successful — credentials are valid']);
    _j(['status' => 'error', 'message' => 'Selcom responded: ' . ($data['message'] ?? $resp)]);
}

_j(['status' => 'error', 'message' => 'Unknown action']);
