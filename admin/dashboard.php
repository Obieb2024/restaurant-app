<?php
session_start();
// Cek Login: Izinkan admin DAN super_admin
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header('Location: ../login.php');
    exit;
}
include '../includes/db.php';

// --- 1. DATA KARTU STATISTIK (REAL-TIME) ---
// Menghitung pesanan yang TIDAK dibatalkan
$income = $pdo->query("SELECT SUM(total) FROM orders WHERE order_status != 'Dibatalkan'")->fetchColumn() ?? 0;
$orderCount = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$productCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$customerCount = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();

// --- 2. DATA GRAFIK 1: PENJUALAN HARIAN (7 Hari Terakhir) ---
$chartQuery = $pdo->query("
    SELECT DATE_FORMAT(order_date, '%d %b') as tanggal, SUM(total) as omzet 
    FROM orders 
    WHERE order_status != 'Dibatalkan' 
    AND order_date >= DATE(NOW()) - INTERVAL 7 DAY
    GROUP BY DATE(order_date)
    ORDER BY order_date ASC
");
$chartData = $chartQuery->fetchAll(PDO::FETCH_ASSOC);
$lbl_sales = []; $dat_sales = [];
foreach($chartData as $d) { $lbl_sales[] = $d['tanggal']; $dat_sales[] = $d['omzet']; }

// --- 3. DATA GRAFIK 2: METODE PEMBAYARAN ---
$payQuery = $pdo->query("SELECT payment_method, COUNT(*) as jumlah FROM orders GROUP BY payment_method");
$payData = $payQuery->fetchAll(PDO::FETCH_ASSOC);
$lbl_pay = []; $dat_pay = [];
foreach($payData as $p) { $lbl_pay[] = $p['payment_method']; $dat_pay[] = $p['jumlah']; }

// --- 4. DATA GRAFIK 3: TOP 5 MENU TERLARIS ---
$topQuery = $pdo->query("
    SELECT p.name, SUM(oi.quantity) as terjual 
    FROM order_items oi 
    JOIN products p ON oi.product_id = p.id 
    JOIN orders o ON oi.order_id = o.id
    WHERE o.order_status != 'Dibatalkan'
    GROUP BY p.name 
    ORDER BY terjual DESC 
    LIMIT 5
");
$topData = $topQuery->fetchAll(PDO::FETCH_ASSOC);
$lbl_top = []; $dat_top = [];
foreach($topData as $t) { $lbl_top[] = $t['name']; $dat_top[] = $t['terjual']; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Dashboard - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        .notif-btn {
            position: relative; cursor: pointer;
            background: var(--yellow); border: 3px solid black;
            width: 50px; height: 50px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; transition: 0.2s;
            box-shadow: 4px 4px 0 black;
        }
        .notif-btn:hover { transform: translate(-2px, -2px); box-shadow: 6px 6px 0 black; }
        .notif-btn.active { animation: shake 0.5s infinite; background: #ff7675; color: white; }

        .notif-badge {
            position: absolute; top: -5px; right: -5px;
            background: red; color: white; border: 2px solid black;
            width: 25px; height: 25px; border-radius: 50%;
            font-size: 12px; font-weight: 900;
            display: flex; align-items: center; justify-content: center;
            display: none; /* Hidden kalau 0 */
        }

        .notif-dropdown {
            display: none; position: absolute;
            top: 80px; right: 0; width: 320px;
            background: white; border: 4px solid black;
            box-shadow: 8px 8px 0 black; z-index: 999;
        }
        .notif-dropdown.show { display: block; animation: popUp 0.2s; }
        
        .notif-header { background: black; color: white; padding: 10px; font-family: 'Archivo Black'; text-align: center; }
        .notif-list { max-height: 300px; overflow-y: auto; }
        .notif-item {
            padding: 15px; border-bottom: 2px solid black;
            display: flex; align-items: center; gap: 10px;
            transition: 0.2s; text-decoration: none; color: black;
        }
        .notif-item:hover { background: #f0f0f0; }
        
        @keyframes shake {
            0% { transform: rotate(0deg); } 25% { transform: rotate(15deg); } 
            50% { transform: rotate(0deg); } 75% { transform: rotate(-15deg); } 100% { transform: rotate(0deg); }
        }
        @keyframes popUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

    <audio id="notifSound" src="https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3"></audio>

    <nav class="admin-sidebar">
        <div class="admin-logo">    ADMIN</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link active"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            <li><a href="users.php" class="sidebar-link"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div class="page-title">
                <h1>DASHBOARD UTAMA</h1>
                <p>Halo, <?= htmlspecialchars($_SESSION['user']['fullname']) ?>! Pantau bisnismu.</p>
            </div>
            
            <div style="display:flex; align-items:center; gap:20px;">
                <div class="badge badge-yellow" style="font-size:16px;">
                    <i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?>
                </div>

                <div style="position:relative;">
                    <div class="notif-btn" onclick="toggleNotif()">
                        <i class="fas fa-bell"></i>
                        <div class="notif-badge" id="notifCount">0</div>
                    </div>

                    <div class="notif-dropdown" id="notifBox">
                        <div class="notif-header">PESANAN BARU MASUK!</div>
                        <div class="notif-list" id="notifList">
                            <div style="padding:20px; text-align:center;">Memuat...</div>
                        </div>
                        <a href="orders.php" style="display:block; background:var(--yellow); padding:10px; text-align:center; font-weight:bold; border-top:3px solid black; color:black;">LIHAT SEMUA</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="background: #ff7675;">
                <i class="fas fa-wallet stat-icon"></i>
                <div class="stat-label" style="color:white;">TOTAL OMZET</div>
                <div class="stat-value" style="color:white;">Rp <?= number_format($income/1000000, 1, ',', '.') ?>Jt</div>
            </div>
            <div class="stat-card" style="background: #ffeaa7;">
                <i class="fas fa-shopping-basket stat-icon"></i>
                <div class="stat-label">TOTAL ORDER</div>
                <div class="stat-value"><?= $orderCount ?></div>
            </div>
            <div class="stat-card" style="background: #74b9ff;">
                <i class="fas fa-box stat-icon"></i>
                <div class="stat-label">JUMLAH MENU</div>
                <div class="stat-value"><?= $productCount ?></div>
            </div>
            <div class="stat-card" style="background: #55efc4;">
                <i class="fas fa-users stat-icon"></i>
                <div class="stat-label">PELANGGAN</div>
                <div class="stat-value"><?= $customerCount ?></div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px; margin-bottom: 30px;">
            <div class="table-container">
                <h3 style="margin-bottom:15px; font-family:'Archivo Black'; text-transform:uppercase;">GRAFIK OMZET (7 HARI)</h3>
                <div style="height: 300px;">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="table-container">
                <h3 style="margin-bottom:15px; font-family:'Archivo Black'; text-transform:uppercase;">METODE BAYAR</h3>
                <div style="height: 300px; display:flex; justify-content:center;">
                    <canvas id="payChart"></canvas>
                </div>
            </div>
        </div>

        <div class="table-container">
            <h3 style="margin-bottom:15px; font-family:'Archivo Black'; text-transform:uppercase;">🔥 5 MENU TERLARIS</h3>
            <div style="height: 250px;">
                <canvas id="topMenuChart"></canvas>
            </div>
        </div>

    </main>

    <script>
        // --- LOGIC NOTIFIKASI ---
        let lastCount = 0;
        const notifSound = document.getElementById('notifSound');

        function checkNotif() {
            fetch('get_notif.php')
                .then(response => response.json())
                .then(data => {
                    const count = data.count;
                    const badge = document.getElementById('notifCount');
                    const btn = document.querySelector('.notif-btn');
                    const list = document.getElementById('notifList');

                    // Update Badge
                    if (count > 0) {
                        badge.style.display = 'flex';
                        badge.innerText = count;
                        btn.classList.add('active');
                        
                        if (count > lastCount) {
                            notifSound.play().catch(e => console.log("Audio perlu interaksi dulu"));
                        }
                    } else {
                        badge.style.display = 'none';
                        btn.classList.remove('active');
                    }
                    lastCount = count;

                    // Update List Dropdown
                    if (count === 0) {
                        list.innerHTML = '<div style="padding:20px; text-align:center; color:#777;">Aman, belum ada pesanan baru.</div>';
                    } else {
                        let html = '';
                        data.orders.forEach(o => {
                            html += `
                                <a href="orders.php" class="notif-item">
                                    <div style="background:#ffeaa7; width:40px; height:40px; border-radius:50%; border:2px solid black; display:flex; align-items:center; justify-content:center; font-weight:900;">!</div>
                                    <div>
                                        <strong style="display:block;">Order #${o.id} - Rp ${parseInt(o.total).toLocaleString()}</strong>
                                        <small style="color:#555;">${o.payment_method} • Menunggu Konfirmasi</small>
                                    </div>
                                </a>
                            `;
                        });
                        list.innerHTML = html;
                    }
                });
        }

        function toggleNotif() {
            document.getElementById('notifBox').classList.toggle('show');
        }

        // Cek setiap 3 detik
        setInterval(checkNotif, 3000);
        checkNotif();

        // --- LOGIC GRAFIK ---
        Chart.defaults.font.family = "'Chakra Petch', sans-serif";
        Chart.defaults.color = '#000';

        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($lbl_sales) ?>,
                datasets: [{
                    label: 'Omzet (Rp)',
                    data: <?= json_encode($dat_sales) ?>,
                    backgroundColor: '#ff0000',
                    borderColor: '#000',
                    borderWidth: 2,
                    hoverBackgroundColor: '#ffff00'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('payChart'), {
            type: 'pie',
            data: {
                labels: <?= json_encode($lbl_pay) ?>,
                datasets: [{
                    data: <?= json_encode($dat_pay) ?>,
                    backgroundColor: ['#55efc4', '#74b9ff', '#ffeaa7'],
                    borderColor: '#000',
                    borderWidth: 2
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        new Chart(document.getElementById('topMenuChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($lbl_top) ?>,
                datasets: [{
                    label: 'Terjual (Porsi)',
                    data: <?= json_encode($dat_top) ?>,
                    backgroundColor: '#a29bfe',
                    borderColor: '#000',
                    borderWidth: 2,
                    indexAxis: 'y'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, indexAxis: 'y' }
        });
    </script>

</body>
</html>
