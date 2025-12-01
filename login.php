<?php
// PENGAMAN SESSION
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $pass === $user['password']) {
        if ($user['status'] === 'pending') {
            $error = "AKUN SEDANG DITINJAU ADMIN!";
        } else {
            $_SESSION['user'] = $user;
            $role = strtolower(trim($user['role']));
            if ($role == 'super_admin' || $role == 'admin') { header("Location: admin/dashboard.php"); } 
            elseif ($role == 'cashier') { header("Location: cashier/dashboard.php"); } 
            else { header("Location: customer/menu.php"); }
            exit;
        }
    } else {
        $error = "EMAIL ATAU PASSWORD SALAH!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Masuk - Warung Bu Yeti</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@400;700&family=Archivo+Black&display=swap" rel="stylesheet">
    <style>
        :root {
            --black: #000000;
            --white: #ffffff;
            --yellow: #ffeb3b;
            --red: #ff0055;
            --blue: #00ccff;
        }
        
        body {
            font-family: 'Chakra Petch', sans-serif;
            margin: 0; padding: 0;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            /* BACKGROUND GERAK WARNA-WARNI */
            background: linear-gradient(-45deg, #ff0055, #ffeb3b, #00ccff, #ff9900);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ANIMASI KARTU MUNCUL */
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.5) translateY(50px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }

        .auth-card {
            background: var(--white);
            width: 100%; max-width: 420px;
            padding: 40px;
            border: 5px solid var(--black);
            /* BAYANGAN KERAS BERWARNA */
            box-shadow: 15px 15px 0 rgba(0,0,0,0.8);
            position: relative;
            animation: popIn 0.6s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
            border-radius: 20px;
            overflow: hidden;
        }

        /* DEKORASI ATAS */
        .card-header-deco {
            height: 15px; background: repeating-linear-gradient(45deg, var(--black), var(--black) 10px, var(--yellow) 10px, var(--yellow) 20px);
            border-bottom: 4px solid var(--black);
            margin: -40px -40px 30px -40px;
        }

        .brand-title {
            font-family: 'Archivo Black'; font-size: 42px;
            color: var(--black); text-transform: uppercase;
            margin: 0; line-height: 0.9; text-align: center;
            text-shadow: 3px 3px 0 var(--yellow);
        }
        .brand-subtitle {
            text-align: center; font-weight: 900; color: #555; margin: 10px 0 30px 0;
            letter-spacing: 1px; font-size: 12px;
        }

        .form-group { margin-bottom: 20px; position: relative; }
        
        .form-label {
            font-weight: 900; font-size: 14px;
            display: block; margin-bottom: 8px; color: var(--black);
            text-transform: uppercase;
        }

        .form-input {
            width: 100%; padding: 15px; border: 3px solid var(--black);
            font-family: 'Chakra Petch'; font-weight: bold; font-size: 16px;
            outline: none; background: #f0f0f0; border-radius: 10px;
            transition: 0.3s; box-sizing: border-box;
        }
        .form-input:focus {
            background: var(--white);
            border-color: var(--red);
            box-shadow: 0 0 15px rgba(255,0,85,0.3);
            transform: scale(1.02);
        }

        /* TOMBOL GOYANG */
        .btn-auth {
            width: 100%; padding: 15px;
            background: var(--black); color: var(--white);
            font-family: 'Archivo Black'; font-size: 20px; text-transform: uppercase;
            border: 3px solid var(--black); cursor: pointer;
            border-radius: 10px; transition: 0.2s;
            position: relative; top: 0;
        }
        .btn-auth:hover {
            background: var(--red);
            color: var(--yellow);
            top: -3px;
            box-shadow: 0 10px 0 var(--black);
        }
        .btn-auth:active {
            top: 2px; box-shadow: 0 2px 0 var(--black);
        }
        
        .alert-box {
            background: #ffcccc; border: 3px solid var(--black);
            color: var(--red); padding: 12px; border-radius: 8px;
            font-weight: 900; margin-bottom: 20px; text-align: center;
            animation: shake 0.5s;
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="card-header-deco"></div>

        <h1 class="brand-title">WARUNG<br><span style="color:var(--red);">BU YETI</span></h1>
        <p class="brand-subtitle">LOGIN DULU BIAR KENYANG</p>

        <?php if(isset($error)): ?>
            <div class="alert-box"><i class="fas fa-bomb"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert-box" style="background:#ccffcc; color:green;">
                <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="form-label">EMAIL KAMU</label>
                <input type="email" name="email" class="form-input" placeholder="contoh@email.com" required>
            </div>

            <div class="form-group">
                <label class="form-label">PASSWORD</label>
                <input type="password" name="password" class="form-input" placeholder="******" required>
            </div>

            <button type="submit" class="btn-auth">GAS MASUK 🚀</button>
        </form>

        <div style="text-align:center; margin-top:30px; border-top:2px dashed #ccc; padding-top:20px;">
            <span style="font-weight:bold; font-size:14px;">Belum punya akun?</span>
            <a href="register.php" style="color:var(--blue); font-weight:900; text-decoration:none; border-bottom:3px solid var(--blue);">DAFTAR SEKARANG</a>
            <br><br>
            <a href="index.php" style="font-size:12px; font-weight:bold; text-decoration:none; color:var(--black); opacity:0.6;"><i class="fas fa-arrow-left"></i> KEMBALI KE BERANDA</a>
        </div>
    </div>

</body>
</html>
