<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if(!in_array($_SESSION['role'], ['student', 'staff'], true))
{
    header("Location: /admin/dashboard.php");
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: /user/notifications.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare(
    "SHOW COLUMNS
     FROM notifications
     LIKE 'link'"
);
$stmt->execute();
$hasNotificationLinkColumn = (bool)$stmt->fetch(PDO::FETCH_ASSOC);

$linkSelect = $hasNotificationLinkColumn
    ? 'link'
    : "NULL AS link";

$stmt = $conn->prepare(
    "SELECT {$linkSelect}
     FROM notifications
     WHERE id = :id
     AND user_id = :user_id
     LIMIT 1"
);

$stmt->execute([
    ':id' => (int)$_GET['id'],
    ':user_id' => $_SESSION['user_id']
]);

$notification = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$notification)
{
    header("Location: /user/notifications.php");
    exit;
}

$stmt = $conn->prepare(
    "UPDATE notifications
     SET is_read = 1
     WHERE id = :id
     AND user_id = :user_id"
);

$stmt->execute([
    ':id' => (int)$_GET['id'],
    ':user_id' => $_SESSION['user_id']
]);

$link = $notification['link'] ?: '/user/notifications.php';

if(strpos($link, '/') !== 0 || strpos($link, '//') === 0)
{
    $link = '/user/notifications.php';
}

header("Location: " . $link);
exit;
