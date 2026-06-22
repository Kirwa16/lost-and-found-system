<?php

session_save_path(__DIR__ . '/../../sessions');
session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/models/Claim.php';

$claimModel = new Claim();
$claims = $claimModel->getAllClaims();

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Claims</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main">

        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <h1>Manage Claims</h1>

            <div class="card">

                <?php if(empty($claims)): ?>

                    <p>No claims found.</p>

                <?php else: ?>

                <table class="table">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach($claims as $claim): ?>

                        <tr>

                            <td><?= $claim['id'] ?></td>

                            <td>
                                <?= htmlspecialchars($claim['fullname']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($claim['claim_message']) ?>
                            </td>

                            <td>
                                <?= ucfirst($claim['status']) ?>
                            </td>

                            <td>
                                <?= date('d M Y', strtotime($claim['created_at'])) ?>
                            </td>

                            <td>

                                <?php if($claim['status'] === 'pending'): ?>

                                    <a
                                        href="process-claim.php?id=<?= $claim['id'] ?>&action=approve"
                                        class="action-btn">
                                        Approve
                                    </a>

                                    <a
                                        href="process-claim.php?id=<?= $claim['id'] ?>&action=reject"
                                        class="action-btn">
                                        Reject
                                    </a>

                                <?php else: ?>

                                    Completed

                                <?php endif; ?>

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
