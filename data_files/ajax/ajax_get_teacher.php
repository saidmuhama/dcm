<?php

include('../config/db.php'); 
$id = $_GET['id'];
$res = $db->query("SELECT * FROM tbl_tutors WHERE usr_code='$id'");
echo json_encode($res->fetch_assoc());