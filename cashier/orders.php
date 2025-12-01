<?php
session_start();
// PENGAMAN: Cek Role Kasir (Admin/Super Admin juga boleh intip sih kalau mau)
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'cashier' && $_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) { 
    header('Location: ../login.php'); exit; 
}

include '../includes/db.php';
include '../includes/Cashier.php';

$cashier = new Cashier($pdo);

// UPDATE STATUS
if (isset($_POST['update_status'])) {
    $cashier->updateStatus($_POST['order_id'], $_POST['status']);
    header("Location: orders.php"); exit;
}

$orders = $cashier->getAllOrders();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Transaksi - Cashier Mode</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Override Sidebar Kasir (Hijau) */
        .admin-sidebar { border-right: 4px solid var(--black); background: #f0fdf4; }
        .admin-logo { background: #25D366; color: black; box-shadow: 4px 4px 0 black; }
        .sidebar-link:hover { background: #bbf7d0; }
        .sidebar-link.active { background: var(--black); color: #25D366; }
        
        .status-select { 
            border: 3px solid black; padding: 8px; font-weight: 900; cursor: pointer; outline: none; 
            font-family: 'Chakra Petch'; margin-right: 5px;
        }
        
        /* Tombol Print Keren */
        .btn-print {
            background: black; color: white; border: 3px solid black;
            padding: 8px 12px; font-size: 16px; cursor: pointer;
            text-decoration: none; display: inline-flex; align-items: center;
            transition: 0.2s;
        }
        .btn-print:hover {
            background: white; color: black; transform: scale(1.1);
        }
    </style>
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">CASHIER</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-home"></i> BERANDA</a></li>
            <li><a href="orders.php" class="sidebar-link active"><i class="fas fa-cash-register"></i> TRANSAKSI</a></li>
            <li><a href="../admin/logout.php" class="sidebar-link" style="background:#ff7675; color:white; margin-top:50px;"><i class="fas fa-sign-out-alt"></i> LOGOUT</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title"><h1>DAFTAR TRANSAKSI</h1></div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Pelanggan & Kontak</th>
                        <th>Total & Metode</th>
                        <th>Status Saat Ini</th>
                        <th width="250">Aksi & Cetak</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                    <tr style="<?= $o['order_status']=='Menunggu' ? 'background:#fffbe6;' : '' ?>">
                        <td><strong>#<?= $o['id'] ?></strong></td>
                        <td>
                            <strong><?= htmlspecialchars($o['fullname']) ?></strong><br>
                            <a href="https://wa.me/<?= $o['phone'] ?>" target="_blank" style="color:green; font-weight:bold; font-size:12px;">
                                <i class="fab fa-whatsapp"></i> Hubungi
                            </a>
                            <?php if(!empty($o['address'])): ?>
                                <div style="font-size:11px; margin-top:5px; border:1px solid black; padding:3px; background:white;"><i class="fas fa-map-marker-alt"></i> <?= $o['address'] ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="font-weight:900; font-size:16px;">Rp <?= number_format($o['total'],0,',','.') ?></div>
                            <span class="badge badge-yellow"><?= $o['payment_method'] ?></span>
                        </td>
                        <td>
                            <span class="badge <?= $o['order_status']=='Selesai'?'badge-green':'badge-red' ?>" style="background:var(--white); color:black; border:2px solid black;">
                                <?= strtoupper($o['order_status']) ?>
                            </span>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center;">
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="update_status" value="1">
                                    <input type="hidden" name="order_id" value="<?= $o['id'] ?>">
                                    <select name="status" class="status-select" onchange="this.form.submit()">
                                        <option value="Menunggu" <?= $o['order_status']=='Menunggu'?'selected':'' ?>>⏳ Wait</option>
                                        <option value="Konfirmasi" <?= $o['order_status']=='Konfirmasi'?'selected':'' ?>>👍 Confirm</option>
                                        <option value="Diproses" <?= $o['order_status']=='Diproses'?'selected':'' ?>>🔥 Cook</option>
                                        <option value="Selesai" <?= $o['order_status']=='Selesai'?'selected':'' ?>>✅ Done</option>
                                        <option value="Dibatalkan" <?= $o['order_status']=='Dibatalkan'?'selected':'' ?>>❌ Cancel</option>
                                    </select>
                                </form>

                                <a href="print_struk.php?id=<?= $o['id'] ?>" target="_blank" class="btn-print" title="Cetak Struk">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>