<?php
session_start();

/*
|--------------------------------------------------------------------------
| Redirect logged-in users
|--------------------------------------------------------------------------
*/

if (isset($_SESSION['user_id'])) {

    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
    } else {
        header("Location: frontend/pages/dashboard.php");
    }

    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Lost & Found Management System</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins',sans-serif;
}

body{
    min-height:100vh;
    background:linear-gradient(135deg,#4a0000,#8b0000,#c62828);
    display:flex;
    justify-content:center;
    align-items:center;
    color:white;
}

.container{
    max-width:900px;
    text-align:center;
    padding:50px;
}

h1{
    font-size:4rem;
    margin-bottom:20px;
}

p{
    font-size:1.2rem;
    line-height:1.8;
    margin-bottom:40px;
}

.buttons{
    display:flex;
    justify-content:center;
    gap:20px;
    flex-wrap:wrap;
}

.btn{
    text-decoration:none;
    padding:15px 35px;
    border-radius:10px;
    font-weight:600;
    transition:0.3s;
}

.login{
    background:white;
    color:#8b0000;
}

.register{
    border:2px solid white;
    color:white;
}

.login:hover,
.register:hover{
    transform:translateY(-3px);
}

.features{
    margin-top:50px;
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:20px;
}

.feature{
    background:rgba(255,255,255,0.1);
    padding:20px;
    border-radius:15px;
    backdrop-filter:blur(10px);
}

.feature h3{
    margin-bottom:10px;
}

</style>

</head>
<body>

<div class="container">

    <h1>Lost & Found System</h1>

    <p>
        A centralized platform for reporting lost items,
        reporting found items, submitting ownership claims,
        and tracking recovery status across campus.
    </p>

    <div class="buttons">
        <a href="frontend/pages/login.php" class="btn login">Login</a>
        <a href="frontend/pages/register.php" class="btn register">Register</a>
    </div>

    <div class="features">

        <div class="feature">
            <h3>Report Lost Items</h3>
            <p>Quickly submit details of missing property.</p>
        </div>

        <div class="feature">
            <h3>Report Found Items</h3>
            <p>Help return recovered items to their owners.</p>
        </div>

        <div class="feature">
            <h3>Track Claims</h3>
            <p>Monitor the progress of ownership verification.</p>
        </div>

    </div>

</div>

</body>
</html>