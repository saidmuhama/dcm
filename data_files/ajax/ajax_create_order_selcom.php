<?php
include('../config/db.php');
include('../config/dump.php');
session_start();

$data = json_decode(file_get_contents("php://input"), true);

$user_id        = $_SESSION['usr_code'] ?? 0;
$course_id      = intval($data['course_id']);
$payment_method = $data['payment_method'];

if(!$user_id || !$course_id){
    echo json_encode(["status"=>"error","message"=>"Invalid request"]);
    exit;
}
if($payment_method == 'CARD')
{
    App::selcomCardPaymentOrder($user_id,$orderId,$currency,$country,$order_amount);
}
else 
{
    App::makePaymentRequest($user_id,$order_id, $mobile_phone);
}