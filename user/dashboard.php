
<?php

require_once '../includes/auth.php';
include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>
            Welcome,
            <?php echo htmlspecialchars($_SESSION['fullname']); ?>
        </h1>

        <p>
            Manage lost and found reports from your dashboard.
        </p>

        <div class="cards">

            <div class="card">
                <h3>Lost Reports</h3>
                <p>0</p>
            </div>

            <div class="card">
                <h3>Found Reports</h3>
                <p>0</p>
            </div>

            <div class="card">
                <h3>Claims</h3>
                <p>0</p>
            </div>

            <div class="card">
                <h3>Recovered</h3>
                <p>0</p>
            </div>

        </div>

        <div class="quick-actions">

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
                    View Claims
                </a>

            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

