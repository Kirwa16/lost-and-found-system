<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /public/login.php");
    exit;
}

if($_SESSION['role'] !== 'admin')
{
    header("Location: /frontend/user/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

$stmt = $conn->query(
    "SELECT
        m.id,
        m.confidence_score,
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

     ORDER BY m.created_at DESC"
);

$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Detected Matches</title>

<link rel="stylesheet"
      href="/frontend/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/frontend/assets/css/admin.css">

</head>

<body>

<div class="admin-layout">

    <?php include '../components/sidebar.php'; ?>

    <div class="main">

        <?php include '../components/topbar.php'; ?>

        <div class="content">

            <h1>Detected Matches</h1>

            <div class="card">

                <?php if(empty($matches)): ?>

                    <p>No matches found.</p>

                <?php else: ?>

                    <table class="table">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Lost Item</th>
                                <th>Found Item</th>
                                <th>Category</th>
                                <th>Confidence</th>
                                <th>Date Matched</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $count = 1; ?>

                        <?php foreach($matches as $match): ?>

                            <tr>

                                <td>
                                    <?= $count++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($match['lost_item']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($match['found_item']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($match['lost_category']) ?>
                                </td>

                                <td>

                                    <?php
                                    $score = (int)$match['confidence_score'];

                                    if($score >= 90)
                                    {
                                        echo '<span class="badge badge-success">'
                                            . $score . '%</span>';
                                    }
                                    elseif($score >= 75)
                                    {
                                        echo '<span class="badge badge-warning">'
                                            . $score . '%</span>';
                                    }
                                    else
                                    {
                                        echo '<span class="badge badge-danger">'
                                            . $score . '%</span>';
                                    }
                                    ?>

                                </td>

                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime($match['created_at'])
                                    ) ?>
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

</body>

</html>
