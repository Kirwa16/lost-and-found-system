<?php

require_once __DIR__ . '/../config/database.php';

class Claim
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /*
    |--------------------------------------------------------------------------
    | Create Claim
    |--------------------------------------------------------------------------
    */

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

        return $stmt->execute([
            ':user_id'       => $userId,
            ':match_id'      => $matchId,
            ':claim_message' => $claimMessage
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Claims
    |--------------------------------------------------------------------------
    */

    public function getAllClaims()
    {
        $stmt = $this->conn->prepare(
            "SELECT
                c.*,
                u.fullname,
                m.confidence_score,

                l.item_name AS lost_item,
                f.item_name AS found_item

             FROM claims c

             INNER JOIN users u
                ON c.user_id = u.id

             INNER JOIN matches m
                ON c.match_id = m.id

             INNER JOIN lost_items l
                ON m.lost_item_id = l.id

             INNER JOIN found_items f
                ON m.found_item_id = f.id

             ORDER BY c.created_at DESC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Claims By User
    |--------------------------------------------------------------------------
    */

    public function getClaimsByUser($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT
                c.*,
                m.confidence_score
             FROM claims c

             INNER JOIN matches m
                ON c.match_id = m.id

             WHERE c.user_id = :user_id

             ORDER BY c.created_at DESC"
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Single Claim
    |--------------------------------------------------------------------------
    */

    public function getClaimById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM claims
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Update Claim Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        $claimId,
        $status
    )
    {
        $stmt = $this->conn->prepare(
            "UPDATE claims
             SET status = :status
             WHERE id = :id"
        );

        return $stmt->execute([
            ':status' => $status,
            ':id'     => $claimId
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Approve Claim
    |--------------------------------------------------------------------------
    */

    public function approveClaim($claimId)
    {
        return $this->updateStatus(
            $claimId,
            'approved'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject Claim
    |--------------------------------------------------------------------------
    */

    public function rejectClaim($claimId)
    {
        return $this->updateStatus(
            $claimId,
            'rejected'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create Notification
    |--------------------------------------------------------------------------
    */

    public function createNotification(
        $userId,
        $message
    )
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO notifications
            (
                user_id,
                message
            )
            VALUES
            (
                :user_id,
                :message
            )"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':message' => $message
        ]);
    }
}

