<?php
ini_set('display_errors', 0);
ob_start();

set_exception_handler(function (Throwable $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
});

include('../config/db.php');
session_start();
header('Content-Type: application/json');

/* Selcom SDK — loaded once at file scope so `use` is valid */
$_selcomLoaded = false;
if (file_exists('../config/selcom/vendor/autoload.php')) {
    require_once '../config/selcom/vendor/autoload.php';
    $_selcomLoaded = true;
}

function _cj(array $d): never { ob_clean(); echo json_encode($d); exit; }

if (!isset($_SESSION['usr_code'])) _cj(['status' => 'error', 'message' => 'Not authenticated']);

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$method = strtoupper(trim($input['method'] ?? ''));   // CARD | MNO
$phone  = preg_replace('/\D/', '', $input['phone'] ?? '');  // digits only
$usr    = $_SESSION['usr_code'];

if (!in_array($method, ['CARD', 'MNO'])) _cj(['status' => 'error', 'message' => 'Invalid payment method']);
if ($method === 'MNO' && strlen($phone) < 9) _cj(['status' => 'error', 'message' => 'Valid phone number required']);

/* ── Normalise phone to 255XXXXXXXXX ────────────────────────── */
if ($method === 'MNO') {
    if (str_starts_with($phone, '0'))  $phone = '255' . substr($phone, 1);
    if (!str_starts_with($phone, '255')) $phone = '255' . $phone;
}

/* ── Fetch cart ──────────────────────────────────────────────── */
$cartRows = $db->query("
    SELECT c.id, c.title, c.price, c.discount, c.instructor_id
    FROM tbl_course_cart cc
    JOIN tbl_courses c ON c.id = cc.course_id
    WHERE cc.user_id = '{$db->escape_string($usr)}'
      AND c.status = 'active' AND c.deleted_at IS NULL
")->fetch_all(MYSQLI_ASSOC);

if (!$cartRows) _cj(['status' => 'error', 'message' => 'Your cart is empty']);

/* ── Calculate total ─────────────────────────────────────────── */
$total = 0;
foreach ($cartRows as $c) {
    $p = (float)$c['price'];
    $d = (float)($c['discount'] ?? 0);
    $total += round($p - ($p * $d / 100), 2);
}
$total = (int)ceil($total);   // Selcom expects integer TZS

/* ── Student info ────────────────────────────────────────────── */
$stu = $db->query("SELECT first_name, last_name, email, phone FROM tbl_students WHERE usr_code='{$db->escape_string($usr)}' LIMIT 1")->fetch_assoc();
$usr_row = $db->query("SELECT email_address, first_name, last_name FROM tbl_all_users WHERE usr_code='{$db->escape_string($usr)}' LIMIT 1")->fetch_assoc();

$firstName  = $stu['first_name'] ?? $usr_row['first_name'] ?? 'Student';
$lastName   = $stu['last_name']  ?? $usr_row['last_name']  ?? '';
$buyerName  = trim("$firstName $lastName") ?: 'Student';
$buyerEmail = $stu['email'] ?? $usr_row['email_address'] ?? 'noreply@dcm.co.tz';
$buyerPhone = $method === 'MNO' ? $phone : preg_replace('/\D/', '', $stu['phone'] ?? '0000000000');
if (str_starts_with($buyerPhone, '0')) $buyerPhone = '255' . substr($buyerPhone, 1);
if (!str_starts_with($buyerPhone, '255')) $buyerPhone = '255' . $buyerPhone;

/* ── Selcom config — loaded from DB ──────────────────────────── */
$selcomCfg = $db->query("SELECT * FROM tbl_payment_settings WHERE gateway='selcom' AND is_active=1 LIMIT 1")->fetch_assoc();
if (!$selcomCfg) _cj(['status' => 'error', 'message' => 'Payment gateway is not configured. Please contact the administrator.']);

$apiKey     = $selcomCfg['api_key'];
$apiSecret  = $selcomCfg['api_secret'];
$vendor     = $selcomCfg['vendor'];
$baseUrl    = rtrim($selcomCfg['base_url'] ?? 'https://apigw.selcommobile.com', '/');
$plainWebhook = $selcomCfg['webhook_url'] ?? '';
if (!$plainWebhook) _cj(['status' => 'error', 'message' => 'Webhook URL is not configured. Please set it in Payment Settings.']);
$webhookUrl = base64_encode($plainWebhook);   // Selcom expects base64-encoded URL

$orderRef = 'DCM-' . date('YmdHis') . '-' . strtoupper(bin2hex(random_bytes(3)));
$courseIds = array_column($cartRows, 'id');

/* ── Save pending checkout intent ────────────────────────────── */
$orderDetails = json_encode(['courses' => $cartRows, 'usr_code' => $usr]);
$db->query("INSERT INTO tbl_payment_order
    (vendor, order_id, buyer_email, buyer_name, buyer_phone, amount, currency,
     webhook, buyer_remarks, merchant_remarks, no_of_items, order_status,
     username, pay_type, order_details, created_at)
    VALUES ('{$vendor}', '{$orderRef}', '{$db->escape_string($buyerEmail)}',
            '{$db->escape_string($buyerName)}', '{$buyerPhone}', {$total}, 'TZS',
            '{$webhookUrl}', 'Course Purchase', 'Course Purchase',
            " . count($cartRows) . ", 'Pending',
            '{$db->escape_string($usr)}', '{$method}',
            '{$db->escape_string($orderDetails)}', NOW())");

/* ─────────────────────────────────────────────────────────────
   SELCOM API HELPER
──────────────────────────────────────────────────────────── */
function selcomPost(string $path, array $payload, string $base, string $key, string $secret): array
{
    $timestamp  = gmdate('D, d M Y H:i:s T');
    $encoded    = base64_encode($secret);
    $dataString = base64_encode(json_encode($payload));
    $sigStr     = "timestamp:{$timestamp}\nmethod:POST\npath:{$path}\ndigest:{$dataString}";
    $sig        = base64_encode(hash_hmac('sha256', $sigStr, $secret, true));

    $ch = curl_init($base . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            "Authorization: SELCOM {$key}",
            "Digest: {$dataString}",
            "Signature: {$sig}",
            "Signed-Fields: timestamp method path digest",
            "Timestamp: {$timestamp}",
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp ?: '{}', true) ?: ['result' => 'error'];
}

/* ─────────────────────────────────────────────────────────────
   CARD PAYMENT
──────────────────────────────────────────────────────────── */
if ($method === 'CARD') {
    $payload = [
        'vendor'            => $vendor,
        'order_id'          => $orderRef,
        'buyer_email'       => $buyerEmail,
        'buyer_name'        => $buyerName,
        'buyer_phone'       => $buyerPhone,
        'amount'            => $total,
        'currency'          => 'TZS',
        'payment_methods'   => 'ALL',
        'webhook'           => $webhookUrl,
        'billing.firstname' => $firstName,
        'billing.lastname'  => $lastName,
        'billing.address_1' => 'Tanzania',
        'billing.city'      => 'Dar es Salaam',
        'billing.state_or_region'   => 'TZ',
        'billing.postcode_or_pobox' => '00255',
        'billing.country'  => 'TZ',
        'billing.phone'    => $buyerPhone,
        'buyer_remarks'    => 'Course Purchase',
        'merchant_remarks' => 'DCM Course Payment',
        'no_of_items'      => count($cartRows),
    ];

    try {
        if (!$_selcomLoaded) throw new RuntimeException('SDK not found');
        $client   = new \Selcom\ApigwClient\Client($baseUrl, $apiKey, $apiSecret);
        $response = $client->postFunc('/v1/checkout/create-order', $payload);
        $data     = is_string($response) ? json_decode($response, true) : $response;
    } catch (Throwable $e) {
        $data = selcomPost('/v1/checkout/create-order', $payload, $baseUrl, $apiKey, $apiSecret);
    }

    if (empty($data['data'][0]['payment_gateway_url'])) {
        _cj(['status' => 'error', 'message' => 'Could not initiate card payment. ' . ($data['message'] ?? 'Selcom error.')]);
    }

    $encodedUrl = $data['data'][0]['payment_gateway_url'];
    // Base64-URL decode
    $b64 = str_replace(['-','_'], ['+','/'], $encodedUrl);
    $b64 .= str_repeat('=', (4 - strlen($b64) % 4) % 4);
    $gatewayUrl = base64_decode($b64);

    // Update order record with URL
    $db->query("UPDATE tbl_payment_order SET payment_gateway_url='{$db->escape_string($gatewayUrl)}' WHERE order_id='{$orderRef}'");

    _cj(['status' => 'success', 'method' => 'CARD', 'redirect_url' => $gatewayUrl, 'order_ref' => $orderRef]);
}

/* ─────────────────────────────────────────────────────────────
   MNO (MOBILE WALLET PUSH)
──────────────────────────────────────────────────────────── */
$minPayload = [
    'vendor'           => $vendor,
    'order_id'         => $orderRef,
    'buyer_email'      => $buyerEmail,
    'buyer_name'       => $buyerName,
    'buyer_phone'      => $buyerPhone,
    'amount'           => $total,
    'currency'         => 'TZS',
    'webhook'          => $webhookUrl,
    'buyer_remarks'    => 'Course Purchase',
    'merchant_remarks' => 'DCM Course Payment',
    'no_of_items'      => count($cartRows),
];

try {
    if (!$_selcomLoaded) throw new RuntimeException('SDK not found');
    $client   = new \Selcom\ApigwClient\Client($baseUrl, $apiKey, $apiSecret);
    $minResp  = $client->postFunc('/v1/checkout/create-order-minimal', $minPayload);
    $minData  = is_string($minResp) ? json_decode($minResp, true) : $minResp;
} catch (Throwable $e) {
    $minData = selcomPost('/v1/checkout/create-order-minimal', $minPayload, $baseUrl, $apiKey, $apiSecret);
}

if (($minData['result'] ?? '') !== '000') {
    _cj(['status' => 'error', 'message' => 'Could not create payment order. ' . ($minData['message'] ?? 'Selcom error.')]);
}

/* ── Push USSD to phone ─────────────────────────────────────── */
$transId     = strtoupper(bin2hex(random_bytes(5)));
$pushPayload = ['transid' => $transId, 'order_id' => $orderRef, 'msisdn' => $phone];

try {
    $pushResp = isset($client) ? $client->postFunc('/v1/checkout/wallet-payment', $pushPayload) : null;
    $pushData = $pushResp ? (is_string($pushResp) ? json_decode($pushResp, true) : $pushResp) : [];
    if (!$pushData) throw new RuntimeException('empty');
} catch (Throwable $e) {
    $pushData = selcomPost('/v1/checkout/wallet-payment', $pushPayload, $baseUrl, $apiKey, $apiSecret);
}

if (($pushData['result'] ?? '') !== '000') {
    _cj(['status' => 'error', 'message' => $pushData['message'] ?? 'Could not send payment request to your phone. Please check the number and try again.']);
}

_cj([
    'status'    => 'pending',
    'method'    => 'MNO',
    'order_ref' => $orderRef,
    'message'   => 'Payment request sent to ' . substr($phone, 0, 6) . 'XXXX. Approve the prompt on your phone to complete enrolment.',
]);
