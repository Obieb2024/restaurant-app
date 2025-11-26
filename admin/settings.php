<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header("Location: ../login.php"); exit;
}
include '../includes/db.php';

// LOGIC UPLOAD QRIS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['qris']) && $_FILES['qris']['error'] == 0) {
        $target_dir = "../assets/img/";
        // Kita simpan dengan nama tetap 'qris.jpg' biar gampang dipanggil
        $target_file = $target_dir . "qris.jpg";
        
        // Hapus yang lama kalau ada
        if (file_exists($target_file)) unlink($target_file);
        
        // Upload yang baru
        if (move_uploaded_file($_FILES['qris']['tmp_name'], $target_file)) {
            echo "<script>alert('QRIS Berhasil Diupdate!'); window.location='settings.php';</script>";
        } else {
            echo "<script>alert('Gagal upload gambar.');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengaturan - Admin Mode</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">ADMIN MODE</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            
            <li><a href="settings.php" class="sidebar-link active"><i class="fas fa-cog"></i> PENGATURAN</a></li>
            
            <li><a href="users.php" class="sidebar-link"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title"><h1>PENGATURAN TOKO</h1><p>Atur metode pembayaran dan info toko.</p></div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:30px;">
            <div class="card-form">
                <h3 style="border-bottom:4px solid black; padding-bottom:10px; margin-bottom:20px;">UPLOAD QRIS PEMBAYARAN</h3>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Pilih Gambar QRIS (JPG/PNG)</label>
                        <input type="file" name="qris" class="form-control" style="padding:10px;" required>
                        <small style="color:red; font-weight:bold;">*Pastikan gambar jelas dan bisa discan.</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fas fa-upload"></i> UPDATE QRIS SEKARANG
                    </button>
                </form>
            </div>

            <div class="card-form" style="text-align:center;">
                <h3 style="border-bottom:4px solid black; padding-bottom:10px; margin-bottom:20px;">PREVIEW SAAT INI</h3>
                <div style="border:4px solid black; display:inline-block; padding:10px; background:white;">
                    <img src="../assets/img/qris.jpg?v=<?= time() ?>" 
                         onerror="this.src='https://via.placeholder.com/300x300?text=BELUM+ADA+QRIS'" 
                         style="width:250px; height:auto; display:block;">
                </div>
                <p style="margin-top:15px; font-weight:bold;">Ini yang akan dilihat customer.</p>
            </div>
        </div>
    </main>
</body>
</html>