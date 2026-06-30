<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FoundItem.php';

class FoundItemController {
    private $db;
    private $item;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->item = new FoundItem($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->readAll();
        } elseif ($method === 'POST') {
            if (!empty($_FILES)) {
                $this->createWithFile();
            } else {
                $input = json_decode(file_get_contents("php://input"));
                $this->create($input);
            }
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method Not Allowed"]);
        }
    }

    private function readAll() {
        $stmt = $this->item->readAll();
        $items_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($items_arr, $row);
        }
        http_response_code(200);
        echo json_encode(["items" => $items_arr]);
    }

    private function create($data) {
        if(!empty($data->user_id) && !empty($data->item_name) && !empty($data->category) && !empty($data->description) && !empty($data->location_found) && !empty($data->date_found)) {
            $this->mapData($data);
            if($this->item->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Found item reported successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to report item."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Data is incomplete."]);
        }
    }

    private function createWithFile() {
        $target_dir = __DIR__ . "/../uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $imageFileType = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["image"]["tmp_name"]);
        
        if($check !== false && $_FILES["image"]["size"] <= 5000000 && in_array($imageFileType, ["jpg","jpeg","png","gif"])) {
            $new_filename = uniqid('found_', true) . '.' . $imageFileType;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $this->item->user_id = $_POST['user_id'];
                $this->item->item_name = $_POST['item_name'];
                $this->item->category = $_POST['category'];
                $this->item->description = $_POST['description'];
                $this->item->location_found = $_POST['location_found'];
                $this->item->date_found = $_POST['date_found'];
                $this->item->color = $_POST['color'] ?? null;
                $this->item->brand_model = $_POST['brand_model'] ?? null;
                $this->item->unique_features = $_POST['unique_features'] ?? null;
                $this->item->image = "uploads/" . $new_filename;
                $this->item->status = 'pending';

                if($this->item->create()) {
                    http_response_code(201);
                    echo json_encode(["message" => "Found item and image uploaded successfully."]);
                } else {
                    http_response_code(503);
                    echo json_encode(["message" => "Unable to report item."]);
                }
            } else {
                http_response_code(500);
                echo json_encode(["message" => "Error uploading file."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Invalid image file."]);
        }
    }

    private function mapData($data) {
        $this->item->user_id = $data->user_id;
        $this->item->item_name = $data->item_name;
        $this->item->category = $data->category;
        $this->item->description = $data->description;
        $this->item->location_found = $data->location_found;
        $this->item->date_found = $data->date_found;
        $this->item->color = $data->color ?? null;
        $this->item->brand_model = $data->brand_model ?? null;
        $this->item->unique_features = $data->unique_features ?? null;
        $this->item->image = $data->image ?? null;
        $this->item->status = 'pending';
    }
}
?>