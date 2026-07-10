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

$currentAdminId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    if (!csrf_validate($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = 'Security token expired. Please try again.';
        header('Location: users.php');
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');

    $existingUser = $id > 0 ? $userModel->getUserById($id) : null;

    if (!$existingUser) {
        $_SESSION['error'] = 'User not found.';
    } elseif ($fullname === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Please provide a valid name and email address.';
    } else {
        if ($id === $currentAdminId) {
            $role = $existingUser['role'];
        }

        if ($userModel->updateUser($id, $fullname, $email, $role)) {
            $_SESSION['success'] = 'User updated successfully.';
        } else {
            $_SESSION['error'] = 'Unable to update user. The email may already be in use.';
        }
    }

    $redirect = 'users.php';
    if (trim($_GET['search'] ?? '') !== '') {
        $redirect .= '?search=' . urlencode(trim($_GET['search']));
    }

    header("Location: {$redirect}");
    exit;
}

$search = trim($_GET['search'] ?? '');

$users = $search !== ''
    ? $userModel->searchUsers($search)
    : $userModel->getAllUsers();

$totalUsers = count($users);

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

<div class="main" id="main">

<?php include __DIR__.'/../components/topbar.php'; ?>

<div class="content">

<h1>Manage Users</h1>

<?php if(isset($_SESSION['success'])): ?>
<div class="success">
<?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="error">
<?= htmlspecialchars($_SESSION['error']) ?>
</div>
<?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="cards users-summary">
<div class="card">
<h3>Total Users</h3>
<p><?= $totalUsers ?></p>
</div>
</div>

<div class="toolbar">

<form class="search-form" id="userSearchForm" method="GET">

<div class="search-box">

<i class="fas fa-search search-icon"></i>

<input
type="text"
id="userSearchInput"
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

<div class="search-meta" id="userSearchMeta">
<i class="fas fa-users"></i>
<strong><?= $totalUsers ?></strong> Users
</div>

</div>

<div class="table-card" data-current-user-id="<?= $currentAdminId ?>">

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

<tr class="user-row"
data-search="<?= htmlspecialchars(strtolower($user['fullname'].' '.$user['email'].' '.$user['role']), ENT_QUOTES) ?>">

<td class="user-row-number"><?= $i+1 ?></td>
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
class="action-btn edit-btn editUser"
data-id="<?= $user['id'] ?>"
data-fullname="<?= htmlspecialchars($user['fullname'], ENT_QUOTES) ?>"
data-email="<?= htmlspecialchars($user['email'], ENT_QUOTES) ?>"
data-role="<?= htmlspecialchars($user['role'], ENT_QUOTES) ?>"
title="Edit user">
<i class="fas fa-edit"></i>
</a>

</div>

</td>

</tr>

<?php endforeach; ?>

<?php if(!$users): ?>
<tr id="noUsersRow">
<td colspan="6">No users found.</td>
</tr>
<?php else: ?>
<tr id="noUsersRow" style="display:none;">
<td colspan="6">No users found.</td>
</tr>
<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

<div class="modal" id="editModal">
<div class="modal-content">

<h2>Edit User</h2>

<form method="POST">
<?= csrf_field() ?>

<input type="hidden" name="id" id="id">

<div class="form-group">
<label for="fullname">Full Name</label>
<input type="text" name="fullname" id="fullname" required>
</div>

<div class="form-group">
<label for="email">Email</label>
<input type="email" name="email" id="email" required>
</div>

<div class="form-group">
<label for="role">Role</label>
<select name="role" id="role" required>
<option value="student">Student</option>
<option value="staff">Staff</option>
<option value="admin">Admin</option>
</select>
</div>

<div class="modal-buttons">
<button type="button" class="search-btn" onclick="closeModal()">Cancel</button>
<button type="submit" name="update_user" class="search-btn">
<i class="fas fa-save"></i>
Save
</button>
</div>

</form>

</div>
</div>

<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/users.js"></script>

</body>
</html>
