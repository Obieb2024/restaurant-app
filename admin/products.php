<?php
session_start();
// 1. Cek Login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}
include '../includes/db.php';

// --- LOGIKA 1: MENANGANI FORM SUBMIT (TAMBAH & EDIT) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id = $_POST['id'] ?? null; // Jika ada ID, berarti mode EDIT
    $name = $_POST['name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $description = $_POST['description'];
    
    // Handle Upload Gambar
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../assets/img/";
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $file_name)) {
            $image = $file_name;
        }
    }

    if ($id) {
        // === MODE UPDATE ===
        if ($image) {
             // Update data + gambar baru
             $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, description=?, image=? WHERE id=?");
             $stmt->execute([$name, $category, $price, $stock, $description, $image, $id]);
        } else {
             // Update data saja, gambar tetap yang lama
             $stmt = $pdo->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, description=? WHERE id=?");
             $stmt->execute([$name, $category, $price, $stock, $description, $id]);
        }
    } else {
        // === MODE INSERT (TAMBAH BARU) ===
        $imgToSave = $image ?? 'default.jpg'; // Gambar default jika tidak upload
        $stmt = $pdo->prepare("INSERT INTO products (name, category, price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $category, $price, $stock, $description, $imgToSave]);
    }
    
    // Redirect kembali ke halaman utama produk agar data ter-refresh
    header("Location: products.php"); 
    exit;
}

// --- LOGIKA 2: MENANGANI HAPUS DATA ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    header("Location: products.php");
    exit;
}

// --- LOGIKA 3: PERSIAPAN TAMPILAN (VIEW) ---
$view = $_GET['view'] ?? 'list'; // Default tampilan adalah 'list'
$formData = [
    'id' => '', 'name' => '', 'category' => 'Makanan', 
    'price' => '', 'stock' => '', 'description' => '', 'image' => ''
];
$formTitle = 'Tambah Produk';

// Jika mode Edit, ambil data lama dari database
if ($view === 'edit' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($data) {
        $formData = $data;
        $formTitle = 'Edit Produk';
    }
} elseif ($view === 'add') {
    $formTitle = 'Tambah Produk';
}

// Ambil semua data produk untuk ditampilkan di List View
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin - Produk</title>
    <link href="../assets/css/admin.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* CSS Khusus Halaman Produk */
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Tombol Tambah */
        .btn-add {
            background: linear-gradient(135deg, #7b2ff7, #f107a3);
            color: white; padding: 12px 25px; border-radius: 12px; text-decoration: none; font-weight: 600;
            box-shadow: 0 4px 15px rgba(123, 47, 247, 0.3); transition: transform 0.2s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-add:hover { transform: translateY(-2px); }

        /* Style Form */
        .form-container { background: white; padding: 30px; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 800px; margin-top: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #4a5568; }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-family: inherit; box-sizing: border-box;
        }
        .form-group input:focus { outline: none; border-color: #7b2ff7; }
        
        .btn-submit { background: linear-gradient(135deg, #7b2ff7, #f107a3); color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-cancel { background: #edf2f7; color: #4a5568; text-decoration: none; padding: 12px 20px; border-radius: 8px; font-weight: 600; margin-right: 10px; }
        
        /* Style Grid Produk */
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 25px; }
        .product-card { background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); transition: 0.3s; display: flex; flex-direction: column; position: relative; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); }
        .card-img-top { width: 100%; height: 180px; object-fit: cover; background: #f0f0f0; }
        .card-body { padding: 20px; flex: 1; display: flex; flex-direction: column; }
        .card-title { font-size: 18px; font-weight: 700; color: #2d3748; margin: 0 0 5px 0; }
        .card-price { font-size: 20px; font-weight: 700; color: #10b981; margin-bottom: 10px; }
        .card-actions { margin-top: auto; display: flex; gap: 10px; border-top: 1px solid #f7fafc; padding-top: 15px; }
        .btn-action { flex: 1; padding: 8px; text-align: center; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; transition: 0.2s; }
        .btn-edit { background: #ebf8ff; color: #3182ce; } .btn-delete { background: #fff5f5; color: #e53e3e; }
        .stock-badge { position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.9); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; color: #333; }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <nav class="admin-sidebar">
        <div class="logo-area"><i class="fas fa-utensils fa-2x" style="color: #ff4757;"></i> <h2>RestoApp</h2></div>
        <ul>
            <li><a href="dashboard.php"><i class="fas fa-chart-pie"></i> Dashboard</a></li>
            <li><a href="products.php" class="active active-purple"><i class="fas fa-box"></i> Produk</a></li>
            <li><a href="orders.php"><i class="fas fa-receipt"></i> Pesanan</a></li>
            <li><a href="customer.php"><i class="fas fa-users"></i> Pelanggan</a></li>
        </ul>
    </nav>

    <main>
        <?php if ($view === 'add' || $view === 'edit'): ?>
            <div class="topbar">
                <div class="page-header">
                    <h1><?= $formTitle ?></h1>
                    <p>Silakan isi informasi produk di bawah ini</p>
                </div>
            </div>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($formData['id']) ?>">

                    <div class="form-group">
                        <label>Nama Produk</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($formData['name']) ?>" required placeholder="Contoh: Ayam Bakar Madu">
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category">
                            <option value="Makanan" <?= $formData['category']=='Makanan'?'selected':'' ?>>Makanan</option>
                            <option value="Minuman" <?= $formData['category']=='Minuman'?'selected':'' ?>>Minuman</option>
                            <option value="Snack" <?= $formData['category']=='Snack'?'selected':'' ?>>Snack</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div class="form-group">
                            <label>Harga (Rp)</label>
                            <input type="number" name="price" value="<?= htmlspecialchars($formData['price']) ?>" required placeholder="0">
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stock" value="<?= htmlspecialchars($formData['stock']) ?>" required placeholder="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" rows="4" placeholder="Deskripsi singkat..."><?= htmlspecialchars($formData['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Foto Produk</label>
                        <input type="file" name="image" accept="image/*">
                        <?php if($formData['image']): ?>
                            <div style="margin-top:10px; display:flex; align-items:center; gap:10px;">
                                <img src="../assets/img/<?= htmlspecialchars($formData['image']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:8px; border:1px solid #eee;">
                                <span style="font-size:12px; color:#888;">Gambar saat ini (Biarkan kosong jika tidak diganti)</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 30px; border-top:1px solid #eee; padding-top:20px;">
                        <a href="products.php" class="btn-cancel">Batal</a>
                        <button type="submit" class="btn-submit">Simpan</button>
                    </div>
                </form>
            </div>

        <?php else: ?>
            <div class="header-actions">
                <div class="page-header">
                    <h1>Daftar Produk</h1>
                    <p style="margin:0; color:#6b7280;">Kelola menu makanan dan minuman</p>
                </div>
                <a href="?view=add" class="btn-add"><i class="fas fa-plus"></i> Tambah Produk</a>
            </div>

            <div class="product-grid">
                <?php if(count($products) > 0): ?>
                    <?php foreach ($products as $p): ?>
                    <div class="product-card">
                        <div class="stock-badge">Stok: <?= $p['stock'] ?></div>
                        <img src="../assets/img/<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="card-img-top" onerror="this.src='https://via.placeholder.com/300x200?text=No+Image'">
                        
                        <div class="card-body">
                            <div style="font-size:12px; color:#a0aec0; text-transform:uppercase; margin-bottom:5px;"><?= htmlspecialchars($p['category']) ?></div>
                            <h3 class="card-title"><?= htmlspecialchars($p['name']) ?></h3>
                            <div class="card-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></div>
                            
                            <div class="card-actions">
                                <a href="?view=edit&id=<?= $p['id'] ?>" class="btn-action btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                <a href="?action=delete&id=<?= $p['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus produk ini?');"><i class="fas fa-trash"></i> Hapus</a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column:1/-1; text-align:center; padding:40px; background:#fff; border-radius:16px; color:#aaa;">
                        <i class="fas fa-box-open fa-3x" style="margin-bottom:15px; opacity:0.5;"></i><br>
                        Belum ada produk. Silakan tambah baru.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>