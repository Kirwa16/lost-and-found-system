<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/LostItem.php';

class LostItemController {
    private $db;
    private $item;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->item = new LostItem($this->db);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            if (isset($_GET['id'])) {
                $this->item->id = $_GET['id'];
                $this->readOne();
            } else {
                $this->readAll();
            }
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

    private function readOne() {
        // (Add readOne logic to LostItem.php model if needed, or keep it simple for now)
        http_response_code(200);
        echo json_encode(["message" => "Read one item logic here"]);
    }

    private function create($data) {
        if(!empty($data->user_id) && !empty($data->item_name) && !empty($data->category) && !empty($data->description) && !empty($data->location_lost) && !empty($data->date_lost)) {
            $this->mapData($data);
            if($this->item->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Lost item reported successfully."]);
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
            $new_filename = uniqid('lost_', true) . '.' . $imageFileType;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $this->item->user_id = $_POST['user_id'];
                $this->item->item_name = $_POST['item_name'];
                $this->item->category = $_POST['category'];
                $this->item->description = $_POST['description'];
                $this->item->location_lost = $_POST['location_lost'];
                $this->item->date_lost = $_POST['date_lost'];
                $this->item->color = $_POST['color'] ?? null;
                $this->item->brand_model = $_POST['brand_model'] ?? null;
                $this->item->unique_features = $_POST['unique_features'] ?? null;
                $this->item->image = "uploads/" . $new_filename;
                $this->item->status = 'pending';

                if($this->item->create()) {
                    http_response_code(201);
                    echo json_encode(["message" => "Lost item and image uploaded successfully."]);
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
        $this->item->location_lost = $data->location_lost;
        $this->item->date_lost = $data->date_lost;
        $this->item->color = $data->color ?? null;
        $this->item->brand_model = $data->brand_model ?? null;
        $this->item->unique_features = $data->unique_features ?? null;
        $this->item->image = $data->image ?? null;
        $this->item->status = 'pending';
    }
}
?>