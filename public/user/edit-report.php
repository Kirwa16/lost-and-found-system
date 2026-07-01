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

$db = new Database();
$conn = $db->getConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $stmt = $conn->prepare(
        "UPDATE lost_items
         SET
            item_name = :item_name,
            category = :category,
            description = :description,
            location_lost = :location_lost,
            date_lost = :date_lost
         WHERE id = :id
         AND user_id = :user_id"
    );

    $stmt->execute([
        ':item_name' => $_POST['item_name'],
        ':category' => $_POST['category'],
        ':description' => $_POST['description'],
        ':location_lost' => $_POST['location_lost'],
        ':date_lost' => $_POST['date_lost'],
        ':id' => $id,
        ':user_id' => $_SESSION['user_id']
    ]);

    $_SESSION['success'] = "Report updated successfully.";

    header("Location: /user/my-reports.php");
    exit;
}

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

            <h1>Edit Report</h1>

            <div class="form-card">

                <form method="POST">

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
                        <label>Location Lost</label>
                        <input
                            type="text"
                            name="location_lost"
                            value="<?= htmlspecialchars($report['location_lost']) ?>"
                            required>
                    </div>

                    <div class="form-group">
                        <label>Date Lost</label>
                        <input
                            type="date"
                            name="date_lost"
                            value="<?= $report['date_lost'] ?>"
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
