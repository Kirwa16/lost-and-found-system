<nav class="sidebar" id="sidebar">

    <div class="logo">
        
            <h2>
                <i class="fas fa-box-open"></i>
                <span>Lost &amp; Found</span>
            </h2>
        
    </div>

    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
    ?>

    <ul class="menu">

        <!-- Main -->
        <li class="menu-title">MAIN</li>

        <li class="<?php echo ($currentPage === 'dashboard.php') ? 'active' : ''; ?>">
            <a href="/admin/dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>


        <!-- Management -->
        <li class="menu-title">MANAGEMENT</li>

        <li class="<?php echo ($currentPage === 'users.php') ? 'active' : ''; ?>">
            <a href="/admin/users.php">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>

        <li class="<?php echo ($currentPage === 'items.php') ? 'active' : ''; ?>">
            <a href="/admin/items.php">
                <i class="fas fa-box-open"></i>
                <span>Items</span>
            </a>
        </li>

        <li class="<?php echo ($currentPage === 'matches.php') ? 'active' : ''; ?>">
            <a href="/admin/matches.php">
                <i class="fas fa-handshake"></i>
                <span>Matches</span>
            </a>
        </li>

        <li class="<?php echo ($currentPage === 'claims.php') ? 'active' : ''; ?>">
            <a href="/admin/claims.php">
                <i class="fas fa-clipboard-check"></i>
                <span>Claims</span>
            </a>
        </li>


        <!-- Analytics -->
        <li class="menu-title">ANALYTICS</li>

        <li class="<?php echo ($currentPage === 'reports.php') ? 'active' : ''; ?>">
            <a href="/admin/reports.php">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>


        <!-- System -->
        <li class="menu-title">SYSTEM</li>

        <li class="<?php echo ($currentPage === 'settings.php') ? 'active' : ''; ?>">
            <a href="/admin/settings.php">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>

    </ul>

</nav>