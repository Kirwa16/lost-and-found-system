<?php
class Claim {
    private $conn;
    private $table_name = "claims";

    // Object properties
    public $claim_id;
    public $item_id;
    public $claimant_id;
    public $proof_of_ownership;
    public $status;
    public $admin_notes;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create claim
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    item_id = :item_id,
                    claimant_id = :claimant_id,
                    proof_of_ownership = :proof_of_ownership,
                    status = :status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":item_id", $this->item_id);
        $stmt->bindParam(":claimant_id", $this->claimant_id);
        $stmt->bindParam(":proof_of_ownership", $this->proof_of_ownership);
        $this->status = 'pending';
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read pending claims for Admin
    public function readPending() {
        $query = "SELECT c.claim_id, i.item_name, u.full_name as claimant_name, c.proof_of_ownership, c.date_submitted 
                  FROM " . $this->table_name . " c
                  JOIN items i ON c.item_id = i.item_id
                  JOIN users u ON c.claimant_id = u.user_id
                  WHERE c.status = 'pending'
                  ORDER BY c.date_submitted DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Update claim status (Approve/Reject)
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status, admin_notes = :admin_notes
                WHERE claim_id = :claim_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":admin_notes", $this->admin_notes);
        $stmt->bindParam(":claim_id", $this->claim_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>