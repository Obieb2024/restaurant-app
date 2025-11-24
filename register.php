<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; 
    $status = ($role === 'admin') ? 'pending' : 'active';
    
    // Upload Foto
    $photo = 'default.png';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $target = "assets/img/users/";
        if (!file_exists($target)) mkdir($target, 0777, true);
        $fname = time() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], $target.$fname);
        $photo = $fname;
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if($check->rowCount() > 0) {
        $error = "Email sudah terdaftar!";
    } else {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullname, email, password, role, photo, status) VALUES (?, ?, ?, ?, ?, ?)";
        if($pdo->prepare($sql)->execute([$fullname, $email, $hash, $role, $photo, $status])) {
            $_SESSION['success'] = ($role === 'admin') ? "Pendaftaran Admin berhasil! Menunggu persetujuan." : "Registrasi berhasil! Silakan login.";
            header("Location: login.php"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar - RestoApp</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="auth-body">

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo"><i class="fas fa-user-plus"></i> Buat Akun</div>
            <p class="auth-subtitle">Isi data diri untuk mulai memesan</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div style="margin-bottom: 20px;">
                <label class="input-label">Daftar Sebagai</label>
                <select name="role" class="form-input">
                    <option value="customer">Pelanggan (Customer)</option>
                    <option value="admin">Admin (Butuh Approval)</option>
                </select>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">
                <div>
                    <label class="input-label">Nama Lengkap</label>
                    <input type="text" name="fullname" class="form-input" required>
                </div>
                <div>
                    <label class="input-label">Foto Profil</label>
                    <input type="file" name="photo" class="form-input" style="padding:10px;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label class="input-label">Email Address</label>
                <input type="email" name="email" class="form-input" required>
            </div>

            <div style="margin-bottom: 30px;">
                <label class="input-label">Password</label>
                <input type="password" name="password" class="form-input" required>
            </div>

            <button type="submit" class="btn-primary-full">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? <a href="login.php" class="auth-link">Login Disini</a>
        </div>
    </div>

</body>
</html>