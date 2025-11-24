<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../index.php"); exit; }
include '../includes/db.php';

// Hitung Total
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $items = $pdo->query("SELECT id, price FROM products WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);
    foreach($items as $i) { $total += $i['price'] * $_SESSION['cart'][$i['id']]; }
} else {
    header("Location: menu.php"); exit;
}

// PROSES PESANAN
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $payment = $_POST['payment_method'];
    $shipping = $_POST['shipping_method'];
    $uid = $_SESSION['user']['id'];
    $date = date('Y-m-d H:i:s');
    $status = 'Menunggu'; 
    $payStatus = ($payment == 'QRIS') ? 'Lunas' : 'Belum Lunas'; 

    $sql = "INSERT INTO orders (user_id, total, order_status, payment_method, shipping, payment_status, order_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$uid, $total, $status, $payment, $shipping, $payStatus, $date])) {
        $orderID = $pdo->lastInsertId();
        $sqlItem = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmtItem = $pdo->prepare($sqlItem);
        foreach($items as $i) {
            $qty = $_SESSION['cart'][$i['id']];
            $stmtItem->execute([$orderID, $i['id'], $qty, $i['price']]);
        }
        unset($_SESSION['cart']);
        header("Location: orders.php"); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Checkout</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="topbar-container">
        <div class="brand"><div class="brand-icon"><i class="fas fa-utensils"></i></div><div class="brand-text"><h2>RestoApp</h2><span>Checkout</span></div></div>
    </nav>

    <div class="main-layout" style="grid-template-columns: 1fr; max-width: 600px;"> 
        <main class="content-area" style="padding: 30px;">
            
            <div class="page-header" style="text-align:center;">
                <h1>Konfirmasi Pesanan</h1>
                <p>Lengkapi data untuk pembayaran</p>
            </div>
            
            <form method="POST">
                
                <h4 style="margin-bottom:15px; font-size:13px; color:#888; text-transform:uppercase; font-weight:700;">Metode Pengiriman</h4>
                <div class="compact-grid">
                    <label>
                        <input type="radio" name="shipping_method" value="Ambil Sendiri" class="option-input" checked>
                        <div class="option-card">
                            <i class="fas fa-store option-icon"></i>
                            <div class="option-title">Ambil Sendiri</div>
                            <div class="option-desc">Ambil di kasir</div>
                        </div>
                    </label>
                    <label>
                        <input type="radio" name="shipping_method" value="Diantar" class="option-input">
                        <div class="option-card">
                            <i class="fas fa-motorcycle option-icon"></i>
                            <div class="option-title">Delivery</div>
                            <div class="option-desc">Diantar kurir</div>
                        </div>
                    </label>
                </div>

                <h4 style="margin:25px 0 15px 0; font-size:13px; color:#888; text-transform:uppercase; font-weight:700;">Metode Pembayaran</h4>
                <div class="compact-grid">
                    <label>
                        <input type="radio" name="payment_method" value="Cash" class="option-input" checked>
                        <div class="option-card">
                            <i class="fas fa-money-bill-wave option-icon"></i>
                            <div class="option-title">Tunai (Cash)</div>
                            <div class="option-desc">Bayar di tempat</div>
                        </div>
                    </label>
                    <label>
                        <input type="radio" name="payment_method" value="QRIS" class="option-input">
                        <div class="option-card">
                            <i class="fas fa-qrcode option-icon"></i>
                            <div class="option-title">QRIS</div>
                            <div class="option-desc">Scan barcode</div>
                        </div>
                    </label>
                </div>

                <div style="background:#f9fafb; padding:30px; border-radius:20px; margin-top:30px; text-align:center; border:1px solid #eee;">
                    <span style="color:#64748b; font-size:13px; font-weight:600; text-transform:uppercase;">Total Tagihan</span>
                    <h2 style="font-size:36px; color:#111; margin:5px 0 25px 0; font-weight:800;">
                        Rp <?= number_format($total,0,',','.') ?>
                    </h2>
                    
                    <button type="submit" style="
                        width: 100%;
                        padding: 16px;
                        background: linear-gradient(135deg, #ff0099 0%, #ff4757 100%);
                        color: white;
                        border: none;
                        border-radius: 50px;
                        font-size: 16px;
                        font-weight: 700;
                        cursor: pointer;
                        box-shadow: 0 10px 20px rgba(255, 0, 153, 0.3);
                        display: flex; align-items: center; justify-content: center; gap: 10px;
                        transition: transform 0.2s;
                    " onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        Bayar Sekarang <i class="fas fa-check-circle"></i>
                    </button>
                    
                    <a href="cart.php" style="display:block; margin-top:20px; font-size:13px; color:#888; font-weight:600; text-decoration:none;">
                        &larr; Kembali ke Keranjang
                    </a>
                </div>

            </form>
        </main>
    </div>
</body>
</html>