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

    // Handle Registration
    public function register() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->full_name) && !empty($data->email) && !empty($data->password) && !empty($data->student_staff_id) && !empty($data->phone_number)) {
            
            $this->user->full_name = $data->full_name;
            $this->user->email = $data->email;
            $this->user->password = $data->password;
            $this->user->student_staff_id = $data->student_staff_id;
            $this->user->phone_number = $data->phone_number;
            $this->user->role = 'student';

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

    // Handle Login
    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if(!empty($data->email) && !empty($data->password)) {
            
            $this->user->email = $data->email;
            $this->user->password = $data->password;

            if($this->user->login()) {
                http_response_code(200);
                echo json_encode(array(
                    "message" => "Login successful.",
                    "user_id" => $this->user->user_id,
                    "full_name" => $this->user->full_name,
                    "role" => $this->user->role
                ));
            } else {
                http_response_code(401);
                echo json_encode(array("message" => "Login failed. Invalid email or password."));
            }
        } else {
            http_response_code(400);
            echo json_encode(array("message" => "Unable to login. Data is incomplete."));
        }
    }
}
?>