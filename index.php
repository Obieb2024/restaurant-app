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
    <title>Warung Bu Yeti - LEGEND!</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <nav class="navbar">
        <div class="container nav-flex">
            <a href="#" class="logo">
                <i class="fas fa-pepper-hot"></i> BU YETI
            </a>
            <div class="nav-links">
                <a href="#home" class="nav-link">BERANDA</a>
                <a href="#features" class="nav-link">KENAPA KAMI?</a>
                <a href="#menu" class="nav-link">MENU</a>
                <?php if(isset($_SESSION['user'])): ?>
                    <?php $dash = ($_SESSION['user']['role'] == 'admin') ? 'admin/dashboard.php' : 'customer/menu.php'; ?>
                    <a href="<?= $dash ?>" class="btn btn-primary">DASHBOARD</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">MASUK / DAFTAR</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <header id="home" class="hero container">
        <div class="hero-text">
            <h1 class="animate-up">RASA OTENTIK<br><span class="highlight">WARISAN</span><br>NUSANTARA</h1>
            <p class="animate-up delay-1">
                Bukan sekadar makan, tapi pengalaman rasa yang bikin nagih! 
                Bumbu rempah asli, resep rahasia turun-temurun, harga bersahabat.
            </p>
            <div class="animate-up delay-2" style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="login.php" class="btn btn-primary">PESAN SEKARANG <i class="fas fa-arrow-right"></i></a>
                <a href="#menu" class="btn btn-secondary">LIHAT MENU</a>
            </div>
        </div>
        
        <div class="hero-img-wrapper animate-up delay-3">
            <div class="sticker-float">
                TERLEZAT<br>SE-CIBOGO
            </div>
            <img src="assets/img/wrb.jpeg" class="hero-img" alt="Ayam Bakar" 
                 onerror="this.src='https://via.placeholder.com/600x500?text=MANTAP+JIWA'">
        </div>
    </header>

    <div class="marquee-container">
        <div class="marquee-content">
            🔥 PEDASNYA NAMPOL • RESEP NENEK MOYANG • HARGA KAKI LIMA RASA BINTANG LIMA • GRATIS ES TEH HARI JUMAT • 
            🔥 PEDASNYA NAMPOL • RESEP NENEK MOYANG • HARGA KAKI LIMA RASA BINTANG LIMA • GRATIS ES TEH HARI JUMAT •
        </div>
    </div>

    <section id="features" class="features">
        <div class="container features-grid">
            <div class="feature-card">
                <i class="fas fa-fire f-icon"></i>
                <h3>PEDAS GILA</h3>
                <p>Sambal dadakan yang dibuat fresh setiap hari. Siap-siap keringetan!</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-wallet f-icon"></i>
                <h3>MURAH MERIAH</h3>
                <p>Makan enak gak perlu mahal. Harga pas di kantong pelajar dan mahasiswa.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-motorcycle f-icon"></i>
                <h3>KIRIM CEPAT</h3>
                <p>Laper? Pesan lewat web, kurir kami langsung meluncur ke tempatmu.</p>
            </div>
        </div>
    </section>

    <section id="menu" class="menu-section container">
        <div class="section-header">
            <div class="section-title">MENU TERFAVORIT</div>
        </div>

        <div class="menu-grid">
            <?php if(count($products) > 0): ?>
                <?php foreach($products as $p): ?>
                <div class="menu-card">
                    <div class="menu-img-box">
                        <img src="assets/img/<?= htmlspecialchars($p['image']) ?>" 
                             onerror="this.src='https://via.placeholder.com/400x300?text=Menu+Enak'">
                    </div>
                    <div class="menu-info">
                        <div class="menu-name"><?= htmlspecialchars($p['name']) ?></div>
                        <div><span class="menu-price">Rp <?= number_format($p['price'], 0, ',', '.') ?></span></div>
                        <p style="color:#555; font-size:14px; margin-bottom:20px; font-weight:600;"><?= htmlspecialchars($p['description']) ?></p>
                        <a href="login.php" class="btn btn-primary" style="width:100%;">PESAN SEKARANG</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align:center; font-weight:bold; width:100%;">Belum ada menu yang ditampilkan.</p>
            <?php endif; ?>
        </div>
        
        <div style="text-align:center; margin-top:50px;">
            <a href="login.php" class="btn btn-secondary" style="font-size:18px;">LIHAT SEMUA MENU &rarr;</a>
        </div>
    </section>

    <footer>
        <div class="container">
            <span class="footer-logo">WARUNG BU YETI</span>
            <p style="margin-bottom:30px; font-size:18px;">Jl. Cibogo</p>
            
            <div style="display:flex; justify-content:center; gap:20px; margin-bottom:40px;">
                <a href="#" style="font-size:24px; color:var(--yellow);"><i class="fab fa-instagram"></i></a>
                <a href="#" style="font-size:24px; color:var(--yellow);"><i class="fab fa-facebook"></i></a>
                <a href="#" style="font-size:24px; color:var(--yellow);"><i class="fab fa-whatsapp"></i></a>
            </div>

            <p style="font-weight:bold;">&copy; 2025 Kelompok 6 - Information System 2b • All Rights Reserved.</p>
        </div>
    </footer>

</body>
</html>