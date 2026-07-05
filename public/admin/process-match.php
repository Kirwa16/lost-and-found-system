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
    header("Location: matches.php?error=" . urlencode("Invalid security token."));
    exit;
}

if (
    !isset($_POST['id']) ||
    !is_numeric($_POST['id']) ||
    !isset($_POST['action'])
) {
    header("Location: matches.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/MatchController.php';

$controller = new MatchController();

$id = (int)$_POST['id'];
$action = $_POST['action'];

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
