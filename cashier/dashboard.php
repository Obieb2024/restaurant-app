<?php
session_start();
// Cek Login: Hanya Cashier
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'cashier') { header('Location: ../login.php'); exit; }
include '../includes/db.php';
include '../includes/Cashier.php';

$cashier = new Cashier($pdo);
$stats = $cashier->getDailyStats();

// Ambil 5 Pesanan Terbaru (Khusus Dashboard)
// Kita query manual disini biar gak ngerubah Class Cashier yg udah ada
$recentOrders = $pdo->query("
    SELECT o.*, u.fullname 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE DATE(o.order_date) = CURDATE()
    ORDER BY o.order_date DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - Cashier Mode</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CUSTOM STYLE KHUSUS KASIR */
        .admin-sidebar { background: #f0fdf4; border-right: 4px solid black; }
        .admin-logo { background: #00b894; color: black; box-shadow: 4px 4px 0 black; }
        .sidebar-link.active { background: black; color: #00b894; border: 3px solid black; }
        
        /* Kartu Status Spesial */
        .stat-card.pending { background: #ffeaa7; animation: pulse 2s infinite; }
        .stat-card.process { background: #74b9ff; }
        .stat-card.done { background: #55efc4; }
        .stat-card.total { background: #a29bfe; }

        @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.02); } 100% { transform: scale(1); } }

        .quick-btn {
            display: block; width: 100%; padding: 20px;
            background: white; border: 4px solid black;
            text-align: left; font-family: 'Archivo Black'; font-size: 18px;
            text-decoration: none; color: black; transition: 0.2s;
            box-shadow: 6px 6px 0 black; margin-bottom: 20px;
        }
        .quick-btn:hover { transform: translate(-4px, -4px); box-shadow: 10px 10px 0 black; background: #00b894; }
        .quick-btn i { font-size: 24px; margin-right: 10px; width: 30px; text-align: center; }
        .quick-btn span { display: block; font-size: 12px; font-family: 'Chakra Petch'; font-weight: bold; color: #555; margin-top: 5px; }
    </style>
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">CASHIER</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link active"><i class="fas fa-home"></i> BERANDA</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-cash-register"></i> TRANSAKSI</a></li>
            <li><a href="../admin/logout.php" class="sidebar-link" style="background:#ff7675; color:white; margin-top:50px;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1 style="font-family:'Archivo Black'; font-size:32px;">HALO, <?= explode(' ',$_SESSION['user']['fullname'])[0] ?>!</h1>
                <p>Selamat bertugas! Semangat cari cuan.</p>
            </div>
            <div class="badge badge-yellow" style="font-size:16px; padding:10px 20px;">
                <i class="far fa-calendar-check"></i> <?= date('l, d M Y') ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card total">
                <i class="fas fa-clipboard-list stat-icon"></i>
                <div class="stat-label">TOTAL ORDER HARI INI</div>
                <div class="stat-value"><?= $stats['total_today'] ?></div>
            </div>
            <div class="stat-card pending">
                <i class="fas fa-bell stat-icon"></i>
                <div class="stat-label">MENUNGGU KONFIRMASI</div>
                <div class="stat-value"><?= $stats['pending'] ?></div>
            </div>
            <div class="stat-card process">
                <i class="fas fa-fire stat-icon"></i>
                <div class="stat-label">SEDANG DIMASAK</div>
                <div class="stat-value"><?= $stats['cooking'] ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-top: 20px;">
            
            <div class="table-container">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:3px solid black; padding-bottom:10px;">
                    <h3 style="font-family:'Archivo Black'; margin:0;">PESANAN TERBARU HARI INI</h3>
                    <a href="orders.php" style="font-weight:bold; color:blue;">Lihat Semua &rarr;</a>
                </div>

                <table width="100%">
                    <thead>
                        <tr style="background:black; color:white;">
                            <th style="padding:10px;">ID</th>
                            <th>PELANGGAN</th>
                            <th>TOTAL</th>
                            <th>STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($recentOrders)): ?>
                            <tr><td colspan="4" style="text-align:center; padding:20px; font-weight:bold;">Belum ada pesanan hari ini. Santai dulu ngopi. ☕</td></tr>
                        <?php else: ?>
                            <?php foreach($recentOrders as $r): ?>
                            <tr style="border-bottom:2px solid #eee;">
                                <td style="padding:15px; font-weight:bold;">#<?= $r['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($r['fullname']) ?><br>
                                    <small style="color:#777;"><?= date('H:i', strtotime($r['order_date'])) ?></small>
                                </td>
                                <td style="font-weight:900;">Rp <?= number_format($r['total'],0,',','.') ?></td>
                                <td>
                                    <?php 
                                        $bg = 'gray'; $txt='white';
                                        if($r['order_status']=='Menunggu') $bg='#ffeaa7'; $txt='black';
                                        if($r['order_status']=='Diproses') $bg='#74b9ff'; $txt='black';
                                        if($r['order_status']=='Selesai') $bg='#55efc4'; $txt='black';
                                        if($r['order_status']=='Dibatalkan') $bg='#ff7675'; $txt='white';
                                    ?>
                                    <span style="background:<?= $bg ?>; color:<?= $txt ?>; padding:3px 8px; border:2px solid black; font-weight:bold; font-size:11px; border-radius:4px;">
                                        <?= strtoupper($r['order_status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div>
                <a href="orders.php" class="quick-btn">
                    <i class="fas fa-cash-register"></i> KELOLA PESANAN
                    <span>Update status & pembayaran</span>
                </a>
                
                <a href="dashboard.php" class="quick-btn" style="background:#ffeaa7;">
                    <i class="fas fa-sync-alt"></i> REFRESH DATA
                    <span>Cek pesanan masuk terbaru</span>
                </a>

                <div style="background:white; border:4px solid black; padding:20px; box-shadow:6px 6px 0 black; text-align:center;">
                    <i class="fas fa-clock" style="font-size:40px; margin-bottom:10px;"></i>
                    <div style="font-weight:900; font-size:24px;" id="jam">00:00:00</div>
                    <small>Waktu Server</small>
                </div>
            </div>

        </div>
    </main>

    <script>
        // Jam Digital Sederhana
        setInterval(() => {
            const now = new Date();
            document.getElementById('jam').innerText = now.toLocaleTimeString('id-ID');
        }, 1000);
    </script>
</body>
</html>