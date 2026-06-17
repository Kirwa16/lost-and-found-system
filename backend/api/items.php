<?php
// Allow cross-origin requests (CORS) - Crucial for Frontend/Backend communication
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and item model
include_once '../config/database.php';
include_once '../models/Item.php';

// Get database connection and create item object
$database = new Database();
$db = $database->getConnection();
$item = new Item($db);

// Check request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Read all items (For Search and Dashboard)
    $stmt = $item->readAll();
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
            "reporter_name" => $reporter_name
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

} elseif ($method === 'POST') {
    // Create item (Report Lost/Found)
    $data = json_decode(file_get_contents("php://input"));

    // Check if required data is provided
    if(!empty($data->reporter_id) && !empty($data->item_name) && !empty($data->category) && !empty($data->description) && !empty($data->location) && !empty($data->date_lost_found)) {
        
        // Set item property values
        $item->reporter_id = $data->reporter_id;
        $item->item_name = $data->item_name;
        $item->category = $data->category;
        $item->description = $data->description;
        $item->location = $data->location;
        $item->color = isset($data->color) ? $data->color : null;
        $item->brand_model = isset($data->brand_model) ? $data->brand_model : null;
        $item->distinguishing_features = isset($data->distinguishing_features) ? $data->distinguishing_features : null;
        $item->date_lost_found = $data->date_lost_found;
        $item->image_path = isset($data->image_path) ? $data->image_path : null;
        $item->status = isset($data->status) ? $data->status : 'pending';

        // Create the item
        if($item->create()) {
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
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>