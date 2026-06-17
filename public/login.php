<?php
session_start();
include '../includes/header.php';
?>

<div class="auth-wrapper">

    <div class="auth-left">

        <h1>Lost & Found</h1>

        <p>
            Helping students and staff recover
            lost items quickly, securely and efficiently.
        </p>

        <ul class="features">
            <li>✓ Report Lost Items</li>
            <li>✓ Report Found Items</li>
            <li>✓ Submit Claims</li>
            <li>✓ Secure Verification</li>
        </ul>

    </div>

    <div class="auth-right">

        <div class="auth-card">

            <h2>Welcome Back</h2>

            <p class="subtitle">
                Sign in to your account
            </p>

            <?php
            if(isset($_SESSION['error']))
            {
                echo "<p class='error'>{$_SESSION['error']}</p>";
                unset($_SESSION['error']);
            }
            ?>

            <form
            action="../public/process-login.php"
            method="POST">

                <div class="form-group">
                    <input
                    type="email"
                    name="email"
                    placeholder="Email Address"
                    required>
                </div>

                <div class="form-group">
                    <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    required>
                </div>

                <button
                type="submit"
                name="login"
                class="btn">
                    Login
                </button>

            </form>

            <div class="link">
                <a href="register.php">
                    Create an Account
                </a>
            </div>

        </div>

    </div>

</div>

<?php include '../includes/footer.php'; ?>