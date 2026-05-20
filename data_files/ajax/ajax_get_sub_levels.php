<?php
include('../config/db.php');
session_start();

$main_id = (int)($_POST['main_id'] ?? 0);
if (!$main_id) { echo '<option value="">Invalid request</option>'; exit; }

$stmt = $db->prepare("SELECT id, sub_level_title FROM tbl_sub_academic_levels WHERE main_level = ? ORDER BY id");
$stmt->bind_param('i', $main_id);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (!$rows) { echo '<option value="">No levels found</option>'; exit; }
foreach ($rows as $r) {
    echo '<option value="' . $r['id'] . '">' . htmlspecialchars($r['sub_level_title']) . '</option>';
}
