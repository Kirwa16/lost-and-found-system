<?php

require_once __DIR__ . '/../config/database.php';

class Claim
{
    private $conn;

    public function __construct()
    {
        $db = new Database();
        $this->conn = $db->connect();
    }

    public function createClaim(
        $userId,
        $matchId,
        $message
    )
    {
        $sql = "
            INSERT INTO claims
            (
                user_id,
                match_id,
                claim_message
            )
            VALUES
            (
                :user_id,
                :match_id,
                :message
            )
        ";

        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':user_id' => $userId,
            ':match_id' => $matchId,
            ':message' => $message
        ]);
    }

    public function getUserClaims($userId)
    {
        $sql = "
            SELECT *
            FROM claims
            WHERE user_id = :user_id
            ORDER BY created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
