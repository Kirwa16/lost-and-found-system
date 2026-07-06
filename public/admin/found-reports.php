<?php
/**
 * admin/found-reports.php
 * Starter analytics page for found item reports.
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

$search   = trim($_GET['search'] ?? '');
$status   = $_GET['status'] ?? 'all';
$category = $_GET['category'] ?? 'all';

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "fi.item_name LIKE :search";
    $params[':search'] = "%{$search}%";
}

if ($status !== 'all') {
    $where[] = "fi.status = :status";
    $params[':status'] = $status;
}

if ($category !== 'all') {
    $where[] = "fi.category = :category";
    $params[':category'] = $category;
}

$sql = "
SELECT
    fi.*,
    u.fullname
FROM found_items fi
JOIN users u
ON fi.user_id = u.id
";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY fi.created_at DESC";

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
<title>Found Reports</title>

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

<h1>Found Reports</h1>

<div class="stats-grid">
<div class="stat-card"><h3>Total Reports</h3><p><?= $totalReports ?></p></div>
<div class="stat-card"><h3>Pending</h3><p><?= $pending ?></p></div>
<div class="stat-card"><h3>Matched</h3><p><?= $matched ?></p></div>
<div class="stat-card"><h3>Claimed</h3><p><?= $claimed ?></p></div>
</div>

<form method="GET" class="filters">

<input type="text" name="search" placeholder="Search item..."
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
<h2>Found Reports by Category</h2>
<canvas id="categoryChart"></canvas>
</div>

<div class="card">
<h2>Monthly Trend</h2>
<canvas id="monthlyChart"></canvas>
</div>

</div>

<div class="card">

<h2>Found Reports History</h2>

<table class="table">
<thead>
<tr>
<th>Item No.</th>
<th>Item</th>
<th>Category</th>
<th>Reported By</th>
<th>Location</th>
<th>Date Found</th>
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
<td><?= htmlspecialchars($report['location_found'] ?? '') ?></td>
<td><?= htmlspecialchars($report['date_found'] ?? '') ?></td>
<td><?= ucfirst($report['status']) ?></td>
<td><?= date('d M Y',strtotime($report['created_at'])) ?></td>
<td><a href="view-item.php?type=found&id=<?= $report['id'] ?>">View</a></td>
</tr>
<?php endforeach; ?>

</tbody>
</table>

</div>

</div>

</div>

</div>

<script>
new Chart(document.getElementById('categoryChart'),{
type:'pie',
data:{labels:['Category'],datasets:[{data:[1]}]}
});

new Chart(document.getElementById('monthlyChart'),{
type:'line',
data:{labels:['Jan'],datasets:[{label:'Reports',data:[1]}]}
});
</script>

</body>
</html>
