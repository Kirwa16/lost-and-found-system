<?php

session_start();
require_once __DIR__ . '/../backend/helpers/csrf.php';
include __DIR__ . '/components/header.php';

$submitted = $_SERVER['REQUEST_METHOD'] === 'POST' && csrf_validate($_POST['csrf_token'] ?? null);

?>

<div class="auth-wrapper">

    <div class="auth-left">
        <h1>Lost & Found</h1>
        <p>Recover access to your account securely.</p>
    </div>

    <div class="auth-right">

        <div class="auth-card">

            <h2>Forgot Password</h2>

            <?php if($submitted): ?>

                <p class="success">
                    If an account exists with this email, instructions have been sent. Please contact the admin office for immediate assistance.
                </p>

                <div class="link">
                    <a href="/login.php">Back to Login</a>
                </div>

            <?php else: ?>

                <p class="subtitle">
                    Enter your account email address.
                </p>

                <form method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <input
                            type="email"
                            name="email"
                            placeholder="Email Address"
                            required>
                    </div>

                    <button type="submit" class="btn">
                        Request Instructions
                    </button>
                </form>

                <div class="link">
                    <a href="/login.php">Back to Login</a>
                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

<?php include __DIR__ . '/components/footer.php'; ?>
