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

/*
|--------------------------------------------------------------------------
| Verify Ownership
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id, item_name, category, {$locationColumn} AS report_location, {$dateColumn} AS report_date
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
    $_SESSION['error'] = "Report not found.";
    header("Location: " . $reportsUrl);
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete Report
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if(!csrf_validate($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Security token expired. Please try again.";
        header("Location: " . $reportsUrl);
        exit;
    }

    $stmt = $conn->prepare(
        "DELETE FROM {$table}
         WHERE id = :id
         AND user_id = :user_id"
    );

    $stmt->execute([
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);

    $_SESSION['success'] = "Report deleted successfully.";

    header("Location: /user/my-reports.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Delete Report</title>

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
                <h1>Delete <?= ucfirst($type) ?> Report</h1>

                <a
                    href="<?= htmlspecialchars($reportsUrl) ?>"
                    class="secondary-btn"
                    title="Back to Reports">
                    <i class="fas fa-arrow-left"></i>
                    Back to Reports
                </a>
            </div>

            <div class="form-card">

                <p>Are you sure you want to delete this report?</p>
                <br>

                <p><strong>Item:</strong> <?= htmlspecialchars($report['item_name']) ?></p>
                <br>

                <p><strong>Category:</strong> <?= htmlspecialchars($report['category']) ?></p>
                <br>

                <p><strong>Location:</strong> <?= htmlspecialchars($report['report_location']) ?></p>
                <br>

                <p><strong>Date:</strong> <?= htmlspecialchars($report['report_date']) ?></p>

                <div class="confirm-actions">
                    <a
                        href="<?= htmlspecialchars($reportsUrl) ?>"
                        class="secondary-btn">
                        Cancel
                    </a>

                    <form method="POST">
                        <?= csrf_field() ?>
                        <button
                            type="submit"
                            class="action-btn danger-btn">
                            <i class="fas fa-trash"></i>
                            Delete Report
                        </button>
                    </form>
                </div>

            </div>

        </div>

    </div>

</div>

<script src="/assets/js/sidebar.js"></script>
</body>

</html>
