<?php
include('../config/db.php'); 

// Example: Monthly rent collected
$query = "
SELECT 
    DATE_FORMAT(payment_date, '%b') as month,
    SUM(amount) as total_amount,
    COUNT(id) as total_transactions
FROM tbl_payments
GROUP BY MONTH(payment_date)
ORDER BY MONTH(payment_date)
";

$result = mysqli_query($conn, $query);

$months = [];
$amounts = [];
$transactions = [];

while($row = mysqli_fetch_assoc($result)){
    $months[] = $row['month'];
    $amounts[] = (int)$row['total_amount'];
    $transactions[] = (int)$row['total_transactions'];
}

echo json_encode([
    "labels" => $months,
    "amounts" => $amounts,
    "transactions" => $transactions
]);