<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header("Location: ../login.php"); exit;
}
include '../includes/db.php';

$customers = $pdo->query("SELECT * FROM users WHERE role='customer' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pelanggan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">ADMIN</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link active"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            <li><a href="users.php" class="sidebar-link"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title"><h1>DATA PELANGGAN</h1></div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="80">FOTO</th>
                        <th>NAMA LENGKAP</th>
                        <th>KONTAK</th>
                        <th>STATUS</th>
                        <th width="100">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($customers as $c): ?>
                    <tr>
                        <td>
                            <img src="../assets/img/users/<?= $c['photo'] ?>" 
                            onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($c['fullname']) ?>&background=random'"
                            style="width:50px; height:50px; border-radius:50%; border:2px solid black; object-fit:cover;">
                        </td>
                        <td><strong><?= htmlspecialchars($c['fullname']) ?></strong></td>
                        <td>
                            <div style="font-size:14px; font-weight:bold;"><i class="far fa-envelope"></i> <?= htmlspecialchars($c['email']) ?></div>
                            <div style="font-size:14px; font-weight:bold; color:green; margin-top:5px;">
                                <a href="https://wa.me/<?= htmlspecialchars($c['phone']??'') ?>" target="_blank" style="text-decoration:none; color:green;">
                                    <i class="fab fa-whatsapp"></i> <?= htmlspecialchars($c['phone']??'-') ?>
                                </a>
                            </div>
                        </td>
                        <td><span class="badge badge-green">AKTIF</span></td>
                        <td>
                            <a href="https://wa.me/<?= htmlspecialchars($c['phone']??'') ?>" target="_blank" class="btn-action" style="background:#25D366; color:white;" title="Chat WA">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>