<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if(!in_array($_SESSION['role'], ['student', 'staff'], true))
{
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';
require_once __DIR__ . '/../../backend/models/Claim.php';
require_once __DIR__ . '/../../backend/helpers/csrf.php';

$db = new Database();
$conn = $db->getConnection();
$claimModel = new Claim($conn);

$claimMode = null;
$match = null;
$item = null;
$matchId = null;
$itemId = null;
$itemType = null;

if(isset($_GET['match_id']) && is_numeric($_GET['match_id']))
{
    $claimMode = 'match';
    $matchId = (int)$_GET['match_id'];

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

    $check = $conn->prepare(
        "SELECT id
         FROM claims
         WHERE user_id = :user_id
         AND match_id = :match_id
         AND status IN ('pending', 'approved', 'collected')"
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
}
elseif(isset($_GET['item_id'], $_GET['item_type']) && is_numeric($_GET['item_id']))
{
    $claimMode = 'item';
    $itemId = (int)$_GET['item_id'];
    $itemType = $_GET['item_type'];

    if($itemType !== 'found')
    {
        header("Location: /user/search.php");
        exit;
    }

    $stmt = $conn->prepare(
        "SELECT *
         FROM found_items
         WHERE id = :item_id
         LIMIT 1"
    );

    $stmt->execute([
        ':item_id' => $itemId
    ]);

    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$item)
    {
        die("Item not found.");
    }

    if((int)$item['user_id'] === (int)$_SESSION['user_id'])
    {
        $_SESSION['error'] = "You cannot claim an item you reported.";
        header("Location: /user/search.php");
        exit;
    }

    if(!in_array($item['status'], ['available', 'pending'], true))
    {
        $_SESSION['error'] = "This item is not available for claim.";
        header("Location: /user/search.php");
        exit;
    }

    $check = $conn->prepare(
        "SELECT id
         FROM claims
         WHERE user_id = :user_id
         AND item_id = :item_id
         AND status IN ('pending', 'approved', 'collected')"
    );

    $check->execute([
        ':user_id' => $_SESSION['user_id'],
        ':item_id' => $itemId
    ]);

    if($check->fetch())
    {
        $_SESSION['error'] =
            "You have already submitted a claim for this item.";

        header("Location: /user/claims.php");
        exit;
    }
}
else
{
    header("Location: /user/search.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| Submit Claim
|--------------------------------------------------------------------------
*/

if($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if(!csrf_validate($_POST['csrf_token'] ?? null)) {
        $_SESSION['error'] = "Security token expired. Please try again.";
        header("Location: /user/claims.php");
        exit;
    }

    $claimMessage = trim($_POST['claim_message']);

    $claimData = [
        'user_id' => $_SESSION['user_id'],
        'claim_message' => $claimMessage
    ];

    if($claimMode === 'match') {
        $claimData['match_id'] = $matchId;
    } else {
        $claimData['item_id'] = $itemId;
        $claimData['item_type'] = $itemType;
    }

    if(!$claimModel->create($claimData)) {
        $_SESSION['error'] = "Unable to submit claim.";
        header("Location: /user/claims.php");
        exit;
    }

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

            <h1>Submit Claim</h1>

            <div class="card">

                <?php if($claimMode === 'match'): ?>

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

                <?php else: ?>

                    <p>
                        <strong>Claiming:</strong>
                        <?= htmlspecialchars($item['item_name']) ?>
                    </p>

                    <br>

                    <p>
                        <strong>Category:</strong>
                        <?= htmlspecialchars($item['category']) ?>
                    </p>

                    <br>

                    <p>
                        <strong>Location Found:</strong>
                        <?= htmlspecialchars($item['location_found']) ?>
                    </p>

                    <br>

                    <p>
                        <strong>Date Found:</strong>
                        <?= htmlspecialchars($item['date_found']) ?>
                    </p>

                <?php endif; ?>

            </div>

            <br>

            <div class="form-card">

                <form method="POST">
                    <?= csrf_field() ?>

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
<Script src="/assets/js/sidebar.js"></script>
</body>

</html>
