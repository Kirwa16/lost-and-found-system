<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: /user/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/models/User.php';

$db = new Database();
$conn = $db->getConnection();

$userModel = new User($conn);

$admin = $userModel->getUserById($_SESSION['user_id']);

if (!$admin) {
    session_destroy();
    header("Location: /login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Update Profile
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_profile'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $currentPassword = $_POST['profile_password'];

    if (
        empty($fullname) ||
        empty($email) ||
        empty($currentPassword)
    ) {

        $_SESSION['error'] = "Please complete all profile fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $_SESSION['error'] = "Please enter a valid email address.";

    } elseif (!$userModel->verifyPassword($_SESSION['user_id'], $currentPassword)) {

        $_SESSION['error'] = "Current password is incorrect.";

    } elseif ($userModel->updateProfile($_SESSION['user_id'], $fullname, $email)) {

        $_SESSION['fullname'] = $fullname;

        $_SESSION['success'] = "Profile updated successfully.";

    } else {

        $_SESSION['error'] = "Email address is already in use.";

    }

    header("Location: profile.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Change Password
|--------------------------------------------------------------------------
*/

if (isset($_POST['change_password'])) {

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];

    if (
        empty($currentPassword) ||
        empty($newPassword) ||
        empty($confirmPassword)
    ) {

        $_SESSION['error'] = "Please fill in all password fields.";

    } elseif (!$userModel->verifyPassword($_SESSION['user_id'], $currentPassword)) {

        $_SESSION['error'] = "Current password is incorrect.";

    } elseif ($newPassword !== $confirmPassword) {

        $_SESSION['error'] = "New passwords do not match.";

    } elseif (strlen($newPassword) < 8) {

        $_SESSION['error'] = "Password must be at least 8 characters.";

    } elseif ($userModel->changePassword($_SESSION['user_id'], $newPassword)) {

        $_SESSION['success'] = "Password changed successfully.";

    } else {

        $_SESSION['error'] = "Unable to change password.";

    }

    header("Location: profile.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Account Settings</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="/assets/css/profile.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main" id="main">

        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <h1>Account Settings</h1>

            <?php if(isset($_SESSION['success'])): ?>

                <div class="success">

                    <?= htmlspecialchars($_SESSION['success']); ?>

                </div>

                <?php unset($_SESSION['success']); ?>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="error">

                    <?= htmlspecialchars($_SESSION['error']); ?>

                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>

            <!-- Profile -->

            <div class="form-card">

                <h2>Profile Information</h2>

                <p class="section-text">

                    Update your administrator account information.

                </p>

                <form method="POST">

                    <div class="form-group">

                        <label>Full Name</label>

                        <input
                            type="text"
                            name="fullname"
                            value="<?= htmlspecialchars($admin['fullname']); ?>"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Email Address</label>

                        <input
                            type="email"
                            name="email"
                            value="<?= htmlspecialchars($admin['email']); ?>"
                            required>

                    </div>

                    <div class="form-group">

                        <label>Role</label>

                        <input
                            type="text"
                            value="<?= ucfirst($admin['role']); ?>"
                            readonly>

                    </div>

                    <div class="form-group">

                        <label>Member Since</label>

                        <input
                            type="text"
                            value="<?= date('F j, Y', strtotime($admin['created_at'])); ?>"
                            readonly>

                    </div>

                    <div class="form-group">

                        <label>Account Status</label>

                        <input
                            type="text"
                            value="Active"
                            readonly>

                    </div>

                    <div class="form-group">

                        <label>Current Password</label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="profile_password"
                                name="profile_password"
                                required>

                            <i class="fas fa-eye toggle-password"
                               data-target="profile_password"></i>

                        </div>

                    </div>

                    <button
                        class="action-btn"
                        type="submit"
                        name="update_profile">

                        <i class="fas fa-save"></i>

                        Save Changes

                    </button>

                </form>

            </div>

            <br>

            <!-- Security -->

            <div class="form-card">

                <h2>Security</h2>

                <p class="section-text">

                    Change your administrator password.

                </p>

                <form method="POST">

                    <div class="form-group">

                        <label>Current Password</label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="current_password"
                                name="current_password"
                                required>

                            <i class="fas fa-eye toggle-password"
                               data-target="current_password"></i>

                        </div>

                    </div>

                    <div class="form-group">

                        <label>New Password</label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                required>

                            <i class="fas fa-eye toggle-password"
                               data-target="new_password"></i>

                        </div>

                    </div>

                    <div class="form-group">

                        <label>Confirm New Password</label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                required>

                            <i class="fas fa-eye toggle-password"
                               data-target="confirm_password"></i>

                        </div>

                    </div>

                    <button
                        class="action-btn"
                        type="submit"
                        name="change_password">

                        <i class="fas fa-lock"></i>

                        Change Password

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/profile.js"></script>

</body>

</html>
