<div class="topbar">

    <button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
    </button>


    <div class="topbar-center">
        <h1>Admin Panel</h1>
    </div>

    <div class="topbar-right">

        <div class="user-profile">

            <div class="avatar">
                <?= strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
            </div>

            <span>
                <?= htmlspecialchars($_SESSION['fullname']); ?>
            </span>

        </div>

        <a href="/logout.php"
           class="logout-btn">
           <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>

    </div>

</div>