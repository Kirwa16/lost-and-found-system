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

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>System Settings</title>

<link rel="stylesheet"
      href="/frontend/assets/css/dashboard.css">

<link rel="stylesheet"
      href="/frontend/assets/css/admin.css">

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
    border:1px solid #e2e8f0;
}

.settings-section h2{
    margin-bottom:20px;
    color:#334155;
}

.form-group{
    margin-bottom:20px;
}

.form-group label{
    display:block;
    margin-bottom:8px;
    font-weight:600;
    color:#334155;
}

.form-group input,
.form-group select,
.form-group textarea{
    width:100%;
    padding:12px;
    border:1px solid #cbd5e1;
    border-radius:8px;
    outline:none;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus{
    border-color:#334155;
    box-shadow:0 0 0 3px rgba(51,65,85,.15);
}

.save-btn{
    background:#334155;
    color:white;
    border:none;
    padding:12px 25px;
    border-radius:8px;
    cursor:pointer;
    transition:.3s;
}

.save-btn:hover{
    background:#1e293b;
}

.switch{
    display:flex;
    align-items:center;
    gap:10px;
    margin-top:10px;
}

.switch label{
    margin:0;
    font-weight:500;
}

</style>

</head>

<body>

<div class="admin-layout">

    <?php include '../components/sidebar.php'; ?>

    <div class="main">

        <?php include '../components/topbar.php'; ?>

        <div class="content">

            <h1>System Settings</h1>

            <div class="settings-container">

                <!-- System Settings -->

                <div class="settings-section">

                    <h2>System Information</h2>

                    <div class="form-group">

                        <label>System Name</label>

                        <input
                            type="text"
                            value="Lost & Found Management System">

                    </div>

                    <div class="form-group">

                        <label>University Name</label>

                        <input
                            type="text"
                            value="Strathmore University">

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

                        <label>
                            Minimum Password Length
                        </label>

                        <select>
                            <option>6</option>
                            <option selected>8</option>
                            <option>10</option>
                            <option>12</option>
                        </select>

                    </div>

                    <div class="form-group">

                        <label>
                            Session Timeout (Minutes)
                        </label>

                        <input
                            type="number"
                            value="30">

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

                <!-- Save Button -->

                <button class="save-btn">
                    Save Changes
                </button>

            </div>

        </div>

    </div>

</div>

</body>

</html>
