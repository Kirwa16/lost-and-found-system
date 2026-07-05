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
require_once __DIR__ . '/../../backend/helpers/csrf.php';

$controller = new MatchController();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_manual_match') {
    if(!csrf_validate($_POST['csrf_token'] ?? null)) {
        header("Location: matches.php?error=" . urlencode("Invalid security token."));
        exit;
    }

    $lostItemId = isset($_POST['lost_item_id']) ? (int)$_POST['lost_item_id'] : 0;
    $foundItemId = isset($_POST['found_item_id']) ? (int)$_POST['found_item_id'] : 0;

    if ($lostItemId <= 0 || $foundItemId <= 0) {
        header("Location: matches.php?error=" . urlencode("Please select both a lost item and a found item."));
        exit;
    }

    if ($controller->create($lostItemId, $foundItemId, 100)) {
        header("Location: matches.php?success=" . urlencode("Manual match created successfully."));
        exit;
    }

    header("Location: matches.php?error=" . urlencode("Unable to create manual match. It may already exist."));
    exit;
}

$matches = $controller->index();
$pendingLostItems = $controller->pendingLostItems();
$pendingFoundItems = $controller->pendingFoundItems();

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
<h2>Create Manual Match</h2>

<?php if (empty($pendingLostItems) || empty($pendingFoundItems)): ?>

<p>No pending lost and found items are available for manual matching.</p>

<?php else: ?>

<form method="POST" class="manual-match-form">
<?= csrf_field() ?>
<input type="hidden" name="action" value="create_manual_match">

<div class="form-grid">

<div class="form-group">
<label for="lost_item_id">Pending Lost Item</label>
<select id="lost_item_id" name="lost_item_id" required>
<option value="">Select lost item</option>
<?php foreach ($pendingLostItems as $item): ?>
<option value="<?= (int)$item['id'] ?>">
<?= htmlspecialchars($item['item_name'] . ' - ' . $item['category'] . ' - ' . $item['location_lost']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="form-group">
<label for="found_item_id">Pending Found Item</label>
<select id="found_item_id" name="found_item_id" required>
<option value="">Select found item</option>
<?php foreach ($pendingFoundItems as $item): ?>
<option value="<?= (int)$item['id'] ?>">
<?= htmlspecialchars($item['item_name'] . ' - ' . $item['category'] . ' - ' . $item['location_found']) ?>
</option>
<?php endforeach; ?>
</select>
</div>

</div>

<button type="submit" class="action-btn approve">
<i class="fas fa-link"></i>
Create Match
</button>
</form>

<?php endif; ?>

</div>

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
<div class="table-actions">

<a href="match-details.php?id=<?= $match['id'] ?>" class="action-btn view" title="View Match" aria-label="View Match">
<i class="fas fa-eye"></i>
</a>

<?php if ($match['status'] === 'pending'): ?>

<form method="POST" action="process-match.php" onsubmit="return confirm('Approve this match?')">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$match['id'] ?>">
<input type="hidden" name="action" value="approve">
<button type="submit" class="action-btn approve" title="Approve Match" aria-label="Approve Match">
<i class="fas fa-check"></i>
</button>
</form>

<form method="POST" action="process-match.php" onsubmit="return confirm('Reject this match?')">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$match['id'] ?>">
<input type="hidden" name="action" value="reject">
<button type="submit" class="action-btn delete" title="Reject Match" aria-label="Reject Match">
<i class="fas fa-times"></i>
</button>
</form>

<?php endif; ?>
</div>

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
