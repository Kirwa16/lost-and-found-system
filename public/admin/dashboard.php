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

$allowedRanges = ['week', 'month', 'year', 'all'];
$range = $_GET['range'] ?? 'month';

if(!in_array($range, $allowedRanges, true)) {
    $range = 'month';
}

function rangeStartDate(string $range): ?string
{
    return match($range) {
        'week' => date('Y-m-d 00:00:00', strtotime('monday this week')),
        'month' => date('Y-m-01 00:00:00'),
        'year' => date('Y-01-01 00:00:00'),
        default => null
    };
}

function dateFilterSql(?string $startDate, string $column = 'created_at'): string
{
    return $startDate ? " WHERE {$column} >= :start_date" : "";
}

function dateFilterAndSql(?string $startDate, string $column = 'created_at'): string
{
    return $startDate ? " AND {$column} >= :start_date" : "";
}

function queryValue(PDO $conn, string $sql, array $params = []): int
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return (int)$stmt->fetchColumn();
}

function queryRows(PDO $conn, string $sql, array $params = []): array
{
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function groupTopCategories(array $rows, int $limit = 6): array
{
    $labels = [];
    $totals = [];
    $other = 0;

    foreach($rows as $index => $row) {
        if($index < $limit) {
            $labels[] = $row['category'] ?: 'Uncategorized';
            $totals[] = (int)$row['total'];
        } else {
            $other += (int)$row['total'];
        }
    }

    if($other > 0) {
        $labels[] = 'Other';
        $totals[] = $other;
    }

    return [$labels, $totals];
}

$startDate = rangeStartDate($range);
$dateParams = $startDate ? [':start_date' => $startDate] : [];
$rangeLabels = [
    'week' => 'This Week',
    'month' => 'This Month',
    'year' => 'This Year',
    'all' => 'All Time'
];

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$totalStudents = queryValue(
    $conn,
    "SELECT COUNT(*) FROM users WHERE role = 'student'" . dateFilterAndSql($startDate),
    $dateParams
);

$totalStaff = queryValue(
    $conn,
    "SELECT COUNT(*) FROM users WHERE role = 'staff'" . dateFilterAndSql($startDate),
    $dateParams
);

$totalLost = queryValue(
    $conn,
    "SELECT COUNT(*) FROM lost_items" . dateFilterSql($startDate),
    $dateParams
);

$totalFound = queryValue(
    $conn,
    "SELECT COUNT(*) FROM found_items" . dateFilterSql($startDate),
    $dateParams
);

$totalClaims = queryValue(
    $conn,
    "SELECT COUNT(*) FROM claims" . dateFilterSql($startDate),
    $dateParams
);

$totalMatches = queryValue(
    $conn,
    "SELECT COUNT(*) FROM matches" . dateFilterSql($startDate),
    $dateParams
);

$pendingClaims = queryValue(
    $conn,
    "SELECT COUNT(*) FROM claims WHERE status = 'pending'" . dateFilterAndSql($startDate),
    $dateParams
);

$approvedClaims = queryValue(
    $conn,
    "SELECT COUNT(*) FROM claims WHERE status = 'approved'" . dateFilterAndSql($startDate),
    $dateParams
);

$rejectedClaims = queryValue(
    $conn,
    "SELECT COUNT(*) FROM claims WHERE status = 'rejected'" . dateFilterAndSql($startDate),
    $dateParams
);

$collectedClaims = queryValue(
    $conn,
    "SELECT COUNT(*) FROM claims WHERE status = 'collected'" . dateFilterAndSql($startDate),
    $dateParams
);

$recoveryRate = $totalLost > 0
    ? round(($collectedClaims / $totalLost) * 100, 1)
    : 0;

/*
|--------------------------------------------------------------------------
| Category Charts
|--------------------------------------------------------------------------
*/

$lostCategoryRows = queryRows(
    $conn,
    "SELECT category, COUNT(*) total
     FROM lost_items" . dateFilterSql($startDate) . "
     GROUP BY category
     ORDER BY total DESC",
    $dateParams
);

$foundCategoryRows = queryRows(
    $conn,
    "SELECT category, COUNT(*) total
     FROM found_items" . dateFilterSql($startDate) . "
     GROUP BY category
     ORDER BY total DESC",
    $dateParams
);

[$lostCategoryLabels, $lostCategoryTotals] = groupTopCategories($lostCategoryRows);
[$foundCategoryLabels, $foundCategoryTotals] = groupTopCategories($foundCategoryRows);

/*
|--------------------------------------------------------------------------
| Monthly Activity
|--------------------------------------------------------------------------
*/

$monthlyLabels = [];
$monthlyLostTotals = [];
$monthlyFoundTotals = [];
$monthlyKeys = [];

for($i = 5; $i >= 0; $i--) {
    $monthStart = date('Y-m-01 00:00:00', strtotime("-{$i} months"));
    $monthlyKeys[] = date('Y-m', strtotime($monthStart));
    $monthlyLabels[] = date('M', strtotime($monthStart));
}

$monthlyWindowStart = date('Y-m-01 00:00:00', strtotime('-5 months'));
$monthlyWindowEnd = date('Y-m-t 23:59:59');

$monthlyLostRows = queryRows(
    $conn,
    "SELECT DATE_FORMAT(created_at, '%Y-%m') month_key, COUNT(*) total
     FROM lost_items
     WHERE created_at BETWEEN :start_date AND :end_date
     GROUP BY month_key",
    [
        ':start_date' => $monthlyWindowStart,
        ':end_date' => $monthlyWindowEnd
    ]
);

$monthlyFoundRows = queryRows(
    $conn,
    "SELECT DATE_FORMAT(created_at, '%Y-%m') month_key, COUNT(*) total
     FROM found_items
     WHERE created_at BETWEEN :start_date AND :end_date
     GROUP BY month_key",
    [
        ':start_date' => $monthlyWindowStart,
        ':end_date' => $monthlyWindowEnd
    ]
);

$monthlyLostMap = array_column($monthlyLostRows, 'total', 'month_key');
$monthlyFoundMap = array_column($monthlyFoundRows, 'total', 'month_key');

foreach($monthlyKeys as $monthKey) {
    $monthlyLostTotals[] = (int)($monthlyLostMap[$monthKey] ?? 0);
    $monthlyFoundTotals[] = (int)($monthlyFoundMap[$monthKey] ?? 0);
}

/*
|--------------------------------------------------------------------------
| Recent Tables
|--------------------------------------------------------------------------
*/

$recentLost = queryRows(
    $conn,
    "SELECT id, item_name, category, status, created_at
     FROM lost_items" . dateFilterSql($startDate) . "
     ORDER BY created_at DESC
     LIMIT 5",
    $dateParams
);

$recentFound = queryRows(
    $conn,
    "SELECT id, item_name, category, status, created_at
     FROM found_items" . dateFilterSql($startDate) . "
     ORDER BY created_at DESC
     LIMIT 5",
    $dateParams
);

$recentClaims = queryRows(
    $conn,
    "SELECT id, status, created_at
     FROM claims" . dateFilterSql($startDate) . "
     ORDER BY created_at DESC
     LIMIT 5",
    $dateParams
);

$processedClaims = queryRows(
    $conn,
    "SELECT id, status, created_at
     FROM claims
     WHERE status IN ('approved', 'rejected', 'collected')" . dateFilterAndSql($startDate) . "
     ORDER BY created_at DESC
     LIMIT 8",
    $dateParams
);

function badgeClassForStatus(string $status): string
{
    return match($status) {
        'approved', 'collected', 'claimed', 'returned' => 'badge-success',
        'pending', 'matched' => 'badge-warning',
        default => 'badge-danger'
    };
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
    <link rel="stylesheet" href="/assets/css/sidebar.css">
    <link rel="stylesheet" href="/assets/css/topbar.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="/assets/js/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/sidebar.php'; ?>

    <div class="main" id="main">

        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <div class="page-header dashboard-header">
                <div>
                    <h1>Admin Dashboard</h1>
                    <p class="dashboard-subtitle"><?= htmlspecialchars($rangeLabels[$range]) ?> overview</p>
                </div>

                <form method="GET" class="range-filter" aria-label="Dashboard date range">
                    <?php foreach($rangeLabels as $value => $label): ?>
                        <button
                            type="submit"
                            name="range"
                            value="<?= htmlspecialchars($value) ?>"
                            class="<?= $range === $value ? 'active' : '' ?>">
                            <?= htmlspecialchars($label) ?>
                        </button>
                    <?php endforeach; ?>
                </form>
            </div>

            <div class="cards">
                <div class="card metric-card" title="Registered student accounts in this range.">
                    <h3>Total Students</h3>
                    <p><?= (int)$totalStudents ?></p>
                </div>

                <div class="card metric-card" title="Registered staff accounts in this range.">
                    <h3>Total Staff</h3>
                    <p><?= (int)$totalStaff ?></p>
                </div>

                <div class="card metric-card" title="Lost item reports created in this range.">
                    <h3>Lost Items</h3>
                    <p><?= (int)$totalLost ?></p>
                </div>

                <div class="card metric-card" title="Found item reports created in this range.">
                    <h3>Found Items</h3>
                    <p><?= (int)$totalFound ?></p>
                </div>

                <div class="card metric-card" title="All claims submitted in this range.">
                    <h3>Claims</h3>
                    <p><?= (int)$totalClaims ?></p>
                </div>

                <div class="card metric-card" title="All system and manual matches created in this range.">
                    <h3>Matches</h3>
                    <p><?= (int)$totalMatches ?></p>
                </div>

                <div class="card metric-card" title="Claims waiting for admin review.">
                    <h3>Pending Claims</h3>
                    <p><?= (int)$pendingClaims ?></p>
                </div>

                <div class="card metric-card" title="Claims marked as collected, including match-based and direct claims.">
                    <h3>Recovered Items</h3>
                    <p><?= (int)$collectedClaims ?></p>
                </div>

                <div class="card metric-card" title="Collected claims divided by lost item reports in this range.">
                    <h3>Recovery Rate</h3>
                    <p><?= $recoveryRate ?>%</p>
                </div>
            </div>

            <div class="dashboard-chart-grid">
                <div class="card chart-card">
                    <h2>Claims Overview</h2>
                    <div class="chart-loading">Loading chart...</div>
                    <canvas id="claimsChart"></canvas>
                </div>

                <div class="card chart-card">
                    <h2>Lost Items by Category</h2>
                    <div class="chart-loading">Loading chart...</div>
                    <canvas id="lostCategoryChart"></canvas>
                </div>

                <div class="card chart-card">
                    <h2>Found Items by Category</h2>
                    <div class="chart-loading">Loading chart...</div>
                    <canvas id="foundCategoryChart"></canvas>
                </div>

                <div class="card chart-card">
                    <h2>Monthly Activity</h2>
                    <div class="chart-loading">Loading chart...</div>
                    <canvas id="monthlyActivityChart"></canvas>
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
                                <th>Item No.</th>
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
                            <tr class="clickable-row" data-href="/admin/view-item.php?type=lost&id=<?= (int)$item['id'] ?>&from=dashboard">
                                <td><?= $count++ ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= htmlspecialchars($item['category']) ?></td>
                                <td><span class="badge <?= badgeClassForStatus($item['status']) ?>"><?= ucfirst($item['status']) ?></span></td>
                                <td><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/view-item.php?type=lost&id=<?= (int)$item['id'] ?>&from=dashboard" class="action-btn view" title="View Lost Item" aria-label="View Lost Item">
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
                                <th>Item No.</th>
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
                            <tr class="clickable-row" data-href="/admin/view-item.php?type=found&id=<?= (int)$item['id'] ?>&from=dashboard">
                                <td><?= $count++ ?></td>
                                <td><?= htmlspecialchars($item['item_name']) ?></td>
                                <td><?= htmlspecialchars($item['category']) ?></td>
                                <td><span class="badge <?= badgeClassForStatus($item['status']) ?>"><?= ucfirst($item['status']) ?></span></td>
                                <td><?= date('d M Y', strtotime($item['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/view-item.php?type=found&id=<?= (int)$item['id'] ?>&from=dashboard" class="action-btn view" title="View Found Item" aria-label="View Found Item">
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
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $count = 1; ?>
                        <?php foreach($recentClaims as $claim): ?>
                            <tr class="clickable-row" data-href="/admin/claim-details.php?id=<?= (int)$claim['id'] ?>&from=dashboard">
                                <td><?= $count++ ?></td>
                                <td><span class="badge <?= badgeClassForStatus($claim['status']) ?>"><?= ucfirst($claim['status']) ?></span></td>
                                <td><?= date('d M Y', strtotime($claim['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/claim-details.php?id=<?= (int)$claim['id'] ?>&from=dashboard" class="action-btn view" title="View Claim" aria-label="View Claim">
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
                        <h2>Claims Status Timeline</h2>
                        <a href="/admin/claims.php" class="view-all-link">View All</a>
                    </div>

                    <?php if(empty($processedClaims)): ?>
                        <p>No processed claims in this range.</p>
                    <?php else: ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Claim No.</th>
                                <th>Status</th>
                                <th>Processed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach($processedClaims as $claim): ?>
                            <tr class="clickable-row" data-href="/admin/claim-details.php?id=<?= (int)$claim['id'] ?>&from=dashboard">
                                <td>#<?= (int)$claim['id'] ?></td>
                                <td><span class="badge <?= badgeClassForStatus($claim['status']) ?>"><?= ucfirst($claim['status']) ?></span></td>
                                <td><?= date('d M Y H:i', strtotime($claim['created_at'])) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="/admin/claim-details.php?id=<?= (int)$claim['id'] ?>&from=dashboard" class="action-btn view" title="View Claim" aria-label="View Claim">
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
const chartTextPlugin = {
    id: 'chartTextPlugin',
    afterDatasetsDraw(chart) {
        const {ctx} = chart;
        ctx.save();
        ctx.font = '600 12px Poppins, sans-serif';
        ctx.fillStyle = '#334155';
        ctx.textAlign = 'center';

        chart.data.datasets.forEach((dataset, datasetIndex) => {
            const meta = chart.getDatasetMeta(datasetIndex);
            if(chart.config.type !== 'bar') {
                return;
            }

            meta.data.forEach((bar, index) => {
                const value = dataset.data[index];
                if(Number(value) <= 0) {
                    return;
                }
                ctx.fillText(value, bar.x, bar.y - 6);
            });
        });
        ctx.restore();
    }
};

Chart.register(chartTextPlugin);

document.querySelectorAll('.chart-loading').forEach(function(loader){
    loader.style.display = 'none';
});

function barGradient(ctx, colorStart, colorEnd) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 260);
    gradient.addColorStop(0, colorStart);
    gradient.addColorStop(1, colorEnd);
    return gradient;
}

const commonBarOptions = {
    responsive: true,
    maintainAspectRatio: false,
    animation: {
        duration: 900,
        easing: 'easeOutQuart'
    },
    plugins: {
        legend: {
            display: false
        },
        tooltip: {
            enabled: true
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'Number of Items'
            },
            ticks: {
                precision: 0
            }
        },
        x: {
            ticks: {
                maxRotation: 0,
                autoSkip: false
            }
        }
    }
};

const claimsCtx = document.getElementById('claimsChart').getContext('2d');
new Chart(claimsCtx, {
    type: 'doughnut',
    data: {
        labels: ['Approved', 'Pending', 'Rejected', 'Collected'],
        datasets: [{
            data: [
                <?= (int)$approvedClaims ?>,
                <?= (int)$pendingClaims ?>,
                <?= (int)$rejectedClaims ?>,
                <?= (int)$collectedClaims ?>
            ],
            backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#2563eb'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        animation: {
            duration: 900
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

const lostCategoryCtx = document.getElementById('lostCategoryChart').getContext('2d');
new Chart(lostCategoryCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($lostCategoryLabels) ?>,
        datasets: [{
            label: 'Lost Items',
            data: <?= json_encode($lostCategoryTotals) ?>,
            backgroundColor: barGradient(lostCategoryCtx, '#ef4444', '#fecaca'),
            borderRadius: 8
        }]
    },
    options: commonBarOptions
});

const foundCategoryCtx = document.getElementById('foundCategoryChart').getContext('2d');
new Chart(foundCategoryCtx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($foundCategoryLabels) ?>,
        datasets: [{
            label: 'Found Items',
            data: <?= json_encode($foundCategoryTotals) ?>,
            backgroundColor: barGradient(foundCategoryCtx, '#16a34a', '#bbf7d0'),
            borderRadius: 8
        }]
    },
    options: commonBarOptions
});

const monthlyActivityCtx = document.getElementById('monthlyActivityChart').getContext('2d');
new Chart(monthlyActivityCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode($monthlyLabels) ?>,
        datasets: [
            {
                label: 'Lost Items',
                data: <?= json_encode($monthlyLostTotals) ?>,
                borderColor: '#ef4444',
                backgroundColor: 'rgba(239,68,68,.12)',
                tension: .35,
                fill: true
            },
            {
                label: 'Found Items',
                data: <?= json_encode($monthlyFoundTotals) ?>,
                borderColor: '#16a34a',
                backgroundColor: 'rgba(22,163,74,.12)',
                tension: .35,
                fill: true
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 900,
            easing: 'easeOutQuart'
        },
        plugins: {
            legend: {
                position: 'bottom'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Number of Items'
                },
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

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
