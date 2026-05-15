<?php

include('../config/db.php'); 
$id = $_GET['id'];
$res = $db->query("SELECT * FROM tbl_students WHERE usr_code='$id'");
echo json_encode($res->fetch_assoc());