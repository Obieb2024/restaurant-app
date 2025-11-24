<nav class="admin-sidebar">
    <ul>
        <li><a href="dashboard.php" class="<?=basename($_SERVER['PHP_SELF'])=='dashboard.php'?'active':''?>">Dashboard</a></li>
        <li><a href="products.php" class="<?=basename($_SERVER['PHP_SELF'])=='products.php'?'active':''?>">Produk</a></li>
        <li><a href="orders.php" class="<?=basename($_SERVER['PHP_SELF'])=='orders.php'?'active':''?>">Pesanan</a></li>
        <li><a href="customers.php" class="<?=basename($_SERVER['PHP_SELF'])=='customers.php'?'active':''?>">Pelanggan</a></li>
    </ul>
</nav>