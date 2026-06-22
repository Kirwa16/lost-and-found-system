<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if($_SESSION['role'] !== 'user')
{
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/models/User.php';

$userModel = new User();

$user = $userModel->getUserById(
    $_SESSION['user_id']
);

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

if(isset($_POST['update_profile']))
{
    $userModel->updateProfile(
        $_SESSION['user_id'],
        trim($_POST['fullname']),
        trim($_POST['email'])
    );

    $_SESSION['success'] =
        "Profile updated successfully.";

    header("Location: profile.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

if(isset($_POST['change_password']))
{
    if(
        $_POST['new_password'] !==
        $_POST['confirm_password']
    )
    {
        $_SESSION['error'] =
            "Passwords do not match.";
    }
    else
    {
        $userModel->changePassword(
            $_SESSION['user_id'],
            $_POST['new_password']
        );

        $_SESSION['success'] =
            "Password changed successfully.";

        header("Location: profile.php");
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Profile</title>

<link rel="stylesheet"
      href="/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/user-sidebar.php'; ?>

    <div class="main">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <h1>My Profile</h1>

            <?php if(isset($_SESSION['success'])): ?>

                <div class="success">

                    <?= htmlspecialchars($_SESSION['success']) ?>

                </div>

                <?php unset($_SESSION['success']); ?>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="error">

                    <?= htmlspecialchars($_SESSION['error']) ?>

                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>

            <!-- Profile Information -->

            <div class="form-card">

                <h2>Profile Information</h2>

                <br>

                <form method="POST">

                    <div class="form-group">

                        <label>Full Name</label>

                        <input
                            type="text"
                            name="fullname"
                            value="<?= htmlspecialchars($user['fullname']) ?>"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Email Address</label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($user['email']) ?>"
                            required>

                    </div>

                    <button
                        type="submit"
                        name="update_profile"
                        class="action-btn">

                        Update Profile

                    </button>

                </form>

            </div>

            <br>

            <!-- Change Password -->

            <div class="form-card">

                <h2>Change Password</h2>

                <br>

                <form method="POST">

                    <div class="form-group">

                        <label>New Password</label>

                        <input
                            type="password"
                            name="new_password"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Confirm Password</label>

                        <input
                            type="password"
                            name="confirm_password"
                            required>

                    </div>

                    <button
                        type="submit"
                        name="change_password"
                        class="action-btn">

                        Change Password

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>