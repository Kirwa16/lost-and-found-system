<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if(!in_array($_SESSION['role'], ['student', 'staff'], true))
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
$type = $_GET['type'] ?? 'lost';

if(!in_array($type, ['lost', 'found']))
{
    header("Location: /user/my-reports.php");
    exit;
}

$table = ($type === 'lost') ? 'lost_items' : 'found_items';
$locationColumn = ($type === 'lost') ? 'location_lost' : 'location_found';
$dateColumn = ($type === 'lost') ? 'date_lost' : 'date_found';
$tab = ($_GET['tab'] ?? $type) === 'found' ? 'found' : 'lost';
$reportsUrl = "/user/my-reports.php?tab=" . $tab;

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare(
    "SELECT *
     FROM {$table}
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

$imagePath = null;
if(!empty($report['image']))
{
    $imagePath = '/' . ltrim($report['image'], '/');
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>View Report</title>

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

            <div class="page-header">
                <h1>Report Details</h1>

                <a
                    href="<?= htmlspecialchars($reportsUrl) ?>"
                    class="secondary-btn"
                    title="Back to Reports">
                    <i class="fas fa-arrow-left"></i>
                    Back to Reports
                </a>
            </div>

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

                <p><strong>Location <?= ucfirst($type) ?>:</strong> <?= htmlspecialchars($report[$locationColumn]) ?></p>
                <br>

                <p><strong>Date <?= ucfirst($type) ?>:</strong> <?= htmlspecialchars($report[$dateColumn]) ?></p>
                <br>

                <p><strong>Status:</strong> <?= ucfirst($report['status']) ?></p>
                <br>

                <p><strong>Image:</strong></p>
                <br>

                <?php if($imagePath): ?>

                    <img
                        class="report-image"
                        src="<?= htmlspecialchars($imagePath) ?>"
                        alt="<?= htmlspecialchars($report['item_name']) ?>">

                <?php else: ?>

                    <p>No image uploaded.</p>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>
<script src="/assets/js/sidebar.js"></script>
</body>
</html>
