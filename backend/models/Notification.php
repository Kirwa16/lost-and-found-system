<?php
class Notification {
    private $conn;
    private $table_name = "notifications";

    // Object properties matching the notifications table
    public $id;
    public $user_id;
    public $message;
    public $is_read;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new notification
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    user_id = :user_id,
                    message = :message,
                    is_read = 0";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":message", $this->message);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read notifications for a specific user
    public function readByUser() {
        $query = "SELECT * FROM " . $this->table_name . "
                  WHERE user_id = ?
                  ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->user_id);
        $stmt->execute();
        return $stmt;
    }

    // Mark a notification as read
    public function markAsRead() {
        $query = "UPDATE " . $this->table_name . "
                SET is_read = 1
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>