<?php
/**
 * admin/lost-reports.php
 *
 * Starter template generated for the Lost & Found Management System.
 * This page is intended to mirror the admin theme and provide:
 * - Summary cards
 * - Search and filters
 * - Category & monthly charts
 * - Export (CSV)
 * - Lost reports history
 *
 * NOTE:
 * This is a starter scaffold. Integrate with your existing dashboard
 * helper functions and styling as needed.
 */

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

$db = new Database();
$conn = $db->getConnection();

/* ---------------------------------------------------------
   Filters
--------------------------------------------------------- */

$search   = trim($_GET['search'] ?? '');
$status   = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 'all';

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "li.item_name LIKE :search";
    $params[':search'] = "%{$search}%";
}

if ($status !== 'all') {
    $where[] = "li.status = :status";
    $params[':status'] = $status;
}

if ($category !== 'all') {
    $where[] = "li.category = :category";
    $params[':category'] = $category;
}

$sql = "
SELECT
    li.*,
    u.fullname
FROM lost_items li
JOIN users u
ON li.user_id = u.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY li.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalReports = count($reports);
$pending = count(array_filter($reports, fn($r)=>$r['status']=='pending'));
$matched = count(array_filter($reports, fn($r)=>$r['status']=='matched'));
$claimed = count(array_filter($reports, fn($r)=>$r['status']=='claimed'));

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lost Reports</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="/assets/css/reports.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>

<div class="admin-layout">

<?php include __DIR__.'/../components/sidebar.php'; ?>

<div class="main">

<?php include __DIR__.'/../components/topbar.php'; ?>

<div class="content">

<h1>Lost Reports</h1>

<div class="stats-grid">

<div class="stat-card">
<h3>Total Reports</h3>
<p><?= $totalReports ?></p>
</div>

<div class="stat-card">
<h3>Pending</h3>
<p><?= $pending ?></p>
</div>

<div class="stat-card">
<h3>Matched</h3>
<p><?= $matched ?></p>
</div>

<div class="stat-card">
<h3>Claimed</h3>
<p><?= $claimed ?></p>
</div>

</div>

<form method="GET" class="filters">

<input
type="text"
name="search"
placeholder="Search item..."
value="<?= htmlspecialchars($search) ?>">

<select name="status">
<option value="all">All Status</option>
<option value="pending">Pending</option>
<option value="matched">Matched</option>
<option value="claimed">Claimed</option>
</select>

<button type="submit">Apply</button>

</form>

<div class="dashboard-chart-grid">

<div class="card">
<h2>Lost Reports by Category</h2>
<canvas id="categoryChart"></canvas>
</div>

<div class="card">
<h2>Monthly Trend</h2>
<canvas id="monthlyChart"></canvas>
</div>

</div>

<div class="card">

<h2>Lost Reports History</h2>

<table class="table">

<thead>

<tr>
<th>Item No.</th>
<th>Item</th>
<th>Category</th>
<th>Reported By</th>
<th>Location</th>
<th>Date Lost</th>
<th>Status</th>
<th>Reported On</th>
<th>Action</th>
</tr>

</thead>

<tbody>

<?php foreach($reports as $i=>$report): ?>

<tr>

<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($report['item_name']) ?></td>
<td><?= htmlspecialchars($report['category']) ?></td>
<td><?= htmlspecialchars($report['fullname']) ?></td>
<td><?= htmlspecialchars($report['location_lost']) ?></td>
<td><?= htmlspecialchars($report['date_lost']) ?></td>
<td><?= ucfirst($report['status']) ?></td>
<td><?= date('d M Y',strtotime($report['created_at'])) ?></td>

<td>
<a href="view-item.php?type=lost&id=<?= $report['id'] ?>">
View
</a>
</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<script>
// Chart placeholders.
// Replace with dynamic Chart.js datasets as desired.
new Chart(document.getElementById('categoryChart'),{
type:'pie',
data:{
labels:['Category'],
datasets:[{data:[1]}]
}
});

new Chart(document.getElementById('monthlyChart'),{
type:'line',
data:{
labels:['Jan'],
datasets:[{label:'Reports',data:[1]}]
}
});
</script>

</body>
</html>
