<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header("Location: ../login.php"); exit;
}
include '../includes/db.php';

// --- LOGIC UPLOAD QRIS ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['qris'])) {
    if ($_FILES['qris']['error'] == 0) {
        $target_file = "../assets/img/qris.jpg";
        if (file_exists($target_file)) unlink($target_file);
        if (move_uploaded_file($_FILES['qris']['tmp_name'], $target_file)) {
            echo "<script>alert('QRIS Berhasil Diupdate!'); window.location='users.php';</script>";
        }
    }
}

// --- LOGIC KELOLA USER ---
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$_GET['approve']]);
    header("Location: users.php"); exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$_GET['delete']]);
    header("Location: users.php"); exit;
}

// QUERY: Ambil semua user yang BUKAN customer
$staffs = $pdo->query("SELECT * FROM users WHERE role != 'customer' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Pengaturan & Tim - Boss Mode</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .file-input-wrapper { margin-bottom: 15px; text-align: center; }
        .file-input-real { display: none; }
        .file-input-label { display: block; width: 100%; padding: 12px; font-family: 'Archivo Black'; font-size: 14px; cursor: pointer; background: var(--white); color: var(--black); border: 3px solid var(--black); box-shadow: 4px 4px 0 var(--black); transition: 0.2s; text-transform: uppercase; }
        .file-input-label:hover { background: var(--yellow); transform: translate(-2px, -2px); box-shadow: 6px 6px 0 var(--black); }
        .qris-preview-box { background: #eee; border: 3px solid var(--black); padding: 10px; margin-bottom: 15px; display: flex; justify-content: center; align-items: center; min-height: 200px; }
    </style>
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">BOSS MODE</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            <li><a href="users.php" class="sidebar-link active"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header"><div class="page-title"><h1>PENGATURAN & TIM</h1></div></div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; align-items: start;">
            
            <div class="table-container">
                <h3 style="font-family:'Archivo Black'; border-bottom:4px solid black; padding-bottom:15px; margin-bottom:20px;">DAFTAR STAFF</h3>
                <table>
                    <thead><tr><th>NAMA</th><th>ROLE</th><th>STATUS</th><th width="50">AKSI</th></tr></thead>
                    <tbody>
                        <?php if(empty($staffs)): ?>
                            <tr><td colspan="4" style="text-align:center; padding:20px;">Belum ada staff.</td></tr>
                        <?php else: ?>
                            <?php foreach($staffs as $u): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($u['fullname']) ?></strong><br>
                                    <small><?= htmlspecialchars($u['email']) ?></small>
                                </td>
                                <td>
                                    <?php 
                                        // --- LOGIKA BADGE DIPERKUAT ---
                                        $role = strtolower(trim($u['role']));
                                        
                                        $bg = 'badge-yellow';
                                        $label = 'ADMIN'; // Default jika error

                                        if($role == 'super_admin') { $bg = 'badge-red'; $label = 'OWNER'; }
                                        else if($role == 'admin') { $bg = 'badge-yellow'; $label = 'ADMIN'; }
                                        else if($role == 'cashier') { $bg = 'badge-green'; $label = 'KASIR'; }
                                        // JURUS PENYELAMAT: Jika kosong/aneh, anggap Kasir
                                        else if(empty($role) || $role == '') { $bg = 'badge-green'; $label = 'KASIR'; }
                                    ?>
                                    <span class="badge <?= $bg ?>"><?= $label ?></span>
                                </td>
                                <td>
                                    <?php if($u['status']=='pending'): ?>
                                        <div style="display:flex; gap:5px; align-items:center;">
                                            <span class="badge badge-red">PENDING</span>
                                            <a href="?approve=<?= $u['id'] ?>" style="background:#00ff00; color:black; padding:3px 8px; border:2px solid black; font-weight:900; font-size:10px; text-decoration:none; box-shadow:2px 2px 0 black; cursor:pointer;"><i class="fas fa-check"></i> ACC</a>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge badge-green">ACTIVE</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($u['role'] !== 'super_admin'): ?>
                                        <a href="?delete=<?= $u['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus staff ini?')"><i class="fas fa-trash"></i></a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="stat-card">
                <h3 style="font-family:'Archivo Black'; text-align:center; margin-bottom:20px;">QRIS TOKO</h3>
                <div class="qris-preview-box">
                    <img src="../assets/img/qris.jpg?v=<?= time() ?>" onerror="this.src='https://via.placeholder.com/300x300?text=QRIS+BELUM+ADA'" style="max-width: 100%; height: auto; display: block;">
                </div>
                <p style="text-align:center; font-size:12px; font-weight:bold; color:#555; margin-bottom:15px;">Akan muncul di halaman bayar customer.</p>
                <form method="POST" enctype="multipart/form-data">
                    <div class="file-input-wrapper">
                        <input type="file" name="qris" id="qrisInput" class="file-input-real" required onchange="document.getElementById('labelTxt').innerText = 'GAMBAR DIPILIH!'">
                        <label for="qrisInput" class="file-input-label"><i class="fas fa-camera"></i> <span id="labelTxt">GANTI GAMBAR</span></label>
                    </div>
                    <button type="submit" class="btn-action" style="width:100%; background:var(--red-primary); color:white; padding:12px; font-size:14px;"><i class="fas fa-upload"></i> UPLOAD SEKARANG</button>
                </form>
            </div>

        </div>
    </main>
</body>
</html>