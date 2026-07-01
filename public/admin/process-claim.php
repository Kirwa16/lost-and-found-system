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
    header("Location: claims.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/ClaimController.php';

$controller = new ClaimController();

$id = (int)$_GET['id'];
$action = $_GET['action'];

$success = false;

switch ($action) {

    case 'approve':
        $success = $controller->approve($id);
        break;

    case 'reject':
        $success = $controller->reject($id);
        break;

    default:
        header("Location: claims.php");
        exit;
}

if ($success) {
    header("Location: claims.php?success=" . urlencode("Claim processed successfully."));
} else {
    header("Location: claims.php?error=" . urlencode("Unable to process the claim."));
}

exit;
