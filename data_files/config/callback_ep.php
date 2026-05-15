<?php
require_once('db.php');

$json = file_get_contents('php://input');

$responseArray = json_decode($json,true);

$result              = $responseArray["result"];
$resultcode          = $responseArray["resultcode"];
$order_id            = $responseArray["order_id"];
$transid             = $responseArray["transid"];
$reference           = $responseArray["reference"];
$channel             = $responseArray["channel"];
$amount              = $responseArray["amount"];
$phone               = $responseArray["phone"];
$payment_status      = $responseArray["payment_status"];

        
$query = mysqli_query($db,"INSERT INTO tbl_order_txn_responce (result,resultcode,order_id,transid,reference,channel,amount,phone,payment_status,created_at) 
                 VALUES ('$result','$resultcode','$order_id','$transid','$reference','$channel','$amount','$phone','$payment_status',NOW())");
if ($query) 
{
    if($payment_status =='COMPLETED')
    {
        
        $sql = mysqli_query($db,"UPDATE `tbl_payment_order` SET `order_status` = '$payment_status' WHERE order_id='$order_id'");
        $sqls = mysqli_query($db,"UPDATE `payment_status` SET `payment_status` = '$payment_status' WHERE order_id='$order_id'");
    
    }
}
?>