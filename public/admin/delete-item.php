<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

if(
    !isset($_GET['id']) ||
    !isset($_GET['type'])
)
{
    header("Location: /admin/items.php");
    exit;
}

$id = (int)$_GET['id'];
$type = $_GET['type'];

if(!in_array($type, ['lost', 'found']))
{
    header("Location: /admin/items.php");
    exit;
}

$table =
    ($type === 'lost')
    ? 'lost_items'
    : 'found_items';

$db = new Database();
$conn = $db->connect();

try
{
    /*
    |--------------------------------------------------------------------------
    | Check Item Exists
    |--------------------------------------------------------------------------
    */

    $check = $conn->prepare(
        "SELECT id
         FROM {$table}
         WHERE id = :id"
    );

    $check->execute([
        ':id' => $id
    ]);

    if(!$check->fetch())
    {
        header("Location: /admin/items.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Item
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare(
        "DELETE FROM {$table}
         WHERE id = :id"
    );

    $stmt->execute([
        ':id' => $id
    ]);

    header("Location: /admin/items.php?success=deleted");
    exit;
}
catch(PDOException $e)
{
    header("Location: /admin/items.php?error=delete_failed");
    exit;
}
