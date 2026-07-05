<?php


session_start();
require_once __DIR__ . '/../../backend/helpers/csrf.php';

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

try
{
    /*
    |--------------------------------------------------------------------------
    | Check Item Exists
    |--------------------------------------------------------------------------
    */

    $check = $conn->prepare(
        "SELECT id, item_name, category, status
         FROM {$table}
         WHERE id = :id"
    );

    $check->execute([
        ':id' => $id
    ]);

    $item = $check->fetch(PDO::FETCH_ASSOC);

    if(!$item)
    {
        header("Location: /admin/items.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Item
    |--------------------------------------------------------------------------
    */

    if($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        if(!csrf_validate($_POST['csrf_token'] ?? null)) {
            header("Location: /admin/items.php?error=invalid_token");
            exit;
        }

        $stmt = $conn->prepare(
            "DELETE FROM {$table}
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);

        header("Location: /admin/items.php?success=deleted");
        exit;
    }
}
catch(PDOException $e)
{
    header("Location: /admin/items.php?error=delete_failed");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Delete Item</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main" id="main">

        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <div class="page-header">
                <h1>Delete <?= ucfirst($type) ?> Item</h1>

                <a
                    href="/admin/items.php"
                    class="secondary-btn"
                    title="Back to Items">
                    <i class="fas fa-arrow-left"></i>
                    Back to Items
                </a>
            </div>

            <div class="form-card">

                <p>Are you sure you want to delete this item?</p>
                <br>

                <p><strong>Item:</strong> <?= htmlspecialchars($item['item_name']) ?></p>
                <br>

                <p><strong>Category:</strong> <?= htmlspecialchars($item['category']) ?></p>
                <br>

                <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($item['status'])) ?></p>

                <div class="confirm-actions">
                    <a
                        href="/admin/items.php"
                        class="secondary-btn">
                        Cancel
                    </a>

                    <form method="POST">
                        <?= csrf_field() ?>
                        <button
                            type="submit"
                            class="action-btn danger-btn">
                            <i class="fas fa-trash"></i>
                            Delete Item
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
