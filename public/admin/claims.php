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

require_once __DIR__ . '/../../backend/controllers/ClaimController.php';
require_once __DIR__ . '/../../backend/helpers/csrf.php';

$controller = new ClaimController();
$claims = $controller->index();

if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="claims-export-' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Claimant', 'Type', 'Lost Item', 'Found Item', 'Status', 'Date Submitted']);

        foreach ($claims as $claim) {
            fputcsv($output, [
                $claim['id'],
                $claim['fullname'],
                (($claim['claim_type'] ?? 'match') === 'direct') ? 'Direct Claim' : 'Match-Based',
                (($claim['claim_type'] ?? 'match') === 'direct') ? 'No linked lost report' : ($claim['lost_item'] ?? 'N/A'),
                $claim['found_item'] ?? $claim['direct_item'] ?? 'Item',
                $claim['status'],
                $claim['created_at']
            ]);
        }

        fclose($output);
        exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Claims</title>

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

    <h1>Manage Claims</h1>

    <div class="page-actions">
    <a class="secondary-btn" href="claims.php?export=csv">
    <i class="fas fa-file-csv"></i>
    Export CSV
    </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="success">
            <?= htmlspecialchars($_GET['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="error">
            <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <div class="card">

    <?php if (empty($claims)): ?>

    <p>No claims found.</p>

    <?php else: ?>

    <table class="table">

    <thead>
    <tr>
    <th>Claim No.</th>
    <th>Claimant</th>
    <th>Type</th>
    <th>Lost Item</th>
    <th>Found Item</th>
    <th>Status</th>
    <th>Date</th>
    <th>Actions</th>
    </tr>
    </thead>

    <tbody>
        
    <?php $count = 1; ?>

    <?php foreach ($claims as $claim): ?>

    <?php
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
    break;
    }
    ?>

    <tr>
    <td><?= $count++ ?></td>
    <td><?= htmlspecialchars($claim['fullname']) ?></td>
    <td>
    <?php if (($claim['claim_type'] ?? 'match') === 'direct'): ?>
    <span class="badge badge-info">Direct Claim</span>
    <?php else: ?>
    <span class="badge badge-primary">Match-Based</span>
    <?php endif; ?>
    </td>
    <td>
    <?php if (($claim['claim_type'] ?? 'match') === 'direct' && empty($claim['lost_item'])): ?>
    <span class="text-muted">No linked lost report</span>
    <?php else: ?>
    <?= htmlspecialchars($claim['lost_item'] ?? 'N/A') ?>
    <?php endif; ?>
    </td>
    <td><?= htmlspecialchars($claim['found_item'] ?? $claim['direct_item'] ?? 'Item') ?></td>
    <td><span class="badge <?= $badge ?>"><?= ucfirst($claim['status']) ?></span></td>
    <td><?= date('d M Y', strtotime($claim['created_at'])) ?></td>
    <td>
    <div class="table-actions">

    <a href="claim-details.php?id=<?= $claim['id'] ?>" class="action-btn view" title="View Claim" aria-label="View Claim">
    <i class="fas fa-eye"></i>
    </a>

    <?php if ($claim['status']==='pending'): ?>

    <form method="POST" action="process-claim.php" onsubmit="return confirm('Approve this claim?')">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$claim['id'] ?>">
    <input type="hidden" name="action" value="approve">
    <button type="submit" class="action-btn approve" title="Approve Claim" aria-label="Approve Claim">
    <i class="fas fa-check"></i>
    </button>
    </form>

    <form method="POST" action="process-claim.php" onsubmit="return confirm('Reject this claim?')">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$claim['id'] ?>">
    <input type="hidden" name="action" value="reject">
    <button type="submit" class="action-btn delete" title="Reject Claim" aria-label="Reject Claim">
    <i class="fas fa-times"></i>
    </button>
    </form>

    <?php endif; ?>

    <?php if ($claim['status']==='approved'): ?>

    <form method="POST" action="process-claim.php" onsubmit="return confirm('Mark this claim as collected?')">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= (int)$claim['id'] ?>">
    <input type="hidden" name="action" value="collect">
    <button type="submit" class="action-btn approve" title="Mark Collected" aria-label="Mark Collected">
    <i class="fas fa-box-open"></i>
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
