<?php

require_once '../includes/auth.php';
require_once '../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

$sql = "
    SELECT *
    FROM claims
    WHERE user_id = ?
    ORDER BY created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);

$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>My Claims</h1>

        <?php if(empty($claims)): ?>

            <div class="card">
                <p>No claims submitted yet.</p>
            </div>

        <?php else: ?>

            <table class="table">

                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Match ID</th>
                        <th>Message</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>

                <?php foreach($claims as $claim): ?>

                    <tr>

                        <td>
                            <?= $claim['id']; ?>
                        </td>

                        <td>
                            <?= $claim['match_id']; ?>
                        </td>

                        <td>
                            <?= htmlspecialchars(
                                $claim['claim_message']
                            ); ?>
                        </td>

                        <td>
                            <?= ucfirst(
                                $claim['status']
                            ); ?>
                        </td>

                        <td>
                            <?= $claim['created_at']; ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        <?php endif; ?>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

