<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: claims.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/ClaimController.php';

$controller = new ClaimController();
$claim = $controller->show((int)$_GET['id']);

if (!$claim || $claim['user_id'] != $_SESSION['user_id']) {
    header("Location: claims.php?error=" . urlencode("Claim not found."));
    exit;
}

switch ($claim['status']) {
    case 'approved':
        $badge = 'badge-success';
        break;
    case 'rejected':
        $badge = 'badge-danger';
        break;
    default:
        $badge = 'badge-warning';
        break;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Claim Details</title>
<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<div class="user-layout">
<?php include __DIR__ . '/../components/user-sidebar.php'; ?>
<div class="main" id="main">
<?php include __DIR__ . '/../components/topbar-user.php'; ?>
<div class="content">
<h1>Claim Details</h1>

<div class="card">

<div class="form-group">
<label>Claim ID</label>
<p><?= htmlspecialchars($claim['id']) ?></p>
</div>

<div class="form-group">
<label>Lost Item</label>
<p><?= htmlspecialchars($claim['lost_item']) ?></p>
</div>

<div class="form-group">
<label>Found Item</label>
<p><?= htmlspecialchars($claim['found_item']) ?></p>
</div>

<div class="form-group">
<label>Claim Message</label>
<p><?= nl2br(htmlspecialchars($claim['claim_message'])) ?></p>
</div>

<div class="form-group">
<label>Status</label>
<span class="badge <?= $badge ?>"><?= ucfirst($claim['status']) ?></span>
</div>

<div class="form-group">
<label>Date Submitted</label>
<p><?= date('d M Y H:i', strtotime($claim['created_at'])) ?></p>
</div>

<hr style="margin:25px 0;">

<a href="claims.php" class="action-btn">
<i class="fas fa-arrow-left"></i> Back to My Claims
</a>

</div>

</div>
</div>
</div>

<script src="/assets/js/sidebar.js"></script>

</body>
</html>
        