<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../index.php"); exit; }
include '../includes/db.php';

if (isset($_POST['add'])) {
    $pid = $_POST['pid'];
    $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + 1;
    header("Location: menu.php"); exit;
}
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Menu</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-icon"><i class="fas fa-utensils"></i></div>
                <div class="brand-text"><h2>RestoApp</h2><span>Pesan Online</span></div>
            </div>
            <nav class="nav-menu">
                <a href="menu.php" class="nav-link active"><i class="fas fa-home"></i> Menu</a>
                <a href="cart.php" class="nav-link"><i class="fas fa-shopping-bag"></i> Keranjang</a>
                <a href="orders.php" class="nav-link"><i class="fas fa-receipt"></i> Pesanan Saya</a>
                <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profil</a>
            </nav>
            <div class="user-mini-profile">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['fullname']) ?>&background=ff0099&color=fff" class="mini-avatar">
                <div class="mini-info"><h4><?= htmlspecialchars($_SESSION['user']['fullname']) ?></h4><span>Customer</span></div>
                <a href="../logout.php" class="btn-logout-mini" title="Keluar"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </aside>

        <div class="main-content">
            <div class="header-bar">
                <div class="page-title"><h1>Menu Spesial</h1><p>Pilih makanan favoritmu hari ini</p></div>
                <a href="cart.php" class="cart-btn-top">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if($cartCount>0): ?><span class="cart-badge"><?= $cartCount ?></span><?php endif; ?>
                </a>
            </div>

            <div class="menu-grid">
                <?php foreach($products as $p): ?>
                <div class="menu-card">
                    <div class="menu-img-wrapper">
                        <img src="../assets/img/<?= htmlspecialchars($p['image']) ?>" class="menu-img" onerror="this.src='https://via.placeholder.com/300x200?text=No+Img'">
                    </div>
                    <div class="menu-body">
                        <h4 class="menu-title"><?= htmlspecialchars($p['name']) ?></h4>
                        <p class="menu-desc"><?= htmlspecialchars($p['description']) ?></p>
                        <div class="menu-footer">
                            <span class="menu-price">Rp <?= number_format($p['price'],0,',','.') ?></span>
                            <form method="POST">
                                <input type="hidden" name="pid" value="<?= $p['id'] ?>">
                                <button type="submit" name="add" class="btn-add"><i class="fas fa-plus"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>