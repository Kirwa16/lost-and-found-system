<?php
class Claim {
    private $conn;

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

    public function createClaim(
        $userId,
        $matchId,
        $claimMessage
    )
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO claims
            (
                user_id,
                match_id,
                claim_message
            )
            VALUES
            (
                :user_id,
                :match_id,
                :claim_message
            )"
        );

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

        $stmt->execute();
        return $stmt;
    }

    // Update claim status (Approve/Reject)
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . "
                SET status = :status, admin_notes = :admin_notes
                WHERE claim_id = :claim_id";

    public function getClaimsByUser($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT
                c.*,
                m.confidence_score
             FROM claims c

             INNER JOIN matches m
                ON c.match_id = m.id

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

