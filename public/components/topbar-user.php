<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$topbarNotifications = [];
$topbarUnreadNotifications = 0;

if(isset($_SESSION['user_id']))
{
    require_once __DIR__ . '/../../backend/config/database.php';

    $__topbarDatabase = new Database();
    $__topbarConn = $__topbarDatabase->getConnection();

    $__topbarStmt = $__topbarConn->prepare(
        "SHOW COLUMNS
         FROM notifications
         LIKE 'link'"
    );
    $__topbarStmt->execute();
    $__topbarHasLinkColumn = (bool)$__topbarStmt->fetch(PDO::FETCH_ASSOC);

    $__topbarStmt = $__topbarConn->prepare(
        "SELECT COUNT(*)
         FROM notifications
         WHERE user_id = :user_id
         AND is_read = 0"
    );

    $__topbarStmt->execute([
        ':user_id' => $_SESSION['user_id']
    ]);

    $topbarUnreadNotifications = (int)$__topbarStmt->fetchColumn();

    $__topbarLinkSelect = $__topbarHasLinkColumn
        ? 'link'
        : "NULL AS link";

    $__topbarStmt = $__topbarConn->prepare(
        "SELECT id, message, {$__topbarLinkSelect}, is_read, created_at
         FROM notifications
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT 5"
    );

    $__topbarStmt->execute([
        ':user_id' => $_SESSION['user_id']
    ]);

    $topbarNotifications = $__topbarStmt->fetchAll(PDO::FETCH_ASSOC);
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

        <div class="notification-dropdown">

            <button class="notification-dropdown-btn" id="notificationDropdown" title="Notifications" aria-label="Notifications">
                <i class="fas fa-bell"></i>
                <?php if($topbarUnreadNotifications > 0): ?>
                    <strong class="topbar-notification-count">
                        <?= $topbarUnreadNotifications > 99 ? '99+' : $topbarUnreadNotifications ?>
                    </strong>
                <?php endif; ?>
            </button>

            <div class="notification-menu" id="notificationDropdownMenu">
                <div class="notification-menu-header">
                    <strong>Notifications</strong>
                    <a href="/user/notifications.php">View all</a>
                </div>

                <?php if(empty($topbarNotifications)): ?>
                    <div class="notification-empty">No notifications</div>
                <?php else: ?>
                    <?php foreach($topbarNotifications as $notification): ?>
                        <a
                            href="/user/open-notification.php?id=<?= (int)$notification['id'] ?>"
                            class="notification-menu-item <?= $notification['is_read'] ? 'is-read' : 'is-unread' ?>">
                            <?php if(!$notification['is_read']): ?>
                                <span class="unread-dot" aria-hidden="true"></span>
                            <?php endif; ?>
                            <span>
                                <?= htmlspecialchars($notification['message']) ?>
                                <small><?= date('d M Y H:i', strtotime($notification['created_at'])) ?></small>
                            </span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <div class="user-dropdown">

            <!-- User Dropdown Button -->
            <button class="user-dropdown-btn" id="userDropdown">

                <div class="avatar">
                    <?= strtoupper(substr($_SESSION['fullname'], 0, 1)); ?>
                </div>

                <span class="user-name">
                    <?= htmlspecialchars($_SESSION['fullname']); ?>
                    <small class="user-role">(<?= htmlspecialchars(ucfirst($_SESSION['role'] ?? '')); ?>)</small>
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
