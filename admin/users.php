<?php
session_start();
// Cek Super Admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    header('Location: dashboard.php'); // Jika admin biasa, lempar ke dashboard
    exit;
}
include '../includes/db.php';

// Approve
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$_GET['approve']]);
    header("Location: users.php");
}
// Reject/Delete
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$_GET['delete']]);
    header("Location: users.php");
}

$pending = $pdo->query("SELECT * FROM users WHERE role='admin' AND status='pending'")->fetchAll(PDO::FETCH_ASSOC);
$users = $pdo->query("SELECT * FROM users WHERE role!='super_admin' ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Kelola Pengguna</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <nav class="admin-sidebar">
            <div class="logo-area"><i class="fas fa-utensils"></i> <h2>Bu Yeti</h2></div>
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-fire"></i> Dashboard</a></li>
                <li><a href="products.php"><i class="fas fa-hamburger"></i> Produk</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Pesanan</a></li>
                <li><a href="customer.php"><i class="fas fa-users"></i> Pelanggan</a></li>
                <li><a href="users.php" class="active active-purple"><i class="fas fa-user-shield"></i> Kelola Admin</a></li>
            </ul>
        </nav>

        <main>
            <div class="topbar">
                <div class="page-header"><h1>Kelola Pengguna</h1><p>Persetujuan Admin & Data User</p></div>
            </div>

            <?php if(count($pending) > 0): ?>
            <div style="background:#fff3cd; padding:20px; border-radius:16px; margin-bottom:30px; border:1px solid #ffeeba;">
                <h3 style="color:#856404; margin-bottom:15px;"><i class="fas fa-exclamation-triangle"></i> Butuh Persetujuan (<?= count($pending) ?>)</h3>
                <table width="100%">
                    <?php foreach($pending as $p): ?>
                    <tr>
                        <td width="50"><img src="../assets/img/users/<?= $p['photo'] ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;"></td>
                        <td><strong><?= htmlspecialchars($p['fullname']) ?></strong><br><small><?= htmlspecialchars($p['email']) ?></small></td>
                        <td align="right">
                            <a href="?approve=<?= $p['id'] ?>" class="btn" style="background:#28a745; color:white;">Setujui</a>
                            <a href="?delete=<?= $p['id'] ?>" class="btn" style="background:#dc3545; color:white;" onclick="return confirm('Tolak user ini?')">Tolak</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <?php endif; ?>

            <div class="table-container">
                <h3>Semua Pengguna</h3>
                <table width="100%">
                    <thead><tr><th>Foto</th><th>Nama</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td><img src="../assets/img/users/<?= $u['photo'] ?>" style="width:40px; height:40px; border-radius:50%; object-fit:cover;" onerror="this.src='https://ui-avatars.com/api/?name=User'"></td>
                            <td><?= htmlspecialchars($u['fullname']) ?><br><small><?= htmlspecialchars($u['email']) ?></small></td>
                            <td>
                                <span style="padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; background:<?= $u['role']=='admin'?'#e0e7ff':'#d1fae5' ?>; color:<?= $u['role']=='admin'?'#4338ca':'#059669' ?>">
                                    <?= strtoupper($u['role']) ?>
                                </span>
                            </td>
                            <td><?= $u['status'] ?></td>
                            <td><a href="?delete=<?= $u['id'] ?>" style="color:red;" onclick="return confirm('Hapus permanen?')"><i class="fas fa-trash"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>