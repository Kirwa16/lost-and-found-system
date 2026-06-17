<?php
class Claim {
    // Database connection and table name
    private $conn;
    private $table_name = "claims";

    // Object properties
    public $claim_id;
    public $item_id;
    public $claimant_id;
    public $proof_of_ownership;
    public $status;
    public $date_reviewed;
    public $admin_notes;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Submit a new claim
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    item_id = :item_id,
                    claimant_id = :claimant_id,
                    proof_of_ownership = :proof_of_ownership,
                    status = :status";

        $stmt = $this->conn->prepare($query);

        // Default status is pending
        $this->status = 'pending';

        // Bind values
        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":claimant_id", $this->claimant_id);
        $stmt->bindParam(":proof_of_ownership", $this->proof_of_ownership);
        $stmt->bindParam(":status", $this->status);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read pending claims (For Admin Dashboard)
    public function readPending() {
        $query = "SELECT c.*, i.item_name, u.full_name as claimant_name 
                  FROM " . $this->table_name . " c
                  LEFT JOIN items i ON c.item_id = i.item_id
                  LEFT JOIN users u ON c.claimant_id = u.user_id
                  WHERE c.status = 'pending'
                  ORDER BY c.date_submitted DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    // Update claim status (Approve/Reject by Admin)
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                  SET status = :status,
                      date_reviewed = NOW(),
                      admin_notes = :admin_notes
                  WHERE claim_id = :claim_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":admin_notes", $this->admin_notes);
        $stmt->bindParam(":claim_id", $this->claim_id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>