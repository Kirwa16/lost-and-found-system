<?php

session_save_path(__DIR__ . '/../../sessions');
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

if(!isset($_GET['id']))
{
    header("Location: /user/my-reports.php");
    exit;
}

$id = (int)$_GET['id'];

$db = new Database();
$conn = $db->connect();

$stmt = $conn->prepare(
    "SELECT *
     FROM lost_items
     WHERE id = :id
     AND user_id = :user_id"
);

$stmt->execute([
    ':id' => $id,
    ':user_id' => $_SESSION['user_id']
]);

$report = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$report)
{
    die("Report not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>View Report</title>

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

            <h1>Report Details</h1>

            <div class="form-card">

                <p><strong>Item:</strong> <?= htmlspecialchars($report['item_name']) ?></p>
                <br>

                <p><strong>Category:</strong> <?= htmlspecialchars($report['category']) ?></p>
                <br>

                <p><strong>Color:</strong> <?= htmlspecialchars($report['color']) ?></p>
                <br>

                <p><strong>Brand / Model:</strong> <?= htmlspecialchars($report['brand_model']) ?></p>
                <br>

                <p><strong>Unique Features:</strong> <?= htmlspecialchars($report['unique_features']) ?></p>
                <br>

                <p><strong>Description:</strong> <?= htmlspecialchars($report['description']) ?></p>
                <br>

                <p><strong>Location Lost:</strong> <?= htmlspecialchars($report['location_lost']) ?></p>
                <br>

                <p><strong>Date Lost:</strong> <?= $report['date_lost'] ?></p>
                <br>

                <p><strong>Status:</strong> <?= ucfirst($report['status']) ?></p>

            </div>

        </div>

    </div>

</div>

</body>
</html>
