<?php

require_once __DIR__ . '/../config/database.php';

class Item
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    public function createLostItem(
        $userId,
        $itemName,
        $category,
        $description,
        $locationLost,
        $dateLost,
        $image = null
    )
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO lost_items
            (
                user_id,
                item_name,
                category,
                description,
                location_lost,
                date_lost,
                image
            )
            VALUES
            (
                :user_id,
                :item_name,
                :category,
                :description,
                :location_lost,
                :date_lost,
                :image
            )"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':item_name' => $itemName,
            ':category' => $category,
            ':description' => $description,
            ':location_lost' => $locationLost,
            ':date_lost' => $dateLost,
            ':image' => $image
        ]);
    }

    public function createFoundItem(
        $userId,
        $itemName,
        $category,
        $description,
        $locationFound,
        $dateFound,
        $image = null
    )
    {
        $stmt = $this->conn->prepare(
            "INSERT INTO found_items
            (
                user_id,
                item_name,
                category,
                description,
                location_found,
                date_found,
                image
            )
            VALUES
            (
                :user_id,
                :item_name,
                :category,
                :description,
                :location_found,
                :date_found,
                :image
            )"
        );

        return $stmt->execute([
            ':user_id' => $userId,
            ':item_name' => $itemName,
            ':category' => $category,
            ':description' => $description,
            ':location_found' => $locationFound,
            ':date_found' => $dateFound,
            ':image' => $image
        ]);
    }

    public function getLostItems()
    {
        $stmt = $this->conn->query(
            "SELECT *
             FROM lost_items
             ORDER BY created_at DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFoundItems()
    {
        $stmt = $this->conn->query(
            "SELECT *
             FROM found_items
             ORDER BY created_at DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

