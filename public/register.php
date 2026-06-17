<?php
session_start();
include '../includes/header.php';
?>

<div class="auth-wrapper">

    <div class="auth-left">

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
                action="../public/process-register.php"
                method="POST"
                id="registerForm">

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
                    <input
                        type="password"
                        name="password"
                        id="password"
                        placeholder="Password"
                        required>
                </div>

                <div class="form-group">
                    <input
                        type="password"
                        name="confirm_password"
                        id="confirm_password"
                        placeholder="Confirm Password"
                        required>
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
                <a href="login.php">
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

<?php include '../includes/footer.php'; ?>

