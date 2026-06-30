<?php
session_start();

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/User.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    $user->email = $_POST['email'];
    $user->password = $_POST['password'];

    if ($user->login()) {
        // Clear old session data but keep the session
        $_SESSION = array();
        
        // Set new session variables
        $_SESSION['user_id'] = $user->id;
        $_SESSION['fullname'] = $user->fullname;
        $_SESSION['email'] = $user->email;
        $_SESSION['role'] = $user->role;

        // Redirect based on role
        if ($user->role === 'admin') {
            header("Location: /admin/dashboard.php");
        } else {
            header("Location: /user/dashboard.php");
        }
        exit();
    }
}
?>