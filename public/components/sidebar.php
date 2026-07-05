<nav class="sidebar" id="sidebar">

    <div class="logo">
        
            <h2>
                <i class="fas fa-box-open"></i>
                <span>Lost &amp; Found</span>
            </h2>
        
    </div>

    <?php
        $currentPage = basename($_SERVER['PHP_SELF']);
        $pendingMatches = 0;
        $pendingClaims = 0;

        if(isset($_SESSION['user_id']))
        {
            require_once __DIR__ . '/../../backend/config/database.php';

            $__sidebarDatabase = new Database();
            $__sidebarConn = $__sidebarDatabase->getConnection();

            $__sidebarStmt = $__sidebarConn->prepare(
                "SELECT COUNT(*)
                 FROM matches
                 WHERE status = 'pending'"
            );
            $__sidebarStmt->execute();
            $pendingMatches = (int)$__sidebarStmt->fetchColumn();

            $__sidebarStmt = $__sidebarConn->prepare(
                "SELECT COUNT(*)
                 FROM claims
                 WHERE status = 'pending'"
            );
            $__sidebarStmt->execute();
            $pendingClaims = (int)$__sidebarStmt->fetchColumn();
        }
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
                <?php if($pendingMatches > 0): ?>
                    <strong class="notification-count">
                        <?= $pendingMatches > 99 ? '99+' : $pendingMatches ?>
                    </strong>
                <?php endif; ?>
            </a>
        </li>

        <li class="<?php echo ($currentPage === 'claims.php') ? 'active' : ''; ?>">
            <a href="/admin/claims.php">
                <i class="fas fa-clipboard-check"></i>
                <span>Claims</span>
                <?php if($pendingClaims > 0): ?>
                    <strong class="notification-count">
                        <?= $pendingClaims > 99 ? '99+' : $pendingClaims ?>
                    </strong>
                <?php endif; ?>
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

    </ul>

</nav>
