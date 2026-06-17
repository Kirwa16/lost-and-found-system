<?php
class Admin {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Get dashboard statistics
    public function getDashboardStats() {
        $stats = array();

        // Total Items
        $query = "SELECT COUNT(*) as total FROM items";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_items'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Pending Claims
        $query = "SELECT COUNT(*) as total FROM claims WHERE status = 'pending'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['pending_claims'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Total Users
        $query = "SELECT COUNT(*) as total FROM users";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }
}
?>