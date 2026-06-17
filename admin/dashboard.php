<?php

require_once '../includes/auth.php';
require_once '../backend/models/Dashboard.php';

if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

$dashboard = new Dashboard();

$totalUsers      = $dashboard->totalUsers();
$totalLost       = $dashboard->totalLostItems();
$totalFound      = $dashboard->totalFoundItems();
$totalMatches    = $dashboard->totalMatches();
$totalClaims     = $dashboard->totalClaims();

$recentLost      = $dashboard->recentLostItems();
$recentFound     = $dashboard->recentFoundItems();
$recentClaims    = $dashboard->recentClaims();

include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>Admin Dashboard</h1>

        <div class="cards">

            <div class="card">
                <h3>Total Users</h3>
                <p><?= $totalUsers ?></p>
            </div>

            <div class="card">
                <h3>Lost Items</h3>
                <p><?= $totalLost ?></p>
            </div>

            <div class="card">
                <h3>Found Items</h3>
                <p><?= $totalFound ?></p>
            </div>

            <div class="card">
                <h3>Matches</h3>
                <p><?= $totalMatches ?></p>
            </div>

            <div class="card">
                <h3>Claims</h3>
                <p><?= $totalClaims ?></p>
            </div>

        </div>

        <br><br>

        <div class="card">

            <h2>Recent Lost Reports</h2>

            <table class="table">

                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Date</th>
                </tr>

                <?php foreach($recentLost as $item): ?>

                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= $item['created_at'] ?></td>
                </tr>

                <?php endforeach; ?>

            </table>

        </div>

        <br>

        <div class="card">

            <h2>Recent Found Reports</h2>

            <table class="table">

                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Date</th>
                </tr>

                <?php foreach($recentFound as $item): ?>

                <tr>
                    <td><?= htmlspecialchars($item['item_name']) ?></td>
                    <td><?= htmlspecialchars($item['category']) ?></td>
                    <td><?= $item['created_at'] ?></td>
                </tr>

                <?php endforeach; ?>

            </table>

        </div>

        <br>

        <div class="card">

            <h2>Recent Claims</h2>

            <table class="table">

                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>

                <?php foreach($recentClaims as $claim): ?>

                <tr>
                    <td><?= $claim['id'] ?></td>
                    <td><?= ucfirst($claim['status']) ?></td>
                    <td><?= $claim['created_at'] ?></td>
                </tr>

                <?php endforeach; ?>

            </table>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

