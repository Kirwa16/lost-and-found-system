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

if(!isset($_GET['id']) || !is_numeric($_GET['id']))
{
    header("Location: /user/search.php");
    exit;
}

$id = (int)$_GET['id'];
$type = $_GET['type'] ?? 'found';

if(!in_array($type, ['lost', 'found'], true))
{
    header("Location: /user/search.php");
    exit;
}

$table = ($type === 'found') ? 'found_items' : 'lost_items';
$locationColumn = ($type === 'found') ? 'location_found' : 'location_lost';
$dateColumn = ($type === 'found') ? 'date_found' : 'date_lost';
$locationLabel = ($type === 'found') ? 'Location Found' : 'Location Lost';
$dateLabel = ($type === 'found') ? 'Date Found' : 'Date Lost';
$backUrl = '/user/search.php';

if(!empty($_GET['return']) && strpos($_GET['return'], '/user/search.php') === 0)
{
    $backUrl = $_GET['return'];
}

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare(
    "SELECT *
     FROM {$table}
     WHERE id = :id
     LIMIT 1"
);

$stmt->execute([
    ':id' => $id
]);

$item = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$item)
{
    die("Item not found.");
}

$imagePath = null;
if(!empty($item['image']))
{
    $imagePath = '/' . ltrim($item['image'], '/');
}

$canClaim = $type === 'found'
    && in_array($item['status'], ['available', 'pending'], true)
    && (int)$item['user_id'] !== (int)$_SESSION['user_id'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Item Details</title>

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
                <h1>Item Details</h1>

                <a
                    href="<?= htmlspecialchars($backUrl) ?>"
                    class="secondary-btn"
                    title="Back to Search">
                    <i class="fas fa-arrow-left"></i>
                    Back to Search
                </a>
            </div>

            <div class="form-card">

                <p><strong>Item:</strong> <?= htmlspecialchars($item['item_name']) ?></p>
                <br>

                <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                <br>

                <p><strong>Color:</strong> <?= htmlspecialchars($item['color'] ?? 'N/A') ?></p>
                <br>

                <p><strong>Brand / Model:</strong> <?= htmlspecialchars($item['brand_model'] ?? 'N/A') ?></p>
                <br>

                <p><strong>Distinguishing Features:</strong> <?= nl2br(htmlspecialchars($item['unique_features'] ?? 'N/A')) ?></p>
                <br>

                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($item['description'])) ?></p>
                <br>

                <p><strong><?= $locationLabel ?>:</strong> <?= htmlspecialchars($item[$locationColumn]) ?></p>
                <br>

                <p><strong><?= $dateLabel ?>:</strong> <?= htmlspecialchars($item[$dateColumn]) ?></p>
                <br>

                <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($item['status'])) ?></p>
                <br>

                <p><strong>Image:</strong></p>
                <br>

                <?php if($imagePath): ?>

                    <img
                        class="report-image"
                        src="<?= htmlspecialchars($imagePath) ?>"
                        alt="<?= htmlspecialchars($item['item_name']) ?>">

                <?php else: ?>

                    <p>No image uploaded.</p>

                <?php endif; ?>

                <?php if($canClaim): ?>

                    <br>

                    <a
                        href="/user/submit-claim.php?item_id=<?= $item['id'] ?>&item_type=found"
                        class="action-btn">
                        <i class="fas fa-hand-holding-heart"></i>
                        Claim This Item
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</div>

<script src="/assets/js/sidebar.js"></script>
</body>

</html>
