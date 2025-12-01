<?php
class Cashier {
    private $pdo;

    public function __construct($pdoConnection) {
        $this->pdo = $pdoConnection;
    }

    // Hitung pesanan yang perlu diproses hari ini
    public function getDailyStats() {
        $today = date('Y-m-d');
        $stmt = $this->pdo->prepare("SELECT 
            (SELECT COUNT(*) FROM orders WHERE DATE(order_date) = ?) as total_today,
            (SELECT COUNT(*) FROM orders WHERE order_status = 'Menunggu') as pending,
            (SELECT COUNT(*) FROM orders WHERE order_status = 'Diproses') as cooking
        ");
        $stmt->execute([$today]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Ambil semua pesanan untuk dikelola kasir
    public function getAllOrders() {
        $sql = "SELECT o.*, u.fullname, u.phone 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                ORDER BY 
                CASE 
                    WHEN o.order_status = 'Menunggu' THEN 1
                    WHEN o.order_status = 'Konfirmasi' THEN 2
                    WHEN o.order_status = 'Diproses' THEN 3
                    ELSE 4 
                END, 
                o.order_date DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update status pesanan
    public function updateStatus($orderId, $status) {
        // Logika Stok sederhana (bisa dikembangkan ambil dari logic admin sebelumnya)
        $stmt = $this->pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        return $stmt->execute([$status, $orderId]);
    }
}
?>