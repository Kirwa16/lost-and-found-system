<?php
/**
 * Improved users.php
 *
 * NOTE:
 * This is a production-ready starter preserving the structure of your current
 * page while modernizing the toolbar. Copy your existing delete/update logic
 * from your current file into the marked section if needed.
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
require_once __DIR__ . '/../../backend/models/User.php';
require_once __DIR__ . '/../../backend/helpers/csrf.php';

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

$search = trim($_GET['search'] ?? '');

$users = $search !== ''
    ? $userModel->searchUsers($search)
    : $userModel->getAllUsers();

$totalUsers = count($users);

/* Keep your existing update/delete handlers here */

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage Users</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="/assets/css/users.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>

<div class="admin-layout">

<?php include __DIR__.'/../components/sidebar.php'; ?>

<div class="main">

<?php include __DIR__.'/../components/topbar.php'; ?>

<div class="content">

<h1>Manage Users</h1>

<div class="cards users-summary">
<div class="card">
<h3>Total Users</h3>
<p><?= $totalUsers ?></p>
</div>
</div>

<div class="toolbar">

<form class="search-form" method="GET">

<div class="search-box">

<i class="fas fa-search search-icon"></i>

<input
type="text"
name="search"
placeholder="Search by name, email or role..."
value="<?= htmlspecialchars($search) ?>">

<?php if($search): ?>
<a href="users.php" class="clear-search">
<i class="fas fa-times"></i>
</a>
<?php endif; ?>

</div>

<button class="search-btn" type="submit">
<i class="fas fa-search"></i>
Search
</button>

</form>

<div class="search-meta">
<i class="fas fa-users"></i>
<strong><?= $totalUsers ?></strong> Users
</div>

</div>

<div class="table-card">

<table class="table">

<thead>
<tr>
<th>User No.</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Joined</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php foreach($users as $i=>$user): ?>

<tr>

<td><?= $i+1 ?></td>
<td><?= htmlspecialchars($user['fullname']) ?></td>
<td><?= htmlspecialchars($user['email']) ?></td>

<td>
<span class="badge <?= strtolower($user['role']) ?>">
<?= ucfirst($user['role']) ?>
</span>
</td>

<td><?= date('d M Y',strtotime($user['created_at'])) ?></td>

<td>

<div class="table-actions">

<a href="#"
class="action-btn editUser"
data-id="<?= $user['id'] ?>"
data-fullname="<?= htmlspecialchars($user['fullname']) ?>"
data-email="<?= htmlspecialchars($user['email']) ?>"
data-role="<?= $user['role'] ?>">
<i class="fas fa-edit"></i>
</a>

</div>

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
<script src="/assets/js/users.js"></script>

</body>
</html>
