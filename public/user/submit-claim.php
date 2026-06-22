<?php

session_save_path(__DIR__ . '/../../sessions');
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if($_SESSION['role'] !== 'user')
{
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

if(!isset($_GET['match_id']))
{
    header("Location: /user/matches.php");
    exit;
}

$matchId = (int)$_GET['match_id'];

$db = new Database();
$conn = $db->connect();

/*
|--------------------------------------------------------------------------
| Get Match Information
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT
        m.id,
        m.confidence_score,

        l.item_name AS lost_item,
        l.category,

        f.item_name AS found_item

     FROM matches m

     INNER JOIN lost_items l
        ON l.id = m.lost_item_id

     INNER JOIN found_items f
        ON f.id = m.found_item_id

     WHERE m.id = :match_id
     AND l.user_id = :user_id"
);

$stmt->execute([
    ':match_id' => $matchId,
    ':user_id' => $_SESSION['user_id']
]);

$match = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$match)
{
    die("Match not found.");
}

/*
|--------------------------------------------------------------------------
| Prevent Duplicate Claims
|--------------------------------------------------------------------------
*/

$check = $conn->prepare(
    "SELECT id
     FROM claims
     WHERE user_id = :user_id
     AND match_id = :match_id"
);

$check->execute([
    ':user_id' => $_SESSION['user_id'],
    ':match_id' => $matchId
]);

if($check->fetch())
{
    $_SESSION['error'] =
        "You have already submitted a claim for this match.";

    header("Location: /user/claims.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Submit Claim
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $claimMessage = trim($_POST['claim_message']);

    $stmt = $conn->prepare(
        "INSERT INTO claims
        (
            user_id,
            match_id,
            claim_message,
            status
        )
        VALUES
        (
            :user_id,
            :match_id,
            :claim_message,
            'pending'
        )"
    );

    $stmt->execute([
        ':user_id'       => $_SESSION['user_id'],
        ':match_id'      => $matchId,
        ':claim_message' => $claimMessage
    ]);

    $_SESSION['success'] =
        "Claim submitted successfully.";

    header("Location: /user/claims.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Submit Claim</title>

<link rel="stylesheet"
      href="/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include __DIR__ . '/../components/user-sidebar.php'; ?>

    <div class="main">
        <?php include __DIR__ . '/../components/topbar.php'; ?>

        <div class="content">

            <h1>Submit Claim</h1>

            <div class="card">

                <p>
                    <strong>Lost Item:</strong>
                    <?= htmlspecialchars($match['lost_item']) ?>
                </p>

                <br>

                <p>
                    <strong>Found Item:</strong>
                    <?= htmlspecialchars($match['found_item']) ?>
                </p>

                <br>

                <p>
                    <strong>Category:</strong>
                    <?= htmlspecialchars($match['category']) ?>
                </p>

                <br>

                <p>
                    <strong>Confidence:</strong>
                    <?= $match['confidence_score'] ?>%
                </p>

            </div>

            <br>

            <div class="form-card">

                <form method="POST">

                    <div class="form-group">

                        <label>
                            Explain why this item belongs to you
                        </label>

                        <textarea
                            name="claim_message"
                            rows="8"
                            required
                            placeholder="Describe ownership details, unique features, contents, markings, serial numbers, or any information that proves ownership."></textarea>

                    </div>

                    <button
                        type="submit"
                        class="action-btn">

                        Submit Claim

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>