<?php

session_save_path(__DIR__ . '/../../sessions');
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if($_SESSION['role'] !== 'user')
{
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->getConnection();

/*
|--------------------------------------------------------------------------
| Mark Notification As Read
|--------------------------------------------------------------------------
*/

if(isset($_GET['read']))
{
    $notificationId = (int)$_GET['read'];

    $stmt = $conn->prepare(
        "UPDATE notifications
         SET is_read = 1
         WHERE id = :id
         AND user_id = :user_id"
    );

    $stmt->execute([
        ':id' => $notificationId,
        ':user_id' => $_SESSION['user_id']
    ]);

    header("Location: notifications.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Fetch Notifications
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT *
     FROM notifications
     WHERE user_id = :user_id
     ORDER BY created_at DESC"
);

$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Notifications</title>

<link rel="stylesheet"
      href="/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/user-sidebar.php'; ?>

    <div class="main">
        <?php include __DIR__ . '/../components/topbar-user.php'; ?>

        <div class="content">

            <h1>Notifications</h1>

            <?php if(empty($notifications)): ?>

                <div class="card">

                    <p>No notifications available.</p>

                </div>

            <?php else: ?>

                <?php $count = 1; ?>

                <?php foreach($notifications as $notification): ?>

                    <div
                        class="card"
                        style="
                            margin-bottom:15px;
                            border-left:
                            <?= $notification['is_read']
                                ? '5px solid #ccc'
                                : '5px solid #8B0000'
                            ?>;
                        "
                    >

                        <h3>

                            Notification #<?= $count++ ?>

                            <?php if(!$notification['is_read']): ?>

                                <span class="badge badge-warning">
                                    New
                                </span>

                            <?php endif; ?>

                        </h3>

                        <br>

                        <p>

                            <?= htmlspecialchars(
                                $notification['message']
                            ) ?>

                        </p>

                        <br>

                        <small>

                            <?= date(
                                'd M Y H:i',
                                strtotime(
                                    $notification['created_at']
                                )
                            ) ?>

                        </small>

                        <br><br>

                        <?php if(!$notification['is_read']): ?>

                            <a
                                href="/user/notifications.php?read=<?= $notification['id'] ?>"
                                class="action-btn">

                                Mark as Read

                            </a>

                        <?php else: ?>

                            <span class="badge badge-success">

                                Read

                            </span>

                        <?php endif; ?>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
