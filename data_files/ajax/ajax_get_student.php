<?php
ini_set('display_errors', 0);
ob_start();
include('../config/db.php');
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usr_code'])) { echo json_encode(null); exit; }

$usr  = $_SESSION['usr_code'];
$stmt = $db->prepare("SELECT * FROM tbl_students WHERE usr_code = ?");
$stmt->bind_param('s', $usr);
$stmt->execute();
$row  = $stmt->get_result()->fetch_assoc();

ob_clean();
echo json_encode($row);
