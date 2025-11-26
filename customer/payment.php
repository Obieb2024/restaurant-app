<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../login.php"); exit; }
include '../includes/db.php';

$oid = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$oid, $_SESSION['user']['id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) { header("Location: orders.php"); exit; }

// Cek apakah Admin sudah upload QRIS
$qrisFile = '../assets/img/qris.jpg';
$qrisUrl = file_exists($qrisFile) ? $qrisFile . '?v=' . time() : 'https://via.placeholder.com/300x300?text=QRIS+BELUM+DIUPLOAD';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pembayaran QRIS - Warung Bu Yeti</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h2>BU YETI</h2><span>CUSTOMER ZONE</span></div>
        <nav class="nav-menu">
            <a href="menu.php" class="nav-link"><i class="fas fa-utensils"></i> MENU KAMI</a>
            <a href="cart.php" class="nav-link active"><i class="fas fa-shopping-cart"></i> KERANJANG</a>
            <a href="orders.php" class="nav-link"><i class="fas fa-receipt"></i> PESANAN</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user-astronaut"></i> PROFIL SAYA</a>
            <a href="../logout.php" class="nav-link" style="margin-top:auto; color:red; border-color:red;"><i class="fas fa-sign-out-alt"></i> KELUAR</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-bar">
            <div class="page-title"><h1>PEMBAYARAN QRIS</h1></div>
        </div>

        <div class="brutal-box" style="max-width: 500px; margin: 0 auto; text-align: center;">
            <h3 style="font-family:'Archivo Black'; margin-bottom: 10px;">SCAN UNTUK BAYAR</h3>
            <p style="font-weight:bold; color:#555;">Total Tagihan:</p>
            <div style="font-size: 32px; font-weight: 900; color: var(--red-primary); margin-bottom: 20px;">
                Rp <?= number_format($order['total'], 0, ',', '.') ?>
            </div>

            <div style="border: 4px solid black; padding: 10px; display: inline-block; background: white; margin-bottom: 20px;">
                <img src="<?= $qrisUrl ?>" alt="QRIS CODE" style="width: 250px; height: auto; display: block;">
            </div>

            <div style="margin-bottom: 30px;">
                <p style="font-size: 14px; margin-bottom: 10px;">Order ID: <strong>#<?= $oid ?></strong></p>
                
                <a href="<?= $qrisUrl ?>" download="QRIS-WARUNG-BU-YETI.jpg" class="btn-pay" 
                   style="background: var(--yellow); color: black; border-color: black; text-decoration: none; display: inline-block; width: auto; padding: 10px 20px; font-size: 14px;">
                   <i class="fas fa-download"></i> DOWNLOAD QRIS
                </a>
            </div>

            <div style="border-top: 2px dashed black; padding-top: 20px;">
                <p style="margin-bottom: 15px;">Sudah bayar? Admin akan segera verifikasi.</p>
                <a href="orders.php" class="btn-pay" style="text-decoration: none;">
                    <i class="fas fa-check-circle"></i> CEK STATUS PESANAN
                </a>
            </div>
        </div>
    </main>
</body>
</html>