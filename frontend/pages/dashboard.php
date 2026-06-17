<?php
session_start();

if(!isset($_SESSION['full_name'])){
    $_SESSION['full_name'] = "Brian";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard</title>

<link rel="stylesheet" href="../assets/css/dashboard.css">
</head>

<body>

<div class="sidebar">

    <h2>Lost & Found</h2>

    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="report-lost.php">Report Lost Item</a></li>
        <li><a href="report-found.php">Report Found Item</a></li>
        <li><a href="search-items.php">Search Items</a></li>
        <li><a href="my-claims.php">My Claims</a></li>
        <li><a href="profile.php">Profile</a></li>
        <li><a href="../../backend/api/logout.php">Logout</a></li>
    </ul>

</div>

<div class="main-content">

    <div class="header">
        <h1>Welcome, <?php echo $_SESSION['full_name']; ?></h1>
    </div>

    <div class="cards">

        <div class="card">
            <h3>Lost Items</h3>
            <p>12</p>
        </div>

        <div class="card">
            <h3>Found Items</h3>
            <p>18</p>
        </div>

        <div class="card">
            <h3>Claims</h3>
            <p>4</p>
        </div>

    </div>

    <div class="activity">

        <h2>Recent Activity</h2>

        <table>

            <tr>
                <th>Item</th>
                <th>Status</th>
                <th>Date</th>
            </tr>

            <tr>
                <td>HP Laptop</td>
                <td>Pending</td>
                <td>10/06/2026</td>
            </tr>

            <tr>
                <td>Student ID</td>
                <td>Verified</td>
                <td>09/06/2026</td>
            </tr>

        </table>

    </div>

</div>

</body>
</html>