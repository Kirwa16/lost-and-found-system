<?php

require_once '../includes/auth.php';
require_once '../backend/config/database.php';

if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

/* Delete Lost Item */
if(isset($_GET['delete_lost']))
{
    $stmt = $conn->prepare(
        "DELETE FROM lost_items
         WHERE id = :id"
    );

    $stmt->execute([
        ':id' => $_GET['delete_lost']
    ]);
}

/* Delete Found Item */
if(isset($_GET['delete_found']))
{
    $stmt = $conn->prepare(
        "DELETE FROM found_items
         WHERE id = :id"
    );

    $stmt->execute([
        ':id' => $_GET['delete_found']
    ]);
}
if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

/* Lost Items */
$lostItems = $conn->query(
    "SELECT l.*,
            u.fullname
     FROM lost_items l
     JOIN users u
     ON l.user_id = u.id
     ORDER BY l.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

/* Found Items */
$foundItems = $conn->query(
    "SELECT f.*,
            u.fullname
     FROM found_items f
     JOIN users u
     ON f.user_id = u.id
     ORDER BY f.created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>Manage Items</h1>

        <div class="card">

            <h2>Lost Items</h2>

            <table class="table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach($lostItems as $item): ?>

                    <tr>

                        <td><?= $item['id']; ?></td>

                        <td>
                            <?= htmlspecialchars($item['fullname']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['item_name']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['category']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['location_lost']); ?>
                        </td>

                        <td>
                            <?= ucfirst($item['status']); ?>
                        </td>

                        <td>

                            <a
                            href="?delete_lost=<?= $item['id']; ?>"
                            onclick="return confirm('Delete report?')">

                                Delete

                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <br>

        <div class="card">

            <h2>Found Items</h2>

            <table class="table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach($foundItems as $item): ?>

                    <tr>

                        <td><?= $item['id']; ?></td>

                        <td>
                            <?= htmlspecialchars($item['fullname']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['item_name']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['category']); ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($item['location_found']); ?>
                        </td>

                        <td>
                            <?= ucfirst($item['status']); ?>
                        </td>

                        <td>

                            <a
                            href="?delete_found=<?= $item['id']; ?>"
                            onclick="return confirm('Delete report?')">

                                Delete

                            </a>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

