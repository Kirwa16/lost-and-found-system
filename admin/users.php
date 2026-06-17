<?php

require_once '../includes/auth.php';
require_once '../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

if(isset($_GET['make_admin']))
{
    $stmt = $conn->prepare(
        "UPDATE users
         SET role='admin'
         WHERE id=:id"
    );

    $stmt->execute([
        ':id' => $_GET['make_admin']
    ]);
}

if(isset($_GET['delete']))
{
    $stmt = $conn->prepare(
        "DELETE FROM users
         WHERE id=:id"
    );

    $stmt->execute([
        ':id' => $_GET['delete']
    ]);
}
if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

$users = $conn->query(
    "SELECT *
     FROM users
     ORDER BY created_at DESC"
)->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>User Management</h1>

        <table class="table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach($users as $user): ?>

                <tr>

                    <td><?= $user['id']; ?></td>

                    <td>
                        <?= htmlspecialchars($user['fullname']); ?>
                    </td>

                    <td>
                        <?= htmlspecialchars($user['email']); ?>
                    </td>

                    <td>
                        <?= ucfirst($user['role']); ?>
                    </td>

                    <td>
                        <?= $user['created_at']; ?>
                    </td>

                    <td>

                        <?php if($user['role'] !== 'admin'): ?>

                            <a href="?make_admin=<?= $user['id']; ?>">
                                Make Admin
                            </a>

                            |

                        <?php endif; ?>

                        <a
                        onclick="return confirm('Delete this user?')"
                        href="?delete=<?= $user['id']; ?>">

                            Delete

                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

