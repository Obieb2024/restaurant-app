<?php
// admin/get_notif.php
include '../includes/db.php';

// Cari pesanan yang statusnya 'Menunggu'
$stmt = $pdo->query("SELECT * FROM orders WHERE order_status = 'Menunggu' ORDER BY order_date DESC");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$count = count($orders);

// Kirim data dalam format JSON biar bisa dibaca Javascript
header('Content-Type: application/json');
echo json_encode([
    'count' => $count,
    'orders' => $orders
]);
?>