<?php

require_once '../includes/auth.php';
require_once '../backend/config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| APPROVE MATCH
|--------------------------------------------------------------------------
*/

if (isset($_GET['approve']))
{
    $matchId = (int)$_GET['approve'];

    $stmt = $conn->prepare(
        "SELECT *
         FROM matches
         WHERE id = :id"
    );

    $stmt->execute([
        ':id' => $matchId
    ]);

    $match = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($match)
    {
        $stmt = $conn->prepare(
            "UPDATE matches
             SET status = 'approved'
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $matchId
        ]);

        $stmt = $conn->prepare(
            "UPDATE lost_items
             SET status = 'claimed'
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $match['lost_item_id']
        ]);

        $stmt = $conn->prepare(
            "UPDATE found_items
             SET status = 'claimed'
             WHERE id = :id"
        );

        $stmt->execute([
            ':id' => $match['found_item_id']
        ]);
    }

    header("Location: matches.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| REJECT MATCH
|--------------------------------------------------------------------------
*/

if (isset($_GET['reject']))
{
    $matchId = (int)$_GET['reject'];

    $stmt = $conn->prepare(
        "UPDATE matches
         SET status = 'rejected'
         WHERE id = :id"
    );

    $stmt->execute([
        ':id' => $matchId
    ]);

    header("Location: matches.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| FETCH MATCHES
|--------------------------------------------------------------------------
*/

$sql = "
SELECT
    m.id,
    m.confidence_score,
    m.status,
    m.created_at,

    l.item_name AS lost_item,
    l.category AS lost_category,

    f.item_name AS found_item,
    f.category AS found_category

FROM matches m

INNER JOIN lost_items l
    ON m.lost_item_id = l.id

INNER JOIN found_items f
    ON m.found_item_id = f.id

ORDER BY m.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

include '../includes/header.php';
?>

<link rel="stylesheet" href="/assets/css/dashboard.css">

<div class="dashboard">

    <?php include '../includes/sidebar.php'; ?>

    <div class="content">

        <h1>Match Management</h1>

        <div class="card">

            <?php if(empty($matches)): ?>

                <p>No matches found.</p>

            <?php else: ?>

                <table class="table">

                    <thead>

                        <tr>
                            <th>ID</th>
                            <th>Lost Item</th>
                            <th>Found Item</th>
                            <th>Confidence</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($matches as $match): ?>

                        <tr>

                            <td>
                                <?= $match['id']; ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($match['lost_item']); ?>
                                </strong>
                                <br>
                                <?= htmlspecialchars($match['lost_category']); ?>
                            </td>

                            <td>
                                <strong>
                                    <?= htmlspecialchars($match['found_item']); ?>
                                </strong>
                                <br>
                                <?= htmlspecialchars($match['found_category']); ?>
                            </td>

                            <td>
                                <?= $match['confidence_score']; ?>%
                            </td>

                            <td>

                                <span class="status status-<?= $match['status']; ?>">
                                    <?= ucfirst($match['status']); ?>
                                </span>

                            </td>

                            <td>
                                <?= $match['created_at']; ?>
                            </td>

                            <td>

                                <?php if($match['status'] === 'pending'): ?>

                                    <a
                                        href="?approve=<?= $match['id']; ?>"
                                        onclick="return confirm('Approve this match?');">

                                        Approve

                                    </a>

                                    |

                                    <a
                                        href="?reject=<?= $match['id']; ?>"
                                        onclick="return confirm('Reject this match?');">

                                        Reject

                                    </a>

                                <?php else: ?>

                                    Completed

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

<?php include '../includes/footer.php'; ?>

