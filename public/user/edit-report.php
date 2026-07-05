<?php

session_start();
require_once __DIR__ . '/../../backend/helpers/csrf.php';

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
if($_SESSION['role'] === 'admin')
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
$locationLabel = ($type === 'lost') ? 'Location Lost' : 'Location Found';
$dateLabel = ($type === 'lost') ? 'Date Lost' : 'Date Found';
$tab = ($_GET['tab'] ?? $type) === 'found' ? 'found' : 'lost';
$reportsUrl = "/user/my-reports.php?tab=" . $tab;

$db = new Database();
$conn = $db->getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if(!csrf_validate($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Security token expired. Please try again.";
        header("Location: " . $reportsUrl);
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE {$table}
         SET
            item_name = :item_name,
            category = :category,
            description = :description,
            {$locationColumn} = :location,
            {$dateColumn} = :report_date
         WHERE id = :id
         AND user_id = :user_id"
    );

    $stmt->execute([
        ':item_name' => $_POST['item_name'],
        ':category' => $_POST['category'],
        ':description' => $_POST['description'],
        ':location' => $_POST['location'],
        ':report_date' => $_POST['report_date'],
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);

    $_SESSION['success'] = "Report updated successfully.";

    header("Location: " . $reportsUrl);
    exit;
}

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

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Report</title>

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
                <h1>Edit <?= ucfirst($type) ?> Report</h1>

                <a
                    href="<?= htmlspecialchars($reportsUrl) ?>"
                    class="secondary-btn"
                    title="Back to Reports">
                    <i class="fas fa-arrow-left"></i>
                    Back to Reports
                </a>
            </div>

            <div class="form-card">

                <form method="POST">
                    <?= csrf_field() ?>

                    <div class="form-group">
                        <label>Item Name</label>
                        <input
                            type="text"
                            name="item_name"
                            value="<?= htmlspecialchars($report['item_name']) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Category</label>
                        <input
                            type="text"
                            name="category"
                            value="<?= htmlspecialchars($report['category']) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea
                            name="description"
                            rows="5"
                            required><?= htmlspecialchars($report['description']) ?></textarea>
                    </div>

                    <div class="form-group">
                        <label><?= $locationLabel ?></label>
                        <input
                            type="text"
                            name="location"
                            value="<?= htmlspecialchars($report[$locationColumn]) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label><?= $dateLabel ?></label>
                        <input
                            type="date"
                            name="report_date"
                            value="<?= htmlspecialchars($report[$dateColumn]) ?>"
                            required>
                    </div>

                    <button
                        type="submit"
                        class="action-btn">

                        Save Changes

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

</body>
</html>
