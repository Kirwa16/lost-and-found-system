<?php

require_once __DIR__ . '/../config/database.php';

class Dashboard
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /*
    |--------------------------------------------------------------------------
    | USER DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function countLostItems($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM lost_items
             WHERE user_id = :user_id"
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchColumn();
    }

    public function countFoundItems($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM found_items
             WHERE user_id = :user_id"
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchColumn();
    }

    public function countClaims($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM claims
             WHERE user_id = :user_id"
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchColumn();
    }

    public function countRecovered($userId)
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM claims
             WHERE user_id = :user_id
             AND status = 'approved'"
        );

        $stmt->execute([
            ':user_id' => $userId
        ]);

        return $stmt->fetchColumn();
    }

    /*
    |--------------------------------------------------------------------------
    | ADMIN DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function totalUsers()
    {
        return $this->conn
            ->query("SELECT COUNT(*) FROM users")
            ->fetchColumn();
    }

    public function totalLostItems()
    {
        return $this->conn
            ->query("SELECT COUNT(*) FROM lost_items")
            ->fetchColumn();
    }

    public function totalFoundItems()
    {
        return $this->conn
            ->query("SELECT COUNT(*) FROM found_items")
            ->fetchColumn();
    }

    public function totalMatches()
    {
        return $this->conn
            ->query("SELECT COUNT(*) FROM matches")
            ->fetchColumn();
    }

    public function totalClaims()
    {
        return $this->conn
            ->query("SELECT COUNT(*) FROM claims")
            ->fetchColumn();
    }

    public function recentLostItems()
    {
        return $this->conn
            ->query(
                "SELECT *
                 FROM lost_items
                 ORDER BY created_at DESC
                 LIMIT 5"
            )
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentFoundItems()
    {
        return $this->conn
            ->query(
                "SELECT *
                 FROM found_items
                 ORDER BY created_at DESC
                 LIMIT 5"
            )
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function recentClaims()
    {
        return $this->conn
            ->query(
                "SELECT *
                 FROM claims
                 ORDER BY created_at DESC
                 LIMIT 5"
            )
            ->fetchAll(PDO::FETCH_ASSOC);
    }
}

