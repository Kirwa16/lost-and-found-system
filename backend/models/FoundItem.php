<?php
class FoundItem {
    private $conn;
    private $table_name = "found_items";

    // Object properties matching the found_items table
    public $id;
    public $user_id;
    public $item_name;
    public $category;
    public $color;
    public $brand_model;
    public $unique_features;
    public $description;
    public $location_found;
    public $date_found;
    public $image;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a found item report
    public function create()
{
    $query = "INSERT INTO " . $this->table_name . "
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
            image,
            status
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
            :image,
            :status
        )";

    $stmt = $this->conn->prepare($query);

    $this->item_name = trim($this->item_name);
    $this->category = trim($this->category);
    $this->description = trim($this->description);
    $this->location_found = trim($this->location_found);

    if (empty($this->status)) {
        $this->status = 'available';
    }

    $stmt->bindParam(":user_id", $this->user_id, PDO::PARAM_INT);
    $stmt->bindParam(":item_name", $this->item_name);
    $stmt->bindParam(":category", $this->category);
    $stmt->bindParam(":color", $this->color);
    $stmt->bindParam(":brand_model", $this->brand_model);
    $stmt->bindParam(":unique_features", $this->unique_features);
    $stmt->bindParam(":description", $this->description);
    $stmt->bindParam(":location_found", $this->location_found);
    $stmt->bindParam(":date_found", $this->date_found);
    $stmt->bindParam(":image", $this->image);
    $stmt->bindParam(":status", $this->status);

    try {

        return $stmt->execute();

    } catch (PDOException $e) {

        die("<pre>" . $e->getMessage() . "</pre>");

    }
}

    // Read all found items (Joins with users table to get reporter name)
    public function readAll() {
        $query = "SELECT f.*, u.fullname as reporter_name 
                  FROM " . $this->table_name . " f 
                  LEFT JOIN users u ON f.user_id = u.id 
                  ORDER BY f.created_at DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>