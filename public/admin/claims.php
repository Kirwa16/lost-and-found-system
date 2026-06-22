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

require_once '../../backend/models/Claim.php';

$claimModel = new Claim();

$claims = $claimModel->getAllClaims();

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Manage Claims</title>

<link rel="stylesheet"
      href="/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/assets/css/admin.css">

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

                            <th>#</th>
                            <th>User</th>
                            <th>Claim Message</th>
                            <th>Status</th>
                            <th>Actions</th>

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
                                <?= htmlspecialchars($claim['fullname']) ?>
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

                                <?php if($claim['status'] === 'pending'): ?>

                                    <a
                                        href="process-claim.php?id=<?= $claim['id'] ?>&action=approve"
                                        class="action-btn"
                                        onclick="return confirm('Approve this claim?')">

                                        Approve

                                    </a>

                                    <a
                                        href="process-claim.php?id=<?= $claim['id'] ?>&action=reject"
                                        class="action-btn"
                                        onclick="return confirm('Reject this claim?')">

                                        Reject

                                    </a>

                                <?php else: ?>

                                    <span style="color:#64748b;">
                                        Completed
                                    </span>

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