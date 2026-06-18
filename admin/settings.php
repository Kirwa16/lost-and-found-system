<!DOCTYPE html>
<html lang="en">

<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Settings</title>

<link rel="stylesheet" href="../frontend/assets/css/admin.css">

<style>

.settings-container{
    margin-top:20px;
}

.settings-section{
    background:white;
    padding:25px;
    border-radius:15px;
    margin-bottom:20px;
    box-shadow:0 4px 10px rgba(0,0,0,.08);
}

.settings-section h2{
    margin-bottom:20px;
    color:#8b0000;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
}

.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    padding:12px;
    border:1px solid #ddd;
    border-radius:8px;
    outline:none;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
    border-color:#8b0000;
}

.save-btn{
    background:#8b0000;
    color:white;
    border:none;
    padding:12px 25px;
    border-radius:8px;
    cursor:pointer;
}

.save-btn:hover{
    background:#a50000;
}

.switch{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:10px;
}

</style>

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

        <li><a href="settings.php">Settings</a></li>

        <li><a href="../frontend/pages/login.php">Logout</a></li>

    </ul>

</div>

<div class="main">

    <h1>System Settings</h1>

    <div class="settings-container">

        <!-- System Settings -->

        <div class="settings-section">

            <h2>System Information</h2>

            <div class="form-group">

                <label>System Name</label>

                <input
                    type="text"
                    value="Lost & Found Management System"
                >

            </div>

            <div class="form-group">

                <label>University Name</label>

                <input
                    type="text"
                    value="Strathmore University"
                >

            </div>

        </div>

        <!-- Notifications -->

        <div class="settings-section">

            <h2>Notifications</h2>

            <div class="switch">

                <input type="checkbox" checked>

                <label>Email Notifications</label>

            </div>

            <div class="switch">

                <input type="checkbox" checked>

                <label>Claim Alerts</label>

            </div>

            <div class="switch">

                <input type="checkbox">

                <label>SMS Notifications</label>

            </div>

        </div>

        <!-- Security -->

        <div class="settings-section">

            <h2>Security</h2>

            <div class="form-group">

                <label>Minimum Password Length</label>

                <select>
                    <option>6</option>
                    <option selected>8</option>
                    <option>10</option>
                    <option>12</option>
                </select>

            </div>

            <div class="form-group">

                <label>Session Timeout (Minutes)</label>

                <input
                    type="number"
                    value="30"
                >

            </div>

        </div>

        <!-- Appearance -->

        <div class="settings-section">

            <h2>Appearance</h2>

            <div class="form-group">

                <label>Theme</label>

                <select>
                    <option selected>Light</option>
                    <option>Dark</option>
                </select>

            </div>

        </div>

        <!-- Save -->

        <button class="save-btn">
            Save Changes
        </button>

    </div>

</div>

</body>
</html>