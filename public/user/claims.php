<?php

session_save_path(__DIR__ . '/../../sessions');
session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

require_once __DIR__ . '/../../backend/models/Claim.php';

$claimModel = new Claim();

$claims = $claimModel->getClaimsByUser(
    $_SESSION['user_id']
);

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

        <?php include __DIR__ . '/../components/topbar-user.php'; ?>

        <div class="content">

            <h1>My Claims</h1>

            <div class="card">

                <?php if(empty($claims)): ?>

                    <p>No claims submitted yet.</p>

                <?php else: ?>

                <table class="table">

                    <thead>

                        <tr>

                            <th>ID</th>
                            <th>Match ID</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($claims as $claim): ?>

                        <tr>

                            <td>
                                <?= $claim['id'] ?>
                            </td>

                            <td>
                                <?= $claim['match_id'] ?>
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
                                <?= date('d M Y', strtotime($claim['created_at'])) ?>
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
