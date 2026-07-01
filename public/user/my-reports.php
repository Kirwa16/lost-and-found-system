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
$conn = $db->getConnection();

/*
|--------------------------------------------------------------------------
| Get User Lost Reports
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT *
     FROM lost_items
     WHERE user_id = :user_id
     ORDER BY created_at DESC"
);

$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Reports</title>

<link rel="stylesheet" href="/assets/css/dashboard.css"> 
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>

<div class="user-layout">

    <?php include __DIR__ . '/../components/user-sidebar.php'; ?>

    <div class="main" id="main">
        <?php include __DIR__ . '/../components/topbar-user.php'; ?>

        <div class="content">

            <h1>My Lost Item Reports</h1>

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

                <?php if(empty($reports)): ?>

                    <p>No reports submitted yet.</p>

                <?php else: ?>

                    <table class="table">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Date Lost</th>
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $count = 1; ?>

                        <?php foreach($reports as $report): ?>

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
                                    <?= htmlspecialchars($report['location_lost']) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime($report['date_lost'])
                                    ) ?>
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

                                    <a
                                        href="/user/view-report.php?id=<?= $report['id'] ?>"
                                        class="action-btn">

                                        View

                                    </a>

                                    <a
                                        href="/user/edit-report.php?id=<?= $report['id'] ?>"
                                        class="action-btn">

                                        Edit

                                    </a>

                                    <a
                                        href="/user/delete-report.php?id=<?= $report['id'] ?>"
                                        class="action-btn"
                                        onclick="return confirm('Delete this report?')">

                                        Delete

                                    </a>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

            <br>

            <div class="card">

                <h2>Quick Actions</h2>

                <br>

                <div class="action-grid">

                    <a href="/user/report-lost.php"
                       class="action-btn">

                        Report Lost Item

                    </a>

                    <a href="/user/search.php"
                       class="action-btn">

                        Search Items

                    </a>

                    <a href="/user/matches.php"
                       class="action-btn">

                        View Matches

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="/assets/js/sidebar.js"></script>
</body>

</html>
