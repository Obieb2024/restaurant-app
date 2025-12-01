<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../login.php"); exit; }
include '../includes/db.php';

// Logic Tambah Keranjang
if (isset($_POST['add'])) {
    $pid = $_POST['pid'];
    $_SESSION['cart'][$pid] = ($_SESSION['cart'][$pid] ?? 0) + 1;
    header("Location: menu.php"); exit;
}

// 1. KATEGORI DITENTUKAN MANUAL (Supaya Tombolnya Pasti Muncul)
$categories = ['Makanan', 'Minuman', 'Snack'];

// 2. Logic Filter & Pencarian
$currentCat = $_GET['cat'] ?? 'all';
$search = $_GET['q'] ?? '';

$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

// LOGIKA SAPU JAGAT (SMART FILTER)
if ($currentCat != 'all') {
    if ($currentCat == 'Makanan') {
        // Ambil semua variasi Makanan
        $sql .= " AND (category = 'Makanan' OR category = 'Makanan Berat')";
    } 
    elseif ($currentCat == 'Minuman') {
        // Ambil semua variasi Minuman
        $sql .= " AND (category = 'Minuman' OR category = 'Minuman Segar')";
    } 
    elseif ($currentCat == 'Snack') {
        // Ambil Snack, Cemilan, Cemilan / Snack, ATAU YANG KOSONG
        $sql .= " AND (category LIKE '%Snack%' OR category LIKE '%Cemilan%' OR category = '' OR category IS NULL)";
    } 
    else {
        // Fallback untuk kategori lain jika ada
        $sql .= " AND category = ?";
        $params[] = $currentCat;
    }
}

if (!empty($search)) {
    $sql .= " AND name LIKE ?";
    $params[] = "%$search%";
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Menu - Warung Bu Yeti</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* CSS KHUSUS FILTER */
        .filter-bar { display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; }
        .filter-btn {
            padding: 10px 20px; border: 3px solid black;
            background: white; color: black; font-weight: 900;
            text-transform: uppercase; cursor: pointer;
            text-decoration: none; transition: 0.2s;
        }
        .filter-btn:hover, .filter-btn.active {
            background: var(--yellow); box-shadow: 4px 4px 0 black; transform: translate(-2px, -2px);
        }
        .filter-btn.active { background: var(--red-primary); color: var(--yellow); }

        .search-box { width: 100%; display: flex; margin-bottom: 20px; }
        .search-input {
            flex: 1; padding: 15px; border: 3px solid black;
            font-family: 'Chakra Petch'; font-size: 16px; font-weight: bold; outline: none;
        }
        .search-btn {
            padding: 0 25px; background: black; color: white;
            border: 3px solid black; cursor: pointer; font-size: 18px;
        }
        .search-btn:hover { background: var(--red-primary); }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h2>CUSTOMER</h2><span></span></div>
        <nav class="nav-menu">
            <a href="menu.php" class="nav-link active"><i class="fas fa-utensils"></i> MENU KAMI</a>
            <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> KERANJANG</a>
            <a href="orders.php" class="nav-link"><i class="fas fa-receipt"></i> PESANAN</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user-astronaut"></i> PROFIL SAYA</a>
        </nav>
        <div class="user-mini">
            <img src="../assets/img/users/<?= $_SESSION['user']['photo'] ?? 'default.png' ?>" 
                 onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($_SESSION['user']['fullname']) ?>&background=random'"
                 class="mini-img">
            <div style="font-weight:800; text-transform: uppercase; font-size: 14px;">
                <?= explode(' ',$_SESSION['user']['fullname'])[0] ?>
            </div>
            <a href="../logout.php" class="btn-out"><i class="fas fa-power-off"></i></a>
        </div>
    </aside>

    <main class="main-content">
        <div class="header-bar">
            <div class="page-title">
                <h1>MENU PILIHAN</h1>
                <p>Mau makan apa hari ini?</p>
            </div>
            <a href="cart.php" class="cart-head">
                <i class="fas fa-shopping-bag"></i>
                <?php if($cartCount>0): ?><div class="cart-badge"><?= $cartCount ?></div><?php endif; ?>
            </a>
        </div>

        <form method="GET" class="search-box">
            <input type="hidden" name="cat" value="<?= htmlspecialchars($currentCat) ?>">
            <input type="text" name="q" class="search-input" placeholder="Cari menu... (Misal: Ayam, Es Jeruk)" value="<?= htmlspecialchars($search) ?>">
            <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
        </form>

        <div class="filter-bar">
            <a href="?cat=all&q=<?= $search ?>" class="filter-btn <?= $currentCat=='all'?'active':'' ?>">SEMUA</a>
            
            <?php foreach($categories as $cat): ?>
                <a href="?cat=<?= $cat ?>&q=<?= $search ?>" 
                   class="filter-btn <?= $currentCat==$cat ? 'active' : '' ?>">
                   <?= strtoupper($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="menu-grid">
            <?php if(count($products) > 0): ?>
                <?php foreach($products as $p): ?>
                <div class="menu-card">
                    <div class="menu-img-box">
                        <img src="../assets/img/<?= htmlspecialchars($p['image']) ?>" class="menu-img" 
                             onerror="this.src='https://via.placeholder.com/300x200?text=NO+IMG'">
                    </div>
                    <div class="menu-info">
                        <div style="display:flex; justify-content:space-between; align-items:start;">
                            <div class="menu-name"><?= htmlspecialchars($p['name']) ?></div>
                            <?php 
                                $label = $p['category'];
                                if(empty($label)) $label = 'SNACK'; // Default kalau kosong
                                if($label == 'Makanan') $label = 'MAKANAN';
                                if($label == 'Minuman') $label = 'MINUMAN';
                            ?>
                            <span style="background:#eee; border:1px solid #000; font-size:10px; padding:2px 5px; height:fit-content; font-weight:bold;">
                                <?= strtoupper($label) ?>
                            </span>
                        </div>
                        <div class="menu-desc"><?= htmlspecialchars($p['description']) ?></div>
                        
                        <div class="menu-action">
                            <div class="menu-price">Rp <?= number_format($p['price'],0,',','.') ?></div>
                            <form method="POST">
                                <input type="hidden" name="pid" value="<?= $p['id'] ?>">
                                <button type="submit" name="add" class="btn-add"><i class="fas fa-plus"></i></button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align:center; padding:40px; border:4px dashed #000; background:#fff;">
                    <h3 style="font-family:'Archivo Black';">MENU TIDAK DITEMUKAN :(</h3>
                    <p>Kategori <b><?= htmlspecialchars($currentCat) ?></b> belum ada menunya nih.</p>
                    <a href="?cat=all" class="filter-btn" style="display:inline-block; margin-top:10px;">LIHAT SEMUA MENU</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>