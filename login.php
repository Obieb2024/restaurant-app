<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Cek user di database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifikasi password (sederhana tanpa hash untuk sementara)
    if ($user && $pass === $user['password']) {
        if ($user['status'] === 'pending') {
            $error = "Sabar ya! Akun kamu masih diverifikasi admin.";
        } else {
            $_SESSION['user'] = $user;
            $role = strtolower(trim($user['role']));

            // LOGIKA REDIRECT (Sudah diperbaiki path-nya)
            if ($role == 'super_admin' || $role == 'admin') {
                header("Location: admin/dashboard.php"); 
            } else {
                header("Location: customer/menu.php");
            }
            exit;
        }
    } else {
        $error = "Waduh! Email atau password salah nih.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Warung Bu Yeti</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- STYLE NEO-BRUTALISM (Sama persis dengan Register biar kompak) --- */
        :root {
            --bg-color: #FFFDF2;       /* Cream Paper */
            --primary: #8B5CF6;        /* Ungu */
            --secondary: #FF4757;      /* Merah */
            --accent: #FFD43B;         /* Kuning */
            --dark: #1E272E;           /* Hitam Tinta */
            --white: #FFFFFF;
            --border-thick: 3px solid var(--dark);
            --shadow-hard: 8px 8px 0px var(--dark);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Public Sans', sans-serif;
            background-color: var(--primary); /* Background beda dikit biar variasi */
            /* Pola titik-titik retro */
            background-image: radial-gradient(rgba(255,255,255,0.3) 1px, transparent 1px);
            background-size: 20px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .auth-card {
            background: var(--white);
            width: 100%;
            max-width: 450px;
            padding: 40px;
            border: var(--border-thick);
            border-radius: 16px;
            box-shadow: 12px 12px 0px rgba(0,0,0,0.2);
            position: relative;
            animation: slideUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Logo Dekorasi */
        .logo-badge {
            display: inline-block;
            background: var(--accent);
            color: var(--dark);
            font-weight: 900;
            font-size: 24px;
            padding: 5px 15px;
            border: var(--border-thick);
            transform: rotate(-3deg);
            margin-bottom: 20px;
            box-shadow: 4px 4px 0 var(--dark);
        }

        .card-header { text-align: center; margin-bottom: 30px; }
        .card-header h2 { font-weight: 900; font-size: 28px; color: var(--dark); margin-bottom: 5px; }
        .card-header p { color: #666; font-weight: 600; }

        /* Form Elements */
        .form-group { margin-bottom: 25px; }
        .input-label {
            display: block;
            font-weight: 800;
            margin-bottom: 10px;
            font-size: 14px;
            text-transform: uppercase;
            color: var(--dark);
        }
        
        .form-input {
            width: 100%;
            padding: 15px;
            background: #fff;
            border: 3px solid var(--dark);
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            font-weight: 700;
            outline: none;
            transition: 0.2s;
            box-shadow: 4px 4px 0 rgba(0,0,0,0.1);
        }

        .form-input:focus {
            border-color: var(--secondary);
            box-shadow: 4px 4px 0 var(--secondary);
            transform: translate(-2px, -2px);
        }

        /* Tombol Keren */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--dark); /* Tombol Hitam */
            color: white;
            border: 3px solid var(--dark);
            border-radius: 8px;
            font-weight: 900;
            font-size: 16px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 5px 5px 0 rgba(255,255,255,0.5);
            transition: all 0.2s;
        }

        .btn-submit:hover {
            transform: translate(-3px, -3px);
            box-shadow: 8px 8px 0 rgba(255,255,255,0.5);
            background: #333;
        }
        
        .btn-submit:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0 rgba(255,255,255,0.5);
        }

        /* Alert Box */
        .alert {
            padding: 15px;
            border: 3px solid var(--dark);
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: bold;
            box-shadow: 4px 4px 0 var(--dark);
        }
        .alert-error { background: #ff7675; color: white; }
        .alert-success { background: #55efc4; color: var(--dark); }

        .footer-link {
            text-align: center;
            margin-top: 30px;
            font-weight: 700;
            padding-top: 20px;
            border-top: 2px dashed #ccc;
        }
        .footer-link a {
            color: var(--secondary);
            text-decoration: none;
            position: relative;
        }
        .footer-link a:hover { text-decoration: underline; text-decoration-thickness: 3px; }
        
        .home-link {
            display: inline-block;
            margin-top: 15px;
            font-size: 13px;
            color: #888;
        }
    </style>
</head>
<body>

    <div class="auth-card">
        <div style="text-align:center;">
            <div class="logo-badge">BU YETI</div>
        </div>
        
        <div class="card-header">
            <h2>Welcome Back! 👋</h2>
            <p>Lapar? Login dulu yuk.</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label class="input-label">Email Address</label>
                <input type="email" name="email" class="form-input" placeholder="kamu@email.com" required>
            </div>

            <div class="form-group">
                <label class="input-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-submit">
                MASUK SEKARANG <i class="fas fa-arrow-right" style="margin-left:5px;"></i>
            </button>
        </form>

        <div class="footer-link">
            Belum punya akun? <a href="register.php">Daftar Disini</a>
            <br>
            <a href="index.php" class="home-link">&larr; Kembali ke Beranda</a>
        </div>
    </div>

</body>
</html>