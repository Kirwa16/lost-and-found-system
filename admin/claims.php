
<?php

require_once '../includes/auth.php';
require_once '../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

if(isset($_GET['approve']))
{
    $stmt = $conn->prepare(
        "UPDATE claims
         SET status='approved'
         WHERE id=?"
    );

    $stmt->execute([
        $_GET['approve']
    ]);
}

if(isset($_GET['reject']))
{
    $stmt = $conn->prepare(
        "UPDATE claims
         SET status='rejected'
         WHERE id=?"
    );

    $stmt->execute([
        $_GET['reject']
    ]);
}
if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

$sql = "
    SELECT c.*, u.fullname
    FROM claims c
    JOIN users u
    ON c.user_id = u.id
    ORDER BY c.created_at DESC
";

$stmt = $conn->query($sql);

$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>Manage Claims</h1>

        <table class="table">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Match</th>
                    <th>Message</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php foreach($claims as $claim): ?>

                <tr>

                    <td>
                        <?= $claim['id']; ?>
                    </td>

                    <td>
                        <?= htmlspecialchars(
                            $claim['fullname']
                        ); ?>
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

                        <a
                        href="?approve=<?= $claim['id']; ?>">
                            Approve
                        </a>

                        |

                        <a
                        href="?reject=<?= $claim['id']; ?>">
                            Reject
                        </a>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<?php include '../includes/footer.php'; ?>

