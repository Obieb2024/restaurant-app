<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../login.php"); exit; }
include '../includes/db.php';

$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$_SESSION['user']['id']]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pesanan Saya - Warung Bu Yeti</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS KHUSUS 4 TAHAP PELANGGAN */
        .stepper-box{margin-top:20px;padding-top:20px;border-top:3px dashed #000}
        .stepper-wrapper{display:flex;justify-content:space-between;position:relative;margin-top:10px}
        .stepper-line-bg{position:absolute;top:15px;left:0;width:100%;height:4px;background:#e0e0e0;border:1px solid #000;z-index:1}
        .stepper-line-progress{position:absolute;top:15px;left:0;height:4px;background:#ffff00;border:1px solid #000;border-right:none;z-index:2;transition:width .5s ease}
        .step-item{position:relative;z-index:3;text-align:center;width:25%;display:flex;flex-direction:column;align-items:center}
        .step-circle{width:35px;height:35px;background:#fff;border:3px solid #000;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;color:#000;transition:.3s;margin-bottom:8px}
        .step-label{font-size:10px;font-weight:900;text-transform:uppercase;background:#fff;padding:2px 6px;border:1px solid #000;box-shadow:2px 2px 0 #000}
        
        /* STATE WARNA */
        .step-item.active .step-circle{background:#ffff00;transform:scale(1.1);box-shadow:3px 3px 0 #000} /* Kuning */
        .step-item.finish .step-circle{background:#55efc4;transform:scale(1.1);box-shadow:3px 3px 0 #000} /* Hijau */
        .step-item.cook .step-circle{background:#74b9ff;transform:scale(1.1);box-shadow:3px 3px 0 #000} /* Biru */
        .step-item.confirm .step-circle{background:#fab1a0;transform:scale(1.1);box-shadow:3px 3px 0 #000} /* Orange */
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h2>BU YETI</h2><span>CUSTOMER ZONE</span></div>
        <nav class="nav-menu">
            <a href="menu.php" class="nav-link"><i class="fas fa-utensils"></i> MENU KAMI</a>
            <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> KERANJANG</a>
            <a href="orders.php" class="nav-link active"><i class="fas fa-receipt"></i> PESANAN</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user-astronaut"></i> PROFIL SAYA</a>
            <a href="../logout.php" class="nav-link" style="margin-top:auto; color:red; border-color:red;"><i class="fas fa-sign-out-alt"></i> KELUAR</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-bar"><div class="page-title"><h1>RIWAYAT PESANAN</h1></div></div>
        <div class="brutal-box">
            <?php if(empty($orders)): ?>
                <div style="text-align:center; padding:40px;"><i class="fas fa-receipt" style="font-size:40px; margin-bottom:10px;"></i><p style="font-weight:bold;">BELUM ADA RIWAYAT PESANAN.</p><a href="menu.php" style="display:inline-block; margin-top:10px; background:black; color:white; padding:10px 20px; text-decoration:none; font-weight:bold;">PESAN SEKARANG</a></div>
            <?php else: foreach($orders as $o): ?>
                <div style="border:4px solid black; padding:25px; margin-bottom:30px; background:#fff; box-shadow:8px 8px 0 #000;">
                    <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:10px;">
                        <div>
                            <div style="font-family:'Archivo Black'; font-size:20px;">ORDER #<?= $o['id'] ?></div>
                            <div style="font-size:14px; color:#555; font-weight:bold; margin-top:5px;"><i class="far fa-calendar-alt"></i> <?= date('d M Y, H:i', strtotime($o['order_date'])) ?> <span style="margin:0 10px;">|</span> <i class="fas fa-truck"></i> <?= htmlspecialchars($o['shipping']) ?></div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:900; font-size:22px;">Rp <?= number_format($o['total'],0,',','.') ?></div>
                            <div style="font-size:12px; font-weight:bold; background:black; color:white; padding:2px 8px; display:inline-block;"><?= htmlspecialchars($o['payment_method']) ?></div>
                        </div>
                    </div>

                    <?php $status=strtolower(trim($o['order_status'])); if($status=='dibatalkan'): ?>
                        <div style="background:#ff7675; color:white; padding:15px; text-align:center; font-weight:900; border:3px solid black; margin-top:15px; box-shadow:4px 4px 0 black;"><i class="fas fa-ban"></i> PESANAN DIBATALKAN</div>
                    <?php else: 
                        // LOGIKA 4 TAHAP CUSTOMER
                        $step=1; 
                        if($status=='konfirmasi')$step=2; 
                        if($status=='diproses')$step=3; 
                        if($status=='selesai')$step=4; 
                        
                        // Panjang Garis Kuning
                        $width='0%'; 
                        if($step==2)$width='33%'; 
                        if($step==3)$width='66%'; 
                        if($step==4)$width='100%'; 
                    ?>
                        <div class="stepper-box">
                            <div style="font-weight:900; margin-bottom:10px; font-size:14px;">STATUS PESANAN:</div>
                            <div class="stepper-wrapper">
                                <div class="stepper-line-bg"></div>
                                <div class="stepper-line-progress" style="width:<?= $width ?>"></div>

                                <div class="step-item <?= $step>=1?'active':'' ?>">
                                    <div class="step-circle"><i class="fas fa-clock"></i></div>
                                    <div class="step-label">MENUNGGU</div>
                                </div>

                                <div class="step-item <?= $step>=2?'confirm':'' ?>">
                                    <div class="step-circle"><i class="fas fa-thumbs-up"></i></div>
                                    <div class="step-label">DITERIMA</div>
                                </div>

                                <div class="step-item <?= $step>=3?'cook':'' ?>">
                                    <div class="step-circle"><i class="fas fa-fire"></i></div>
                                    <div class="step-label">DIMASAK</div>
                                </div>

                                <div class="step-item <?= $step>=4?'finish':'' ?>">
                                    <div class="step-circle"><i class="fas fa-check"></i></div>
                                    <div class="step-label">SELESAI</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; endif; ?>
        </div>
    </main>
</body>
</html>
<?php
