<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Lost & Found</title>

    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="container">

    <div class="left">

        <h1>Lost & Found</h1>

        <p>
            Strathmore University's digital platform
            for reporting, tracking and recovering
            lost items across campus.
        </p>

        <div class="features">
            <div>✓ Report lost items instantly</div>
            <div>✓ Upload photos and descriptions</div>
            <div>✓ Track ownership claims</div>
            <div>✓ Real-time status updates</div>
            <div>✓ Secure verification process</div>
        </div>

    </div>

    <div class="right">

        <div class="form-box">

            <h2>Login</h2>

            <form action="../../backend/api/login.php" method="POST">

                <div class="input-group">
                    <input
                        type="email"
                        name="email"
                        placeholder="Email Address"
                        required
                    >
                </div>

                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        placeholder="Password"
                        required
                    >
                </div>

                <button class="btn" type="submit">
                    Login
                </button>

            </form>

            <div class="switch">
                Don't have an account?
                <a href="register.php">Register</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>