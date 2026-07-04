<?php
/* Skeleton redesigned admin/items.php
 * Replace model calls with your LostItem/FoundItem models as you implement them.
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

$search = trim($_GET['search'] ?? '');
$type = $_GET['type'] ?? 'all';
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'desc';

$order = $sort === 'asc' ? 'ASC' : 'DESC';

$sql = "
SELECT
    li.id,
    'Lost' AS item_type,
    li.item_name,
    li.category,
    li.status,
    li.created_at,
    u.fullname
FROM lost_items li
JOIN users u ON li.user_id=u.id

UNION ALL

SELECT
    fi.id,
    'Found' AS item_type,
    fi.item_name,
    fi.category,
    fi.status,
    fi.created_at,
    u.fullname
FROM found_items fi
JOIN users u ON fi.user_id=u.id
";

$items = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

if ($search !== '') {
    $items = array_filter($items, fn($i)=>stripos($i['item_name'],$search)!==false);
}
if ($type !== 'all') {
    $items = array_filter($items, fn($i)=>strtolower($i['item_type'])==$type);
}
if ($status !== 'all') {
    $items = array_filter($items, fn($i)=>$i['status']==$status);
}

$total=count($items);
$lost=count(array_filter($items,fn($i)=>$i['item_type']=="Lost"));
$found=count(array_filter($items,fn($i)=>$i['item_type']=="Found"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Items</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="/assets/css/items.css">

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="admin-layout">

<?php include __DIR__.'/../components/sidebar.php'; ?>

<div class="main" id="main">

<?php include __DIR__.'/../components/topbar.php'; ?>

<div class="content">

<h1>Manage Items</h1>

<div class="stats-grid">
<div class="stat-card"><h3>Total</h3><p><?= $total ?></p></div>
<div class="stat-card"><h3>Lost</h3><p><?= $lost ?></p></div>
<div class="stat-card"><h3>Found</h3><p><?= $found ?></p></div>
</div>

<form class="filters" method="GET">

<input type="text" name="search" placeholder="Search item..." value="<?= htmlspecialchars($search) ?>">

<select name="type">
<option value="all">All Types</option>
<option value="lost" <?= $type=='lost'?'selected':'' ?>>Lost</option>
<option value="found" <?= $type=='found'?'selected':'' ?>>Found</option>
</select>

<select name="status">
<option value="all">All Status</option>
<option value="pending">Pending</option>
<option value="matched">Matched</option>
<option value="claimed">Claimed</option>
</select>

<select name="sort">
<option value="desc">Newest</option>
<option value="asc">Oldest</option>
</select>

<button class="action-btn">Apply</button>

</form>

<div class="card">

<table class="table">

<thead>
<tr>
<th>Item No.</th>
<th>Type</th>
<th>Item</th>
<th>Category</th>
<th>Reported By</th>
<th>Status</th>
<th>Date</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php $i=1; foreach($items as $item): ?>

<tr>

<td><?= $i++ ?></td>

<td><?= $item['item_type'] ?></td>

<td><?= htmlspecialchars($item['item_name']) ?></td>

<td><?= htmlspecialchars($item['category']) ?></td>

<td><?= htmlspecialchars($item['fullname']) ?></td>

<td>
<span class="badge <?= strtolower($item['status']) ?>">
<?= ucfirst($item['status']) ?>
</span>
</td>

<td><?= date('d M Y',strtotime($item['created_at'])) ?></td>

<td>

<a class="action-btn view-btn"
href="view-item.php?type=<?= strtolower($item['item_type']) ?>&id=<?= $item['id'] ?>">
  <i class="fas fa-eye"></i>
View
</a>

<a class="action-btn edit-btn"
href="edit-item.php?type=<?= strtolower($item['item_type']) ?>&id=<?= $item['id'] ?>">
  <i class="fas fa-edit"></i>
Edit
</a>

<a class="action-btn delete-btn"
onclick="return confirm('Delete this item?')"
href="delete-item.php?type=<?= strtolower($item['item_type']) ?>&id=<?= $item['id'] ?>">
  <i class="fas fa-trash"></i>
Delete
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

<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/items.js"></script>

</body>
</html>
