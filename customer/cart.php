<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../index.php"); exit; }
include '../includes/db.php';

// Hapus Item
if (isset($_GET['remove'])) { unset($_SESSION['cart'][(int)$_GET['remove']]); header("Location: cart.php"); exit; }

// Proses Order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['cart'])) {
    $shipping = $_POST['shipping']; // Ambil Sendiri / Delivery
    $payment = $_POST['payment'];   // Cash / QRIS
    $uid = $_SESSION['user']['id'];
    $total = $_POST['total_amount'];
    $payStatus = ($payment == 'QRIS') ? 'Lunas' : 'Belum Lunas';
    
    $sql = "INSERT INTO orders (user_id, total, order_status, shipping, payment_method, payment_status, order_date) VALUES (?, ?, 'Menunggu', ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    
    if($stmt->execute([$uid, $total, $shipping, $payment, $payStatus])) {
        $oid = $pdo->lastInsertId();
        $sqlItem = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmtItem = $pdo->prepare($sqlItem);
        
        $ids = implode(',', array_keys($_SESSION['cart']));
        $items = $pdo->query("SELECT id, price FROM products WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($items as $i) {
            $qty = $_SESSION['cart'][$i['id']];
            $stmtItem->execute([$oid, $i['id'], $qty, $i['price']]);
        }
        unset($_SESSION['cart']);
        header("Location: orders.php"); exit;
    }
}

// Ambil Cart
$cartItems = []; $total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $cartItems = $pdo->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);
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
    <div class="app-container">
        <aside class="sidebar">
            <div class="brand"><div class="brand-icon"><i class="fas fa-utensils"></i></div><div class="brand-text"><h2>RestoApp</h2><span>Pesan Online</span></div></div>
            <nav class="nav-menu">
                <a href="menu.php" class="nav-link"><i class="fas fa-home"></i> Menu</a>
                <a href="cart.php" class="nav-link active"><i class="fas fa-shopping-bag"></i> Keranjang</a>
                <a href="orders.php" class="nav-link"><i class="fas fa-receipt"></i> Pesanan Saya</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            </nav>
            <div class="user-mini-profile">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['fullname']) ?>&background=ff0099&color=fff" class="mini-avatar">
                <div class="mini-info"><h4><?= htmlspecialchars($_SESSION['user']['fullname']) ?></h4><span>Customer</span></div>
                <a href="../logout.php" class="btn-logout-mini"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </aside>

        <main class="main-content">
            <div class="header-bar">
                <div class="page-title"><h1>Checkout</h1><p>Selesaikan pesanan Anda</p></div>
            </div>

            <?php if(empty($cartItems)): ?>
                <div style="text-align:center; padding:60px;">
                    <i class="fas fa-shopping-basket" style="font-size:60px; color:#e5e7eb; margin-bottom:20px;"></i>
                    <h3 style="margin-bottom:10px;">Keranjang Kosong</h3>
                    <a href="menu.php" style="color:#ff0099; font-weight:700;">Mulai Belanja &rarr;</a>
                </div>
            <?php else: ?>
                
                <form method="POST" style="display:grid; gap:30px;">
                    <div class="summary-card">
                        <span class="section-label">Pesanan Anda</span>
                        <?php foreach($cartItems as $item): 
                            $qty = $_SESSION['cart'][$item['id']]; $sub = $item['price']*$qty; $total+=$sub;
                        ?>
                        <div style="display:flex; justify-content:space-between; padding:15px 0; border-bottom:1px solid #eee;">
                            <div style="display:flex; gap:15px; align-items:center;">
                                <img src="../assets/img/<?= $item['image'] ?>" style="width:50px; height:50px; border-radius:10px; object-fit:cover;">
                                <div>
                                    <h4 style="margin:0; font-size:14px;"><?= htmlspecialchars($item['name']) ?></h4>
                                    <span style="font-size:12px; color:#888;"><?= $qty ?> x Rp <?= number_format($item['price'],0,',','.') ?></span>
                                </div>
                            </div>
                            <div style="font-weight:700;">Rp <?= number_format($sub,0,',','.') ?></div>
                            <a href="?remove=<?= $item['id'] ?>" style="color:#ef4444; font-size:12px;"><i class="fas fa-trash"></i></a>
                        </div>
                        <?php endforeach; ?>
                        <input type="hidden" name="total_amount" value="<?= $total ?>">
                    </div>

                    <div>
                        <span class="section-label">Metode Pengiriman</span>
                        <div class="option-grid">
                            <label class="option-card">
                                <input type="radio" name="shipping" value="Ambil Sendiri" checked>
                                <div class="option-label">
                                    <div class="option-icon"><i class="fas fa-walking"></i></div>
                                    <div class="option-title">Ambil Sendiri</div>
                                    <div class="option-desc">Ambil pesanan di kasir</div>
                                </div>
                            </label>
                            <label class="option-card">
                                <input type="radio" name="shipping" value="Diantar">
                                <div class="option-label">
                                    <div class="option-icon"><i class="fas fa-motorcycle"></i></div>
                                    <div class="option-title">Delivery</div>
                                    <div class="option-desc">Diantar ke lokasi Anda</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <span class="section-label">Metode Pembayaran</span>
                        <div class="option-grid">
                            <label class="option-card">
                                <input type="radio" name="payment" value="Cash" checked>
                                <div class="option-label">
                                    <div class="option-icon"><i class="fas fa-money-bill-wave"></i></div>
                                    <div class="option-title">Tunai (Cash)</div>
                                    <div class="option-desc">Bayar saat terima</div>
                                </div>
                            </label>
                            <label class="option-card">
                                <input type="radio" name="payment" value="QRIS">
                                <div class="option-label">
                                    <div class="option-icon"><i class="fas fa-qrcode"></i></div>
                                    <div class="option-title">QRIS</div>
                                    <div class="option-desc">Scan cepat & praktis</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="summary-card" style="margin-bottom:0;">
                        <div class="total-row">
                            <span>Total Pembayaran</span>
                            <span style="color:#ff0099;">Rp <?= number_format($total,0,',','.') ?></span>
                        </div>
                        <button type="submit" class="btn-pay">Buat Pesanan Sekarang</button>
                    </div>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>