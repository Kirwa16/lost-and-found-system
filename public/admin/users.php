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
| Update User Role
|--------------------------------------------------------------------------
*/

if(isset($_GET['action']) && isset($_GET['id']))
{
    $id = (int)$_GET['id'];

    if($_GET['action'] === 'make_admin')
    {
        $stmt = $conn->prepare(
            "UPDATE users
             SET role = 'admin'
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);
    }

    if($_GET['action'] === 'make_user')
    {
        $stmt = $conn->prepare(
            "UPDATE users
             SET role = 'user'
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $id
        ]);
    }

    header("Location: /admin/users.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Users
|--------------------------------------------------------------------------
*/

$users = $conn
    ->query(
        "SELECT *
         FROM users
         ORDER BY created_at DESC"
    )
    ->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>User Management</title>

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

            <h1>User Management</h1>

            <div class="card">

                <table class="table">

                    <thead>

                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php $count = 1; ?>

                    <?php foreach($users as $user): ?>

                        <tr>

                            <td>
                                <?= $count++ ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['fullname']) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($user['email']) ?>
                            </td>

                            <td>

                                <?php if($user['role'] === 'admin'): ?>

                                    <span class="badge badge-success">
                                        Admin
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-warning">
                                        User
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>
                                <?= date(
                                    'd M Y',
                                    strtotime($user['created_at'])
                                ) ?>
                            </td>

                            <td>

                                <?php if($user['role'] === 'user'): ?>

                                    <a
                                        href="?action=make_admin&id=<?= $user['id'] ?>"
                                        class="action-btn">

                                        Make Admin

                                    </a>

                                <?php else: ?>

                                    <a
                                        href="?action=make_user&id=<?= $user['id'] ?>"
                                        class="action-btn">

                                        Make User

                                    </a>

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>

</html>

