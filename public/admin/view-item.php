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

if(
    !isset($_GET['id']) ||
    !isset($_GET['type'])
)
{
    header("Location: /admin/items.php");
    exit;
}

$id = (int)$_GET['id'];
$type = $_GET['type'];

if(!in_array($type, ['lost', 'found']))
{
    header("Location: /admin/items.php");
    exit;
}

$table =
    ($type === 'lost')
    ? 'lost_items'
    : 'found_items';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare(
    "SELECT *
     FROM {$table}
     WHERE id = :id"
);

$stmt->execute([
    ':id' => $id
]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$item)
{
    die("Item not found.");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>View Item</title>

<link rel="stylesheet"
      href="/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/components/sidebar.php'; ?>

    <div class="main">

        <?php include __DIR__ . '/components/topbar.php'; ?>

        <div class="content">

            <h1>Item Details</h1>

            <div class="card">

                <table class="table">

                    <tr>
                        <th width="250">ID</th>
                        <td><?= $item['id'] ?></td>
                    </tr>

                    <tr>
                        <th>Item Name</th>
                        <td><?= htmlspecialchars($item['item_name']) ?></td>
                    </tr>

                    <tr>
                        <th>Category</th>
                        <td><?= htmlspecialchars($item['category']) ?></td>
                    </tr>

                    <tr>
                        <th>Color</th>
                        <td><?= htmlspecialchars($item['color'] ?? 'N/A') ?></td>
                    </tr>

                    <tr>
                        <th>Brand / Model</th>
                        <td><?= htmlspecialchars($item['brand_model'] ?? 'N/A') ?></td>
                    </tr>

                    <tr>
                        <th>Unique Features</th>
                        <td><?= nl2br(htmlspecialchars($item['unique_features'] ?? 'N/A')) ?></td>
                    </tr>

                    <tr>
                        <th>Description</th>
                        <td><?= nl2br(htmlspecialchars($item['description'] ?? 'N/A')) ?></td>
                    </tr>

                    <?php if($type === 'lost'): ?>

                    <tr>
                        <th>Location Lost</th>
                        <td><?= htmlspecialchars($item['location_lost'] ?? 'N/A') ?></td>
                    </tr>

                    <tr>
                        <th>Date Lost</th>
                        <td><?= htmlspecialchars($item['date_lost'] ?? 'N/A') ?></td>
                    </tr>

                    <?php endif; ?>

                    <?php if($type === 'found'): ?>

                    <tr>
                        <th>Location Found</th>
                        <td><?= htmlspecialchars($item['location_found'] ?? 'N/A') ?></td>
                    </tr>

                    <tr>
                        <th>Date Found</th>
                        <td><?= htmlspecialchars($item['date_found'] ?? 'N/A') ?></td>
                    </tr>

                    <?php endif; ?>

                    <tr>
                        <th>Status</th>
                        <td><?= ucfirst($item['status']) ?></td>
                    </tr>

                    <tr>
                        <th>Date Reported</th>
                        <td><?= $item['created_at'] ?? 'N/A' ?></td>
                    </tr>

                    <tr>
                        <th>Image</th>
                        <td>

                            <?php if(!empty($item['image'])): ?>

                                <img
                                    src="/backend/uploads/<?= htmlspecialchars($item['image']) ?>"
                                    alt="Item Image"
                                    style="
                                        max-width:300px;
                                        border-radius:10px;
                                    ">

                            <?php else: ?>

                                No image uploaded.

                            <?php endif; ?>

                        </td>
                    </tr>

                </table>

                <br>

                <a
                    href="/admin/items.php"
                    class="action-btn">

                    Back to Items

                </a>

                <a
                    href="/admin/edit-item.php?type=<?= $type ?>&id=<?= $item['id'] ?>"
                    class="action-btn">

                    Edit Item

                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>
