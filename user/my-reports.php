
<?php

require_once '../includes/auth.php';
require_once '../backend/models/Item.php';

$itemModel = new Item();

$reports = $itemModel->getUserLostItems(
    $_SESSION['user_id']
);

include '../includes/header.php';

?>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/reports.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>My Lost Item Reports</h1>

        <?php if(empty($reports)): ?>

            <div class="empty-state">
                No reports found.
            </div>

        <?php else: ?>

            <div class="reports-grid">

                <?php foreach($reports as $report): ?>

                    <div class="report-card">

                        <?php if(!empty($report['image'])): ?>

                            <img
                                src="/backend/uploads/lost-items/<?=
                                htmlspecialchars($report['image']) ?>"
                                alt="Item Image">

                        <?php endif; ?>

                        <h3>
                            <?= htmlspecialchars($report['item_name']) ?>
                        </h3>

                        <p>
                            <strong>Category:</strong>
                            <?= htmlspecialchars($report['category']) ?>
                        </p>

                        <p>
                            <strong>Location:</strong>
                            <?= htmlspecialchars($report['location_lost']) ?>
                        </p>

                        <p>
                            <strong>Date Lost:</strong>
                            <?= htmlspecialchars($report['date_lost']) ?>
                        </p>

                        <span class="status status-<?= $report['status']; ?>">
                            <?= ucfirst($report['status']); ?>
                        </span>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

