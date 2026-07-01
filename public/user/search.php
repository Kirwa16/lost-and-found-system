<?php

session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if ($_SESSION['role'] !== 'user')
{
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/models/Search.php';

$searchModel = new Search();

$results = [];
$keyword = '';
$category = '';

if (isset($_GET['search']))
{
    $keyword = trim($_GET['keyword'] ?? '');
    $category = trim($_GET['category'] ?? '');

    $results = $searchModel->searchItems(
        $keyword,
        $category
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Search Items</title>

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

            <h1>Search Items</h1>

            <div class="form-card">

                <form method="GET">

                    <div class="form-group">

                        <label>Keyword</label>

                        <input
                            type="text"
                            name="keyword"
                            placeholder="Item name, description or location"
                            value="<?= htmlspecialchars($keyword) ?>">

                    </div>

                    <div class="form-group">

                        <label>Category</label>

                        <select name="category">

                            <option value="">All Categories</option>

                            <option value="Electronics" <?= $category === 'Electronics' ? 'selected' : '' ?>>
                                Electronics
                            </option>

                            <option value="Documents" <?= $category === 'Documents' ? 'selected' : '' ?>>
                                Documents
                            </option>

                            <option value="Keys" <?= $category === 'Keys' ? 'selected' : '' ?>>
                                Keys
                            </option>

                            <option value="Clothing" <?= $category === 'Clothing' ? 'selected' : '' ?>>
                                Clothing
                            </option>

                            <option value="Bags" <?= $category === 'Bags' ? 'selected' : '' ?>>
                                Bags
                            </option>

                            <option value="Accessories" <?= $category === 'Accessories' ? 'selected' : '' ?>>
                                Accessories
                            </option>

                        </select>

                    </div>

                    <button
                        type="submit"
                        name="search"
                        class="action-btn">
                        Search
                    </button>

                </form>

            </div>

            <br>

            <?php if(isset($_GET['search'])): ?>

                <div class="card">

                    <?php if(empty($results)): ?>

                        <p>No items found.</p>

                    <?php else: ?>

                        <table class="table">

                            <thead>

                                <tr>
                                    <th>#</th>
                                    <th>Type</th>
                                    <th>Item</th>
                                    <th>Category</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php $count = 1; ?>

                            <?php foreach($results as $item): ?>

                                <tr>

                                    <td>
                                        <?= $count++ ?>
                                    </td>

                                    <td>

                                        <?php if($item['item_type'] === 'Lost Item'): ?>

                                            <span class="badge badge-danger">
                                                Lost
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-success">
                                                Found
                                            </span>

                                        <?php endif; ?>

                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['item_name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['category']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($item['location']) ?>
                                    </td>

                                    <td>

                                        <?php if($item['status'] === 'matched'): ?>

                                            <span class="badge badge-success">
                                                Matched
                                            </span>

                                        <?php elseif($item['status'] === 'claimed'): ?>

                                            <span class="badge badge-warning">
                                                Claimed
                                            </span>

                                        <?php elseif($item['status'] === 'available'): ?>

                                            <span class="badge badge-success">
                                                Available
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
                                            strtotime($item['item_date'])
                                        ) ?>
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
