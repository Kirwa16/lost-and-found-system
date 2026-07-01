<?php


session_start();

if (!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$stmt = $conn->prepare(
    "SELECT
        m.id,
        m.confidence_score,
        m.status,

        l.item_name AS lost_item,
        l.category,

        f.item_name AS found_item

     FROM matches m

     INNER JOIN lost_items l
        ON l.id = m.lost_item_id

     INNER JOIN found_items f
        ON f.id = m.found_item_id

     WHERE l.user_id = :user_id

     ORDER BY m.created_at DESC"
);

$stmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Matches</title>

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

            <h1>Potential Matches</h1>

            <div class="card">

                <?php if(empty($matches)): ?>

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
                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>
                        <?php $count = 1; ?>

                    <?php foreach($matches as $match): ?>

                        <tr>
                            <td><?= $count++; ?></td>

                                <td><?= htmlspecialchars($match['lost_item']) ?></td>

                                <td><?= htmlspecialchars($match['found_item']) ?></td>

                                <td><?= htmlspecialchars($match['category']) ?></td>

                                <td><?= number_format($match['confidence_score'], 2) ?>%</td>
                            <td>

                                <?php if($match['status'] === 'approved'): ?>

                                    <span class="badge badge-success">
                                        Approved
                                    </span>

                                <?php elseif($match['status'] === 'rejected'): ?>

                                    <span class="badge badge-danger">
                                        Rejected
                                    </span>

                                <?php else: ?>

                                    <span class="badge badge-warning">
                                        Pending
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <?php if($match['status'] === 'approved'): ?>

                                    <a
                                        href="/user/submit-claim.php?match_id=<?= $match['id'] ?>"
                                        class="action-btn">

                                        Submit Claim

                                    </a>

                                <?php else: ?>

                                    <span style="color:#64748b;">
                                        Awaiting Review
                                    </span>

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
