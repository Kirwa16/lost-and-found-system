<?php
$currentPage = basename($_SERVER['PHP_SELF']);

require_once __DIR__ . '/../../backend/config/database.php';

$database = new Database();
$conn = $database->getConnection();

$pendingAdminCounts = [
    'matches' => 0,
    'claims' => 0
];

$stmt = $conn->query(
    "SELECT
        (SELECT COUNT(*) FROM matches WHERE status = 'pending') AS pending_matches,
        (SELECT COUNT(*) FROM claims WHERE status = 'pending') AS pending_claims"
);

$counts = $stmt->fetch(PDO::FETCH_ASSOC);

if ($counts) {
    $pendingAdminCounts['matches'] = (int)$counts['pending_matches'];
    $pendingAdminCounts['claims'] = (int)$counts['pending_claims'];
}

function sidebarBadge(int $count): string
{
    return $count > 99 ? '99+' : (string)$count;
}
?>

<nav class="sidebar" id="sidebar">

    <div class="logo">
        <h2>
            <i class="fas fa-box-open"></i>
            <span>Lost &amp; Found</span>
        </h2>
    </div>

    <ul class="menu">

        <!-- MAIN -->
        <li class="menu-title">MAIN</li>

        <li>
            <a href="/admin/dashboard.php"
               class="<?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <!-- MANAGEMENT -->
        <li class="menu-title">MANAGEMENT</li>

        <li>
            <a href="/admin/users.php"
               class="<?= $currentPage === 'users.php' ? 'active' : '' ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>

        <li>
            <a href="/admin/items.php"
               class="<?= $currentPage === 'items.php' ? 'active' : '' ?>">
                <i class="fas fa-boxes"></i>
                <span>Items</span>
            </a>
        </li>

        <li>
            <a href="/admin/matches.php"
               class="<?= in_array($currentPage, ['matches.php', 'match-details.php', 'process-match.php']) ? 'active' : '' ?>">
                <i class="fas fa-link"></i>
                <span>Matches</span>
                <?php if ($pendingAdminCounts['matches'] > 0): ?>
                    <strong class="notification-count"
                            title="<?= $pendingAdminCounts['matches'] ?> pending match<?= $pendingAdminCounts['matches'] === 1 ? '' : 'es' ?>"
                            aria-label="<?= $pendingAdminCounts['matches'] ?> pending match<?= $pendingAdminCounts['matches'] === 1 ? '' : 'es' ?>">
                        <?= sidebarBadge($pendingAdminCounts['matches']) ?>
                    </strong>
                <?php endif; ?>
            </a>
        </li>

        <li>
            <a href="/admin/claims.php"
               class="<?= in_array($currentPage, ['claims.php', 'claim-details.php']) ? 'active' : '' ?>">
                <i class="fas fa-handshake"></i>
                <span>Claims</span>
                <?php if ($pendingAdminCounts['claims'] > 0): ?>
                    <strong class="notification-count"
                            title="<?= $pendingAdminCounts['claims'] ?> pending claim<?= $pendingAdminCounts['claims'] === 1 ? '' : 's' ?>"
                            aria-label="<?= $pendingAdminCounts['claims'] ?> pending claim<?= $pendingAdminCounts['claims'] === 1 ? '' : 's' ?>">
                        <?= sidebarBadge($pendingAdminCounts['claims']) ?>
                    </strong>
                <?php endif; ?>
            </a>
        </li>

        <!-- ANALYTICS -->
        <li class="menu-title">ANALYTICS</li>

        <li>
            <a href="/admin/lost-reports.php"
               class="<?= $currentPage === 'lost-reports.php' ? 'active' : '' ?>">
                <i class="fas fa-search"></i>
                <span>Lost Reports</span>
            </a>
        </li>

        <li>
            <a href="/admin/found-reports.php"
               class="<?= $currentPage === 'found-reports.php' ? 'active' : '' ?>">
                <i class="fas fa-box-open"></i>
                <span>Found Reports</span>
            </a>
        </li>

        <li>
            <a href="/admin/generated-reports.php"
               class="<?= $currentPage === 'generated-reports.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-line"></i>
                <span>Generated Reports</span>
            </a>
        </li>

    </ul>

</nav>
