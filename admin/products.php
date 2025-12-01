<?php
session_start();

// Cek apakah user sudah login dan apakah role-nya admin atau super_admin
// Jika tidak memenuhi, user diarahkan kembali ke halaman login
if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'super_admin')) {
    header("Location: ../login.php"); 
    exit;
}

include '../includes/db.php'; // Koneksi ke database


// =============================
//         LOGIC DELETE
// =============================
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Ambil nama file gambar lama untuk dihapus dari folder
    $prev = $pdo->prepare("SELECT image FROM products WHERE id=?");
    $prev->execute([$id]);
    $img = $prev->fetchColumn();

    // Jika file gambar ada dan bukan default.jpg → hapus file dari folder
    if ($img && $img != 'default.jpg' && file_exists("../assets/img/$img")) {
        unlink("../assets/img/$img");
    }

    // Hapus data produk dari database
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    // Refresh halaman supaya list produk ter-update
    header("Location: products.php");
    exit;
}


// =============================
//         LOGIC SAVE
// =============================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Data inputan dari form
    $name  = $_POST['name'];
    $cat   = $_POST['category'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc  = $_POST['description'];

    // Jika edit, id akan dikirim melalui hidden input
    $id = $_POST['id'] ?? null;

    // Jika gambar tidak diganti, pakai gambar lama
    $image = $_POST['old_image'] ?? 'default.jpg';

    // Jika user upload gambar baru
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        // Lokasi folder penyimpanan gambar
        $target_dir = "../assets/img/";

        // Ambil ekstensi file (.jpg, .png, dll)
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        // Buat nama file baru yang unik
        $fname = time() . "_" . uniqid() . "." . $ext;

        // Upload file ke folder
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $fname)) {
            $image = $fname; // Set gambar baru
        }
    }

    // Jika sedang edit data (UPDATE)
    if ($id) {
        $sql = "UPDATE products 
                SET name=?, category=?, price=?, stock=?, description=?, image=? 
                WHERE id=?";
        $params = [$name, $cat, $price, $stock, $desc, $image, $id];

    // Jika tambah produk baru (INSERT)
    } else {
        $sql = "INSERT INTO products (name, category, price, stock, description, image) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$name, $cat, $price, $stock, $desc, $image];
    }

    // Eksekusi query
    $pdo->prepare($sql)->execute($params);

    // Redirect agar tidak double submit & refresh data
    header("Location: products.php");
    exit;
}


// =============================
//       LOGIC EDIT (ambil data)
// =============================
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC); // Data produk yang mau diedit
}


// Ambil semua produk untuk ditampilkan di tabel
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Sidebar Admin -->
    <nav class="admin-sidebar">
        <div class="admin-logo">ADMIN</div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php" class="sidebar-link"><i class="fas fa-fire"></i> DASHBOARD</a></li>
            <li><a href="products.php" class="sidebar-link active"><i class="fas fa-hamburger"></i> PRODUK</a></li>
            <li><a href="orders.php" class="sidebar-link"><i class="fas fa-receipt"></i> PESANAN</a></li>
            <li><a href="customer.php" class="sidebar-link"><i class="fas fa-users"></i> PELANGGAN</a></li>
            <li><a href="reports.php" class="sidebar-link"><i class="fas fa-file-invoice-dollar"></i> LAPORAN</a></li>
            <li><a href="users.php" class="sidebar-link"><i class="fas fa-user-shield"></i> ADMIN</a></li>
            <li><a href="logout.php" class="sidebar-link" style="background:#000; color:#fff;">
                <i class="fas fa-sign-out-alt"></i> KELUAR
            </a></li>
        </ul>
    </nav>


    <!-- Konten Utama -->
    <main class="main-content">

        <!-- Header Halaman -->
        <div class="page-header">
            <div>
                <h1>KELOLA MENU</h1>
                <p>Tambah atau edit menu makanan.</p>
            </div>

            <!-- Reset Form kembali ke mode tambah -->
            <a href="products.php" class="btn btn-secondary" style="font-size:12px; padding:10px 15px;">
                <i class="fas fa-plus"></i> RESET FORM
            </a>
        </div>


        <div style="display:grid; grid-template-columns: 1fr 2fr; gap:30px; align-items: start;">


            <!-- ===========================
                     FORM INPUT PRODUK
            ============================ -->
            <div class="card-form">
                <h3 style="margin-bottom:20px; border-bottom:4px solid black; padding-bottom:10px; text-transform:uppercase;">
                    <?= $editData ? 'EDIT: ' . htmlspecialchars($editData['name']) : 'TAMBAH MENU BARU' ?>
                </h3>

                <form method="POST" enctype="multipart/form-data">

                    <?php if ($editData): ?>
                        <!-- Hidden input untuk edit -->
                        <input type="hidden" name="id" value="<?= $editData['id'] ?>">
                        <input type="hidden" name="old_image" value="<?= $editData['image'] ?>">
                    <?php endif; ?>

                    <!-- Nama Produk -->
                    <div class="form-group">
                        <label>Nama Menu</label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($editData['name'] ?? '') ?>" required>
                    </div>

                    <!-- Kategori -->
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category" class="form-control">
                            <option value="Makanan" <?= ($editData['category'] ?? '') == 'Makanan' ? 'selected':'' ?>>
                                Makanan Berat
                            </option>
                            <option value="Minuman" <?= ($editData['category'] ?? '') == 'Minuman' ? 'selected':'' ?>>
                                Minuman Segar
                            </option>
                            <option value="Snack" <?= ($editData['category'] ?? '') == 'Snack' ? 'selected':'' ?>>
                                Cemilan / Snack
                            </option>
                        </select>
                    </div>

                    <!-- Harga dan Stok -->
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <div class="form-group">
                            <label>Harga (Rp)</label>
                            <input type="number" name="price" class="form-control" 
                                   value="<?= $editData['price'] ?? '' ?>" required>
                        </div>

                        <div class="form-group">
                            <label>Stok</label>
                            <input type="number" name="stock" class="form-control" 
                                   value="<?= $editData['stock'] ?? '100' ?>" required>
                        </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3">
                            <?= htmlspecialchars($editData['description'] ?? '') ?>
                        </textarea>
                    </div>

                    <!-- Upload Foto -->
                    <div class="form-group">
                        <label>Foto</label>
                        <input type="file" name="image" class="form-control" 
                               style="padding:10px;" accept="image/*">
                    </div>

                    <!-- Tombol Simpan -->
                    <button type="submit" class="btn btn-primary" style="width:100%; margin-top:10px;">
                        <i class="fas fa-save"></i> SIMPAN DATA
                    </button>
                </form>
            </div>



            <!-- ===========================
                     TABLE LIST PRODUK
            ============================ -->
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
                        <?php if (count($products) > 0): ?>
                            <?php foreach($products as $p): ?>
                                <tr>

                                    <!-- Foto Produk -->
                                    <td>
                                        <img src="../assets/img/<?= $p['image'] ?>" 
                                             onerror="this.src='https://via.placeholder.com/60x60?text=IMG'"
                                             style="width:60px; height:60px; object-fit:cover; border:2px solid black;">
                                    </td>

                                    <!-- Nama dan Kategori -->
                                    <td>
                                        <strong style="font-size:16px;">
                                            <?= htmlspecialchars($p['name']) ?>
                                        </strong><br>

                                        <?php 
                                            // Konversi kategori untuk tampilan
                                            $catLabel = $p['category'];
                                            if ($catLabel == 'Makanan') $catLabel = 'Makanan Berat';
                                            if ($catLabel == 'Minuman') $catLabel = 'Minuman Segar';
                                            if ($catLabel == 'Snack')   $catLabel = 'Cemilan / Snack';
                                        ?>

                                        <span class="badge badge-yellow" style="font-size:10px; padding:2px 6px;">
                                            <?= $catLabel ?>
                                        </span>
                                    </td>

                                    <!-- Harga & Stok -->
                                    <td>
                                        <div style="font-weight:900; font-size:15px;">
                                            Rp <?= number_format($p['price'],0,',','.') ?>
                                        </div>

                                        <div style="font-size:12px; margin-top:5px;">
                                            Stok: <b><?= $p['stock'] ?></b>
                                        </div>
                                    </td>

                                    <!-- Tombol Edit & Delete -->
                                    <td>
                                        <div style="display:flex; gap:8px;">
                                            <a href="?edit=<?= $p['id'] ?>" class="btn-action btn-edit">
                                                <i class="fas fa-pen"></i>
                                            </a>

                                            <a href="?delete=<?= $p['id'] ?>" 
                                               class="btn-action btn-delete"
                                               onclick="return confirm('Hapus menu ini?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:30px;">Belum ada menu.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </div>

    </main>

</body>
</html>
