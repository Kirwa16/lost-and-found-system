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

        <h1>Lost & Found Management System</h1>

        <p>
            Helping students and staff recover lost items quickly,
            securely and efficiently through a centralized platform.
        </p>

        <ul class="features">
            <li>✓ Report Lost Items</li>
            <li>✓ Report Found Items</li>
            <li>✓ Search Existing Reports</li>
            <li>✓ Submit Ownership Claims</li>
            <li>✓ Secure User Verification</li>
        </ul>

    </div>

    <div class="auth-right">

        <div class="auth-card">

            <h2>Create Account</h2>

            <p class="subtitle">
                Register to access the Lost & Found System
            </p>

            <?php
            if(isset($_SESSION['error']))
            {
                echo "<p class='error'>" . $_SESSION['error'] . "</p>";
                unset($_SESSION['error']);
            }

            if(isset($_SESSION['success']))
            {
                echo "<p class='success'>" . $_SESSION['success'] . "</p>";
                unset($_SESSION['success']);
            }
            ?>

            <form
                action="/process-register.php"
                method="POST"
                id="registerForm">
                <?= csrf_field() ?>

                <div class="form-group">
                    <input
                        type="text"
                        name="fullname"
                        placeholder="Full Name"
                        required>
                </div>

                <div class="form-group">
                    <input
                        type="email"
                        name="email"
                        placeholder="University Email Address"
                        required>
                </div>

                <div class="form-group">
                    <select name="role" required>
                        <option value="">Select Account Type</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>
                    </select>
                </div>

                <div class="form-group">
                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="password"
                            id="password"
                            placeholder="Password"
                            required>

                        <i
                            class="fas fa-eye toggle-password"
                            data-target="password"
                            role="button"
                            tabindex="0"
                            aria-label="Show password"></i>
                    </div>
                </div>

                <div class="form-group">
                    <div class="password-wrapper">
                        <input
                            type="password"
                            name="confirm_password"
                            id="confirm_password"
                            placeholder="Confirm Password"
                            required>

                        <i
                            class="fas fa-eye toggle-password"
                            data-target="confirm_password"
                            role="button"
                            tabindex="0"
                            aria-label="Show password"></i>
                    </div>
                </div>

                <button
                    type="submit"
                    name="register"
                    class="btn">
                    Create Account
                </button>

            </form>

            <div class="link">
                Already have an account?
                <a href="/login.php">
                    Login
                </a>
            </div>

        </div>

    </div>

</div>

<script>
document.getElementById('registerForm')
.addEventListener('submit', function(e){

    const password =
        document.getElementById('password').value;

    const confirmPassword =
        document.getElementById('confirm_password').value;

    if(password !== confirmPassword)
    {
        e.preventDefault();
        alert('Passwords do not match.');
    }
});
</script>

<script src="/assets/js/password-toggle.js"></script>

<?php include __DIR__ . '/components/footer.php'; ?>
