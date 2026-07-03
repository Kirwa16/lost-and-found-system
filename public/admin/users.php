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
require_once __DIR__ . '/../../backend/models/User.php';

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

/* Search */
$search = trim($_GET['search'] ?? '');
$users = $search !== '' ? $userModel->searchUsers($search) : $userModel->getAllUsers();

/* Delete */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    if ($id == $_SESSION['user_id']) {
        $_SESSION['error'] = "You cannot delete your own account.";
    } elseif ($userModel->deleteUser($id)) {
        $_SESSION['success'] = "User deleted successfully.";
    } else {
        $_SESSION['error'] = "Unable to delete user.";
    }

    header("Location: users.php");
    exit;
}

/* Update */
if (isset($_POST['update_user'])) {

    $id = (int)$_POST['id'];
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $role = $_POST['role'];

    if ($id == $_SESSION['user_id']) {
        $current = $userModel->getUserById($id);
        $role = $current['role'];
    }

    if ($userModel->updateUser($id, $fullname, $email, $role)) {
        $_SESSION['success'] = "User updated successfully.";
    } else {
        $_SESSION['error'] = "Email already exists.";
    }

    header("Location: users.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<div class="main" id="main">

<?php include __DIR__ . '/../components/topbar.php'; ?>

<div class="content">

<h1>Manage Users</h1>

<?php if(isset($_SESSION['success'])): ?>
<div class="success"><?= htmlspecialchars($_SESSION['success']) ?></div>
<?php unset($_SESSION['success']); endif; ?>

<?php if(isset($_SESSION['error'])): ?>
<div class="error"><?= htmlspecialchars($_SESSION['error']) ?></div>
<?php unset($_SESSION['error']); endif; ?>

<div class="toolbar">
<form class="search-form" method="GET">
<input type="text" name="search" placeholder="Search users..." value="<?= htmlspecialchars($search) ?>">
<button class="action-btn"><i class="fas fa-search"></i> Search</button>
</form>
</div>

<div class="table-card">

<table class="table">

<thead>
<tr>
<th>User No</th>
<th>Name</th>
<th>Email</th>
<th>Role</th>
<th>Joined</th>
<th>Actions</th>
</tr>
</thead>

<tbody>
<?php $i=1; foreach($users as $user): ?>
<tr>
<td><?= $i++ ?></td>
<td><?= htmlspecialchars($user['fullname']) ?></td>
<td><?= htmlspecialchars($user['email']) ?></td>
<td><?= ucfirst($user['role']) ?></td>
<td><?= date('d M Y',strtotime($user['created_at'])) ?></td>
<td>

<div class="actions">
<a href="#" class="action-btn editUser"
data-id="<?= $user['id'] ?>"
data-fullname="<?= htmlspecialchars($user['fullname']) ?>"
data-email="<?= htmlspecialchars($user['email']) ?>"
data-role="<?= $user['role'] ?>">
<i class="fas fa-edit"></i> Edit
</a>

<?php if($user['id'] != $_SESSION['user_id']): ?>
<a class="action-btn delete-btn"
onclick="return confirm('Delete this user?')"
href="users.php?delete=<?= $user['id'] ?>">
<i class="fas fa-trash"></i> Delete
</a>
<?php endif; ?>
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

<div class="modal" id="editModal">
<div class="modal-content">
<h2>Edit User</h2>

<form method="POST">

<input type="hidden" name="id" id="id">

<div class="form-group">
<label>Full Name</label>
<input type="text" id="fullname" name="fullname" required>
</div>

<div class="form-group">
<label>Email</label>
<input type="email" id="email" name="email" required>
</div>

<div class="form-group">
<label>Role</label>
<select id="role" name="role">
<option value="user">User</option>
<option value="admin">Administrator</option>
</select>
</div>

<div class="modal-buttons">
<button type="button" onclick="closeModal()">Cancel</button>
<button class="action-btn" name="update_user">Save Changes</button>
</div>

</form>

</div>
</div>

<script src="/assets/js/sidebar.js"></script>
<script src="/assets/js/users.js"></script>

</script>

</body>
</html>
