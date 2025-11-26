<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone']; // TANGKAP DATA NO HP
    $password = $_POST['password']; 
    $role = $_POST['role']; 
    $status = ($role === 'admin') ? 'pending' : 'active';
    
    // Upload Foto Logic
    $photo = 'default.png';
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
        $target = "assets/img/users/";
        if (!file_exists($target)) mkdir($target, 0777, true);
        $fname = time() . '_' . $_FILES['photo']['name'];
        move_uploaded_file($_FILES['photo']['tmp_name'], $target.$fname);
        $photo = $fname;
    }

    // Cek Email
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    
    if($check->rowCount() > 0) {
        $error = "Yah, email ini sudah dipakai orang lain!";
    } else {
        // Simpan Data (Tambah kolom phone)
        $sql = "INSERT INTO users (fullname, email, phone, password, role, photo, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if($pdo->prepare($sql)->execute([$fullname, $email, $phone, $password, $role, $photo, $status])) {
            $_SESSION['success'] = ($role === 'admin') ? "Admin terdaftar! Tunggu approval ya." : "Registrasi sukses! Yuk login.";
            header("Location: login.php"); exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Baru - Warung Bu Yeti</title>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* --- STYLE NEO-BRUTALISM --- */
        :root {
            --bg-color: #FFFDF2;       
            --primary: #8B5CF6;        
            --secondary: #FF4757;      
            --accent: #FFD43B;         
            --dark: #1E272E;           
            --white: #FFFFFF;
            --border-thick: 3px solid var(--dark);
            --shadow-hard: 8px 8px 0px var(--dark);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Public Sans', sans-serif;
            background-color: var(--accent);
            background-image: radial-gradient(var(--dark) 1px, transparent 1px);
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
            max-width: 500px;
            padding: 40px;
            border: var(--border-thick);
            border-radius: 16px;
            box-shadow: var(--shadow-hard);
            position: relative;
            animation: popUp 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popUp {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .card-header { text-align: center; margin-bottom: 30px; }
        .card-header h2 { font-weight: 900; font-size: 32px; color: var(--dark); text-transform: uppercase; margin-bottom: 5px; }
        .card-header p { color: #555; font-weight: 500; }
        
        .badge-deco {
            position: absolute;
            top: -15px; right: -15px;
            background: var(--secondary);
            color: white;
            padding: 5px 15px;
            border: var(--border-thick);
            transform: rotate(5deg);
            font-weight: 900;
            box-shadow: 4px 4px 0 var(--dark);
        }

        .form-group { margin-bottom: 20px; }
        .input-label {
            display: block;
            font-weight: 800;
            margin-bottom: 8px;
            font-size: 14px;
            text-transform: uppercase;
            color: var(--dark);
        }
        
        .form-input, select.form-input {
            width: 100%;
            padding: 14px;
            background: #fff;
            border: 3px solid var(--dark);
            border-radius: 8px;
            font-size: 16px;
            font-family: inherit;
            font-weight: 600;
            outline: none;
            transition: 0.2s;
            box-shadow: 4px 4px 0 rgba(0,0,0,0.1);
        }

        .form-input:focus {
            border-color: var(--primary);
            box-shadow: 4px 4px 0 var(--primary);
            transform: translate(-2px, -2px);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: var(--primary);
            color: white;
            border: 3px solid var(--dark);
            border-radius: 8px;
            font-weight: 900;
            font-size: 16px;
            text-transform: uppercase;
            cursor: pointer;
            box-shadow: 6px 6px 0 var(--dark);
            transition: all 0.2s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translate(-2px, -2px);
            box-shadow: 8px 8px 0 var(--dark);
            background: #7c3aed;
        }

        .btn-submit:active {
            transform: translate(2px, 2px);
            box-shadow: 2px 2px 0 var(--dark);
        }

        .alert {
            padding: 15px;
            border: 3px solid var(--dark);
            background: #ff7675;
            color: white;
            font-weight: bold;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 4px 4px 0 var(--dark);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            font-weight: 700;
            padding-top: 20px;
            border-top: 2px dashed #ccc;
        }
        .login-link a {
            color: var(--secondary);
            text-decoration: none;
            border-bottom: 3px solid var(--secondary);
        }
        .login-link a:hover { background: var(--secondary); color: white; }

        .row-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media (max-width: 480px) { .row-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <div class="auth-card">
        <div class="badge-deco">BARU!</div>
        
        <div class="card-header">
            <h2>Buat Akun 🚀</h2>
            <p>Gabung & nikmati makanan enak!</p>
        </div>

        <?php if(isset($error)): ?>
            <div class="alert"><i class="fas fa-exclamation-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label class="input-label">Mau jadi apa?</label>
                <select name="role" class="form-input">
                    <option value="customer">Pelanggan (Customer)</option>
                    <option value="admin">Admin (Butuh Approval)</option>
                </select>
            </div>

            <div class="row-grid">
                <div class="form-group">
                    <label class="input-label">Nama Lengkap</label>
                    <input type="text" name="fullname" class="form-input" placeholder="Isi nama kamu..." required>
                </div>
                <div class="form-group">
                    <label class="input-label">Foto Profil</label>
                    <input type="file" name="photo" class="form-input" style="padding: 11px;">
                </div>
            </div>

            <div class="form-group">
                <label class="input-label">No. WhatsApp / Telepon</label>
                <input type="number" name="phone" class="form-input" placeholder="0812xxxx" required>
            </div>

            <div class="form-group">
                <label class="input-label">Email Aktif</label>
                <input type="email" name="email" class="form-input" placeholder="email@contoh.com" required>
            </div>

            <div class="form-group">
                <label class="input-label">Password</label>
                <input type="password" name="password" class="form-input" placeholder="******" required>
            </div>

            <button type="submit" class="btn-submit">
                DAFTAR SEKARANG <i class="fas fa-arrow-right"></i>
            </button>

        </form>

        <div class="login-link">
            Sudah punya akun? <a href="login.php">Login Disini</a>
        </div>
    </div>

</body>
</html>