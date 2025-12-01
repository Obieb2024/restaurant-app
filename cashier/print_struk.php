<?php
session_start();
// Cek Login: Kasir, Admin, atau Owner boleh cetak
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['cashier', 'admin', 'super_admin'])) {
    header("Location: ../login.php"); exit;
}
include '../includes/db.php';

$id = $_GET['id'] ?? 0;

// Ambil Data Pesanan & Nama Pelanggan
$stmt = $pdo->prepare("
    SELECT o.*, u.fullname as customer_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ?
");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) { die("Pesanan tidak ditemukan."); }

// Ambil Detail Menu
$stmtItems = $pdo->prepare("
    SELECT oi.*, p.name 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    WHERE oi.order_id = ?
");
$stmtItems->execute([$id]);
$items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk #<?= $id ?> - Warung Bu Yeti</title>
    <style>
        /* RESET BROWSER DEFAULT */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        /* SETUP HALAMAN PRINT */
        @page { margin: 0; size: auto; }
        body { 
            font-family: 'Courier New', Courier, monospace; 
            width: 300px; /* Standar lebar kertas thermal 80mm */
            margin: 0 auto; 
            padding: 15px 10px;
            color: #000; 
            background: #fff;
        }

        /* UTILS */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        /* GARIS PUTUS-PUTUS TEBAL */
        .dashed-line {
            border-top: 2px dashed #000;
            margin: 10px 0;
            width: 100%;
        }

        /* HEADER */
        .header { margin-bottom: 10px; }
        .shop-name { font-size: 20px; font-weight: 900; margin-bottom: 5px; letter-spacing: 1px; }
        .shop-addr { font-size: 12px; line-height: 1.4; }

        /* META INFO (Kiri - Kanan Rapi) */
        .meta-row {
            display: flex; justify-content: space-between;
            font-size: 12px; margin-bottom: 3px;
        }
        
        /* ITEM LIST */
        .item-box { margin: 5px 0; }
        .item-name { font-size: 12px; margin-bottom: 2px; font-weight: bold; }
        .item-calc {
            display: flex; justify-content: space-between;
            font-size: 12px;
        }

        /* TOTAL AREA */
        .total-row {
            display: flex; justify-content: space-between;
            font-size: 12px; font-weight: bold; margin-bottom: 5px;
        }
        .grand-total { font-size: 16px; font-weight: 900; }

        /* FOOTER */
        .footer { font-size: 11px; line-height: 1.5; margin-top: 15px; }

        /* Sembunyikan elemen saat print (jika ada) */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header text-center">
        <div class="shop-name uppercase">WARUNG BU YETI</div>
        <div class="shop-addr">Jl. Raya Kebon Jeruk No. 12</div>
        <div class="shop-addr">Telp: 0812-3456-7890</div>
    </div>

    <div class="dashed-line"></div>

    <div class="meta-info">
        <div class="meta-row">
            <span>No. Order:</span>
            <span class="bold">#<?= $order['id'] ?></span>
        </div>
        <div class="meta-row">
            <span>Tanggal:</span>
            <span><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></span>
        </div>
        <div class="meta-row">
            <span>Kasir:</span>
            <span><?= htmlspecialchars($_SESSION['user']['fullname']) ?></span>
        </div>
        <div class="meta-row">
            <span>Pelanggan:</span>
            <span><?= htmlspecialchars($order['customer_name']) ?></span>
        </div>
    </div>

    <div class="dashed-line"></div>

    <?php foreach($items as $item): ?>
    <div class="item-box">
        <div class="item-name"><?= $item['name'] ?></div>
        <div class="item-calc">
            <span><?= $item['quantity'] ?>x &nbsp; @ <?= number_format($item['price'], 0, ',', '.') ?></span>
            <span><?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></span>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="dashed-line"></div>

    <div class="total-area">
        <div class="total-row">
            <span class="uppercase">TOTAL TAGIHAN</span>
            <span class="grand-total">Rp <?= number_format($order['total'], 0, ',', '.') ?></span>
        </div>
        <div class="total-row">
            <span>METODE BAYAR</span>
            <span class="uppercase"><?= $order['payment_method'] ?></span>
        </div>
        <div class="total-row">
            <span>STATUS</span>
            <span class="uppercase"><?= $order['payment_status'] ?></span>
        </div>
    </div>

    <div class="dashed-line"></div>

    <div class="footer text-center uppercase">
        TERIMA KASIH SUDAH MAKAN DISINI!<br>
        JANGAN LUPA DATANG LAGI YA.<br>
        === LAYANAN PELANGGAN ===<br>
        wa.me/6281234567890
    </div>

</body>
</html>