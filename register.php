<?php
// PENGAMAN SESSION
if (session_status() == PHP_SESSION_NONE) { session_start(); }
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password']; 
    $role = $_POST['role']; 
    $status = ($role === 'admin' || $role === 'cashier') ? 'pending' : 'active';
    
    $photo = 'default.png';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $target = "assets/img/users/";
        $fname = time() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], $target.$fname);
        $photo = $fname;
    }

    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if($check->rowCount() > 0) {
        $error = "EMAIL SUDAH TERDAFTAR!";
    } else {
        $sql = "INSERT INTO users (fullname, email, phone, password, role, photo, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if($pdo->prepare($sql)->execute([$fullname, $email, $phone, $password, $role, $photo, $status])) {
            $_SESSION['success'] = "REGISTRASI BERHASIL! SILAKAN LOGIN.";
            header("Location: login.php"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar - Warung Bu Yeti</title>
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
            /* BACKGROUND GERAK (Gradient beda arah) */
            background: linear-gradient(45deg, #00ccff, #ff0055, #ffeb3b);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center; padding: 20px; margin:0;
        }
        @keyframes gradientBG {
            0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; }
        }
        @keyframes popIn {
            0% { opacity: 0; transform: scale(0.8) translateY(-50px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        
        .auth-card {
            background: var(--white); width: 100%; max-width: 500px; padding: 40px;
            border: 5px solid var(--black); border-radius: 20px;
            box-shadow: 15px 15px 0 var(--black);
            position: relative; animation: popIn 0.7s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards;
            overflow: hidden;
        }
        
        .card-header-deco {
            height: 15px; background: repeating-linear-gradient(-45deg, var(--black), var(--black) 10px, var(--blue) 10px, var(--blue) 20px);
            border-bottom: 4px solid var(--black); margin: -40px -40px 30px -40px;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-label { font-weight: 900; text-transform: uppercase; font-size: 13px; display: block; margin-bottom: 5px; color: var(--black); }
        .form-input, select.form-input {
            width: 100%; padding: 12px; border: 3px solid var(--black); border-radius: 10px;
            font-family: 'Chakra Petch'; font-weight: bold; font-size: 16px; outline: none;
            background: #f9f9f9; transition: 0.3s; box-sizing: border-box;
        }
        .form-input:focus { background: var(--white); border-color: var(--blue); box-shadow: 0 0 15px rgba(0,204,255,0.3); transform: scale(1.02); }
        
        .btn-auth {
            width: 100%; padding: 15px; background: var(--yellow); color: var(--black);
            font-family: 'Archivo Black'; font-size: 20px; text-transform: uppercase;
            border: 3px solid var(--black); border-radius: 10px; cursor: pointer;
            transition: 0.2s; margin-top: 10px; position: relative; top:0;
        }
        .btn-auth:hover { background: var(--white); top: -3px; box-shadow: 0 10px 0 var(--black); }
        .btn-auth:active { top: 2px; box-shadow: 0 2px 0 var(--black); }
        
        .alert-box { background: #ffcccc; border: 3px solid var(--black); color: var(--red); padding: 10px; border-radius: 8px; font-weight: 900; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="card-header-deco"></div>
        
        <div style="text-align:center; margin-bottom:30px;">
            <h1 style="font-family:'Archivo Black'; font-size:32px; margin:0; line-height:1;">GABUNG SEKARANG</h1>
            <p style="font-weight:bold; color:#555; margin-top:5px;">Jadi member Bu Yeti itu keren!</p>
        </div>

        <?php if(isset($error)): ?><div class="alert-box"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div><?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label">Daftar Sebagai</label>
                <select name="role" class="form-input">
                    <option value="customer">PELANGGAN (CUSTOMER)</option>
                    <option value="cashier">KASIR (STAFF)</option>
                    <option value="admin">ADMIN (OWNER)</option>
                </select>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                <div class="form-group"><label class="form-label">Nama Lengkap</label><input type="text" name="fullname" class="form-input" required></div>
                <div class="form-group"><label class="form-label">Foto Profil</label><input type="file" name="photo" class="form-input" style="padding:9px;"></div>
            </div>

            <div class="form-group"><label class="form-label">No. WhatsApp</label><input type="number" name="phone" class="form-input" placeholder="08xx..." required></div>
            <div class="form-group"><label class="form-label">Email Aktif</label><input type="email" name="email" class="form-input" placeholder="email@contoh.com" required></div>
            <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-input" placeholder="******" required></div>

            <button type="submit" class="btn-auth">DAFTAR AKUN 🔥</button>
        </form>

        <div style="text-align:center; margin-top:20px; border-top:2px dashed #ccc; padding-top:15px;">
            <span style="font-weight:bold;">Sudah punya akun?</span>
            <a href="login.php" style="color:var(--blue); font-weight:900; text-decoration:none; border-bottom:3px solid var(--blue);">LOGIN AJA</a>
        </div>
    </div>

</body>
</html>