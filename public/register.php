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

        <h1>Lost &amp; Found Management System</h1>

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
                Register to access the Lost &amp; Found System
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

                <!-- Full Name -->

                <div class="form-group">
                    <input
                        type="text"
                        name="fullname"
                        placeholder="Full Name"
                        required>
                </div>

                <!-- Email -->

                <div class="form-group">
                    <input
                        type="email"
                        name="email"
                        placeholder="University Email Address"
                        required>
                </div>

                <!-- Role -->

                <div class="form-group">
                    <select
                        name="role"
                        id="role"
                        required>

                        <option value="">Select Account Type</option>
                        <option value="student">Student</option>
                        <option value="staff">Staff</option>

                    </select>
                </div>

                <!-- Admission Number -->

                <div
                    class="form-group"
                    id="admissionGroup"
                    style="display:none;">

                    <input
                        type="text"
                        id="admission_number"
                        name="admission_number"
                        placeholder="Admission Number">

                </div>

                <!-- Registration Number -->

                <div
                    class="form-group"
                    id="registrationGroup"
                    style="display:none;">

                    <input
                        type="text"
                        id="registration_number"
                        name="registration_number"
                        placeholder="Registration Number">

                </div>

                <!-- Password -->

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
                            aria-label="Show password">
                        </i>

                    </div>

                </div>

                <!-- Confirm Password -->

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
                            aria-label="Show password">
                        </i>

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

const role = document.getElementById("role");

const admissionGroup = document.getElementById("admissionGroup");
const registrationGroup = document.getElementById("registrationGroup");

const admissionInput = document.getElementById("admission_number");
const registrationInput = document.getElementById("registration_number");

role.addEventListener("change", function(){

    admissionInput.required = false;
    registrationInput.required = false;

    admissionGroup.style.display = "none";
    registrationGroup.style.display = "none";

    if(this.value === "student")
    {
        admissionGroup.style.display = "block";
        admissionInput.required = true;
    }

    if(this.value === "staff")
    {
        registrationGroup.style.display = "block";
        registrationInput.required = true;
    }

});

document.getElementById("registerForm")
.addEventListener("submit", function(e){

    const password =
        document.getElementById("password").value;

    const confirmPassword =
        document.getElementById("confirm_password").value;

    if(password !== confirmPassword)
    {
        e.preventDefault();
        alert("Passwords do not match.");
        return;
    }

    if(role.value === "")
    {
        e.preventDefault();
        alert("Please select an account type.");
        return;
    }

    if(role.value === "student" && admissionInput.value.trim() === "")
    {
        e.preventDefault();
        alert("Please enter your admission number.");
        return;
    }

    if(role.value === "staff" && registrationInput.value.trim() === "")
    {
        e.preventDefault();
        alert("Please enter your registration number.");
        return;
    }

});

</script>

<script src="/assets/js/password-toggle.js"></script>

<?php include __DIR__ . '/components/footer.php'; ?>