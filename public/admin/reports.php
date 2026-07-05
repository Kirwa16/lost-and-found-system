<?php

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
$conn = $db->getConnection();

function reportValue(PDO $conn, string $sql): int
{
    return (int)$conn->query($sql)->fetchColumn();
}

function reportRows(PDO $conn, string $sql): array
{
    return $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

function reportBadgeClass(string $status): string
{
    return match($status) {
        'approved', 'collected', 'claimed', 'returned' => 'badge-success',
        'pending', 'matched' => 'badge-warning',
        default => 'badge-danger'
    };
}

/*
|--------------------------------------------------------------------------
| Accurate System-Wide Statistics
|--------------------------------------------------------------------------
*/

$totalStudents = reportValue($conn, "SELECT COUNT(*) FROM users WHERE role = 'student'");
$totalStaff = reportValue($conn, "SELECT COUNT(*) FROM users WHERE role = 'staff'");
$totalLost = reportValue($conn, "SELECT COUNT(*) FROM lost_items");
$totalFound = reportValue($conn, "SELECT COUNT(*) FROM found_items");
$totalMatches = reportValue($conn, "SELECT COUNT(*) FROM matches");
$totalClaims = reportValue($conn, "SELECT COUNT(*) FROM claims");

$pendingClaims = reportValue($conn, "SELECT COUNT(*) FROM claims WHERE status = 'pending'");
$approvedClaims = reportValue($conn, "SELECT COUNT(*) FROM claims WHERE status = 'approved'");
$rejectedClaims = reportValue($conn, "SELECT COUNT(*) FROM claims WHERE status = 'rejected'");
$collectedClaims = reportValue($conn, "SELECT COUNT(*) FROM claims WHERE status = 'collected'");

$directClaims = reportValue($conn, "SELECT COUNT(*) FROM claims WHERE match_id IS NULL");
$matchBasedClaims = reportValue($conn, "SELECT COUNT(*) FROM claims WHERE match_id IS NOT NULL");

$recoveryRate = $totalLost > 0
    ? round(($collectedClaims / $totalLost) * 100, 1)
    : 0;

/*
|--------------------------------------------------------------------------
| Recent Activity
|--------------------------------------------------------------------------
*/

$recentLost = reportRows(
    $conn,
    "SELECT id, item_name, category, status, created_at
     FROM lost_items
     ORDER BY created_at DESC
     LIMIT 5"
);

$recentFound = reportRows(
    $conn,
    "SELECT id, item_name, category, status, created_at
     FROM found_items
     ORDER BY created_at DESC
     LIMIT 5"
);

$recentClaims = reportRows(
    $conn,
    "SELECT
        c.id,
        c.status,
        c.created_at,
        u.fullname,
        CASE
            WHEN c.match_id IS NULL THEN 'Direct Claim'
            ELSE 'Match-Based'
        END AS claim_type,
        COALESCE(f.item_name, direct_found.item_name, 'Item') AS found_item,
        l.item_name AS lost_item
     FROM claims c
     INNER JOIN users u
        ON u.id = c.user_id
     LEFT JOIN matches m
        ON m.id = c.match_id
     LEFT JOIN lost_items l
        ON l.id = m.lost_item_id
     LEFT JOIN found_items f
        ON f.id = m.found_item_id
     LEFT JOIN found_items direct_found
        ON c.item_type = 'found'
        AND c.item_id = direct_found.id
     ORDER BY c.created_at DESC
     LIMIT 5"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Reports</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script src="/assets/js/sidebar.js"></script>
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main" id="main">

        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <div class="page-header">
                <div>
                    <h1>System Reports</h1>
                    <p class="dashboard-subtitle">All-time operational summary</p>
                </div>
            </div>

            <div class="cards">
                <div class="card metric-card" title="Registered student accounts.">
                    <h3>Total Students</h3>
                    <p><?= (int)$totalStudents ?></p>
                </div>

                <div class="card metric-card" title="Registered staff accounts.">
                    <h3>Total Staff</h3>
                    <p><?= (int)$totalStaff ?></p>
                </div>

                <div class="card metric-card" title="All lost reports submitted.">
                    <h3>Lost Items</h3>
                    <p><?= (int)$totalLost ?></p>
                </div>

                <div class="card metric-card" title="All found reports submitted.">
                    <h3>Found Items</h3>
                    <p><?= (int)$totalFound ?></p>
                </div>

                <div class="card metric-card" title="All manual and detected matches created.">
                    <h3>Matches</h3>
                    <p><?= (int)$totalMatches ?></p>
                </div>

                <div class="card metric-card" title="All claims, including match-based and direct claims.">
                    <h3>Total Claims</h3>
                    <p><?= (int)$totalClaims ?></p>
                </div>

                <div class="card metric-card" title="Claims waiting for admin review.">
                    <h3>Pending Claims</h3>
                    <p><?= (int)$pendingClaims ?></p>
                </div>

                <div class="card metric-card" title="Claims approved but not yet collected.">
                    <h3>Approved Claims</h3>
                    <p><?= (int)$approvedClaims ?></p>
                </div>

                <div class="card metric-card" title="Claims rejected by admin.">
                    <h3>Rejected Claims</h3>
                    <p><?= (int)$rejectedClaims ?></p>
                </div>

                <div class="card metric-card" title="Claims marked as collected.">
                    <h3>Collected Claims</h3>
                    <p><?= (int)$collectedClaims ?></p>
                </div>

                <div class="card metric-card" title="Claims submitted directly from search results.">
                    <h3>Direct Claims</h3>
                    <p><?= (int)$directClaims ?></p>
                </div>

                <div class="card metric-card" title="Claims submitted from approved matches.">
                    <h3>Match-Based Claims</h3>
                    <p><?= (int)$matchBasedClaims ?></p>
                </div>

                <div class="card metric-card" title="Collected claims divided by total lost reports.">
                    <h3>Recovery Rate</h3>
                    <p><?= $recoveryRate ?>%</p>
                </div>
            </div>

            <div class="dashboard-table-grid">
                <div class="card">
                    <div class="section-header">
                        <h2>Recent Lost Reports</h2>
                        <a href="/admin/items.php?type=lost" class="view-all-link">View All</a>
                    </div>

                    <?php if(empty($recentLost)): ?>
                        <p>No lost reports available.</p>
                    <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Report No.</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $count = 1; ?>
                        <?php foreach($recentLost as $item): ?>
                            <tr class="clickable-row" data-href="/admin/view-item.php?type=lost&id=<?= (int)$item['id'] ?>">
                                <td><?= $count++ ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= htmlspecialchars($item['category']) ?></td>
                                <td><span class="badge <?= reportBadgeClass($item['status']) ?>"><?= ucfirst($item['status']) ?></span></td>
                                <td><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/view-item.php?type=lost&id=<?= (int)$item['id'] ?>" class="action-btn view" title="View Lost Item" aria-label="View Lost Item">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="section-header">
                        <h2>Recent Found Reports</h2>
                        <a href="/admin/items.php?type=found" class="view-all-link">View All</a>
                    </div>

                    <?php if(empty($recentFound)): ?>
                        <p>No found reports available.</p>
                    <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Report No.</th>
                                <th>Item</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $count = 1; ?>
                        <?php foreach($recentFound as $item): ?>
                            <tr class="clickable-row" data-href="/admin/view-item.php?type=found&id=<?= (int)$item['id'] ?>">
                                <td><?= $count++ ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= htmlspecialchars($item['category']) ?></td>
                                <td><span class="badge <?= reportBadgeClass($item['status']) ?>"><?= ucfirst($item['status']) ?></span></td>
                                <td><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/view-item.php?type=found&id=<?= (int)$item['id'] ?>" class="action-btn view" title="View Found Item" aria-label="View Found Item">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <div class="section-header">
                        <h2>Recent Claims</h2>
                        <a href="/admin/claims.php" class="view-all-link">View All</a>
                    </div>

                    <?php if(empty($recentClaims)): ?>
                        <p>No claims available.</p>
                    <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Claim No.</th>
                                <th>Claimant</th>
                                <th>Type</th>
                                <th>Claim</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $count = 1; ?>
                        <?php foreach($recentClaims as $claim): ?>
                            <tr class="clickable-row" data-href="/admin/claim-details.php?id=<?= (int)$claim['id'] ?>">
                                <td><?= $count++ ?></td>
                                <td><?= htmlspecialchars($claim['fullname']) ?></td>
                                <td>
                                    <span class="badge <?= $claim['claim_type'] === 'Direct Claim' ? 'badge-info' : 'badge-primary' ?>">
                                        <?= htmlspecialchars($claim['claim_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if($claim['claim_type'] === 'Direct Claim'): ?>
                                        Claiming: <?= htmlspecialchars($claim['found_item']) ?>
                                    <?php else: ?>
                                        Lost: <?= htmlspecialchars($claim['lost_item'] ?? 'N/A') ?> /
                                        Found: <?= htmlspecialchars($claim['found_item']) ?>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge <?= reportBadgeClass($claim['status']) ?>"><?= ucfirst($claim['status']) ?></span></td>
                                <td><?= date('d M Y', strtotime($claim['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/claim-details.php?id=<?= (int)$claim['id'] ?>" class="action-btn view" title="View Claim" aria-label="View Claim">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>

</div>

<script>
document.querySelectorAll('.clickable-row').forEach(function(row){
    row.addEventListener('click', function(event){
        if(event.target.closest('a')) {
            return;
        }

        window.location.href = row.dataset.href;
    });
});
</script>

</body>
</html>
