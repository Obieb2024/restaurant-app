<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header("Location: ../login.php"); exit;
}
include '../includes/db.php';

$startDate = $_GET['start'] ?? date('Y-m-d');
$endDate   = $_GET['end']   ?? date('Y-m-d');

$sql = "SELECT o.*, u.fullname FROM orders o JOIN users u ON o.user_id = u.id WHERE DATE(o.order_date) BETWEEN ? AND ? AND o.order_status != 'Dibatalkan' ORDER BY o.order_date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$startDate, $endDate]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalOmzet = 0;
foreach ($reports as $r) { $totalOmzet += $r['total']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Laporan - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .filter-box { background: var(--white); border: 4px solid var(--black); padding: 25px; margin-bottom: 30px; box-shadow: 8px 8px 0 var(--black); display: grid; grid-template-columns: 1fr 1fr auto; gap: 20px; align-items: end; }
        .form-group-filter { display: flex; flex-direction: column; gap: 8px; }
        .form-group-filter label { font-weight: 900; font-size: 14px; text-transform: uppercase; }
        .date-input { padding: 12px; border: 3px solid var(--black); font-weight: bold; font-family: 'Chakra Petch'; width: 100%; font-size: 16px; }
        .btn-filter { background: var(--yellow); color: var(--black); border: 3px solid var(--black); padding: 12px 30px; font-weight: 900; cursor: pointer; font-size: 16px; text-transform: uppercase; height: 50px; transition: 0.2s; }
        .btn-filter:hover { background: var(--black); color: var(--yellow); transform: translate(-2px, -2px); box-shadow: 4px 4px 0 var(--black); }
        .btn-print { background: var(--black); color: white; border: 3px solid var(--black); padding: 10px 20px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; }
        .btn-print:hover { background: var(--white); color: var(--black); }
        @media print { @page { size: A4; margin: 2cm; } body { background: white; -webkit-print-color-adjust: exact; } .admin-sidebar, .filter-box, .btn-print, .page-header button { display: none !important; } .main-content { margin: 0; padding: 0; width: 100%; } .page-header { box-shadow: none; border: none; border-bottom: 3px solid black; padding: 0 0 20px 0; margin-bottom: 30px; } .table-container { box-shadow: none; border: 2px solid black; } .signature-area { display: block !important; margin-top: 50px; text-align: right; } }
        .signature-area { display: none; }
    </style>
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">ADMIN</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link active"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            <li><a href="users.php" class="sidebar-link"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div><h1>LAPORAN KEUANGAN</h1><p>Periode: <strong><?= date('d M Y', strtotime($startDate)) ?></strong> s/d <strong><?= date('d M Y', strtotime($endDate)) ?></strong></p></div>
            <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> CETAK LAPORAN</button>
        </div>

        <form method="GET" class="filter-box">
            <div class="form-group-filter"><label>DARI TANGGAL</label><input type="date" name="start" class="date-input" value="<?= $startDate ?>"></div>
            <div class="form-group-filter"><label>SAMPAI TANGGAL</label><input type="date" name="end" class="date-input" value="<?= $endDate ?>"></div>
            <div><button type="submit" class="btn-filter"><i class="fas fa-search"></i> TAMPILKAN DATA</button></div>
        </form>

        <div class="stats-grid">
            <div class="stat-card" style="background:#ff7675;"><div class="stat-label" style="color:white;">TOTAL OMZET</div><div class="stat-value" style="color:white;">Rp <?= number_format($totalOmzet, 0, ',', '.') ?></div></div>
            <div class="stat-card" style="background:#74b9ff;"><div class="stat-label">TOTAL TRANSAKSI</div><div class="stat-value"><?= count($reports) ?></div></div>
        </div>

        <div class="table-container">
            <table width="100%" border="1" cellspacing="0" cellpadding="10">
                <thead><tr style="background:black; color:yellow;"><th>NO</th><th>TANGGAL</th><th>PELANGGAN</th><th>METODE</th><th>STATUS</th><th>TOTAL</th></tr></thead>
                <tbody>
                    <?php if(empty($reports)): ?>
                        <tr><td colspan="6" style="text-align:center; padding:20px; font-weight:bold;">Tidak ada data transaksi pada tanggal ini.</td></tr>
                    <?php else: $no=1; foreach($reports as $r): ?>
                        <tr>
                            <td align="center"><?= $no++ ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($r['order_date'])) ?></td>
                            <td><?= htmlspecialchars($r['fullname']) ?></td>
                            <td align="center"><?= $r['payment_method'] ?></td>
                            <td align="center"><span class="badge badge-green"><?= strtoupper($r['order_status']) ?></span></td>
                            <td align="right" style="font-weight:bold;">Rp <?= number_format($r['total'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                        <tr style="background:#eee; border-top:3px solid black;"><td colspan="5" align="right" style="font-weight:900; font-size:18px;">GRAND TOTAL</td><td align="right" style="font-weight:900; font-size:18px;">Rp <?= number_format($totalOmzet,0,',','.') ?></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="signature-area">
            <p>Jakarta, <?= date('d F Y') ?></p><br><br><br><p style="font-weight:bold; text-decoration:underline; text-transform:uppercase;"><?= $_SESSION['user']['fullname'] ?></p><p>Owner / Admin</p>
        </div>
    </main>
</body>
</html>