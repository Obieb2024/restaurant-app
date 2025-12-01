<?php
session_start();
// Cek Login
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header("Location: ../login.php");
    exit;
}
include '../includes/db.php';

// --- LOGIKA UPDATE STATUS ---
// (Sekarang dipicu oleh hidden input 'update_status')
if (isset($_POST['update_status'])) {
    $oid = $_POST['order_id'];
    $new_status = $_POST['status'];

    // Ambil status lama & data produk untuk manajemen stok
    $cek = $pdo->prepare("SELECT order_status FROM orders WHERE id = ?");
    $cek->execute([$oid]);
    $old_status = $cek->fetchColumn();

    $items = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $items->execute([$oid]);
    $products = $items->fetchAll(PDO::FETCH_ASSOC);

    // Logika Balikin Stok jika Batal
    if ($old_status != 'Dibatalkan' && $new_status == 'Dibatalkan') {
        foreach ($products as $p) { $pdo->prepare("UPDATE products SET stock = stock + ? WHERE id = ?")->execute([$p['quantity'], $p['product_id']]); }
    } elseif ($old_status == 'Dibatalkan' && $new_status != 'Dibatalkan') {
        foreach ($products as $p) { $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$p['quantity'], $p['product_id']]); }
    }

    // Simpan Perubahan
    $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
    $stmt->execute([$new_status, $oid]);
    
    // Refresh halaman
    header("Location: orders.php"); exit;
}

$sql = "SELECT o.*, u.fullname FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC";
$orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pesanan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* STEPPER VISUAL */
        .admin-stepper { display: flex; align-items: center; gap: 5px; margin-bottom: 5px; }
        .step-dot { width: 20px; height: 20px; border: 2px solid black; border-radius: 50%; background: #eee; display: flex; align-items: center; justify-content: center; font-size: 9px; color: #999; transition: 0.3s; }
        .step-line { flex: 1; height: 3px; background: #ddd; border: 1px solid black; min-width: 10px; }
        
        /* WARNA STATUS */
        .s-wait { background: #ffeaa7; color: black; transform: scale(1.1); border-color: black; }
        .s-conf { background: #fab1a0; color: black; transform: scale(1.1); border-color: black; }
        .s-cook { background: #74b9ff; color: black; transform: scale(1.1); border-color: black; }
        .s-done { background: #55efc4; color: black; transform: scale(1.1); border-color: black; }
        .l-fill { background: black; }

        .cancelled-badge { background: #ff7675; color: white; padding: 5px 10px; border: 2px solid black; font-weight: 900; font-size: 12px; display: inline-block; transform: rotate(-2deg); }

        /* CUSTOM SELECT (DROPDOWN KEREN) */
        .status-select {
            border: 3px solid black; 
            padding: 8px 10px; 
            font-weight: 900; 
            width: 100%; 
            cursor: pointer;
            font-family: 'Chakra Petch', sans-serif;
            background-color: white;
            outline: none;
            transition: 0.2s;
        }
        .status-select:hover { background-color: var(--yellow); }
        .status-select:focus { box-shadow: 4px 4px 0 black; }
    </style>
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">ADMIN</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link active"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            <li><a href="users.php" class="sidebar-link"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title"><h1>DAFTAR PESANAN MASUK</h1></div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>PELANGGAN</th>
                        <th>TOTAL</th>
                        <th>METODE</th>
                        <th width="280">PROSES PESANAN (4 TAHAP)</th>
                        <th width="180">UPDATE STATUS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                    <tr>
                        <td><strong>#<?= $o['id'] ?></strong></td>
                        <td>
                            <span style="font-weight:bold; font-size:16px;"><?= htmlspecialchars($o['fullname']) ?></span><br>
                            <small style="color:#555;"><?= date('d/m H:i', strtotime($o['order_date'])) ?></small>
                        </td>
                        <td style="font-weight:900;">Rp <?= number_format($o['total'],0,',','.') ?></td>
                        <td><span class="badge badge-yellow"><?= $o['payment_method'] ?></span></td>
                        
                        <td>
                            <?php if($o['order_status'] == 'Dibatalkan'): ?>
                                <div class="cancelled-badge">🚫 DIBATALKAN</div>
                            <?php else: ?>
                                <?php 
                                    $s1=$s2=$s3=$s4=''; $l1=$l2=$l3=''; 
                                    $status = $o['order_status'];
                                    if($status=='Menunggu') { $s1='s-wait'; }
                                    if($status=='Konfirmasi') { $s1='s-wait'; $l1='l-fill'; $s2='s-conf'; }
                                    if($status=='Diproses') { $s1='s-wait'; $l1='l-fill'; $s2='s-conf'; $l2='l-fill'; $s3='s-cook'; }
                                    if($status=='Selesai') { $s1='s-wait'; $l1='l-fill'; $s2='s-conf'; $l2='l-fill'; $s3='s-cook'; $l3='l-fill'; $s4='s-done'; }
                                ?>
                                <div class="admin-stepper">
                                    <div class="step-dot <?= $s1 ?>" title="Menunggu"><i class="fas fa-clock"></i></div>
                                    <div class="step-line <?= $l1 ?>"></div>
                                    <div class="step-dot <?= $s2 ?>" title="Konfirmasi"><i class="fas fa-thumbs-up"></i></div>
                                    <div class="step-line <?= $l2 ?>"></div>
                                    <div class="step-dot <?= $s3 ?>" title="Diproses"><i class="fas fa-fire"></i></div>
                                    <div class="step-line <?= $l3 ?>"></div>
                                    <div class="step-dot <?= $s4 ?>" title="Selesai"><i class="fas fa-check"></i></div>
                                </div>
                                <div style="font-family:'Archivo Black'; font-size:11px; margin-top:5px;">
                                    <?= strtoupper($o['order_status']) ?>
                                </div>
                            <?php endif; ?>
                        </td>

                        <td>
                            <form method="POST">
                                <input type="hidden" name="update_status" value="1">
                                <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                
                                <select name="status" class="status-select" onchange="this.form.submit()">
                                    <option value="Menunggu" <?= $o['order_status']=='Menunggu'?'selected':'' ?>>⏳ MENUNGGU</option>
                                    <option value="Konfirmasi" <?= $o['order_status']=='Konfirmasi'?'selected':'' ?>>👍 CONFIRM</option>
                                    <option value="Diproses" <?= $o['order_status']=='Diproses'?'selected':'' ?>>🔥 MASAK</option>
                                    <option value="Selesai" <?= $o['order_status']=='Selesai'?'selected':'' ?>>✅ SELESAI</option>
                                    <option value="Dibatalkan" <?= $o['order_status']=='Dibatalkan'?'selected':'' ?>>❌ BATAL</option>
                                </select>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php
