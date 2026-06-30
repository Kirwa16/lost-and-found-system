<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    // Public method to handle all requests
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'POST') {
            $input = json_decode(file_get_contents("php://input"));
            
            // Check if this is a login or register request based on the data
            if (isset($input->password) && isset($input->fullname)) {
                $this->register($input);
            } elseif (isset($input->email) && isset($input->password)) {
                $this->login($input);
            } else {
                http_response_code(400);
                echo json_encode(["message" => "Invalid request data"]);
            }
        } else {
            http_response_code(405);
            echo json_encode(["message" => "Method Not Allowed"]);
        }
    }

    // Private method for login
    private function login($data) {
        if(!empty($data->email) && !empty($data->password)) {
            $this->user->email = $data->email;
            $this->user->password = $data->password;

            if($this->user->login()) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login successful.",
                    "user_id" => $this->user->id,
                    "fullname" => $this->user->fullname,
                    "role" => $this->user->role
                ));
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Invalid email or password."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to login. Data is incomplete."));
        }
    }

    // Private method for register
    private function register($data) {
        if(!empty($data->fullname) && !empty($data->email) && !empty($data->password)) {
            
            // Check if email already exists
            $this->user->email = $data->email;
            if($this->user->emailExists()) {
                http_response_code(400);
                echo json_encode(array("message" => "Email already exists."));
                return;
            }

            $this->user->fullname = $data->fullname;
            $this->user->email = $data->email;
            $this->user->password = $data->password;

            if($this->user->register()) {
                http_response_code(201);
                echo json_encode(array("message" => "User was successfully registered."));
            } else {
                http_response_code(503);
                echo json_encode(array("message" => "Unable to register user."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to register user. Data is incomplete."));
        }
    }
}
?>