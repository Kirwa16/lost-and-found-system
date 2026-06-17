<?php

require_once __DIR__ . '/../config/database.php';

class Search
{
    private $conn;

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->connect();
    }

    /*
    |--------------------------------------------------------------------------
    | Search Lost Items
    |--------------------------------------------------------------------------
    */

    public function searchItems($keyword = '', $category = '')
    {
        $sql = "
            SELECT *
            FROM lost_items
            WHERE 1=1
        ";

        $params = [];

        if (!empty($keyword))
        {
            $sql .= "
                AND (
                    item_name LIKE :keyword
                    OR category LIKE :keyword
                    OR description LIKE :keyword
                    OR location_lost LIKE :keyword
                )
            ";

            $params[':keyword'] = '%' . $keyword . '%';
        }

        if (!empty($category))
        {
            $sql .= "
                AND category = :category
            ";

            $params[':category'] = $category;
        }

        $sql .= "
            ORDER BY created_at DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Single Item
    |--------------------------------------------------------------------------
    */

    public function getItemById($id)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM lost_items
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Get All Lost Items
    |--------------------------------------------------------------------------
    */

    public function getAllItems()
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM lost_items
             ORDER BY created_at DESC"
        );

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | Get Items By Category
    |--------------------------------------------------------------------------
    */

    public function getItemsByCategory($category)
    {
        $stmt = $this->conn->prepare(
            "SELECT *
             FROM lost_items
             WHERE category = :category
             ORDER BY created_at DESC"
        );

        $stmt->execute([
            ':category' => $category
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}