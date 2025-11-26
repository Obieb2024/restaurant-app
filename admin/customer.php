<?php
session_start();
include '../includes/db.php';

// Ambil data customer
$stmt = $pdo->query("SELECT * FROM users WHERE role='customer'");
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pelanggan</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar">
            <div class="logo-area">
                <i class="fas fa-utensils fa-2x" style="color: #ff4757;"></i>
                <h2>RestoApp</h2>
            </div>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-box"></i> Produk</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Pesanan</a></li>
                <li><a href="customer.php" class="active" style="background:#10b981; color:white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);"><i class="fas fa-users"></i> Pelanggan</a></li>
            </ul>
        </nav>

        <main>
            <div class="page-header">
                <h1>Pelanggan</h1>
                <p>Kelola data dan riwayat pelanggan</p>
            </div>

            <section class="stats-grid">
                <div class="stat-card purple">
                    <h3>Total Pelanggan</h3>
                    <div class="value"><?= count($customers) ?></div>
                    <small>Pelanggan terdaftar</small>
                </div>
                <div class="stat-card green">
                    <h3>Total Pendapatan</h3>
                    <div class="value">Rp 7.3Jt</div>
                    <small>Dari semua pelanggan</small>
                </div>
                <div class="stat-card blue">
                    <h3>Rata-rata Pesanan</h3>
                    <div class="value">Rp 60K</div>
                    <small>Per transaksi</small>
                </div>
                <div class="stat-card orange">
                    <h3>Pelanggan Terbaik</h3>
                    <div class="value">Ahmad Rizki</div>
                    <small>Rp 2.150K</small>
                </div>
            </section>

            <div class="customer-grid">
                <?php foreach($customers as $c): ?>
                <div class="customer-card">
                    <div class="card-header">
                        <div class="avatar-circle">
                            <?= strtoupper(substr($c['fullname'], 0, 1)) ?>
                        </div>
                        <div>
                            <h4 style="margin:0; font-size:16px;"><?= htmlspecialchars($c['fullname']) ?></h4>
                            <div style="color:#f59e0b; font-size:12px; margin-top:2px;">
                                <i class="fas fa-star"></i> 4.8
                            </div>
                        </div>
                        <div class="badge-vip">VIP Customer</div>
                    </div>
                    
                    <div style="font-size:13px; color:#666;">
                        <div style="margin-bottom:5px;"><i class="far fa-envelope"></i> <?= htmlspecialchars($c['email']) ?></div>
                        <div style="margin-bottom:5px;"><i class="fas fa-phone-alt"></i> <?= htmlspecialchars($c['phone']) ?></div>
                        <div><i class="fas fa-map-marker-alt"></i> Jl. Sudirman No. 123, Jakarta</div>
                    </div>

                    <div class="card-stats">
                        <div class="stat-item">
                            <span>Total Pesanan</span>
                            <strong style="color:#3b82f6">24</strong>
                        </div>
                        <div class="stat-item">
                            <span>Total Belanja</span>
                            <strong class="green-text">1450K</strong>
                        </div>
                        <div class="stat-item">
                            <span>Terakhir</span>
                            <strong style="color:#8b5cf6">12 November</strong>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</body>
</html>