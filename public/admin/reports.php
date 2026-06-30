<?php

session_save_path(__DIR__ . '/../../sessions');
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

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->getConnection();

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalUsers = $conn
    ->query("SELECT COUNT(*) FROM users")
    ->fetchColumn();

$totalLost = $conn
    ->query("SELECT COUNT(*) FROM lost_items")
    ->fetchColumn();

$totalFound = $conn
    ->query("SELECT COUNT(*) FROM found_items")
    ->fetchColumn();

$totalMatches = $conn
    ->query("SELECT COUNT(*) FROM matches")
    ->fetchColumn();

$totalClaims = $conn
    ->query("SELECT COUNT(*) FROM claims")
    ->fetchColumn();

$approvedClaims = $conn
    ->query("SELECT COUNT(*) FROM claims WHERE status = 'approved'")
    ->fetchColumn();

$pendingClaims = $conn
    ->query("SELECT COUNT(*) FROM claims WHERE status = 'pending'")
    ->fetchColumn();

$recoveryRate = 0;

if($totalLost > 0)
{
    $recoveryRate =
        round(($totalMatches / $totalLost) * 100, 1);
}

/*
|--------------------------------------------------------------------------
| Recent Activity
|--------------------------------------------------------------------------
*/

$recentLost = $conn->query(
    "SELECT
        item_name,
        category,
        created_at
     FROM lost_items
     ORDER BY created_at DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>System Reports</title>

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

            <h1>System Reports</h1>

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
                    <h3>Total Claims</h3>
                    <p><?= $totalClaims ?></p>
                </div>

                <div class="card">
                    <h3>Approved Claims</h3>
                    <p><?= $approvedClaims ?></p>
                </div>

                <div class="card">
                    <h3>Pending Claims</h3>
                    <p><?= $pendingClaims ?></p>
                </div>

                <div class="card">
                    <h3>Recovery Rate</h3>
                    <p><?= $recoveryRate ?>%</p>
                </div>

            </div>

            <div class="card">

                <h2>Recent Lost Item Reports</h2>

                <br>

                <?php if(empty($recentLost)): ?>

                    <p>No reports available.</p>

                <?php else: ?>

                <table class="table">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Item Name</th>
                            <th>Category</th>
                            <th>Date Reported</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php $count = 1; ?>

                    <?php foreach($recentLost as $item): ?>

                        <tr>

                            <td>
                                <?= $count++ ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['item_name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['category']) ?>
                            </td>

                            <td>
                                <?= date(
                                    'd M Y',
                                    strtotime($item['created_at'])
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