<?php
session_start();
?>

<!DOCTYPE html>
<html>
<head>
<title>Profile</title>
</head>
<body>

<h1>User Profile</h1>

<p>Name: <?php echo $_SESSION['full_name']; ?></p>

<p>Email: <?php echo $_SESSION['email']; ?>oka</p>

</body>
</html>