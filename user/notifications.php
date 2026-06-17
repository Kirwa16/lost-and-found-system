<?php

require_once '../includes/auth.php';
require_once '../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| MARK NOTIFICATION AS READ
|--------------------------------------------------------------------------
*/

if(isset($_GET['read']))
{
    $stmt = $conn->prepare(
        "UPDATE notifications
         SET is_read = 1
         WHERE id = :id
         AND user_id = :user_id"
    );

    $stmt->execute([
        ':id' => $_GET['read'],
        ':user_id' => $_SESSION['user_id']
    ]);

    header("Location: notifications.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH USER NOTIFICATIONS
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

include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>Notifications</h1>

        <?php if(empty($notifications)): ?>

            <div class="card">
                <p>No notifications available.</p>
            </div>

        <?php else: ?>

            <?php foreach($notifications as $notification): ?>

                <div
                    class="card"
                    style="
                        margin-bottom:15px;
                        border-left:
                        <?= $notification['is_read']
                            ? '4px solid #ccc'
                            : '4px solid #8B0000'
                        ?>;
                    "
                >

                    <p>
                        <?= htmlspecialchars(
                            $notification['message']
                        ); ?>
                    </p>

                    <small>
                        <?= $notification['created_at']; ?>
                    </small>

                    <br><br>

                    <?php if(!$notification['is_read']): ?>

                        <a
                            href="?read=<?= $notification['id']; ?>"
                            class="action-btn"
                        >
                            Mark as Read
                        </a>

                    <?php else: ?>

                        <span>
                            Read
                        </span>

                    <?php endif; ?>

                </div>

            <?php endforeach; ?>

        <?php endif; ?>

    </div>

</div>

<?php include '../includes/footer.php'; ?>
