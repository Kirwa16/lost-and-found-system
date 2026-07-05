<?php

session_start();
require_once __DIR__ . '/../../backend/helpers/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: /user/dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_validate($_POST['csrf_token'] ?? null)) {
    header("Location: claims.php?error=" . urlencode("Invalid security token."));
    exit;
}

if (
    !isset($_POST['id']) ||
    !is_numeric($_POST['id']) ||
    !isset($_POST['action'])
) {
    header("Location: claims.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/ClaimController.php';

$controller = new ClaimController();

$id = (int)$_POST['id'];
$action = $_POST['action'];

$success = false;

switch ($action) {

    case 'approve':
        $success = $controller->approve($id);
        break;

    case 'reject':
        $success = $controller->reject($id);
        break;

    case 'collect':
        $success = $controller->collect($id);
        break;

    default:
        header("Location: claims.php");
        exit;
}

if ($success) {
    $message = ($action === 'collect')
        ? "Claim marked as collected."
        : "Claim processed successfully.";

    header("Location: claims.php?success=" . urlencode($message));
} else {
    header("Location: claims.php?error=" . urlencode("Unable to process the claim."));
}

exit;
