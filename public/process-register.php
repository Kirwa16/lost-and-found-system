<?php
session_start();

// Include backend config and model
require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/User.php';
require_once __DIR__ . '/../backend/helpers/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!csrf_validate($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Security token expired. Please try again.";
        header("Location: /register.php");
        exit();
    }

    
    $database = new Database();
    $db = $database->getConnection();
    $user = new User($db);

    // Map registration form fields to the User model
    // Adjust these keys if Brian's register.php uses different input names
    $user->fullname = $_POST['fullname'] ?? $_POST['name'] ?? '';
    $user->email = $_POST['email'] ?? '';
    $user->password = $_POST['password'] ?? '';
    $user->role = in_array($_POST['role'] ?? '', ['student', 'staff'], true)
        ? $_POST['role']
        : 'student';

    if ($user->register()) {
        // Registration successful
        $_SESSION['success'] = "Registration successful. Please login.";
        header("Location: /login.php");
        exit();
    } else {
        // Registration failed (e.g., email already exists)
        $_SESSION['error'] = "Registration failed. Email might already exist.";
        header("Location: /register.php");
        exit();
    }
}
?>
