<div class="sidebar">

    <div class="logo">
        <h2>Lost & Found</h2>
    </div>

    <ul class="menu">

        <?php if($_SESSION['role'] === 'admin'): ?>

            <li>
                <a href="/admin/dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="/admin/users.php">
                    Users
                </a>
            </li>

            <li>
                <a href="/admin/items.php">
                    Manage Items
                </a>
            </li>

            <li>
                <a href="/admin/matches.php">
                    Matches
                </a>
            </li>

            <li>
                <a href="/admin/claims.php">
                    Claims
                </a>
            </li>

        <?php else: ?>

            <li>
                <a href="/user/dashboard.php">
                    Dashboard
                </a>
            </li>

            <li>
                <a href="/user/report-lost.php">
                    Report Lost Item
                </a>
            </li>

            <li>
                <a href="/user/report-found.php">
                    Report Found Item
                </a>
            </li>

            <li>
                <a href="/user/my-reports.php">
                    My Reports
                </a>
            </li>

            <li>
                <a href="/user/search.php">
                    Search Items
                </a>
            </li>

            
<li>
    <a href="/user/notifications.php">
        Notifications
    </a>
</li>



            <li>
                <a href="/user/claims.php">
                    My Claims
                </a>
            </li>

            <li>
                <a href="/user/profile.php">
                    Profile
                </a>
            </li>


        <?php endif; ?>

        <li>
            <a href="/public/logout.php">
                Logout
            </a>
        </li>

    </ul>

</div>

