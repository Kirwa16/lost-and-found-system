<?php

require_once '../includes/auth.php';
require_once '../backend/models/User.php';

$userModel = new User();

$user = $userModel->getUserById(
    $_SESSION['user_id']
);

if(isset($_POST['update_profile']))
{
    $userModel->updateProfile(
        $_SESSION['user_id'],
        trim($_POST['fullname']),
        trim($_POST['email'])
    );

    header("Location: profile.php");
    exit;
}

if(isset($_POST['change_password']))
{
    if(
        $_POST['new_password'] ===
        $_POST['confirm_password']
    )
    {
        $userModel->changePassword(
            $_SESSION['user_id'],
            $_POST['new_password']
        );

        header("Location: profile.php");
        exit;
    }
}

include '../includes/header.php';
?>

<link rel="stylesheet"
href="/assets/css/dashboard.css">

<div class="dashboard">

<?php include '../includes/sidebar.php'; ?>

<div class="content">

<h1>My Profile</h1>

<div class="card">

<h2>Profile Information</h2>

<form method="POST">

<label>Full Name</label>

<input
type="text"
name="fullname"
value="<?= htmlspecialchars($user['fullname']); ?>"
required>

<br><br>

<label>Email</label>

<input
type="email"
name="email"
value="<?= htmlspecialchars($user['email']); ?>"
required>

<br><br>

<button
class="action-btn"
name="update_profile">

Update Profile

</button>

</form>

</div>

<br>

<div class="card">

<h2>Change Password</h2>

<form method="POST">

<label>New Password</label>

<input
type="password"
name="new_password"
required>

<br><br>

<label>Confirm Password</label>

<input
type="password"
name="confirm_password"
required>

<br><br>

<button
class="action-btn"
name="change_password">

Change Password

</button>

</form>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>

