<?php
class LostItem {
    private $conn;
    private $table_name = "lost_items";

    // Object properties matching the lost_items table
    public $id;
    public $user_id;
    public $item_name;
    public $category;
    public $color;
    public $brand_model;
    public $unique_features;
    public $description;
    public $location_lost;
    public $date_lost;
    public $image;
    public $status;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a lost item report
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET user_id = :user_id, item_name = :item_name, category = :category, 
                      color = :color, brand_model = :brand_model, unique_features = :unique_features, 
                      description = :description, location_lost = :location_lost, date_lost = :date_lost, 
                      image = :image, status = :status";

        $stmt = $this->conn->prepare($query);

        // Sanitize inputs
        $this->item_name = htmlspecialchars(strip_tags($this->item_name));
        $this->description = htmlspecialchars(strip_tags($this->description));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":brand_model", $this->brand_model);
        $stmt->bindParam(":unique_features", $this->unique_features);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location_lost", $this->location_lost);
        $stmt->bindParam(":date_lost", $this->date_lost);
        $stmt->bindParam(":image", $this->image);
        
        // Default status is 'pending' as per your database ENUM
        $this->status = $this->status ? $this->status : 'pending';
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all lost items (Joins with users table to get reporter name)
    public function readAll() {
        $query = "SELECT l.*, u.fullname as reporter_name 
                  FROM " . $this->table_name . " l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  ORDER BY l.created_at DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>