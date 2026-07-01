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

require_once __DIR__ . '/../../backend/controllers/SettingsController.php';

$controller = new SettingsController();

$message = "";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $controller->update($_POST);

    if ($result['success']) {
        $message = $result['message'];
    } else {
        $errors = $result['errors'];
    }
}

$settings = $controller->index();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>System Settings</title>

<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="admin-layout">

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<div class="main" id="main">

<?php include __DIR__ . '/../components/topbar.php'; ?>

<div class="content">

<h1>System Settings</h1>

<?php if($message): ?>
<div class="success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if(!empty($errors)): ?>
<div class="error">
<ul>
<?php foreach($errors as $error): ?>
<li><?= htmlspecialchars($error) ?></li>
<?php endforeach; ?>
</ul>
</div>
<?php endif; ?>

<form method="POST">

<div class="settings-container">

<div class="settings-section">
<h2><i class="fas fa-server"></i> System Information</h2>

<div class="form-group">
<label>System Name</label>
<input type="text" name="system_name"
value="<?= htmlspecialchars($settings['system_name'] ?? '') ?>">
</div>

<div class="form-group">
<label>Institution Name</label>
<input type="text" name="institution_name"
value="<?= htmlspecialchars($settings['institution_name'] ?? '') ?>">
</div>

</div>

<div class="settings-section">
<h2><i class="fas fa-bell"></i> Notifications</h2>

<div class="switch">
<input type="checkbox" id="email_notifications"
name="email_notifications"
<?= (($settings['email_notifications'] ?? '0')=='1')?'checked':''; ?>>
<label for="email_notifications">Enable Email Notifications</label>
</div>

<div class="switch">
<input type="checkbox" id="claim_notifications"
name="claim_notifications"
<?= (($settings['claim_notifications'] ?? '0')=='1')?'checked':''; ?>>
<label for="claim_notifications">Enable Claim Notifications</label>
</div>

</div>

<div class="settings-section">
<h2><i class="fas fa-shield-alt"></i> Security</h2>

<div class="form-group">
<label>Minimum Password Length</label>
<select name="minimum_password_length">
<?php foreach([6,8,10,12] as $len): ?>
<option value="<?= $len ?>"
<?= (($settings['minimum_password_length'] ?? '8') == (string)$len)?'selected':''; ?>>
<?= $len ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="form-group">
<label>Session Timeout (Minutes)</label>
<input type="number" min="5"
name="session_timeout"
value="<?= htmlspecialchars($settings['session_timeout'] ?? '30') ?>">
</div>

</div>

<div class="settings-section">
<h2><i class="fas fa-palette"></i> Appearance</h2>

<div class="form-group">
<label>Theme</label>
<select name="theme">
<option value="light"
<?= (($settings['theme'] ?? 'light')=='light')?'selected':''; ?>>
Light
</option>

<option value="dark"
<?= (($settings['theme'] ?? 'light')=='dark')?'selected':''; ?>>
Dark
</option>
</select>
</div>

</div>

<button type="submit" class="save-btn">
<i class="fas fa-save"></i> Save Changes
</button>

</div>

</form>

</div>
</div>
</div>

<script src="/assets/js/sidebar.js"></script>

</body>
</html>
