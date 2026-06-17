<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Lost & Found</title>

    <link rel="stylesheet" href="../assets/css/auth.css">
</head>
<body>

<div class="container">

    <div class="left">

        <h1>Create Account</h1>

        <p>
            Join the Lost & Found Management System
            and help recover lost property across campus.
        </p>

        <div class="features">
            <div>✓ Report lost items instantly</div>
            <div>✓ Report found items</div>
            <div>✓ Submit ownership claims</div>
            <div>✓ Track item recovery</div>
            <div>✓ Secure user verification</div>
        </div>

    </div>

    <div class="right">

        <div class="form-box">

            <h2>Register</h2>

            <form action="../../backend/api/register.php" method="POST">

                <div class="input-group">
                    <input
                        type="text"
                        name="full_name"
                        placeholder="Full Name"
                        required
                    >
                </div>

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
                        type="text"
                        name="phone"
                        placeholder="Phone Number"
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
                    Create Account
                </button>

            </form>

            <div class="switch">
                Already have an account?
                <a href="login.php">Login</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>