<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') { header("Location: ../login.php"); exit; }
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Profil Saya - Warung Bu Yeti</title>
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <div class="header-bar"><div class="page-title"><h1>KARTU IDENTITAS</h1></div></div>
        <div class="profile-card">
            <div class="profile-header"></div>
            <img src="../assets/img/users/<?= $user['photo'] ?>" onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($user['fullname']) ?>&background=random'" class="profile-pic">
            <h2 style="font-family:'Archivo Black'; font-size:32px; margin:0;"><?= htmlspecialchars($user['fullname']) ?></h2>
            <span style="background:var(--yellow); padding:5px 15px; font-weight:900; border:2px solid black; display:inline-block; margin-top:10px;">PELANGGAN</span>
            <div style="margin-top: 20px;">
                <a href="profile_edit.php" class="btn-pay" style="width:auto; display:inline-block; padding:10px 30px; text-decoration:none; color:white;"><i class="fas fa-pen"></i> EDIT PROFIL</a>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; padding:40px; text-align:left;">
                <div style="border:3px solid black; padding:15px; background:#f9f9f9;"><small style="font-weight:bold; color:#777;">EMAIL</small><div style="font-weight:900; font-size:18px; overflow-wrap:break-word;"><?= htmlspecialchars($user['email']) ?></div></div>
                <div style="border:3px solid black; padding:15px; background:#f9f9f9;"><small style="font-weight:bold; color:#777;">TELEPON</small><div style="font-weight:900; font-size:18px; color:var(--red-primary);"><?= htmlspecialchars($user['phone'] ?? '-') ?></div></div>
                <div style="border:3px solid black; padding:15px; background:#f9f9f9; grid-column: span 2;"><small style="font-weight:bold; color:#777;">STATUS</small><div style="font-weight:900; font-size:18px; color:green;">AKTIF & AMAN</div></div>
            </div>
        </div>
    </main>
</body>
</html>