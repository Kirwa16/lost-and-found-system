<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /public/login.php");
    exit;
}

if($_SESSION['role'] !== 'user')
{
    header("Location: /frontend/admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

if(!isset($_GET['id']))
{
    header("Location: my-reports.php");
    exit;
}

$id = (int)$_GET['id'];

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Verify Ownership
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id
     FROM lost_items
     WHERE id = :id
     AND user_id = :user_id"
);

$stmt->execute([
    ':id' => $id,
    ':user_id' => $_SESSION['user_id']
]);

if(!$stmt->fetch())
{
    $_SESSION['error'] = "Report not found.";
    header("Location: my-reports.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete Report
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "DELETE FROM lost_items
     WHERE id = :id
     AND user_id = :user_id"
);

$stmt->execute([
    ':id' => $id,
    ':user_id' => $_SESSION['user_id']
]);

$_SESSION['success'] = "Report deleted successfully.";

header("Location: my-reports.php");
exit;
