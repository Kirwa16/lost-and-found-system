<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_save_path(__DIR__ . '/../../sessions');

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/User.php';

class AuthController
{
    private $user;

    public function __construct()
    {
        $database = new Database();
        $db = $database->connect();

        $this->user = new User($db);
    }

    public function register()
    {
        if (isset($_POST['register']))
        {
            $fullname = trim($_POST['fullname']);
            $email = trim($_POST['email']);

            $password = $_POST['password'];
            $confirmPassword = $_POST['confirm_password'];

            if ($password !== $confirmPassword)
            {
                $_SESSION['error'] = "Passwords do not match.";

                header("Location: /register.php");
                exit;
            }

            $existingUser = $this->user->findByEmail($email);

            if ($existingUser)
            {
                $_SESSION['error'] = "Email already exists.";

                header("Location: /register.php");
                exit;
            }

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            if (
                !$this->user->register(
                    $fullname,
                    $email,
                    $hashedPassword
                )
            ) {
                $_SESSION['error'] =
                    "Failed to create account.";

                header("Location: /register.php");
                exit;
            }

            $_SESSION['success'] =
                "Account created successfully. Please login.";

            header("Location: /login.php");
            exit;
        }
    }

    public function login()
    {
        if (isset($_POST['login']))
        {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $user = $this->user->findByEmail($email);

            if (!$user)
            {
                $_SESSION['error'] =
                    "Invalid email or password.";

                header("Location: /login.php");
                exit;
            }

            if (
                !password_verify(
                    $password,
                    $user['password']
                )
            )
            {
                $_SESSION['error'] =
                    "Invalid email or password.";

                header("Location: /login.php");
                exit;
            }

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin')
            {
                header("Location: /admin/dashboard.php");
            }
            else
            {
                header("Location: /user/dashboard.php");
            }

            exit;
        }
    }
}

$auth = new AuthController();

if (isset($_POST['register']))
{
    $auth->register();
}

if (isset($_POST['login']))
{
    $auth->login();
}