<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../login.php"); exit; }
include '../includes/db.php';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?"); $stmt->execute([$_SESSION['user']['id']]); $user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname']; $email = $_POST['email']; $phone = $_POST['phone']; $new_password = $_POST['password'];
    $photo = $user['photo'];
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $target = "../assets/img/users/"; $fname = time() . "_" . $_FILES['photo']['name'];
        if(move_uploaded_file($_FILES['photo']['tmp_name'], $target.$fname)) { $photo = $fname; }
    }
    $sql = "UPDATE users SET fullname=?, email=?, phone=?, photo=?"; $params = [$fullname, $email, $phone, $photo];
    if (!empty($new_password)) { $sql .= ", password=?"; $params[] = $new_password; }
    $sql .= " WHERE id=?"; $params[] = $user['id'];
    $pdo->prepare($sql)->execute($params);
    $_SESSION['user']['fullname'] = $fullname; $_SESSION['user']['photo'] = $photo;
    echo "<script>alert('Profil Berhasil Diupdate!'); window.location='profile.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Edit Profil - Warung Bu Yeti</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>.form-label { display:block; font-weight:900; margin-bottom:5px; font-size:14px; text-transform:uppercase; color:#555; } .form-control { width:100%; padding:12px; border:3px solid black; font-family:'Chakra Petch'; font-weight:bold; font-size:16px; margin-bottom:20px; } .form-control:focus { background:var(--yellow); }</style>
</head>
<body>
    <aside class="sidebar">
        <div class="brand"><h2>CUSTOMER</h2><span></span></div>
        <nav class="nav-menu">
            <a href="menu.php" class="nav-link"><i class="fas fa-utensils"></i> MENU KAMI</a>
            <a href="cart.php" class="nav-link"><i class="fas fa-shopping-cart"></i> KERANJANG</a>
            <a href="orders.php" class="nav-link"><i class="fas fa-receipt"></i> PESANAN</a>
            <a href="profile.php" class="nav-link active"><i class="fas fa-user-astronaut"></i> PROFIL SAYA</a>
            <a href="../logout.php" class="nav-link" style="margin-top:auto; color:red; border-color:red;"><i class="fas fa-sign-out-alt"></i> KELUAR</a>
        </nav>
    </aside>
    <main class="main-content">
        <div class="header-bar"><div class="page-title"><h1>EDIT DATA PRIBADI</h1></div><a href="profile.php" class="btn-add" style="width:auto; padding:0 15px; text-decoration:none; color:white; font-weight:bold; background:black;">KEMBALI</a></div>
        <div class="brutal-box">
            <form method="POST" enctype="multipart/form-data">
                <div style="text-align:center; margin-bottom:30px;"><img src="../assets/img/users/<?= $user['photo'] ?>" style="width:100px; height:100px; border-radius:50%; border:3px solid black; object-fit:cover; margin-bottom:10px;"><br><label style="cursor:pointer; background:var(--yellow); border:2px solid black; padding:5px 10px; font-weight:bold; font-size:12px;"><i class="fas fa-camera"></i> GANTI FOTO<input type="file" name="photo" style="display:none;"></label></div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;"><div><label class="form-label">Nama Lengkap</label><input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required></div><div><label class="form-label">No. Telepon / WA</label><input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div></div>
                <label class="form-label">Email Login</label><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                <label class="form-label">Password Baru (Opsional)</label><input type="password" name="password" class="form-control" placeholder="(Kosongkan jika tidak ganti)">
                <button type="submit" class="btn-pay">SIMPAN PERUBAHAN</button>
            </form>
        </div>
    </main>
</body>
</html>