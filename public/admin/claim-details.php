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
require_once __DIR__ . '/../../backend/helpers/csrf.php';

$controller = new ClaimController();
$claim = $controller->show((int)$_GET['id']);
$backHref = ($_GET['from'] ?? '') === 'dashboard'
    ? 'dashboard.php'
    : 'claims.php';

if (!$claim) {
    header("Location: claims.php");
    exit;
}

switch ($claim['status']) {
    case 'approved':
        $badge='badge-success';
        break;
    case 'collected':
        $badge='badge-success';
        break;
    case 'rejected':
        $badge='badge-danger';
        break;
    default:
        $badge='badge-warning';
}

$foundImage = null;
if(!empty($claim['found_image'])) {
    $foundImage = '/' . ltrim($claim['found_image'], '/');
}

$lostImage = null;
if(!empty($claim['lost_image'])) {
    $lostImage = '/' . ltrim($claim['lost_image'], '/');
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

<div class="card claim-review">

<div class="report-tabs" role="tablist">
<button type="button" class="report-tab active" data-tab="claimTab" role="tab">Claim Details</button>
<button type="button" class="report-tab" data-tab="foundItemTab" role="tab">Found Item</button>
<?php if(($claim['claim_type'] ?? 'match') !== 'direct' || !empty($claim['lost_item'])): ?>
<button type="button" class="report-tab" data-tab="lostItemTab" role="tab">
<?= (($claim['claim_type'] ?? 'match') === 'direct') ? 'Related Lost Report' : 'Lost Item' ?>
</button>
<?php endif; ?>
</div>

<div id="claimTab" class="report-tab-panel active">

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
<label>Claim Type</label>
<p><?= (($claim['claim_type'] ?? 'match') === 'direct') ? 'Direct item claim' : 'Matched item claim' ?></p>
</div>

<div class="form-group">
<label><?= (($claim['claim_type'] ?? 'match') === 'direct') ? 'Claiming' : 'Items' ?></label>
<?php if(($claim['claim_type'] ?? 'match') === 'direct'): ?>
<p><?= htmlspecialchars($claim['direct_item'] ?? $claim['found_item'] ?? 'Item') ?></p>
<?php if(!empty($claim['lost_item'])): ?>
<p>Related lost report: <?= htmlspecialchars($claim['lost_item']) ?></p>
<?php endif; ?>
<?php else: ?>
<p>
Lost: <?= htmlspecialchars($claim['lost_item']) ?> /
Found: <?= htmlspecialchars($claim['found_item']) ?>
</p>
<?php endif; ?>
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

</div>

<div id="foundItemTab" class="report-tab-panel">

<div class="detail-grid">

<div class="form-group">
<label>Item Name</label>
<p><?= htmlspecialchars($claim['found_item'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Category</label>
<p><?= htmlspecialchars($claim['found_category'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Color</label>
<p><?= htmlspecialchars($claim['found_color'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Brand / Model</label>
<p><?= htmlspecialchars($claim['found_brand_model'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Location Found</label>
<p><?= htmlspecialchars($claim['location_found'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Date Found</label>
<p><?= htmlspecialchars($claim['date_found'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Status</label>
<p><?= htmlspecialchars(ucfirst($claim['found_status'] ?? 'N/A')) ?></p>
</div>

</div>

<div class="form-group">
<label>Distinguishing Features</label>
<p><?= nl2br(htmlspecialchars($claim['found_unique_features'] ?? 'N/A')) ?></p>
</div>

<div class="form-group">
<label>Description</label>
<p><?= nl2br(htmlspecialchars($claim['found_description'] ?? 'N/A')) ?></p>
</div>

<div class="form-group">
<label>Image</label>
<?php if($foundImage): ?>
<img class="report-image" src="<?= htmlspecialchars($foundImage) ?>" alt="<?= htmlspecialchars($claim['found_item'] ?? 'Found item') ?>">
<?php else: ?>
<p>No image uploaded.</p>
<?php endif; ?>
</div>

</div>

<?php if(($claim['claim_type'] ?? 'match') !== 'direct' || !empty($claim['lost_item'])): ?>

<div id="lostItemTab" class="report-tab-panel">

<div class="detail-grid">

<div class="form-group">
<label>Item Name</label>
<p><?= htmlspecialchars($claim['lost_item'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Category</label>
<p><?= htmlspecialchars($claim['lost_category'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Color</label>
<p><?= htmlspecialchars($claim['lost_color'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Brand / Model</label>
<p><?= htmlspecialchars($claim['lost_brand_model'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Location Lost</label>
<p><?= htmlspecialchars($claim['location_lost'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Date Lost</label>
<p><?= htmlspecialchars($claim['date_lost'] ?? 'N/A') ?></p>
</div>

<div class="form-group">
<label>Status</label>
<p><?= htmlspecialchars(ucfirst($claim['lost_status'] ?? 'N/A')) ?></p>
</div>

</div>

<div class="form-group">
<label>Distinguishing Features</label>
<p><?= nl2br(htmlspecialchars($claim['lost_unique_features'] ?? 'N/A')) ?></p>
</div>

<div class="form-group">
<label>Description</label>
<p><?= nl2br(htmlspecialchars($claim['lost_description'] ?? 'N/A')) ?></p>
</div>

<div class="form-group">
<label>Image</label>
<?php if($lostImage): ?>
<img class="report-image" src="<?= htmlspecialchars($lostImage) ?>" alt="<?= htmlspecialchars($claim['lost_item'] ?? 'Lost item') ?>">
<?php else: ?>
<p>No image uploaded.</p>
<?php endif; ?>
</div>

</div>

<?php endif; ?>

<hr style="margin:25px 0;">

<a href="<?= htmlspecialchars($backHref) ?>" class="action-btn">
<i class="fas fa-arrow-left"></i>
Back
</a>

<?php if ($claim['status']==='pending'): ?>

<form method="POST" action="process-claim.php" style="display:inline-flex;" onsubmit="return confirm('Approve this claim?');">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$claim['id'] ?>">
<input type="hidden" name="action" value="approve">
<button type="submit" class="action-btn approve">
<i class="fas fa-check"></i>
Approve
</button>
</form>

<form method="POST" action="process-claim.php" style="display:inline-flex;" onsubmit="return confirm('Reject this claim?');">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$claim['id'] ?>">
<input type="hidden" name="action" value="reject">
<button type="submit" class="action-btn delete">
<i class="fas fa-times"></i>
Reject
</button>
</form>

<?php endif; ?>

<?php if ($claim['status']==='approved'): ?>

<form method="POST" action="process-claim.php" style="display:inline-flex;" onsubmit="return confirm('Mark this claim as collected?');">
<?= csrf_field() ?>
<input type="hidden" name="id" value="<?= (int)$claim['id'] ?>">
<input type="hidden" name="action" value="collect">
<button type="submit" class="action-btn approve">
<i class="fas fa-box-open"></i>
Mark as Collected
</button>
</form>

<?php endif; ?>

</div>

</div>

</div>

</div>

<script src="/assets/js/sidebar.js"></script>
<script>
document.querySelectorAll('.report-tab').forEach(function(tab){
    tab.addEventListener('click', function(){
        document.querySelectorAll('.report-tab').forEach(function(item){
            item.classList.remove('active');
        });

        document.querySelectorAll('.report-tab-panel').forEach(function(panel){
            panel.classList.remove('active');
        });

        tab.classList.add('active');
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});
</script>

</body>
</html>
