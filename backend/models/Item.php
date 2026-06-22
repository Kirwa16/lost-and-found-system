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
        $color,
        $brandModel,
        $uniqueFeatures,
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
                color,
                brand_model,
                unique_features,
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
                :color,
                :brand_model,
                :unique_features,
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
            ':color' => $color,
            ':brand_model' => $brandModel,
            ':unique_features' => $uniqueFeatures,
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
        $color,
        $brandModel,
        $uniqueFeatures,
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
                color,
                brand_model,
                unique_features,
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
                :color,
                :brand_model,
                :unique_features,
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
            ':color' => $color,
            ':brand_model' => $brandModel,
            ':unique_features' => $uniqueFeatures,
            ':description' => $description,
            ':location_found' => $locationFound,
            ':date_found' => $dateFound,
            ':image' => $image
        ]);
    }
}