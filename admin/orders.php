<?php
session_start();
// 1. Cek Login Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include '../includes/db.php';

// --- LOGIKA 1: UPDATE STATUS PESANAN (Ini yang memperbaiki error) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    // Update ke database
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    if ($stmt->execute([$new_status, $order_id])) {
        // Refresh halaman agar data terupdate
        header("Location: orders.php");
        exit;
    } else {
        echo "<script>alert('Gagal mengubah status');</script>";
    }
}

// --- LOGIKA 2: FILTER STATUS (Tab Menu) ---
$status_filter = $_GET['status'] ?? 'Semua';
$sql = "SELECT o.*, u.fullname FROM orders o JOIN users u ON o.user_id = u.id";

if ($status_filter !== 'Semua') {
    $sql .= " WHERE o.order_status = :status";
}
$sql .= " ORDER BY o.order_date DESC";

$stmt = $pdo->prepare($sql);
if ($status_filter !== 'Semua') {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung Statistik untuk Kartu Atas
$cntTotal = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$cntMenunggu = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status='Menunggu'")->fetchColumn();
$cntDiproses = $pdo->query("SELECT COUNT(*) FROM orders WHERE order_status='Diproses'")->fetchColumn();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Pesanan</title>
    <link href="../assets/css/admin.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS Khusus Dropdown Status agar Modern */
        .status-select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background-color: white;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            cursor: pointer;
            outline: none;
            transition: all 0.3s;
        }
        .status-select:hover { border-color: #a29bfe; }
        .status-select:focus { border-color: #6c5ce7; box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.1); }
        
        /* Warna Text Status di Table */
        .badge-text { font-weight: 700; font-size: 12px; padding: 5px 10px; border-radius: 20px; }
        .badge-text.menunggu { background: #fff3cd; color: #d97706; }
        .badge-text.diproses { background: #e0e7ff; color: #4f46e5; }
        .badge-text.selesai { background: #d1fae5; color: #059669; }
        .badge-text.dibatalkan { background: #fee2e2; color: #dc2626; }
    </style>
</head>
<body>
<div class="admin-wrapper">
    
    <nav class="admin-sidebar">
        <div class="logo-area"><i class="fas fa-utensils"></i> <h2>RestoApp</h2></div>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="products.php"><i class="fas fa-hamburger"></i> Produk</a></li>
            <li><a href="orders.php" class="active active-purple"><i class="fas fa-receipt"></i> Pesanan</a></li>
            <li><a href="customer.php"><i class="fas fa-users"></i> Pelanggan</a></li>
        </ul>
    </nav>

    <main>
        <div class="topbar">
            <div class="page-header">
                <h1>Pesanan Masuk</h1>
                <p>Kelola status pesanan pelanggan secara realtime</p>
            </div>
            <div class="user-info">
                <div class="notif-btn"><i class="far fa-bell"></i><span class="notif-badge"></span></div>
                <div class="profile-card">
                    <div class="profile-info"><span class="profile-name"><?= htmlspecialchars($_SESSION['user']['fullname']) ?></span><span class="profile-role">Admin</span></div>
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['fullname']) ?>&background=6c5ce7&color=fff" class="profile-img">
                </div>
            </div>
        </div>

        <section class="stats-grid">
            <div class="stat-card blue">
                <i class="fas fa-clipboard-list bg-icon"></i>
                <h3>Total Pesanan</h3>
                <div class="value"><?= $cntTotal ?></div>
            </div>
            <div class="stat-card orange">
                <i class="fas fa-clock bg-icon"></i>
                <h3>Menunggu</h3>
                <div class="value"><?= $cntMenunggu ?></div>
            </div>
            <div class="stat-card purple">
                <i class="fas fa-cog bg-icon"></i>
                <h3>Diproses</h3>
                <div class="value"><?= $cntDiproses ?></div>
            </div>
        </section>

        <div class="filter-tabs">
            <a href="?status=Semua" class="<?= $status_filter=='Semua'?'active':'' ?>">Semua</a>
            <a href="?status=Menunggu" class="<?= $status_filter=='Menunggu'?'active':'' ?>">Menunggu</a>
            <a href="?status=Diproses" class="<?= $status_filter=='Diproses'?'active':'' ?>">Diproses</a>
            <a href="?status=Selesai" class="<?= $status_filter=='Selesai'?'active':'' ?>">Selesai</a>
        </div>

        <div class="table-container">
            <table width="100%">
                <thead>
                    <tr>
                        <th>ID Pesanan</th>
                        <th>Pelanggan</th>
                        <th>Total</th>
                        <th>Pembayaran</th>
                        <th>Status Saat Ini</th>
                        <th>Ubah Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($orders) > 0): ?>
                        <?php foreach ($orders as $o): ?>
                        <tr>
                            <td>
                                <strong>#<?= $o['order_id'] ?></strong><br>
                                <small style="color:#aaa"><?= date('d M Y H:i', strtotime($o['order_date'])) ?></small>
                            </td>
                            <td><?= htmlspecialchars($o['fullname']) ?></td>
                            <td style="color:#10b981; font-weight:700;">Rp <?= number_format($o['total'], 0, ',', '.') ?></td>
                            <td>
                                <i class="<?= $o['payment_method'] == 'Cash' ? 'fas fa-money-bill-wave' : 'fas fa-qrcode' ?>"></i> 
                                <?= $o['payment_method'] ?>
                            </td>
                            <td>
                                <span class="badge-text <?= strtolower($o['order_status']) ?>">
                                    <?= $o['order_status'] ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="Menunggu" <?= $o['order_status']=='Menunggu'?'selected':'' ?>>Menunggu</option>
                                        <option value="Diproses" <?= $o['order_status']=='Diproses'?'selected':'' ?>>Diproses</option>
                                        <option value="Selesai" <?= $o['order_status']=='Selesai'?'selected':'' ?>>Selesai</option>
                                        <option value="Dibatalkan" <?= $o['order_status']=='Dibatalkan'?'selected':'' ?>>Dibatalkan</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align:center; padding:30px; color:#999;">Tidak ada data pesanan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>
</body>
</html>