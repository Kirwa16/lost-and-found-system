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

require_once __DIR__ . '/../../backend/controllers/MatchController.php';

$controller = new MatchController();
$matches = $controller->index();

?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Detected Matches</title>

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

<h1>Detected Matches</h1>

<?php if (isset($_GET['success'])): ?>
<div class="success"><?= htmlspecialchars($_GET['success']) ?></div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="error"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<div class="card">

<?php if (empty($matches)): ?>

<p>No matches found.</p>

<?php else: ?>

<table class="table">

<thead>
<tr>
<th>Match No.</th>
<th>Lost Item</th>
<th>Found Item</th>
<th>Category</th>
<th>Confidence</th>
<th>Status</th>
<th>Date Matched</th>
<th>Actions</th>
</tr>
</thead>

<tbody>

<?php $count = 1; ?>

<?php foreach ($matches as $match): ?>

<?php
switch ($match['status']) {
    case 'approved':
        $statusBadge = 'badge-success';
        break;
    case 'rejected':
        $statusBadge = 'badge-danger';
        break;
    default:
        $statusBadge = 'badge-warning';
        break;
}

$score = (float)$match['confidence_score'];

if ($score >= 90) {
    $confidenceBadge = 'badge-success';
} elseif ($score >= 75) {
    $confidenceBadge = 'badge-warning';
} else {
    $confidenceBadge = 'badge-danger';
}
?>

<tr>

<td><?= $count++; ?></td>

<td><?= htmlspecialchars($match['lost_item']) ?></td>

<td><?= htmlspecialchars($match['found_item']) ?></td>

<td><?= htmlspecialchars($match['category']) ?></td>

<td>
<span class="badge <?= $confidenceBadge ?>">
<?= number_format($score,2) ?>%
</span>
</td>

<td>
<span class="badge <?= $statusBadge ?>">
<?= ucfirst($match['status']) ?>
</span>
</td>

<td><?= date('d M Y', strtotime($match['created_at'])) ?></td>

<td>

<a href="match-details.php?id=<?= $match['id'] ?>" class="action-btn view">
<i class="fas fa-eye"></i> View
</a>

<?php if ($match['status'] === 'pending'): ?>

<a href="process-match.php?id=<?= $match['id'] ?>&action=approve"
class="action-btn approve"
onclick="return confirm('Approve this match?')">
<i class="fas fa-check"></i> Approve
</a>

<a href="process-match.php?id=<?= $match['id'] ?>&action=reject"
class="action-btn delete"
onclick="return confirm('Reject this match?')">
<i class="fas fa-times"></i> Reject
</a>

<?php endif; ?>

</td>

</tr>

<?php endforeach; ?>

</tbody>

</table>

<?php endif; ?>

</div>

</div>

</div>

</div>

<script src="/assets/js/sidebar.js"></script>

</body>
</html>
