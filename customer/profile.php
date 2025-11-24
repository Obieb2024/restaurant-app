<?php
session_start();
// 1. Cek Login
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'customer') {
    header("Location: ../index.php");
    exit;
}

// 2. Ambil Data User
$fullname = $_SESSION['user']['fullname'];
$email = $_SESSION['user']['email'];
// Inisial Nama
$initials = strtoupper(substr($fullname, 0, 2));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - RestoApp</title>
    
    <link href="../assets/css/customer.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* CUSTOM CSS UNTUK KARTU PROFIL AGAR TERLIHAT MEWAH */
        .profile-card-wrapper {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            position: relative;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }

        /* Banner Gradient di Atas */
        .pc-banner {
            height: 140px;
            background: linear-gradient(135deg, #ff0099, #ff4757);
            width: 100%;
        }

        /* Foto Profil Mengambang */
        .pc-avatar-container {
            position: absolute;
            top: 90px; /* Posisi menumpuk banner */
            left: 40px;
            width: 110px; height: 110px;
            background: white;
            padding: 5px;
            border-radius: 50%;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .pc-avatar-img {
            width: 100%; height: 100%;
            border-radius: 50%;
            object-fit: cover;
            background: #f3f4f6;
        }

        /* Area Konten Profil */
        .pc-body {
            padding: 60px 40px 40px 40px; /* Padding atas besar agar tidak nabrak foto */
        }

        .pc-header {
            margin-bottom: 30px;
        }

        .pc-name {
            font-size: 26px;
            font-weight: 800;
            color: #1f2937;
            margin: 0;
            line-height: 1.2;
        }
        
        .pc-badge {
            display: inline-block;
            margin-top: 8px;
            background: #ecfdf5; color: #059669;
            padding: 5px 15px; border-radius: 50px;
            font-size: 12px; font-weight: 700; 
            text-transform: uppercase; letter-spacing: 0.5px;
        }

        /* Grid Informasi (Email, Telp, dll) */
        .pc-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            padding-top: 30px;
            border-top: 1px solid #f3f4f6;
        }

        .pc-item {
            display: flex; align-items: center; gap: 15px;
            padding: 18px;
            border: 1px solid #f3f4f6;
            border-radius: 16px;
            background: #f9fafb;
            transition: 0.3s;
        }
        .pc-item:hover {
            border-color: #ff0099;
            background: #fff0f9;
            transform: translateY(-3px);
        }

        .pc-icon {
            width: 45px; height: 45px;
            background: white;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            color: #ff0099; font-size: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .pc-label { font-size: 11px; color: #6b7280; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .pc-value { font-size: 15px; font-weight: 600; color: #1f2937; }

        /* Tombol Edit */
        .btn-edit-profile {
            display: inline-flex; align-items: center; gap: 8px;
            margin-top: 30px;
            padding: 12px 30px;
            background: white;
            border: 2px solid #e5e7eb;
            color: #374151;
            border-radius: 50px;
            font-size: 14px; font-weight: 700;
            text-decoration: none; transition: 0.2s;
        }
        .btn-edit-profile:hover {
            border-color: #ff0099; color: #ff0099; background: #fff;
        }
    </style>
</head>
<body>

    <nav class="topbar-container">
        <div class="brand">
            <div class="brand-icon"><i class="fas fa-utensils"></i></div>
            <div class="brand-text"><h2>RestoApp</h2><span>Pesan Online</span></div>
        </div>
        <div class="nav-right">
             <a href="cart.php" class="cart-btn-top"><i class="fas fa-shopping-cart cart-icon"></i></a>
             <div class="user-profile">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($fullname) ?></span>
                    <span class="user-role">Customer</span>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=ff0099&color=fff" class="user-avatar">
            </div>
        </div>
    </nav>

    <div class="main-layout">
        
        <aside class="sidebar-nav">
            <ul>
                <li><a href="menu.php"><i class="fas fa-home"></i> Menu</a></li>
                <li><a href="cart.php"><i class="fas fa-shopping-bag"></i> Keranjang</a></li>
                <li><a href="orders.php"><i class="fas fa-receipt"></i> Pesanan Saya</a></li>
                <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profil</a></li>
                
                <li class="logout-item">
                    <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                </li>
            </ul>
        </aside>

        <main class="content-area">
            <div class="page-header">
                <h1>Profil Saya</h1>
                <p>Kelola informasi akun dan preferensi Anda</p>
            </div>

            <div class="profile-card-wrapper">
                <div class="pc-banner"></div>
                
                <div class="pc-avatar-container">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($fullname) ?>&background=ff0099&color=fff&size=128" class="pc-avatar-img">
                </div>
                
                <div class="pc-body">
                    <div class="pc-header">
                        <h2 class="pc-name"><?= htmlspecialchars($fullname) ?></h2>
                        <span class="pc-badge">Verified Member</span>
                    </div>

                    <div class="pc-grid">
                        <div class="pc-item">
                            <div class="pc-icon"><i class="fas fa-envelope"></i></div>
                            <div>
                                <span class="pc-label">Email Address</span>
                                <span class="pc-value"><?= htmlspecialchars($email) ?></span>
                            </div>
                        </div>

                        <div class="pc-item">
                            <div class="pc-icon"><i class="fas fa-phone-alt"></i></div>
                            <div>
                                <span class="pc-label">No. Telepon</span>
                                <span class="pc-value">0812-3456-7890</span>
                            </div>
                        </div>

                        <div class="pc-item">
                            <div class="pc-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <span class="pc-label">Lokasi Utama</span>
                                <span class="pc-value">Jakarta, Indonesia</span>
                            </div>
                        </div>

                        <div class="pc-item">
                            <div class="pc-icon"><i class="fas fa-shield-alt"></i></div>
                            <div>
                                <span class="pc-label">Keamanan</span>
                                <span class="pc-value">Aman (Aktif)</span>
                            </div>
                        </div>
                    </div>

                    <a href="#" class="btn-edit-profile"><i class="fas fa-pen"></i> Edit Profil</a>
                </div>
            </div>

        </main>
    </div>

</body>
</html>