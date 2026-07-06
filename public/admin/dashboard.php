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

function countValue(PDO $conn, $sql){
    return (int)$conn->query($sql)->fetchColumn();
}

$totalStudents = countValue($conn,"SELECT COUNT(*) FROM users WHERE role='student'");
$totalStaff    = countValue($conn,"SELECT COUNT(*) FROM users WHERE role='staff'");
$totalLost     = countValue($conn,"SELECT COUNT(*) FROM lost_items");
$totalFound    = countValue($conn,"SELECT COUNT(*) FROM found_items");
$totalClaims   = countValue($conn,"SELECT COUNT(*) FROM claims");
$totalMatches  = countValue($conn,"SELECT COUNT(*) FROM matches");
$pendingClaims = countValue($conn,"SELECT COUNT(*) FROM claims WHERE status='pending'");
$claimedLost   = countValue($conn,"SELECT COUNT(*) FROM lost_items WHERE status='claimed'");

$recoveryRate = $totalLost>0 ? round(($claimedLost/$totalLost)*100,1) : 0;

$activity = [];

$q=$conn->query("SELECT item_name,'Lost Report' action,created_at FROM lost_items ORDER BY created_at DESC LIMIT 5");
$activity=array_merge($activity,$q->fetchAll(PDO::FETCH_ASSOC));

$q=$conn->query("SELECT item_name,'Found Report' action,created_at FROM found_items ORDER BY created_at DESC LIMIT 5");
$activity=array_merge($activity,$q->fetchAll(PDO::FETCH_ASSOC));

usort($activity,function($a,$b){
    return strtotime($b['created_at'])<=>strtotime($a['created_at']);
});
$activity=array_slice($activity,0,8);

$lostCats=$conn->query("SELECT category,COUNT(*) total FROM lost_items GROUP BY category")->fetchAll(PDO::FETCH_ASSOC);
$foundCats=$conn->query("SELECT category,COUNT(*) total FROM found_items GROUP BY category")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Dashboard</title>


<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
.quick-actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:15px;margin:25px 0}
.quick-actions a{background:#64748b;color:#fff;text-decoration:none;padding:18px;border-radius:10px;text-align:center;font-weight:600}
.activity{background:#fff;padding:20px;border-radius:12px;margin-top:25px}
.activity ul{list-style:none;padding:0}
.activity li{padding:12px 0;border-bottom:1px solid #eee}
.chart-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(400px,1fr));gap:20px;margin:25px 0}
.chart-card{background:#fff;padding:20px;border-radius:12px}
.chart-card canvas{height:320px!important}
</style>
</head>
<body>
<div class="admin-layout">
<?php include __DIR__.'/../components/sidebar.php'; ?>
<div class="main">
<?php include __DIR__.'/../components/topbar.php'; ?>
<div class="content">

<h1>Dashboard</h1>

<div class="cards">
<div class="card"><h3>Students</h3><p><?=$totalStudents?></p></div>
<div class="card"><h3>Staff</h3><p><?=$totalStaff?></p></div>
<div class="card"><h3>Lost Items</h3><p><?=$totalLost?></p></div>
<div class="card"><h3>Found Items</h3><p><?=$totalFound?></p></div>
<div class="card"><h3>Claims</h3><p><?=$totalClaims?></p></div>
<div class="card"><h3>Matches</h3><p><?=$totalMatches?></p></div>
<div class="card"><h3>Pending Claims</h3><p><?=$pendingClaims?></p></div>
<div class="card"><h3>Recovery Rate</h3><p><?=$recoveryRate?>%</p></div>
</div>

<div class="chart-grid">
<div class="chart-card">
<h2>Lost Categories</h2>
<canvas id="lostChart"></canvas>
</div>
<div class="chart-card">
<h2>Found Categories</h2>
<canvas id="foundChart"></canvas>
</div>
</div>

<h2>Quick Actions</h2>
<div class="quick-actions">
<a href="/admin/items.php">Manage Items</a>
<a href="/admin/matches.php">Manage Matches</a>
<a href="/admin/claims.php">Review Claims</a>
<a href="/admin/generated-reports.php">Generated Reports</a>
</div>

<div class="activity">
<h2>Recent Activity</h2>
<ul>
<?php if(empty($activity)): ?>
<li>No recent activity.</li>
<?php else: foreach($activity as $row): ?>
<li><strong><?=htmlspecialchars($row['action'])?>:</strong> <?=htmlspecialchars($row['item_name'])?> <small>(<?=date('d M Y H:i',strtotime($row['created_at']))?>)</small></li>
<?php endforeach; endif; ?>
</ul>
</div>

</div>
</div>
</div>

<script>
new Chart(document.getElementById('lostChart'),{
type:'bar',
data:{
labels:<?=json_encode(array_column($lostCats,'category'))?>,
datasets:[{label:'Lost',data:<?=json_encode(array_map('intval',array_column($lostCats,'total')))?>}]
}
});

new Chart(document.getElementById('foundChart'),{
type:'bar',
data:{
labels:<?=json_encode(array_column($foundCats,'category'))?>,
datasets:[{label:'Found',data:<?=json_encode(array_map('intval',array_column($foundCats,'total')))?>}]
}
});
</script>

<script src="/assets/js/sidebar.js"></script>
</body>
</html>
