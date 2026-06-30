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

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'POST') {
            $this->create();
        } elseif ($method === 'GET') {
            $this->readPending();
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method Not Allowed"]);
        }
    }

    private function create() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->user_id) && !empty($data->match_id) && !empty($data->claim_message)) {
            $this->claim->user_id = $data->user_id;
            $this->claim->match_id = $data->match_id;
            $this->claim->claim_message = $data->claim_message;

            if($this->claim->create()) {
                http_response_code(201);
                echo json_encode(["message" => "Claim submitted successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to submit claim."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Data is incomplete."]);
        }
    }

    private function readPending() {
        $stmt = $this->claim->readPending();
        $claims_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($claims_arr, $row);
        }
        http_response_code(200);
        echo json_encode(["claims" => $claims_arr]);
    }
}
?>