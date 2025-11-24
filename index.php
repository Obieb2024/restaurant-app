<?php
session_start();
include 'includes/db.php';
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Makan Bu Yeti - Premium</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="navbar">
        <div class="container navbar-content">
            <a href="#" class="logo">
                <div class="logo-icon"><i class="fas fa-utensils"></i></div>
                <span>RestoApp</span>
            </a>
            <ul class="nav-menu">
                <li><a href="#home" class="nav-link">Beranda</a></li>
                <li><a href="#menu" class="nav-link">Menu</a></li>
                <li><a href="#features" class="nav-link">Keunggulan</a></li>
                <li><a href="#about" class="nav-link">Tentang</a></li>
            </ul>
            <?php if(isset($_SESSION['user'])): ?>
                <?php $dash = ($_SESSION['user']['role']=='admin' || $_SESSION['user']['role']=='super_admin') ? 'admin/dashboard.php' : 'customer/menu.php'; ?>
                <a href="<?= $dash ?>" class="btn-cta">Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="btn-cta">Masuk / Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <section id="home" class="hero">
        <div class="container hero-content">
            <div class="hero-text">
                <div class="badge-hero"><i class="fas fa-star"></i> Restoran Pilihan Keluarga</div>
                <h1>Nikmati Kelezatan <br> <span>Citarasa Nusantara</span></h1>
                <p>Kami menghadirkan pengalaman kuliner terbaik dengan resep warisan turun-temurun. Bahan segar, rasa otentik, dan harga bersahabat.</p>
                
                <div class="hero-buttons">
                    <a href="<?= isset($_SESSION['user']) ? 'customer/menu.php' : 'login.php' ?>" class="btn-primary-lg">
                        Pesan Sekarang <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#menu" class="btn-secondary-lg" style="
                        padding: 16px 35px; background: white; color: #0f172a;
                        border-radius: 99px; font-weight: 700; font-size: 16px;
                        border: 1px solid #e2e8f0;
                    ">Lihat Menu</a>
                </div>
            </div>
            <div class="hero-image-box">
                <img src="assets/img/ayam-geprek.png" class="hero-img" alt="Ayam Geprek" onerror="this.src='https://via.placeholder.com/400x300?text=Ayam+Geprek'">
            </div>
        </div>
    </section>

    <section id="features" class="features">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="f-icon"><i class="fas fa-leaf"></i></div>
                    <h3>Bahan 100% Segar</h3>
                    <p>Kami belanja bahan baku setiap pagi langsung dari petani lokal untuk menjamin kesegaran.</p>
                </div>
                <div class="feature-card">
                    <div class="f-icon"><i class="fas fa-award"></i></div>
                    <h3>Koki Berpengalaman</h3>
                    <p>Dimasak oleh chef profesional dengan pengalaman lebih dari 10 tahun di kuliner nusantara.</p>
                </div>
                <div class="feature-card">
                    <div class="f-icon"><i class="fas fa-shipping-fast"></i></div>
                    <h3>Pengiriman Kilat</h3>
                    <p>Lapar? Jangan khawatir. Kurir kami siap mengantar pesanan Anda dalam waktu singkat.</p>
                </div>
            </div>
        </div>
    </section>

    <section id="menu" class="section-menu">
        <div class="container">
            <div class="section-header">
                <h2>Menu Favorit Minggu Ini</h2>
                <p>Temukan hidangan yang paling banyak dipesan oleh pelanggan setia kami.</p>
            </div>

            <div class="menu-grid">
                <?php if(count($products) > 0): ?>
                    <?php foreach($products as $p): ?>
                    <div class="menu-card">
                        <div class="menu-img-box">
                            <img src="assets/img/<?= htmlspecialchars($p['image']) ?>" class="menu-img" onerror="this.src='https://via.placeholder.com/400x300?text=Menu'">
                        </div>
                        <div class="menu-content">
                            <h3 class="menu-title"><?= htmlspecialchars($p['name']) ?></h3>
                            <p class="menu-desc"><?= htmlspecialchars($p['description']) ?></p>
                            <span class="menu-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></span>
                            <a href="login.php" class="btn-order">Pesan Sekarang</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center; width:100%; color:#999;">Belum ada menu yang ditampilkan.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section id="about" class="about">
        <div class="container">
            <div class="about-container">
                <div class="about-info">
                    <h3>Tentang Warung Bu Yeti</h3>
                    <p>Berawal dari warung kecil di tahun 2010, kini kami telah melayani ribuan pelanggan dengan cita rasa yang konsisten. Kami percaya bahwa makanan enak bisa menyatukan semua orang.</p>
                    
                    <div class="info-item">
                        <div class="i-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div class="i-text">Jl. Kebon Jeruk No. 12, Jakarta Selatan</div>
                    </div>
                    <div class="info-item">
                        <div class="i-icon"><i class="fas fa-clock"></i></div>
                        <div class="i-text">Buka Setiap Hari: 08.00 - 22.00 WIB</div>
                    </div>
                    <div class="info-item">
                        <div class="i-icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="i-text">Hubungi Kami: 0812-3456-7890</div>
                    </div>
                </div>
                <div class="map-view">
                    <div style="text-align:center; color:#94a3b8;">
                        <i class="fas fa-map-marked-alt" style="font-size:80px; margin-bottom:20px;"></i><br>
                        <span style="font-weight:700; font-size:20px;">Peta Lokasi Google Maps</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container">
            <div class="footer-main">
                <span class="footer-logo">Warung Bu Yeti</span>
                <div class="footer-nav">
                    <a href="#home">Beranda</a>
                    <a href="#menu">Menu</a>
                    <a href="#features">Layanan</a>
                    <a href="#about">Kontak</a>
                </div>
                <p style="color:#64748b; max-width:600px; margin:0 auto;">Menyajikan kebahagiaan melalui hidangan lezat sejak 2010. Terima kasih telah menjadi bagian dari perjalanan kami.</p>
            </div>
            <div class="copyright">
                &copy; 2025 RestoApp System. All Rights Reserved.
            </div>
        </div>
    </footer>

</body>
</html>