<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<div class="topbar">

    <!-- Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Page Title -->
    <h1>User Panel</h1>

    <!-- Right Section -->
    <div class="topbar-right">

        <div class="user-dropdown">

            <!-- User Dropdown Button -->
            <button class="user-dropdown-btn" id="userDropdown">

                <div class="avatar">
                    <?= strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                </div>

                <span class="user-name">
                    <?= htmlspecialchars($_SESSION['fullname']); ?>
                </span>

                <i class="fas fa-chevron-down"></i>

            </button>

            <!-- Dropdown Menu -->
            <div class="dropdown-menu" id="userDropdownMenu">

                <a href="/user/profile.php">
                    <i class="fas fa-user-circle"></i>
                    Account Settings
                </a>

                <a href="/logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>

            </div>

        </div>

    </div>

</div>
<script src="/assets/js/topbar.js"></script>


