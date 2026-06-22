<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Item.php';

class ItemController {
    private $db;
    private $item;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->item = new Item($this->db);
    }

    // Main router for the controller
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $input = json_decode(file_get_contents("php://input"));

        if ($method === 'GET') {
            // If an ID is provided in the URL (e.g., ?id=5), read one item. Otherwise, read all.
            if (isset($_GET['id'])) {
                $this->item->item_id = $_GET['id'];
                $this->readOne();
            } else {
                $this->readAll();
            }
        } elseif ($method === 'POST') {
            $this->create($input);
        } elseif ($method === 'PUT') {
            $this->update($input);
        } elseif ($method === 'DELETE') {
            if (isset($_GET['id'])) {
                $this->item->item_id = $_GET['id'];
                $this->delete();
            } else {
                http_response_code(400);
                echo json_encode(array("message" => "Item ID is required to delete."));
            }
        } else {
            http_response_code(405);
            echo json_encode(array("message" => "Method Not Allowed"));
        }
    }

    // Read all items
    private function readAll() {
        $stmt = $this->item->readAll();
        $items_arr = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            extract($row);
            $item_details = array(
                "item_id" => $item_id,
                "item_name" => $item_name,
                "category" => $category,
                "description" => $description,
                "location" => $location,
                "date_lost_found" => $date_lost_found,
                "status" => $status,
                "image_path" => $image_path,
                "reporter_name" => isset($reporter_name) ? $reporter_name : null
            );
            array_push($items_arr, $item_details);
        }

        if(count($items_arr) > 0) {
            http_response_code(200);
            echo json_encode(array("items" => $items_arr));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "No items found."));
        }
    }

    // Read single item
    private function readOne() {
        if($this->item->readOne()) {
            http_response_code(200);
            echo json_encode(array(
                "item_id" => $this->item->item_id,
                "item_name" => $this->item->item_name,
                "category" => $this->item->category,
                "description" => $this->item->description,
                "location" => $this->item->location,
                "color" => $this->item->color,
                "brand_model" => $this->item->brand_model,
                "distinguishing_features" => $this->item->distinguishing_features,
                "date_lost_found" => $this->item->date_lost_found,
                "image_path" => $this->item->image_path,
                "status" => $this->item->status
            ));
        } else {
            http_response_code(404);
            echo json_encode(array("message" => "Item not found."));
        }
    }

    // Create a new item
    private function create($data) {
        if(!empty($data->reporter_id) && !empty($data->item_name) && !empty($data->category) && !empty($data->description) && !empty($data->location) && !empty($data->date_lost_found)) {
            
            $this->item->reporter_id = $data->reporter_id;
            $this->item->item_name = $data->item_name;
            $this->item->category = $data->category;
            $this->item->description = $data->description;
            $this->item->location = $data->location;
            $this->item->color = isset($data->color) ? $data->color : null;
            $this->item->brand_model = isset($data->brand_model) ? $data->brand_model : null;
            $this->item->distinguishing_features = isset($data->distinguishing_features) ? $data->distinguishing_features : null;
            $this->item->date_lost_found = $data->date_lost_found;
            $this->item->image_path = isset($data->image_path) ? $data->image_path : null;
            $this->item->status = isset($data->status) ? $data->status : 'pending';

            if($this->item->create()) {
                http_response_code(201);
                echo json_encode(array("message" => "Item was successfully reported."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to report item."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to report item. Data is incomplete."));
        }
    }

    // Update an existing item
    private function update($data) {
        if(!empty($data->item_id) && !empty($data->item_name)) {
            
            $this->item->item_id = $data->item_id;
            $this->item->item_name = $data->item_name;
            $this->item->category = $data->category;
            $this->item->description = $data->description;
            $this->item->location = $data->location;
            $this->item->color = isset($data->color) ? $data->color : null;
            $this->item->brand_model = isset($data->brand_model) ? $data->brand_model : null;
            $this->item->distinguishing_features = isset($data->distinguishing_features) ? $data->distinguishing_features : null;
            $this->item->date_lost_found = $data->date_lost_found;
            $this->item->image_path = isset($data->image_path) ? $data->image_path : null;
            $this->item->status = isset($data->status) ? $data->status : 'pending';

            if($this->item->update()) {
                http_response_code(200);
                echo json_encode(array("message" => "Item was successfully updated."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to update item."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to update item. Data is incomplete."));
        }
    }

    // Delete an item
    private function delete() {
        if($this->item->delete()) {
            http_response_code(200);
            echo json_encode(array("message" => "Item was successfully deleted."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to delete item."));
        }
    }
}
?>