<?php

session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

require_once __DIR__ . '/../../backend/controllers/ClaimController.php';

$controller = new ClaimController();

$claims = $controller->userClaims($_SESSION['user_id']);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Claims</title>

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

            <h1>My Claims</h1>

            <div class="card">

                <?php if(empty($claims)): ?>

                    <p>No claims submitted yet.</p>

                <?php else: ?>

                <table class="table">

                    <thead>

                        <tr>

                            <th>Claim No.</th>
                            <th>Lost Item</th>
                            <th>Found Item</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>
                        <?php $count = 1; ?>

                        <?php foreach ($claims as $claim): ?>

                            <?php

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

<tr>

    <td><?= $count++; ?></td>

    <td><?= htmlspecialchars($claim['lost_item']); ?></td>

    <td><?= htmlspecialchars($claim['found_item']); ?></td>

    <td>
        <span class="badge <?= $badge ?>">
            <?= ucfirst($claim['status']) ?>
        </span>
    </td>

    <td>
        <?= date('d M Y', strtotime($claim['created_at'])) ?>
    </td>

    <td>

        <a
            href="claim-details.php?id=<?= $claim['id'] ?>"
            class="action-btn view">

            <i class="fas fa-eye"></i>

            View

        </a>

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
