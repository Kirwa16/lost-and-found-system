<div class="sidebar" id="sidebar">

    <div class="logo">

        <h2>
            <i class="fas fa-box-open"></i>
            <span>Lost & Found</span>
        </h2>

    </div>

    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
    ?>

    <ul class="menu">

        <!-- MAIN -->
        <li class="menu-title">MAIN</li>

        <li class="<?= $currentPage == 'dashboard.php' ? 'active' : '' ?>">
            <a href="/user/dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- ITEMS -->
        <li class="menu-title">ITEMS</li>

        <li class="<?= $currentPage == 'report-lost.php' ? 'active' : '' ?>">
            <a href="/user/report-lost.php">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Report Lost Item</span>
            </a>
        </li>

        <li class="<?= $currentPage == 'report-found.php' ? 'active' : '' ?>">
            <a href="/user/report-found.php">
                <i class="fas fa-check-circle"></i>
                <span>Report Found Item</span>
            </a>
        </li>

        <li class="<?= $currentPage == 'search.php' ? 'active' : '' ?>">
            <a href="/user/search.php">
                <i class="fas fa-search"></i>
                <span>Search Items</span>
            </a>
        </li>

        <li class="<?= $currentPage == 'my-reports.php' ? 'active' : '' ?>">
            <a href="/user/my-reports.php">
                <i class="fas fa-file-alt"></i>
                <span>My Reports</span>
            </a>
        </li>

        <!-- ACTIVITY -->
        <li class="menu-title">ACTIVITY</li>

        <li class="<?= $currentPage == 'matches.php' ? 'active' : '' ?>">
            <a href="/user/matches.php">
                <i class="fas fa-handshake"></i>
                <span>Matches</span>
            </a>
        </li>

        <li class="<?= $currentPage == 'claims.php' ? 'active' : '' ?>">
            <a href="/user/claims.php">
                <i class="fas fa-clipboard-check"></i>
                <span>My Claims</span>
            </a>
        </li>

        <li class="<?= $currentPage == 'notifications.php' ? 'active' : '' ?>">
            <a href="/user/notifications.php">
                <i class="fas fa-bell"></i>
                <span>Notifications</span>
            </a>
        </li>

        <!-- ACCOUNT -->
        <li class="menu-title">ACCOUNT</li>

        <li class="<?= $currentPage == 'profile.php' ? 'active' : '' ?>">
            <a href="/user/profile.php">
                <i class="fas fa-user"></i>
                <span>Profile</span>
            </a>
        </li>

    </ul>

</div>  