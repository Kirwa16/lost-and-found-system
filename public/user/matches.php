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

require_once __DIR__ . '/../../backend/models/MatchEngine.php';

$matchEngine = new MatchEngine();

$matches = $matchEngine->getUserMatches(
    $_SESSION['user_id']
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Matches</title>

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

            <h1>My Matches</h1>

            <?php if(empty($matches)): ?>

                <div class="card">

                    <h3>No Matches Found</h3>

                    <br>

                    <p>
                        No potential matches have been detected yet.
                    </p>

                </div>

            <?php else: ?>

                <div class="card">

                    <table class="table">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Lost Item</th>
                                <th>Found Item</th>
                                <th>Category</th>
                                <th>Confidence</th>
                                <th>Status</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $count = 1; ?>

                        <?php foreach($matches as $match): ?>

                            <tr>

                                <td>
                                    <?= $count++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($match['lost_item']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($match['found_item']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($match['category']) ?>
                                </td>

                                <td>

                                    <?php
                                    $score = (int)$match['confidence_score'];

                                    if($score >= 90)
                                    {
                                        echo '<span class="badge badge-success">'
                                            . $score . '%</span>';
                                    }
                                    elseif($score >= 75)
                                    {
                                        echo '<span class="badge badge-warning">'
                                            . $score . '%</span>';
                                    }
                                    else
                                    {
                                        echo '<span class="badge badge-danger">'
                                            . $score . '%</span>';
                                    }
                                    ?>

                                </td>

                                <td>

                                    <?php if($match['status'] === 'approved'): ?>

                                        <span class="badge badge-success">
                                            Approved
                                        </span>

                                    <?php elseif($match['status'] === 'rejected'): ?>

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

                                    <?php if($match['status'] === 'pending'): ?>

                                        <a
                                            href="submit-claim.php?match_id=<?= $match['id'] ?>"
                                            class="action-btn">

                                            Submit Claim

                                        </a>

                                    <?php else: ?>

                                        Completed

                                    <?php endif; ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>