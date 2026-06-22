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

if(
    !isset($_GET['id']) ||
    !isset($_GET['type'])
)
{
    header("Location: items.php");
    exit;
}

$id = (int)$_GET['id'];

$type = $_GET['type'];

if(!in_array($type, ['lost', 'found']))
{
    header("Location: items.php");
    exit;
}

$table =
    ($type === 'lost')
    ? 'lost_items'
    : 'found_items';

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Update Item
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $stmt = $conn->prepare(
        "UPDATE {$table}
         SET
            item_name = :item_name,
            category = :category,
            color = :color,
            brand_model = :brand_model,
            unique_features = :unique_features,
            description = :description,
            status = :status
         WHERE id = :id"
    );

    $stmt->execute([
        ':item_name'       => trim($_POST['item_name']),
        ':category'        => trim($_POST['category']),
        ':color'           => trim($_POST['color']),
        ':brand_model'     => trim($_POST['brand_model']),
        ':unique_features' => trim($_POST['unique_features']),
        ':description'     => trim($_POST['description']),
        ':status'          => trim($_POST['status']),
        ':id'              => $id
    ]);

    header("Location: items.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Load Item
|--------------------------------------------------------------------------
*/

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

<title>Edit Item</title>

<link rel="stylesheet"
      href="/frontend/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/frontend/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include '../components/sidebar.php'; ?>

    <div class="main">

        <?php include '../components/topbar.php'; ?>

        <div class="content">

            <h1>Edit Item</h1>

            <div class="form-card">

                <form method="POST">

                    <div class="form-group">

                        <label>Item Name</label>

                        <input
                            type="text"
                            name="item_name"
                            value="<?= htmlspecialchars($item['item_name']) ?>"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Category</label>

                        <input
                            type="text"
                            name="category"
                            value="<?= htmlspecialchars($item['category']) ?>"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Color</label>

                        <input
                            type="text"
                            name="color"
                            value="<?= htmlspecialchars($item['color'] ?? '') ?>">

                    </div>

                    <div class="form-group">

                        <label>Brand / Model</label>

                        <input
                            type="text"
                            name="brand_model"
                            value="<?= htmlspecialchars($item['brand_model'] ?? '') ?>">

                    </div>

                    <div class="form-group">

                        <label>Unique Features</label>

                        <textarea
                            name="unique_features"
                            rows="4"><?= htmlspecialchars($item['unique_features'] ?? '') ?></textarea>

                    </div>

                    <div class="form-group">

                        <label>Description</label>

                        <textarea
                            name="description"
                            rows="5"
                            required><?= htmlspecialchars($item['description'] ?? '') ?></textarea>

                    </div>

                    <div class="form-group">

                        <label>Status</label>

                        <select name="status">

                            <option
                                value="pending"
                                <?= ($item['status'] === 'pending') ? 'selected' : '' ?>>
                                Pending
                            </option>

                            <option
                                value="matched"
                                <?= ($item['status'] === 'matched') ? 'selected' : '' ?>>
                                Matched
                            </option>

                            <option
                                value="claimed"
                                <?= ($item['status'] === 'claimed') ? 'selected' : '' ?>>
                                Claimed
                            </option>

                        </select>

                    </div>

                    <button
                        type="submit"
                        class="action-btn">

                        Save Changes

                    </button>

                    <a
                        href="items.php"
                        class="action-btn"
                        style="margin-left:10px;">

                        Cancel

                    </a>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>
