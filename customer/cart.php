<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../login.php"); exit; }
include '../includes/db.php';

if (isset($_GET['remove'])) { unset($_SESSION['cart'][(int)$_GET['remove']]); header("Location: cart.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_SESSION['cart'])) {
    $uid = $_SESSION['user']['id'];
    $total = $_POST['total_amount'];
    $address = htmlspecialchars($_POST['address']); // TANGKAP ALAMAT
    $shipping = $_POST['shipping'];
    $payment = $_POST['payment'];
    
    // Validasi Alamat jika Diantar
    if ($shipping == 'Diantar' && empty(trim($address))) {
        echo "<script>alert('Woy! Alamat harus diisi kalau minta diantar!'); window.location='cart.php';</script>";
        exit;
    }

    // QUERY INSERT UPDATE (Nambah Address)
    $sql = "INSERT INTO orders (user_id, total, order_status, shipping, address, payment_method, payment_status, order_date) VALUES (?, ?, 'Menunggu', ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $payStatus = ($payment == 'QRIS') ? 'Lunas' : 'Belum Lunas';
    
    if($stmt->execute([$uid, $total, $shipping, $address, $payment, $payStatus])) {
        $oid = $pdo->lastInsertId();
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
        $ids = implode(',', array_keys($_SESSION['cart']));
        $items = $pdo->query("SELECT id, price FROM products WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);
        
        foreach($items as $i) {
            $stmtItem->execute([$oid, $i['id'], $_SESSION['cart'][$i['id']], $i['price']]);
        }
        unset($_SESSION['cart']);
        
        if ($payment == 'QRIS') { header("Location: payment.php?id=$oid"); } 
        else { header("Location: orders.php"); }
        exit;
    }
}

$cartItems = []; $total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = implode(',', array_keys($_SESSION['cart']));
    $cartItems = $pdo->query("SELECT * FROM products WHERE id IN ($ids)")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Keranjang - Warung Bu Yeti</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* STYLE KHUSUS ALAMAT */
        .address-box { margin-bottom: 20px; }
        .address-input {
            width: 100%; padding: 12px; border: 3px solid black;
            font-family: 'Chakra Petch'; font-weight: bold; font-size: 14px;
            outline: none; resize: none; background: #fff;
        }
        .address-input:focus { background: var(--yellow); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h2>CUSTOMER</h2><span></span></div>
        <nav class="nav-menu">
            <a href="menu.php" class="nav-link"><i class="fas fa-utensils"></i> MENU KAMI</a>
            <a href="cart.php" class="nav-link active"><i class="fas fa-shopping-cart"></i> KERANJANG</a>
            <a href="orders.php" class="nav-link"><i class="fas fa-receipt"></i> PESANAN</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user-astronaut"></i> PROFIL SAYA</a>
            <a href="../logout.php" class="nav-link" style="margin-top:auto; color:red; border-color:red;"><i class="fas fa-sign-out-alt"></i> KELUAR</a>
        </nav>
    </aside>

    <main class="main-content">
        <div class="header-bar"><div class="page-title"><h1>KERANJANG BELANJA</h1></div></div>

        <?php if(empty($cartItems)): ?>
            <div class="brutal-box" style="text-align:center; padding:50px;">
                <h2 style="font-family:'Archivo Black'">KERANJANG KOSONG!</h2>
                <a href="menu.php" class="btn-pay" style="display:inline-block; width:auto; padding:10px 20px; margin-top:20px; text-decoration:none; color:white;">KE MENU</a>
            </div>
        <?php else: ?>
            <form method="POST" style="display:grid; grid-template-columns: 2fr 1fr; gap:30px;">
                <div class="brutal-box">
                    <div class="box-title">LIST PESANAN</div>
                    <?php foreach($cartItems as $item): 
                        $qty = $_SESSION['cart'][$item['id']]; $sub = $item['price']*$qty; $total+=$sub;
                    ?>
                    <div class="cart-item">
                        <div style="display:flex; gap:15px; align-items:center;">
                            <img src="../assets/img/<?= $item['image'] ?>" style="width:60px; height:60px; border:2px solid black; object-fit:cover;">
                            <div>
                                <div style="font-weight:800; font-size:16px;"><?= htmlspecialchars($item['name']) ?></div>
                                <div style="font-size:14px; color:#555;"><?= $qty ?> x Rp <?= number_format($item['price'],0,',','.') ?></div>
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-weight:900; font-size:16px;">Rp <?= number_format($sub,0,',','.') ?></div>
                            <a href="?remove=<?= $item['id'] ?>" style="color:red; font-weight:bold; text-decoration:underline; font-size:12px;">HAPUS</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <input type="hidden" name="total_amount" value="<?= $total ?>">
                </div>

                <div class="brutal-box">
                    <div class="box-title">CHECKOUT</div>
                    
                    <div class="address-box">
                        <strong style="display:block; margin-bottom:10px;">ALAMAT LENGKAP (Opsional jika ambil sendiri)</strong>
                        <textarea name="address" rows="3" class="address-input" placeholder="Contoh: Jl. Mawar No. 10, Depan Indomaret..."></textarea>
                    </div>

                    <strong style="display:block; margin-bottom:10px;">PENGIRIMAN</strong>
                    <div class="radio-group">
                        <label class="radio-option"><input type="radio" name="shipping" value="Ambil Sendiri" checked> AMBIL</label>
                        <label class="radio-option"><input type="radio" name="shipping" value="Diantar"> ANTAR</label>
                    </div>

                    <strong style="display:block; margin-bottom:10px; margin-top:20px;">PEMBAYARAN</strong>
                    <div class="radio-group">
                        <label class="radio-option"><input type="radio" name="payment" value="Cash" checked> CASH</label>
                        <label class="radio-option"><input type="radio" name="payment" value="QRIS"> QRIS</label>
                    </div>

                    <div style="border-top:4px solid black; padding-top:20px; margin-top:20px; display:flex; justify-content:space-between; font-size:20px; font-weight:900;">
                        <span>TOTAL</span><span>Rp <?= number_format($total,0,',','.') ?></span>
                    </div>
                    <button type="submit" class="btn-pay">BAYAR SEKARANG</button>
                </div>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>