<?php
require_once('tcpdf/tcpdf.php');
include('../config/db.php');

session_start();

$order_id = $_GET['order_id'] ?? 0;

if(!$order_id){
    die("Invalid order");
}

/* ✅ FETCH ORDER */
$order = mysqli_fetch_assoc(mysqli_query($db, "
SELECT * FROM tbl_orders WHERE id='$order_id'
"));

/* ✅ FETCH ITEMS + COURSE */
$items = mysqli_query($db, "
SELECT oi.*, c.title 
FROM tbl_order_items oi
LEFT JOIN tbl_courses c ON c.id = oi.course_id
WHERE oi.order_id='$order_id'
");

/* ✅ CREATE PDF */
$pdf = new TCPDF();
$pdf->AddPage();

$html = '
<h2>Invoice</h2>
<p><b>Invoice ID:</b> '.$order['invoice_id'].'</p>
<p><b>Date:</b> '.$order['created_at'].'</p>
<hr>

<table border="1" cellpadding="6">
<tr>
    <th><b>Course</b></th>
    <th><b>Price</b></th>
</tr>';

$total = 0;

while($row = mysqli_fetch_assoc($items)){
    $html .= '
    <tr>
        <td>'.$row['title'].'</td>
        <td>TZS '.number_format($row['price']).'</td>
    </tr>';
    $total += $row['price'];
}

$html .= '
<tr>
    <td><b>Total</b></td>
    <td><b>TZS '.number_format($total).'</b></td>
</tr>
</table>

<br><br>
<p>Thank you for your purchase 🙏</p>
';

$pdf->writeHTML($html);
$pdf->Output("invoice_".$order['invoice_id'].".pdf", "I");