<?php
// Allow cross-origin requests (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include database and user model
include_once '../config/database.php';
include_once '../models/User.php';

// Get database connection and create user object
$database = new Database();
$db = $database->getConnection();
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if(!empty($data->email) && !empty($data->password)) {

    // Set user property values
    $user->email = $data->email;
    $user->password = $data->password;

    // Login the user
    if($user->login()) {

        // Set response code - 200 ok
        http_response_code(200);

        // Tell the user
        echo json_encode(array(
            "message" => "Login successful.",
            "user_id" => $user->user_id,
            "full_name" => $user->full_name,
            "role" => $user->role
        ));
    }

    // If unable to login, tell the user
    else {
        // Set response code - 401 unauthorized
        http_response_code(401);

        // Tell the user
        echo json_encode(array("message" => "Login failed. Invalid email or password."));
    }
}

// Tell the user data is incomplete
else {
    // Set response code - 400 bad request
    http_response_code(400);

    // Tell the user
    echo json_encode(array("message" => "Unable to login. Data is incomplete."));
}
?>