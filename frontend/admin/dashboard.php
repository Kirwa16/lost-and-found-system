<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard</title>

<link rel="stylesheet" href="../frontend/assets/css/admin.css">

</head>

<body>

<div class="sidebar">

    <h2>Admin Panel</h2>

    <ul>

        <li><a href="dashboard.php">Dashboard</a></li>

        <li><a href="manage-items.php">Manage Items</a></li>

        <li><a href="verify-claims.php">Verify Claims</a></li>

        <li><a href="manage-users.php">Manage Users</a></li>

        <li><a href="reports.php">Reports</a></li>

        <li><a href="../frontend/pages/login.php">Logout</a></li>

    </ul>

</div>

<div class="main">

    <h1>Administrator Dashboard</h1>

    <div class="cards">

        <div class="card">
            <h3>Total Users</h3>
            <p>245</p>
        </div>

        <div class="card">
            <h3>Lost Reports</h3>
            <p>72</p>
        </div>

        <div class="card">
            <h3>Found Reports</h3>
            <p>58</p>
        </div>

        <div class="card">
            <h3>Pending Claims</h3>
            <p>14</p>
        </div>

    </div>

    <div class="recent">

        <h2>Recent Activity</h2>

        <table>

            <tr>
                <th>ID</th>
                <th>Item</th>
                <th>Status</th>
                <th>Date</th>
            </tr>

            <tr>
                <td>#101</td>
                <td>HP Laptop</td>
                <td>Pending Claim</td>
                <td>15 Jun 2026</td>
            </tr>

            <tr>
                <td>#102</td>
                <td>Student ID</td>
                <td>Verified</td>
                <td>14 Jun 2026</td>
            </tr>

        </table>

    </div>

</div>

</body>
</html>