<?php
session_start();
include('../config/db.php');
header('Content-Type: application/json');

$user_code = $_SESSION['usr_code'] ?? '';
if (!$user_code) { echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$body   = [];
if ($method === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = json_decode($raw, true) ?? $_POST;
}
$action = $method === 'GET' ? ($_GET['action'] ?? '') : ($body['action'] ?? '');

/* ── List recent notifications ────────────────────────────── */
if ($action === 'list') {
    $limit = min(50, intval($_GET['limit'] ?? 20));
    $stmt  = $db->prepare("
        SELECT id, type, title, body, link, icon, color, is_read, created_at
        FROM tbl_notifications
        WHERE user_code = ?
        ORDER BY id DESC
        LIMIT ?
    ");
    $stmt->bind_param('si', $user_code, $limit);
    $stmt->execute();
    $rows   = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $unread = array_sum(array_map(fn($r) => (int)$r['is_read'] === 0 ? 1 : 0, $rows));
    echo json_encode(['status' => 'success', 'data' => $rows, 'unread' => $unread]);
    exit;
}

/* ── Unread count only (for fast badge refresh) ────────────── */
if ($action === 'count') {
    $stmt = $db->prepare("SELECT COUNT(*) FROM tbl_notifications WHERE user_code=? AND is_read=0");
    $stmt->bind_param('s', $user_code);
    $stmt->execute();
    $count = (int)$stmt->get_result()->fetch_row()[0];
    echo json_encode(['status' => 'success', 'count' => $count]);
    exit;
}

/* ── Mark as read ──────────────────────────────────────────── */
if ($action === 'mark_read') {
    $id = intval($body['id'] ?? 0);
    if ($id) {
        $stmt = $db->prepare("UPDATE tbl_notifications SET is_read=1 WHERE id=? AND user_code=?");
        $stmt->bind_param('is', $id, $user_code);
    } else {
        $stmt = $db->prepare("UPDATE tbl_notifications SET is_read=1 WHERE user_code=?");
        $stmt->bind_param('s', $user_code);
    }
    $stmt->execute();
    echo json_encode(['status' => 'success', 'affected' => $stmt->affected_rows]);
    exit;
}

/* ── Delete old notifications ──────────────────────────────── */
if ($action === 'clear') {
    $stmt = $db->prepare("DELETE FROM tbl_notifications WHERE user_code=? AND is_read=1");
    $stmt->bind_param('s', $user_code);
    $stmt->execute();
    echo json_encode(['status' => 'success']);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
