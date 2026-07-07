<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if (!in_array($_SESSION['role'], ['student', 'staff'], true)) {
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->getConnection();

/*
|--------------------------------------------------------------------------
| Check if notification links are supported
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SHOW COLUMNS
    FROM notifications
    LIKE 'link'
");

$stmt->execute();

$hasNotificationLinkColumn = (bool)$stmt->fetch(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Automatically mark all unread notifications as read
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    UPDATE notifications
    SET is_read = 1
    WHERE user_id = :user_id
    AND is_read = 0
");

$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

/*
|--------------------------------------------------------------------------
| Fetch Notifications
|--------------------------------------------------------------------------
*/

$linkSelect = $hasNotificationLinkColumn
    ? "link"
    : "NULL AS link";

$stmt = $conn->prepare("
    SELECT
        id,
        user_id,
        message,
        {$linkSelect},
        is_read,
        created_at
    FROM notifications
    WHERE user_id = :user_id
    ORDER BY created_at DESC
");

$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Notifications</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>

<div class="user-layout">

    <?php include __DIR__ . '/../components/user-sidebar.php'; ?>

    <div class="main" id="main">

        <?php include __DIR__ . '/../components/topbar-user.php'; ?>

        <div class="content">

            <h1>Notifications</h1>

            <?php if (empty($notifications)): ?>

                <div class="card empty-state">

                    <i class="fas fa-bell-slash"></i>

                    <h2>No Notifications</h2>

                    <p>
                        You are all caught up. Claim updates and match alerts will appear here.
                    </p>

                </div>

            <?php else: ?>

                <?php $count = count($notifications); ?>

                <?php foreach ($notifications as $notification): ?>

                    <?php
                    $link = $notification['link'] ?: '/user/notifications.php';

                    if (
                        strpos($link, '/') !== 0 ||
                        strpos($link, '//') === 0
                    ) {
                        $link = '/user/notifications.php';
                    }
                    ?>

                    <a
                        href="/user/open-notification.php?id=<?= (int)$notification['id'] ?>"
                        class="card notification-card notification-link <?= $notification['is_read'] ? 'is-read' : 'is-unread' ?>"
                    >

                        <h3>

                            <?php if (!$notification['is_read']): ?>

                                <span class="unread-dot"></span>

                                <span class="badge badge-warning">
                                    New
                                </span>

                            <?php endif; ?>

                            Notification <?= $count-- ?>

                        </h3>

                        <p>
                            <?= htmlspecialchars($notification['message']) ?>
                        </p>

                        <small>
                            <?= date(
                                'd M Y H:i',
                                strtotime($notification['created_at'])
                            ) ?>
                        </small>

                    </a>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</div>

<script src="/assets/js/sidebar.js"></script>

</body>
</html>