
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
        $image
    )
    {
        $sql = "INSERT INTO lost_items
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
        )";

        $stmt = $this->conn->prepare($sql);

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

    public function getUserLostItems($userId)
    {
        $sql = "
            SELECT *
            FROM lost_items
        WHERE user_id = :user_id
        ORDER BY created_at DESC
    ";

    $stmt = $this->conn->prepare($sql);

    $stmt->execute([
        ':user_id' => $userId
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function createFoundItem(
    $userId,
    $itemName,
    $category,
    $description,
    $locationFound,
    $dateFound,
    $image
)
{
    $sql = "
        INSERT INTO found_items
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
        )
    ";

    $stmt = $this->conn->prepare($sql);

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



    
}

