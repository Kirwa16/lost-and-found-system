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
    header("Location: matches.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/MatchController.php';

$controller = new MatchController();
$match = $controller->show((int)$_GET['id']);

if (!$match) {
    header("Location: matches.php?error=" . urlencode("Match not found."));
    exit;
}

switch ($match['status']) {
    case 'approved': $badge='badge-success'; break;
    case 'rejected': $badge='badge-danger'; break;
    default: $badge='badge-warning';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Match Details</title>
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

<h1>Match Details</h1>

<div class="card">

<div class="form-group">
<label>Match ID</label>
<p><?= htmlspecialchars($match['id']) ?></p>
</div>

<div class="form-group">
<label>Lost Item</label>
<p><?= htmlspecialchars($match['lost_item']) ?></p>
</div>

<div class="form-group">
<label>Found Item</label>
<p><?= htmlspecialchars($match['found_item']) ?></p>
</div>

<div class="form-group">
<label>Category</label>
<p><?= htmlspecialchars($match['category']) ?></p>
</div>

<div class="form-group">
<label>Location Lost</label>
<p><?= htmlspecialchars($match['location_lost']) ?></p>
</div>

<div class="form-group">
<label>Location Found</label>
<p><?= htmlspecialchars($match['location_found']) ?></p>
</div>

<div class="form-group">
<label>Confidence Score</label>
<p><?= number_format($match['confidence_score'],2) ?>%</p>
</div>

<div class="form-group">
<label>Status</label>
<span class="badge <?= $badge ?>"><?= ucfirst($match['status']) ?></span>
</div>

<div class="form-group">
<label>Date Matched</label>
<p><?= date('d M Y H:i', strtotime($match['created_at'])) ?></p>
</div>

<hr style="margin:25px 0;">

<a href="matches.php" class="action-btn">
<i class="fas fa-arrow-left"></i> Back
</a>

<?php if ($match['status']==='pending'): ?>

<a href="process-match.php?id=<?= $match['id'] ?>&action=approve"
class="action-btn approve"
onclick="return confirm('Approve this match?');">
<i class="fas fa-check"></i> Approve
</a>

<a href="process-match.php?id=<?= $match['id'] ?>&action=reject"
class="action-btn delete"
onclick="return confirm('Reject this match?');">
<i class="fas fa-times"></i> Reject
</a>

<?php endif; ?>

</div>

</div>

</div>

</div>

<script src="/assets/js/sidebar.js"></script>

</body>
</html>
