
<?php

require_once '../includes/auth.php';
require_once '../backend/models/Dashboard.php';

if($_SESSION['role'] !== 'user')
{
    header("Location: /admin/dashboard.php");
    exit;
}

$dashboard = new Dashboard();

$userId = $_SESSION['user_id'];

$totalLost      = $dashboard->countLostItems($userId);
$totalFound     = $dashboard->countFoundItems($userId);
$totalClaims    = $dashboard->countClaims($userId);
$totalRecovered = $dashboard->countRecovered($userId);

include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>
            Welcome,
            <?= htmlspecialchars($_SESSION['fullname']); ?>
        </h1>

        <p>
            Manage your lost and found reports.
        </p>

        <div class="cards">

            <div class="card">
                <h3>Lost Reports</h3>
                <p><?= $totalLost ?></p>
            </div>

            <div class="card">
                <h3>Found Reports</h3>
                <p><?= $totalFound ?></p>
            </div>

            <div class="card">
                <h3>Claims</h3>
                <p><?= $totalClaims ?></p>
            </div>

            <div class="card">
                <h3>Recovered</h3>
                <p><?= $totalRecovered ?></p>
            </div>

        </div>

        <div class="card">

            <h2>Quick Actions</h2>

            <div class="action-grid">

                <a href="/user/report-lost.php"
                   class="action-btn">
                    Report Lost Item
                </a>

                <a href="/user/report-found.php"
                   class="action-btn">
                    Report Found Item
                </a>

                <a href="/user/search.php"
                   class="action-btn">
                    Search Items
                </a>

                <a href="/user/claims.php"
                   class="action-btn">
                    My Claims
                </a>

            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

