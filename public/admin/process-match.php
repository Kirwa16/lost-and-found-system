<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: /user/dashboard.php");
    exit;
}

if (
    !isset($_GET['id']) ||
    !is_numeric($_GET['id']) ||
    !isset($_GET['action'])
) {
    header("Location: matches.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/MatchController.php';

$controller = new MatchController();

$id = (int)$_GET['id'];
$action = $_GET['action'];

switch ($action) {
    case 'approve':
        $success = $controller->approve($id);
        break;

    case 'reject':
        $success = $controller->reject($id);
        break;

    default:
        header("Location: matches.php");
        exit;
}

if ($success) {
    header("Location: matches.php?success=" . urlencode("Match processed successfully."));
} else {
    header("Location: matches.php?error=" . urlencode("Unable to process match."));
}

exit;
