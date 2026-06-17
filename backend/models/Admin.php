<?php
class Admin {
    // Database connection
    private $conn;
    private $table_name = "users"; // Admins are stored in the users table with role 'admin'

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Get Dashboard Statistics for the Admin
    public function getStats() {
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

        // Active Users
        $query = "SELECT COUNT(*) as total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['active_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Items Returned
        $query = "SELECT COUNT(*) as total FROM items WHERE status = 'returned'";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $stats['items_returned'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return $stats;
    }

    // Get all users (for User Management)
    public function getAllUsers() {
        $query = "SELECT user_id, full_name, email, student_staff_id, role, created_at 
                  FROM " . $this->table_name . " 
                  ORDER BY created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }
}
?>