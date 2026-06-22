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

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

$sort = $_GET['sort'] ?? 'desc';
$typeFilter = $_GET['type'] ?? 'all';

$order = ($sort === 'asc') ? 'ASC' : 'DESC';

/*
|--------------------------------------------------------------------------
| Lost Items
|--------------------------------------------------------------------------
*/

$lostItems = [];

if($typeFilter === 'all' || $typeFilter === 'lost')
{
    $lostItems = $conn->query(
        "SELECT
            id,
            item_name,
            category,
            status,
            created_at
         FROM lost_items
         ORDER BY created_at $order"
    )->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| Found Items
|--------------------------------------------------------------------------
*/

$foundItems = [];

if($typeFilter === 'all' || $typeFilter === 'found')
{
    $foundItems = $conn->query(
        "SELECT
            id,
            item_name,
            category,
            status,
            created_at
         FROM found_items
         ORDER BY created_at $order"
    )->fetchAll(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Manage Items</title>

<link rel="stylesheet"
      href="/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main">

        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <h1>Manage Items</h1>

            <?php if(isset($_GET['success'])): ?>

                <div class="success">

                    Item deleted successfully.

                </div>

            <?php endif; ?>

            <?php if(isset($_GET['error'])): ?>

                <div class="error">

                    Unable to delete item.
                    It may be linked to matches or claims.

                </div>

            <?php endif; ?>

            <!-- Filters -->

            <div class="card">

                <form method="GET">

                    <div style="display:flex;gap:20px;flex-wrap:wrap;align-items:end;">

                        <div class="form-group">

                            <label>Item Type</label>

                            <select name="type">

                                <option value="all"
                                    <?= $typeFilter === 'all' ? 'selected' : '' ?>>
                                    All Items
                                </option>

                                <option value="lost"
                                    <?= $typeFilter === 'lost' ? 'selected' : '' ?>>
                                    Lost Items
                                </option>

                                <option value="found"
                                    <?= $typeFilter === 'found' ? 'selected' : '' ?>>
                                    Found Items
                                </option>

                            </select>

                        </div>

                        <div class="form-group">

                            <label>Sort Order</label>

                            <select name="sort">

                                <option value="desc"
                                    <?= $sort === 'desc' ? 'selected' : '' ?>>
                                    Newest First
                                </option>

                                <option value="asc"
                                    <?= $sort === 'asc' ? 'selected' : '' ?>>
                                    Oldest First
                                </option>

                            </select>

                        </div>

                        <div class="form-group">

                            <button
                                type="submit"
                                class="action-btn">

                                Apply Filters

                            </button>

                        </div>

                    </div>

                </form>

            </div>

            <br>

            <?php if($typeFilter === 'all' || $typeFilter === 'lost'): ?>

            <div class="card">

                <h2>Lost Items</h2>

                <br>

                <?php if(empty($lostItems)): ?>

                    <p>No lost items found.</p>

                <?php else: ?>

                <table class="table">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date Reported</th>
                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php $count = 1; ?>

                    <?php foreach($lostItems as $item): ?>

                        <tr>

                            <td>
                                <?= $count++ ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['item_name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['category']) ?>
                            </td>

                            <td>
                                <?= ucfirst($item['status']) ?>
                            </td>

                            <td>
                                <?= date(
                                    'd M Y',
                                    strtotime($item['created_at'])
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="view-item.php?type=lost&id=<?= $item['id'] ?>"
                                    class="action-btn">

                                    View

                                </a>

                                <a
                                    href="edit-item.php?type=lost&id=<?= $item['id'] ?>"
                                    class="action-btn">

                                    Edit

                                </a>

                                <a
                                    href="delete-item.php?type=lost&id=<?= $item['id'] ?>"
                                    class="action-btn"
                                    onclick="return confirm('Delete this item?')">

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

            <?php endif; ?>

            <?php if($typeFilter === 'all' || $typeFilter === 'found'): ?>

            <div class="card">

                <h2>Found Items</h2>

                <br>

                <?php if(empty($foundItems)): ?>

                    <p>No found items found.</p>

                <?php else: ?>

                <table class="table">

                    <thead>

                        <tr>

                            <th>#</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Date Reported</th>
                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php $count = 1; ?>

                    <?php foreach($foundItems as $item): ?>

                        <tr>

                            <td>
                                <?= $count++ ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['item_name']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['category']) ?>
                            </td>

                            <td>
                                <?= ucfirst($item['status']) ?>
                            </td>

                            <td>
                                <?= date(
                                    'd M Y',
                                    strtotime($item['created_at'])
                                ) ?>
                            </td>

                            <td>

                                <a
                                    href="view-item.php?type=found&id=<?= $item['id'] ?>"
                                    class="action-btn">

                                    View

                                </a>

                                <a
                                    href="edit-item.php?type=found&id=<?= $item['id'] ?>"
                                    class="action-btn">

                                    Edit

                                </a>

                                <a
                                    href="delete-item.php?type=found&id=<?= $item['id'] ?>"
                                    class="action-btn"
                                    onclick="return confirm('Delete this item?')">

                                    Delete

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

                <?php endif; ?>

            </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</body>

</html>
