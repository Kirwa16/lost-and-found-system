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

$db = new Database();
$conn = $db->getConnection();

/* ---------------- Filters ---------------- */

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

$sql = "SELECT fi.*, u.fullname
        FROM found_items fi
        JOIN users u ON fi.user_id=u.id";

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

/* ---------------- Category Chart ---------------- */

$cat = $conn->query("
SELECT category, COUNT(*) total
FROM found_items
GROUP BY category
ORDER BY total DESC
");

$categoryLabels = [];
$categoryData = [];

while($row = $cat->fetch(PDO::FETCH_ASSOC)){
    $categoryLabels[] = $row['category'];
    $categoryData[] = (int)$row['total'];
}

/* ---------------- Monthly Chart ---------------- */

$months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
$monthData = array_fill(0,12,0);

$m = $conn->query("
SELECT MONTH(created_at) month_no,
COUNT(*) total
FROM found_items
GROUP BY MONTH(created_at)
");

while($row = $m->fetch(PDO::FETCH_ASSOC)){
    $monthData[$row['month_no']-1] = (int)$row['total'];
}

/* ---------------- Categories ---------------- */

$categories = $conn->query("SELECT DISTINCT category FROM found_items ORDER BY category")
                   ->fetchAll(PDO::FETCH_COLUMN);
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

<div class="main" id="main">

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
<?php foreach(['pending','matched','claimed'] as $s): ?>
<option value="<?= $s ?>" <?= $status==$s?'selected':'' ?>><?= ucfirst($s) ?></option>
<?php endforeach; ?>
</select>

<select name="category">
<option value="all">All Categories</option>
<?php foreach($categories as $cat): ?>
<option value="<?= htmlspecialchars($cat) ?>" <?= $category==$cat?'selected':'' ?>>
<?= htmlspecialchars($cat) ?>
</option>
<?php endforeach; ?>
</select>

<button type="submit">Apply</button>

</form>

<div class="dashboard-chart-grid">

<div class="card" style="height:420px">
<h2>Found Reports by Category</h2>
<canvas id="categoryChart"></canvas>
</div>

<div class="card" style="height:420px">
<h2>Monthly Trend</h2>
<canvas id="monthlyChart"></canvas>
</div>

</div>

<div class="card">
<h2>Found Reports History</h2>

<table class="table">
<thead>
<tr>
<th>#</th>
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
<td><?= htmlspecialchars($report['location_found']) ?></td>
<td><?= htmlspecialchars($report['date_found']) ?></td>
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

<script src="/assets/js/sidebar.js"></script>

<script>
new Chart(document.getElementById('categoryChart'),{
type:'pie',
data:{
labels:<?= json_encode($categoryLabels) ?>,
datasets:[{
data:<?= json_encode($categoryData) ?>
}]
},
options:{responsive:true,maintainAspectRatio:false}
});

new Chart(document.getElementById('monthlyChart'),{
type:'line',
data:{
labels:<?= json_encode($months) ?>,
datasets:[{
label:'Found Reports',
data:<?= json_encode($monthData) ?>,
fill:false,
tension:.35
}]


},
options:{responsive:true,maintainAspectRatio:false}
});
</script>

</body>
</html>
