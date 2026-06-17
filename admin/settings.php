<?php

require_once '../includes/auth.php';

if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>System Settings</h1>

        <div class="card">

            <h3>Application Information</h3>

            <p>
                Lost & Found Management System
            </p>

            <p>
                Version 1.0
            </p>

            <p>
                Administrator Panel
            </p>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>
