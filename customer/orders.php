<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../index.php"); exit; }
include '../includes/db.php';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$_SESSION['user']['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pesanan Saya</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="brand"><div class="brand-icon"><i class="fas fa-utensils"></i></div><div class="brand-text"><h2>RestoApp</h2><span>Pesan Online</span></div></div>
            <nav class="nav-menu">
                <a href="menu.php" class="nav-link"><i class="fas fa-home"></i> Menu</a>
                <a href="cart.php" class="nav-link"><i class="fas fa-shopping-bag"></i> Keranjang</a>
                <a href="orders.php" class="nav-link active"><i class="fas fa-receipt"></i> Pesanan Saya</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            </nav>
            <div class="user-mini-profile">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['fullname']) ?>&background=ff0099&color=fff" class="mini-avatar">
                <div class="mini-info"><h4><?= htmlspecialchars($_SESSION['user']['fullname']) ?></h4><span>Customer</span></div>
                <a href="../logout.php" class="btn-logout-mini"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="header-bar">
                <div class="page-title"><h1>Riwayat Pesanan</h1><p>Pantau status pesananmu</p></div>
            </div>

            <div class="order-list-container">
                <?php if(empty($orders)): ?>
                    <p style="text-align:center; color:#999; padding:40px;">Belum ada riwayat pesanan.</p>
                <?php else: ?>
                    <?php foreach($orders as $o): ?>
                    <div class="order-row">
                        <div class="order-info">
                            <h4>Pesanan #<?= $o['order_id'] ?></h4>
                            <div class="order-meta">
                                <span><i class="far fa-calendar"></i> <?= date('d M Y, H:i', strtotime($o['order_date'])) ?></span>
                                <span><i class="<?= $o['shipping']=='Diantar'?'fas fa-motorcycle':'fas fa-walking' ?>"></i> <?= $o['shipping'] ?></span>
                                <span><i class="<?= $o['payment_method']=='Cash'?'fas fa-money-bill-wave':'fas fa-qrcode' ?>"></i> <?= $o['payment_method'] ?></span>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <span class="badge-status <?= ($o['order_status']=='Selesai'?'done':'process') ?>"><?= $o['order_status'] ?></span>
                            <div style="font-weight:800; color:#10b981; font-size:16px; margin-top:5px;">Rp <?= number_format($o['total'],0,',','.') ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>