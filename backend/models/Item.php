<?php
class Item {
    private $conn;

    // Object properties
    public $item_id;
    public $reporter_id;
    public $item_name;
    public $category;
    public $description;
    public $location;
    public $color;
    public $brand_model;
    public $distinguishing_features;
    public $date_lost_found;
    public $image_path;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create item
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    reporter_id = :reporter_id,
                    item_name = :item_name,
                    category = :category,
                    description = :description,
                    location = :location,
                    color = :color,
                    brand_model = :brand_model,
                    distinguishing_features = :distinguishing_features,
                    date_lost_found = :date_lost_found,
                    image_path = :image_path,
                    status = :status";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":reporter_id", $this->reporter_id);
        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":brand_model", $this->brand_model);
        $stmt->bindParam(":distinguishing_features", $this->distinguishing_features);
        $stmt->bindParam(":date_lost_found", $this->date_lost_found);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Read all items (Joins with users table to get reporter name)
    public function readAll() {
        $query = "SELECT i.*, u.full_name as reporter_name 
                  FROM " . $this->table_name . " i 
                  LEFT JOIN users u ON i.reporter_id = u.user_id 
                  ORDER BY i.created_at DESC";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Read single item
    public function readOne() {
        $query = "SELECT i.*, u.full_name as reporter_name, u.email as reporter_email 
                  FROM " . $this->table_name . " i 
                  LEFT JOIN users u ON i.reporter_id = u.user_id 
                  WHERE i.item_id = ? LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->item_id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->item_name = $row['item_name'];
            $this->category = $row['category'];
            $this->description = $row['description'];
            $this->location = $row['location'];
            $this->color = $row['color'];
            $this->brand_model = $row['brand_model'];
            $this->distinguishing_features = $row['distinguishing_features'];
            $this->date_lost_found = $row['date_lost_found'];
            $this->image_path = $row['image_path'];
            $this->status = $row['status'];
            return true;
        }
        return false;
    }

    // Update item
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    item_name = :item_name,
                    category = :category,
                    description = :description,
                    location = :location,
                    color = :color,
                    brand_model = :brand_model,
                    distinguishing_features = :distinguishing_features,
                    date_lost_found = :date_lost_found,
                    image_path = :image_path,
                    status = :status
                WHERE item_id = :item_id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":item_name", $this->item_name);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":location", $this->location);
        $stmt->bindParam(":color", $this->color);
        $stmt->bindParam(":brand_model", $this->brand_model);
        $stmt->bindParam(":distinguishing_features", $this->distinguishing_features);
        $stmt->bindParam(":date_lost_found", $this->date_lost_found);
        $stmt->bindParam(":image_path", $this->image_path);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":item_id", $this->item_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Delete item
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE item_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->item_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>