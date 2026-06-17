<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Claim.php';

class ClaimController {
    private $db;
    private $claim;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->claim = new Claim($this->db);
    }

    // Main router for the controller
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $this->create();
        } elseif ($method === 'GET') {
            $this->readPending();
        } else {
            http_response_code(405);
            echo json_encode(array("message" => "Method Not Allowed"));
        }
    }

    // Submit a new claim
    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->item_id) && !empty($data->claimant_id) && !empty($data->proof_of_ownership)) {
            
            $this->claim->item_id = $data->item_id;
            $this->claim->claimant_id = $data->claimant_id;
            $this->claim->proof_of_ownership = $data->proof_of_ownership;

            if($this->claim->create()) {
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
    }

    // Read pending claims (For Admin Dashboard)
    private function readPending() {
        $stmt = $this->claim->readPending();
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
    }
}
?>