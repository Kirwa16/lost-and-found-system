<?php
class ItemMatch {
    private $conn;
    private $table_name = "matches";

    // Object properties matching the matches table
    public $id;
    public $lost_item_id;
    public $found_item_id;
    public $confidence_score;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new match
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    lost_item_id = :lost_item_id,
                    found_item_id = :found_item_id,
                    confidence_score = :confidence_score,
                    status = :status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":lost_item_id", $this->lost_item_id);
        $stmt->bindParam(":found_item_id", $this->found_item_id);
        $stmt->bindParam(":confidence_score", $this->confidence_score);
        
        $this->status = $this->status ? $this->status : 'pending';
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all pending matches for Admin review
    public function readPending() {
        $query = "SELECT m.id, m.confidence_score, m.status, m.created_at,
                         l.item_name as lost_item_name, l.location_lost,
                         f.item_name as found_item_name, f.location_found
                  FROM " . $this->table_name . " m
                  JOIN lost_items l ON m.lost_item_id = l.id
                  JOIN found_items f ON m.found_item_id = f.id
                  WHERE m.status = 'pending'
                  ORDER BY m.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update match status (Approve/Reject)
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>