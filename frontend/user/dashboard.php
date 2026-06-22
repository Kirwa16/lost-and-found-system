<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /public/login.php");
    exit;
}

if($_SESSION['role'] !== 'user')
{
    header("Location: /frontend/admin/dashboard.php");
    exit;

}
if($_SESSION['role'] === 'admin')
{
    header("Location: /frontend/admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

$userId = $_SESSION['user_id'];

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT COUNT(*)
     FROM lost_items
     WHERE user_id = :user_id"
);

$stmt->execute([
    ':user_id' => $userId
]);

$totalLost = $stmt->fetchColumn();

$stmt = $conn->prepare(
    "SELECT COUNT(*)
     FROM found_items
     WHERE user_id = :user_id"
);

$stmt->execute([
    ':user_id' => $userId
]);

$totalFound = $stmt->fetchColumn();

$stmt = $conn->prepare(
    "SELECT COUNT(*)
     FROM claims
     WHERE user_id = :user_id"
);

$stmt->execute([
    ':user_id' => $userId
]);

$totalClaims = $stmt->fetchColumn();

$stmt = $conn->prepare(
    "SELECT COUNT(*)
     FROM lost_items
     WHERE user_id = :user_id
     AND status = 'claimed'"
);

$stmt->execute([
    ':user_id' => $userId
]);

$totalRecovered = $stmt->fetchColumn();

/*
|--------------------------------------------------------------------------
| Recent Lost Reports
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        item_name,
        category,
        status,
        created_at
     FROM lost_items
     WHERE user_id = :user_id
     ORDER BY created_at DESC
     LIMIT 5"
);

$stmt->execute([
    ':user_id' => $userId
]);

$recentReports = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>User Dashboard</title>

<link rel="stylesheet"
      href="/frontend/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/frontend/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include '../components/user-sidebar.php'; ?>

    <div class="main">

        <div class="content">

            <h1>
                Welcome,
                <?= htmlspecialchars($_SESSION['fullname']) ?>
            </h1>

            <p>
                Manage your lost and found reports from one place.
            </p>

            <div class="cards">

                <div class="card">
                    <h3>Lost Reports</h3>
                    <p><?= $totalLost ?></p>
                </div>

                <div class="card">
                    <h3>Found Reports</h3>
                    <p><?= $totalFound ?></p>
                </div>

                <div class="card">
                    <h3>Claims</h3>
                    <p><?= $totalClaims ?></p>
                </div>

                <div class="card">
                    <h3>Recovered Items</h3>
                    <p><?= $totalRecovered ?></p>
                </div>

            </div>

            <div class="card">

                <h2>Quick Actions</h2>

                <br>

                <div class="action-grid">

                    <a href="/frontend/user/report-lost.php"
                       class="action-btn">
                        Report Lost Item
                    </a>

                    <a href="/frontend/user/report-found.php"
                       class="action-btn">
                        Report Found Item
                    </a>

                    <a href="/frontend/user/search.php"
                       class="action-btn">
                        Search Items
                    </a>

                    <a href="/frontend/user/claims.php"
                       class="action-btn">
                        My Claims
                    </a>

                </div>

            </div>

            <br>

            <div class="card">

                <h2>Recent Reports</h2>

                <br>

                <?php if(empty($recentReports)): ?>

                    <p>
                        No reports submitted yet.
                    </p>

                <?php else: ?>

                    <table class="table">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $count = 1; ?>

                        <?php foreach($recentReports as $report): ?>

                            <tr>

                                <td>
                                    <?= $count++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['item_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['category']) ?>
                                </td>

                                <td>

                                    <?php if($report['status'] === 'claimed'): ?>

                                        <span class="badge badge-success">
                                            Claimed
                                        </span>

                                    <?php elseif($report['status'] === 'matched'): ?>

                                        <span class="badge badge-warning">
                                            Matched
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-danger">
                                            Pending
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime(
                                            $report['created_at']
                                        )
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
