<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Cek password biasa (tanpa enkripsi)
if ($user && $pass === $user['password']) {
        if ($user['status'] === 'pending') {
            $error = "Akun Anda sedang menunggu persetujuan Admin.";
        } else {
            $_SESSION['user'] = $user;

        // 1. Bersihkan role dari spasi & ubah ke huruf kecil semua
        $role = strtolower(trim($user['role'])); 

        // 2. Cek logika
        if ($role == 'super_admin' || $role == 'admin') {
            header("Location: restaurant-app/admin/dashboard.php");
        } else {
            // Jika bukan admin, arahkan ke menu customer
            header("Location: restaurant-app/customer/menu.php");
        }
        exit;}
    } else {
        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Masuk - RestoApp</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-utensils"></i> RestoApp
            </div>
            <p class="auth-subtitle">Selamat datang kembali! Silakan masuk.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 20px;">
                <label class="input-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="contoh@email.com" required>
            </div>

            <div style="margin-bottom: 30px;">
                <label class="input-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-primary-full">
                Masuk Sekarang <i class="fas fa-arrow-right" style="margin-left:8px;"></i>
            </button>
        </form>

        <div class="auth-footer">
            Belum punya akun? <a href="register.php" class="auth-link">Daftar Disini</a>
            <br>
            <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
        </div>
    </div>

</body>
</html>