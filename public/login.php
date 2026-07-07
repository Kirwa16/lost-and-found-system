<?php


session_start();
require_once __DIR__ . '/../backend/helpers/csrf.php';

include __DIR__ . '/components/header.php';
?>

<div class="auth-wrapper">

    <div class="auth-left">

        <div class="auth-logo" aria-hidden="true">
            <i class="fas fa-box-open"></i>
        </div>

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
            

            <form action="/process-login.php" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <input
                    type="email"
                    name="email"
                    placeholder="Email Address"
                    required>
                </div>

                <div class="form-group">
                    <div class="password-wrapper">
                        <input
                        type="password"
                        name="password"
                        id="login_password"
                        placeholder="Password"
                        required>

                        <i
                        class="fas fa-eye toggle-password"
                        data-target="login_password"
                        role="button"
                        tabindex="0"
                        aria-label="Show password"></i>
                    </div>
                </div>

                <button
                type="submit"
                name="login"
                class="btn">
                    Login
                </button>

            </form>

            <div class="link">
                <a href="/forgot-password.php">
                    Forgot Password?
                </a>
            </div>

            <div class="link">
                <a href="/register.php">
                    Create an Account
                </a>
            </div>

        </div>

    </div>

</div>

<script src="/assets/js/password-toggle.js"></script>

<?php include __DIR__ . '/components/footer.php'; ?>
