<?php

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
$conn = $db->connect();

$stmt = $conn->prepare(
    "SELECT

        c.id,
        c.claim_message,
        c.status,
        c.created_at,

        l.item_name AS lost_item,
        f.item_name AS found_item

     FROM claims c

     INNER JOIN matches m
        ON c.match_id = m.id

     INNER JOIN lost_items l
        ON m.lost_item_id = l.id

     INNER JOIN found_items f
        ON m.found_item_id = f.id

     WHERE c.user_id = :user_id

     ORDER BY c.created_at DESC"
);

$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Claims</title>

<link rel="stylesheet"
      href="/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

<?php include __DIR__ . '/../components/user-sidebar.php'; ?>


    <div class="main">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <h1>My Claims</h1>

            <?php if(isset($_SESSION['success'])): ?>

                <div class="success">

                    <?= htmlspecialchars($_SESSION['success']) ?>

                </div>

                <?php unset($_SESSION['success']); ?>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="error">

                    <?= htmlspecialchars($_SESSION['error']) ?>

                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>

            <div class="card">

                <?php if(empty($claims)): ?>

                    <p>No claims submitted yet.</p>

                <?php else: ?>

                    <table class="table">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Lost Item</th>
                                <th>Found Item</th>
                                <th>Claim Message</th>
                                <th>Status</th>
                                <th>Date</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $count = 1; ?>

                        <?php foreach($claims as $claim): ?>

                            <tr>

                                <td>
                                    <?= $count++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($claim['lost_item']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($claim['found_item']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($claim['claim_message']) ?>
                                </td>

                                <td>

                                    <?php if($claim['status'] === 'approved'): ?>

                                        <span class="badge badge-success">
                                            Approved
                                        </span>

                                    <?php elseif($claim['status'] === 'rejected'): ?>

                                        <span class="badge badge-danger">
                                            Rejected
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-warning">
                                            Pending
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= date(
                                        'd M Y H:i',
                                        strtotime($claim['created_at'])
                                    ) ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

</body>

</html>
