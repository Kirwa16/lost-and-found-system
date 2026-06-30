<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/ItemMatch.php';  // Changed from Match.php

class MatchController {
    private $db;
    private $match;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->match = new ItemMatch($this->db);  // Changed from Match to ItemMatch
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method === 'GET') {
            $this->readPending();
        } elseif ($method === 'PUT') {
            $this->updateStatus();
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method Not Allowed"]);
        }
    }

    private function readPending() {
        $stmt = $this->match->readPending();
        $matches_arr = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($matches_arr, $row);
        }
        http_response_code(200);
        echo json_encode(["matches" => $matches_arr]);
    }

    private function updateStatus() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->id) && !empty($data->status)) {
            $this->match->id = $data->id;
            $this->match->status = $data->status;

            if($this->match->updateStatus()) {
                http_response_code(200);
                echo json_encode(["message" => "Match status updated successfully."]);
            } else {
                http_response_code(503);
                echo json_encode(["message" => "Unable to update match."]);
            }
        } else {
            http_response_code(400);
            echo json_encode(["message" => "Data is incomplete."]);
        }
    }
}
?>