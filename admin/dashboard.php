<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

include '../includes/db.php';

// --- QUERY DATA DASHBOARD ---
$totalIncome = $pdo->query("SELECT SUM(total) FROM orders WHERE payment_status='Lunas'")->fetchColumn() ?? 0;
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$avgOrder = $pdo->query("SELECT AVG(total) FROM orders")->fetchColumn() ?? 0;
$totalCustomers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();

// --- QUERY KHUSUS NOTIFIKASI (Pesanan Menunggu) ---
$stmtNotif = $pdo->query("SELECT * FROM orders WHERE order_status = 'Menunggu' ORDER BY order_date DESC LIMIT 5");
$notifItems = $stmtNotif->fetchAll(PDO::FETCH_ASSOC);
$notifCount = count($notifItems); // Hitung jumlah notifikasi
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        
        <nav class="admin-sidebar">
            <div class="logo-area">
                <i class="fas fa-utensils"></i>
                <h2>Warung Bu Yeti</h2>
            </div>
            <ul>
                <li><a href="dashboard.php" class="active"><i class="fas fa-fire"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-hamburger"></i> Produk</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Pesanan</a></li>
                <li><a href="customer.php"><i class="fas fa-users"></i> Pelanggan</a></li>
            </ul>
            <div style="margin-top: auto; text-align:center; color:#b2bec3; font-size:12px;">&copy; 2024 RestoApp</div>
        </nav>

        <main>
            <header class="topbar">
                <div class="page-header">
                    <h1>Halo, <?= explode(' ', $_SESSION['user']['fullname'])[0] ?>! 👋</h1>
                    <p>Berikut ringkasan performa restoran hari ini.</p>
                </div>

                <div class="header-right">
                    
                    <div class="notif-wrapper">
                        <button class="notif-btn" onclick="toggleNotif()">
                            <i class="far fa-bell"></i>
                            <?php if($notifCount > 0): ?>
                                <span class="notif-badge"><?= $notifCount ?></span>
                            <?php endif; ?>
                        </button>

                        <div class="notif-dropdown" id="notifDropdown">
                            <div class="notif-header">
                                <span>Notifikasi</span>
                                <a href="orders.php?status=Menunggu">Lihat Semua</a>
                            </div>
                            <div class="notif-list">
                                <?php if($notifCount > 0): ?>
                                    <?php foreach($notifItems as $n): ?>
                                    <a href="orders.php" class="notif-item">
                                        <div class="notif-icon-circle"><i class="fas fa-shopping-bag"></i></div>
                                        <div class="notif-text">
                                            <strong>Pesanan Baru #<?= $n['order_id'] ?></strong>
                                            <p>Rp <?= number_format($n['total'],0,',','.') ?> - <?= $n['payment_method'] ?></p>
                                            <small><?= date('H:i', strtotime($n['order_date'])) ?> WIB</small>
                                        </div>
                                    </a>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="notif-empty">
                                        <i class="far fa-bell-slash"></i>
                                        <p>Tidak ada pesanan baru</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="profile-card">
                        <div class="profile-info">
                            <span class="profile-name"><?= htmlspecialchars($_SESSION['user']['fullname']) ?></span>
                            <span class="profile-role">ADMIN MANAGER</span>
                        </div>
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?= urlencode($_SESSION['user']['fullname']) ?>&backgroundColor=c0aede" alt="Profile" class="profile-img">
                        <a href="logout.php" class="btn-logout-icon" title="Keluar"><i class="fas fa-power-off"></i></a>
                    </div>
                </div>
            </header>

            <section class="stats-grid">
                <div class="stat-card blue">
                    <i class="fas fa-wallet bg-icon"></i>
                    <h3>Total Pendapatan</h3>
                    <div class="value" style="color:#0984e3">Rp <?= number_format($totalIncome/1000000, 1, ',', '.') ?>Jt</div>
                    <small style="color:#00b894"><i class="fas fa-arrow-up"></i> +12% bulan ini</small>
                </div>
                <div class="stat-card green">
                    <i class="fas fa-shopping-basket bg-icon"></i>
                    <h3>Total Pesanan</h3>
                    <div class="value" style="color:#00b894"><?= $totalOrders ?></div>
                    <small style="color:#636e72">Transaksi berhasil</small>
                </div>
                <div class="stat-card purple">
                    <i class="fas fa-chart-line bg-icon"></i>
                    <h3>Rata-rata</h3>
                    <div class="value" style="color:#6c5ce7">Rp <?= number_format($avgOrder/1000, 0, ',', '.') ?>K</div>
                    <small style="color:#636e72">Per transaksi</small>
                </div>
                <div class="stat-card orange">
                    <i class="fas fa-user-friends bg-icon"></i>
                    <h3>Pelanggan</h3>
                    <div class="value" style="color:#e17055"><?= $totalCustomers ?></div>
                    <small style="color:#00b894"><i class="fas fa-plus"></i> Aktif</small>
                </div>
            </section>
            
            <div style="background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); height: 400px; display: flex; align-items: center; justify-content: center; flex-direction: column; border: 1px solid rgba(0,0,0,0.02); transition: all 0.4s ease;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 20px 40px rgba(0,0,0,0.1)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.05)'">
                <i class="fas fa-chart-pie" style="font-size: 80px; color: #dfe6e9; margin-bottom: 20px;"></i>
                <h3 style="color: #b2bec3; margin:0;">Grafik Penjualan</h3>
            </div>
        </main>
    </div>

    <script>
        function toggleNotif() {
            const dropdown = document.getElementById('notifDropdown');
            dropdown.classList.toggle('active');
        }

        // Tutup dropdown jika klik di luar
        window.onclick = function(event) {
            if (!event.target.closest('.notif-wrapper')) {
                const dropdown = document.getElementById('notifDropdown');
                if (dropdown.classList.contains('active')) {
                    dropdown.classList.remove('active');
                }
            }
        }
    </script>
</body>
</html>