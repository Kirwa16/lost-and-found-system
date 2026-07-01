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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: claims.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/ClaimController.php';

$controller = new ClaimController();
$claim = $controller->show((int)$_GET['id']);

if (!$claim) {
    header("Location: claims.php");
    exit;
}

switch ($claim['status']) {
    case 'approved':
        $badge='badge-success';
        break;
    case 'rejected':
        $badge='badge-danger';
        break;
    default:
        $badge='badge-warning';
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

<div class="admin-layout">

<?php include __DIR__ . '/../components/sidebar.php'; ?>

<div class="main" id="main">

<?php include __DIR__ . '/../components/topbar.php'; ?>

<div class="content">

<h1>Claim Details</h1>

<div class="card">

<div class="form-group">
<label>Claim ID</label>
<p><?= $claim['id'] ?></p>
</div>

<div class="form-group">
<label>Claimant</label>
<p><?= htmlspecialchars($claim['fullname']) ?></p>
</div>

<div class="form-group">
<label>Email</label>
<p><?= htmlspecialchars($claim['email']) ?></p>
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
<p><span class="badge <?= $badge ?>"><?= ucfirst($claim['status']) ?></span></p>
</div>

<div class="form-group">
<label>Date Submitted</label>
<p><?= date('d M Y H:i', strtotime($claim['created_at'])) ?></p>
</div>

<hr style="margin:25px 0;">

<a href="claims.php" class="action-btn">
<i class="fas fa-arrow-left"></i>
Back
</a>

<?php if ($claim['status']==='pending'): ?>

<a href="process-claim.php?id=<?= $claim['id'] ?>&action=approve"
class="action-btn approve"
onclick="return confirm('Approve this claim?');">
<i class="fas fa-check"></i>
Approve
</a>

<a href="process-claim.php?id=<?= $claim['id'] ?>&action=reject"
class="action-btn delete"
onclick="return confirm('Reject this claim?');">
<i class="fas fa-times"></i>
Reject
</a>

<?php endif; ?>

</div>

</div>

</div>

</div>

<script src="/assets/js/sidebar.js"></script>

</body>
</html>
