<?php
session_start();
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header("Location: ../login.php"); exit;
}
include '../includes/db.php';

// --- LOGIC DELETE ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $prev = $pdo->prepare("SELECT image FROM products WHERE id=?");
    $prev->execute([$id]);
    $img = $prev->fetchColumn();
    if($img && $img != 'default.jpg' && file_exists("../assets/img/$img")) { unlink("../assets/img/$img"); }
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: products.php"); exit;
}

// --- LOGIC SAVE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $cat = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];
    $id = $_POST['id'] ?? null;
    
    $image = $_POST['old_image'] ?? 'default.jpg';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0){
        $target_dir = "../assets/img/";
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fname = time() . "_" . uniqid() . "." . $ext;
        if(move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $fname)){
            $image = $fname;
        }
    }

    if ($id) {
        $sql = "UPDATE products SET name=?, category=?, price=?, stock=?, description=?, image=? WHERE id=?";
        $params = [$name, $cat, $price, $stock, $desc, $image, $id];
    } else {
        $sql = "INSERT INTO products (name, category, price, stock, description, image) VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$name, $cat, $price, $stock, $desc, $image];
    }
    $pdo->prepare($sql)->execute($params);
    header("Location: products.php"); exit;
}

$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Produk - Admin Mode</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <nav class="admin-sidebar">
        <div class="admin-logo">ADMIN MODE</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link active"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            <li><a href="users.php" class="sidebar-link"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;"><i class="fas fa-sign-out-alt"></i> KELUAR</a></li>
        </ul>
    </nav>

    <main class="main-content">
        <div class="page-header">
            <div><h1>KELOLA MENU</h1><p>Tambah atau edit menu makanan.</p></div>
            <a href="products.php" class="btn btn-secondary" style="font-size:12px; padding:10px 15px;"><i class="fas fa-plus"></i> RESET FORM</a>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px; align-items: start;">
            
            <div class="card-form">
                <h3 style="margin-bottom:20px; border-bottom:4px solid black; padding-bottom:10px; text-transform:uppercase;">
                    <?= $editData ? 'EDIT: ' . htmlspecialchars($editData['name']) : 'TAMBAH MENU BARU' ?>
                </h3>
                
                <form method="POST" enctype="multipart/form-data">
                    <?php if($editData): ?>
                        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                        <input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nama Menu</label>
                        <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editData['name']??'') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category" class="form-control">
                            <option value="Makanan" <?= ($editData['category']??'')=='Makanan'?'selected':'' ?>>Makanan Berat</option>
                            <option value="Minuman" <?= ($editData['category']??'')=='Minuman'?'selected':'' ?>>Minuman Segar</option>
                            <option value="Snack" <?= ($editData['category']??'')=='Snack'?'selected':'' ?>>Cemilan / Snack</option>
                        </select>
                    </div>

                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label>Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" value="<?= $editData['price']??'' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stock" class="form-control" value="<?= $editData['stock']??'100' ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($editData['description']??'') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" name="image" class="form-control" style="padding:10px;" accept="image/*">
                    </div>

                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">
                        <i class="fas fa-save"></i> SIMPAN DATA
                    </button>
                </form>
            </div>

            <div class="card-table">
                <table>
                    <thead>
                        <tr>
                            <th width="70">FOTO</th>
                            <th>INFO MENU</th>
                            <th>HARGA & STOK</th>
                            <th width="100">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(count($products) > 0): ?>
                            <?php foreach($products as $p): ?>
                            <tr>
                                <td>
                                    <img src="../assets/img/<?= $p['image'] ?>" 
                                         onerror="this.src='https://via.placeholder.com/60x60?text=IMG'"
                                         style="width:60px; height:60px; object-fit:cover; border:2px solid black;">
                                </td>
                                <td>
                                    <strong style="font-size:16px;"><?= htmlspecialchars($p['name']) ?></strong><br>
                                    
                                    <?php 
                                        $catLabel = $p['category'];
                                        if(empty($catLabel)) $catLabel = 'Cemilan / Snack'; // Default kalau kosong
                                        if($catLabel == 'Makanan') $catLabel = 'Makanan Berat';
                                        if($catLabel == 'Minuman') $catLabel = 'Minuman Segar';
                                        if($catLabel == 'Snack')   $catLabel = 'Cemilan / Snack';
                                    ?>
                                    
                                    <span class="badge badge-yellow" style="font-size:10px; padding:2px 6px;">
                                        <?= $catLabel ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:900; font-size:15px;">Rp <?= number_format($p['price'],0,',','.') ?></div>
                                    <div style="font-size:12px; margin-top:5px;">Stok: <b><?= $p['stock'] ?></b></div>
                                </td>
                                <td>
                                    <div style="display:flex; gap:8px;">
                                        <a href="?edit=<?= $p['id'] ?>" class="btn-action btn-edit"><i class="fas fa-pen"></i></a>
                                        <a href="?delete=<?= $p['id'] ?>" class="btn-action btn-delete" onclick="return confirm('Hapus menu ini?');"><i class="fas fa-trash"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center; padding:30px;">Belum ada menu.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>