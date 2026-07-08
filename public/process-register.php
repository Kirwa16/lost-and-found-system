<?php

session_start();

require_once __DIR__ . '/../backend/config/database.php';
require_once __DIR__ . '/../backend/models/User.php';
require_once __DIR__ . '/../backend/helpers/csrf.php';

/* ----------------------------------
   Only Allow POST Requests
----------------------------------- */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /register.php");
    exit();
}

/* ----------------------------------
   CSRF Validation
----------------------------------- */

if (!csrf_validate($_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = "Security token expired. Please try again.";
    header("Location: /register.php");
    exit();
}

/* ----------------------------------
   Get Form Data
----------------------------------- */

$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$role = $_POST['role'] ?? '';
$admissionNumber = trim($_POST['admission_number'] ?? '');
$registrationNumber = trim($_POST['registration_number'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';

/* ----------------------------------
   Validation
----------------------------------- */

if (
    $fullname === '' ||
    $email === '' ||
    $password === '' ||
    $confirmPassword === '' ||
    $role === ''
) {
    $_SESSION['error'] = "Please fill in all required fields.";
    header("Location: /register.php");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Please enter a valid email address.";
    header("Location: /register.php");
    exit();
}

if (!in_array($role, ['student', 'staff'], true)) {
    $_SESSION['error'] = "Invalid account type selected.";
    header("Location: /register.php");
    exit();
}

if ($role === 'student' && $admissionNumber === '') {
    $_SESSION['error'] = "Admission number is required.";
    header("Location: /register.php");
    exit();
}

if ($role === 'staff' && $registrationNumber === '') {
    $_SESSION['error'] = "Registration number is required.";
    header("Location: /register.php");
    exit();
}

if ($password !== $confirmPassword) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: /register.php");
    exit();
}

if (strlen($password) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters long.";
    header("Location: /register.php");
    exit();
}

/* ----------------------------------
   Register User
----------------------------------- */

$database = new Database();
$db = $database->getConnection();

$user = new User($db);

$user->fullname = $fullname;
$user->email = $email;
$user->role = $role;
$user->admission_number = $admissionNumber;
$user->registration_number = $registrationNumber;
$user->password = $password;

if ($user->register()) {

    $_SESSION['success'] = "Registration successful. You can now log in.";

    header("Location: /login.php");
    exit();
}

$_SESSION['error'] = "Registration failed. The email, admission number, or registration number may already exist.";

header("Location: /register.php");
exit();

?>