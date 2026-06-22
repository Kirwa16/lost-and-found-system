<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /public/login.php");
    exit;
}

if($_SESSION['role'] !== 'admin')
{
    header("Location: /frontend/user/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$totalUsers = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalLost = $conn->query("SELECT COUNT(*) FROM lost_items")->fetchColumn();
$totalFound = $conn->query("SELECT COUNT(*) FROM found_items")->fetchColumn();
$totalClaims = $conn->query("SELECT COUNT(*) FROM claims")->fetchColumn();
$totalMatches = $conn->query("SELECT COUNT(*) FROM matches")->fetchColumn();
$pendingClaims = $conn->query("SELECT COUNT(*) FROM claims WHERE status = 'pending'")->fetchColumn();

$approvedClaims = $conn->query("SELECT COUNT(*) FROM claims WHERE status = 'approved'")->fetchColumn();
$rejectedClaims = $conn->query("SELECT COUNT(*) FROM claims WHERE status = 'rejected'")->fetchColumn();

$recoveryRate = 0;
if($totalLost > 0)
{
    $recoveryRate = round(($totalMatches / $totalLost) * 100, 1);
}

/*
|--------------------------------------------------------------------------
| Categories
|--------------------------------------------------------------------------
*/

$categoryQuery = $conn->query(
    "SELECT category, COUNT(*) total
     FROM lost_items
     GROUP BY category
     ORDER BY total DESC"
);

$categoryData = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

$categoryLabels = [];
$categoryTotals = [];

foreach($categoryData as $row)
{
    $categoryLabels[] = $row['category'];
    $categoryTotals[] = $row['total'];
}

/*
|--------------------------------------------------------------------------
| Recent Lost Reports
|--------------------------------------------------------------------------
*/

$recentLost = $conn->query(
    "SELECT item_name, category, created_at
     FROM lost_items
     ORDER BY created_at DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Recent Found Reports
|--------------------------------------------------------------------------
*/

$recentFound = $conn->query(
    "SELECT item_name, category, created_at
     FROM found_items
     ORDER BY created_at DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Recent Claims
|--------------------------------------------------------------------------
*/

$recentClaims = $conn->query(
    "SELECT id, status, created_at
     FROM claims
     ORDER BY created_at DESC
     LIMIT 5"
)->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/frontend/assets/css/dashboard.css">
    <link rel="stylesheet" href="/frontend/assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<div class="admin-layout">

    <?php include '../components/sidebar.php'; ?>

    <div class="main">

        <?php include '../components/topbar.php'; ?>

        <div class="content">

            <h1>Admin Dashboard</h1>

            <!-- Statistics Cards -->
            <div class="cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <p><?= (int)$totalUsers ?></p>
                </div>

                <div class="card">
                    <h3>Lost Items</h3>
                    <p><?= (int)$totalLost ?></p>
                </div>

                <div class="card">
                    <h3>Found Items</h3>
                    <p><?= (int)$totalFound ?></p>
                </div>

                <div class="card">
                    <h3>Claims</h3>
                    <p><?= (int)$totalClaims ?></p>
                </div>

                <div class="card">
                    <h3>Matches</h3>
                    <p><?= (int)$totalMatches ?></p>
                </div>

                <div class="card">
                    <h3>Pending Claims</h3>
                    <p><?= (int)$pendingClaims ?></p>
                </div>

                <div class="card">
                    <h3>Recovery Rate</h3>
                    <p><?= $recoveryRate ?>%</p>
                </div>
            </div>

            <!-- Charts -->
            <div class="card">
                <h2>Claims Overview</h2>
                <canvas id="claimsChart"></canvas>
            </div>

            <div class="card">
                <h2>Lost Items by Category</h2>
                <canvas id="categoryChart"></canvas>
            </div>

            <!-- Recent Lost Reports -->
            <div class="card">
                <h2>Recent Lost Reports</h2>

                <?php if(empty($recentLost)): ?>
                    <p>No lost reports available.</p>
                <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $count = 1; ?>
                    <?php foreach($recentLost as $item): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Recent Found Reports -->
            <div class="card">
                <h2>Recent Found Reports</h2>

                <?php if(empty($recentFound)): ?>
                    <p>No found reports available.</p>
                <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $count = 1; ?>
                    <?php foreach($recentFound as $item): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td><?= htmlspecialchars($item['item_name']) ?></td>
                            <td><?= htmlspecialchars($item['category']) ?></td>
                            <td><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

            <!-- Recent Claims -->
            <div class="card">
                <h2>Recent Claims</h2>

                <?php if(empty($recentClaims)): ?>
                    <p>No claims available.</p>
                <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $count = 1; ?>
                    <?php foreach($recentClaims as $claim): ?>
                        <tr>
                            <td><?= $count++ ?></td>
                            <td>
                                <?php if($claim['status'] === 'approved'): ?>
                                    <span class="badge badge-success">Approved</span>
                                <?php elseif($claim['status'] === 'pending'): ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d M Y', strtotime($claim['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<script>
// Claims Chart
const claimsCtx = document.getElementById('claimsChart').getContext('2d');
const claimsChart = new Chart(claimsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: [
                <?= (int)$approvedClaims ?>,
                <?= (int)$pendingClaims ?>,
                <?= (int)$rejectedClaims ?>
            ],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545'] // Green, Yellow, Red
        }]
    }
});

// Category Chart
const categoryCtx = document.getElementById('categoryChart').getContext('2d');
const categoryChart = new Chart(categoryCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($categoryLabels) ?>,
        datasets: [{
            label: 'Lost Items',
            data: <?= json_encode($categoryTotals) ?>,
            backgroundColor: '#007bff'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>

</body>
</html>