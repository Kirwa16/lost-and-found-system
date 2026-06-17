<?php

require_once '../includes/auth.php';
require_once __DIR__ . '/../backend/models/Search.php';

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

include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/reports.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>Search Items</h1>

        <form method="GET">

            <div class="form-card">

                <div class="form-group">

                    <input
                        type="text"
                        name="keyword"
                        placeholder="Search item name, description or location"
                        value="<?= htmlspecialchars($keyword); ?>"
                    >

                </div>

                <div class="form-group">

                    <select name="category">

                        <option value="">
                            All Categories
                        </option>

                        <option value="Electronics"
                            <?= $category === 'Electronics' ? 'selected' : ''; ?>>
                            Electronics
                        </option>

                        <option value="Documents"
                            <?= $category === 'Documents' ? 'selected' : ''; ?>>
                            Documents
                        </option>

                        <option value="Keys"
                            <?= $category === 'Keys' ? 'selected' : ''; ?>>
                            Keys
                        </option>

                        <option value="Clothing"
                            <?= $category === 'Clothing' ? 'selected' : ''; ?>>
                            Clothing
                        </option>

                        <option value="Bags"
                            <?= $category === 'Bags' ? 'selected' : ''; ?>>
                            Bags
                        </option>

                        <option value="Accessories"
                            <?= $category === 'Accessories' ? 'selected' : ''; ?>>
                            Accessories
                        </option>

                    </select>

                </div>

                <button
                    type="submit"
                    class="action-btn"
                    name="search">

                    Search

                </button>

            </div>

        </form>

        <?php if(isset($_GET['search']) && empty($results)): ?>

            <div class="card" style="margin-top:20px;">
                <p>No items found.</p>
            </div>

        <?php endif; ?>

        <div class="reports-grid">

            <?php foreach($results as $item): ?>

                <div class="report-card">

                    <?php if(!empty($item['image'])): ?>

                        <img
                            src="/backend/uploads/lost-items/<?= htmlspecialchars($item['image']); ?>"
                            alt="Item Image"
                        >

                    <?php endif; ?>

                    <h3>
                        <?= htmlspecialchars($item['item_name']); ?>
                    </h3>

                    <p>
                        <strong>Category:</strong>
                        <?= htmlspecialchars($item['category']); ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?= htmlspecialchars($item['location_lost']); ?>
                    </p>

                    <p>
                        <strong>Description:</strong>
                        <?= htmlspecialchars($item['description']); ?>
                    </p>

                    <p>
                        <strong>Date Lost:</strong>
                        <?= htmlspecialchars($item['date_lost']); ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?= ucfirst($item['status']); ?>
                    </p>

                </div>

            <?php endforeach; ?>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>
