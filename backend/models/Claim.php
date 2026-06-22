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
                claim_message,
                status
            )
            VALUES
            (
                :user_id,
                :match_id,
                :claim_message,
                'pending'
            )"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':match_id' => $matchId,
            ':claim_message' => $claimMessage
        ]);
    }

    public function getClaimsByUser($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT
                c.*,
                m.confidence_score
             FROM claims c

             LEFT JOIN matches m
                ON c.match_id = m.id

             WHERE c.user_id = :user_id

             ORDER BY c.created_at DESC"
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllClaims()
    {
        $stmt = $this->conn->prepare(
            "SELECT
                c.*,
                u.fullname
             FROM claims c

             INNER JOIN users u
                ON c.user_id = u.id

             ORDER BY c.created_at DESC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($claimId, $status)
    {
        $stmt = $this->conn->prepare(
            "UPDATE claims
             SET status = :status
             WHERE id = :id"
        );

        return $stmt->execute([
            ':status' => $status,
            ':id' => $claimId
        ]);
    }

    public function getClaimById($claimId)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM claims
             WHERE id = :id
             LIMIT 1"
        );

        $stmt->execute([
            ':id' => $claimId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}