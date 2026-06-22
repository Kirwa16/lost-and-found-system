<div class="topbar">

    <h1>User Panel</h1>

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
            Logout
        </a>

    </div>

</div>