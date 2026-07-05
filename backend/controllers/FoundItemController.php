<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/FoundItem.php';
require_once __DIR__ . '/../helpers/csrf.php';

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
            if (!empty($_POST) || $this->isMultipartRequest()) {
                $this->createFromForm();
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
        if(!$this->hasLoggedInUser()) {
            http_response_code(401);
            echo json_encode(["message" => "Please log in before reporting an item."]);
            return;
        }

        if(!empty($data->item_name) && !empty($data->category) && !empty($data->description) && !empty($data->location_found) && !empty($data->date_found)) {
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

    private function createFromForm() {
        if(!csrf_validate($_POST['csrf_token'] ?? null)) {
            $_SESSION['error'] = "Security token expired. Please try again.";
            header("Location: /user/report-found.php");
            exit;
        }

        if(!$this->hasLoggedInUser()) {
            $_SESSION['error'] = "Please log in before reporting an item.";
            header("Location: /login.php");
            exit;
        }

        if(empty($_POST['item_name']) || empty($_POST['category']) || empty($_POST['description']) || empty($_POST['location_found']) || empty($_POST['date_found'])) {
            $_SESSION['error'] = "Please fill in all required fields.";
            header("Location: /user/report-found.php");
            exit;
        }

        $image = $this->uploadImage('found_');
        if($image['error'] !== null) {
            $_SESSION['error'] = $image['error'];
            header("Location: /user/report-found.php");
            exit;
        }

        $category = trim($_POST['category']);
        if($category === 'Other' && !empty($_POST['custom_category'])) {
            $category = trim($_POST['custom_category']);
        }

        $this->item->user_id = $_SESSION['user_id'];
        $this->item->item_name = $_POST['item_name'];
        $this->item->category = $category;
        $this->item->description = $_POST['description'];
        $this->item->location_found = $_POST['location_found'];
        $this->item->date_found = $_POST['date_found'];
        $this->item->color = $_POST['color'] ?? null;
        $this->item->brand_model = $_POST['brand_model'] ?? null;
        $this->item->unique_features = $_POST['unique_features'] ?? null;
        $this->item->image = $image['path'];
        $this->item->status = 'pending';

        try {
            if($this->item->create()) {
                $_SESSION['success'] = "Item reported successfully";
                header("Location: /user/my-reports.php");
                exit;
            }
        } catch (Throwable $e) {
            $_SESSION['error'] = "Unable to report item.";
            header("Location: /user/report-found.php");
            exit;
        }

        $_SESSION['error'] = "Unable to report item.";
        header("Location: /user/report-found.php");
        exit;
    }

    private function mapData($data) {
        $this->item->user_id = $_SESSION['user_id'];
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

    private function uploadImage($prefix) {
        if(!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
            return ['path' => null, 'error' => null];
        }

        if($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            return ['path' => null, 'error' => "Image upload failed."];
        }

        if($_FILES['image']['size'] > 2 * 1024 * 1024) {
            return ['path' => null, 'error' => "Image must be 2MB or smaller."];
        }

        $allowedMimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif'
        ];

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($_FILES['image']['tmp_name']);

        if(!isset($allowedMimeTypes[$mimeType])) {
            return ['path' => null, 'error' => "Image must be a JPG, PNG, or GIF file."];
        }

        if(getimagesize($_FILES['image']['tmp_name']) === false) {
            return ['path' => null, 'error' => "Invalid image file."];
        }

        $imageFileType = $allowedMimeTypes[$mimeType];

        $target_dir = __DIR__ . "/../../public/uploads/";
        if(!is_dir($target_dir) && !mkdir($target_dir, 0777, true)) {
            return ['path' => null, 'error' => "Unable to prepare upload folder."];
        }

        $new_filename = uniqid($prefix, true) . '.' . $imageFileType;
        $target_file = $target_dir . $new_filename;

        if(!move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            return ['path' => null, 'error' => "Error uploading file."];
        }

        return ['path' => "uploads/" . $new_filename, 'error' => null];
    }

    private function hasLoggedInUser() {
        return isset($_SESSION['user_id']);
    }

    private function isMultipartRequest() {
        return isset($_SERVER['CONTENT_TYPE'])
            && stripos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;
    }
}
?>
