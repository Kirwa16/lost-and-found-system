<?php
// Allow cross-origin requests (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and claim model
include_once '../config/database.php';
include_once '../models/Claim.php';

// Get database connection and create claim object
$database = new Database();
$db = $database->getConnection();
$claim = new Claim($db);

// Check request method
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Submit a new claim
    $data = json_decode(file_get_contents("php://input"));

    // Check if required data is provided
    if(!empty($data->item_id) && !empty($data->claimant_id) && !empty($data->proof_of_ownership)) {
        
        // Set claim property values
        $claim->item_id = $data->item_id;
        $claim->claimant_id = $data->claimant_id;
        $claim->proof_of_ownership = $data->proof_of_ownership;

        // Create the claim
        if($claim->create()) {
            http_response_code(201);
            echo json_encode(array("message" => "Claim submitted successfully."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Unable to submit claim."));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Unable to submit claim. Data is incomplete."));
    }

} elseif ($method === 'GET') {
    // Read pending claims (For Admin Dashboard)
    $stmt = $claim->readPending();
    $claims_arr = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $claim_details = array(
            "claim_id" => $claim_id,
            "item_name" => $item_name,
            "claimant_name" => $claimant_name,
            "proof_of_ownership" => $proof_of_ownership,
            "date_submitted" => $date_submitted
        );
        array_push($claims_arr, $claim_details);
    }

    if(count($claims_arr) > 0) {
        http_response_code(200);
        echo json_encode(array("claims" => $claims_arr));
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "No pending claims found."));
    }
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Method Not Allowed"));
}
?>