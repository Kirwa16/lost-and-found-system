<?php

session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if(!in_array($_SESSION['role'], ['student', 'staff'], true))
{
    header("Location: /admin/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/config/database.php';

$db = new Database();
$conn = $db->getConnection();

$lostStmt = $conn->prepare(
    "SELECT *
     FROM lost_items
     WHERE user_id = :user_id
     ORDER BY created_at DESC"
);

$lostStmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$lostReports = $lostStmt->fetchAll(PDO::FETCH_ASSOC);

$foundStmt = $conn->prepare(
    "SELECT *
     FROM found_items
     WHERE user_id = :user_id
     ORDER BY created_at DESC"
);

$foundStmt->execute([
    ':user_id' => $_SESSION['user_id']
]);

$foundReports = $foundStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>My Reports</title>

<link rel="stylesheet" href="/assets/css/dashboard.css"> 
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

</head>

<body>

<div class="user-layout">

    <?php include __DIR__ . '/../components/user-sidebar.php'; ?>

    <div class="main" id="main">
        <?php include __DIR__ . '/../components/topbar-user.php'; ?>

        <div class="content">

            <h1>My Reports</h1>

            <?php if(isset($_SESSION['success'])): ?>

                <div class="success">

                    <?= htmlspecialchars($_SESSION['success']) ?>

                </div>

                <?php unset($_SESSION['success']); ?>

            <?php endif; ?>

            <?php if(isset($_SESSION['error'])): ?>

                <div class="error">

                    <?= htmlspecialchars($_SESSION['error']) ?>

                </div>

                <?php unset($_SESSION['error']); ?>

            <?php endif; ?>

            <div class="card">

                <div class="report-tabs" role="tablist">
                    <button
                        type="button"
                        class="report-tab active"
                        data-tab="lostReports"
                        role="tab">
                        Lost Reports
                    </button>

                    <button
                        type="button"
                        class="report-tab"
                        data-tab="foundReports"
                        role="tab">
                        Found Reports
                    </button>
                </div>

                <div
                    id="lostReports"
                    class="report-tab-panel active">

                <?php if(empty($lostReports)): ?>

                    <p>No reports submitted yet.</p>

                <?php else: ?>

                    <table class="table">

                        <thead>

                            <tr>

                                <th>Report No.</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Location</th>
                                <th>Date Lost</th>
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $count = 1; ?>

                        <?php foreach($lostReports as $report): ?>

                            <tr>

                                <td>
                                    <?= $count++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['item_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['category']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['location_lost']) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime($report['date_lost'])
                                    ) ?>
                                </td>

                                <td>

                                    <?php if($report['status'] === 'claimed'): ?>

                                        <span class="badge badge-success">
                                            Claimed
                                        </span>

                                    <?php elseif($report['status'] === 'matched'): ?>

                                        <span class="badge badge-warning">
                                            Matched
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-danger">
                                            Pending
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <div class="table-actions">

                                    <a
                                        href="/user/view-report.php?type=lost&id=<?= $report['id'] ?>&tab=lost"
                                        class="action-btn view"
                                        title="View Report"
                                        aria-label="View Report">
                                        <i class="fas fa-eye"></i>

                                    </a>

                                    <a
                                        href="/user/edit-report.php?type=lost&id=<?= $report['id'] ?>&tab=lost"
                                        class="action-btn edit-btn"
                                        title="Edit Report"
                                        aria-label="Edit Report">
                                        <i class="fas fa-edit"></i>

                                    </a>

                                    <a
                                        href="/user/delete-report.php?type=lost&id=<?= $report['id'] ?>&tab=lost"
                                        class="action-btn delete-btn"
                                        title="Delete Report"
                                        aria-label="Delete Report">
                                        <i class="fas fa-trash"></i>

                                    </a>
                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

                </div>

                <div
                    id="foundReports"
                    class="report-tab-panel">

                <?php if(empty($foundReports)): ?>

                    <p>No found reports submitted yet.</p>

                <?php else: ?>

                    <table class="table">

                        <thead>

                            <tr>

                                <th>Report No.</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Location Found</th>
                                <th>Date Found</th>
                                <th>Status</th>
                                <th>Actions</th>

                            </tr>

                        </thead>

                        <tbody>

                        <?php $count = 1; ?>

                        <?php foreach($foundReports as $report): ?>

                            <tr>

                                <td>
                                    <?= $count++ ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['item_name']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['category']) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($report['location_found']) ?>
                                </td>

                                <td>
                                    <?= date(
                                        'd M Y',
                                        strtotime($report['date_found'])
                                    ) ?>
                                </td>

                                <td>

                                    <?php if($report['status'] === 'returned'): ?>

                                        <span class="badge badge-success">
                                            Returned
                                        </span>

                                    <?php elseif($report['status'] === 'matched'): ?>

                                        <span class="badge badge-warning">
                                            Matched
                                        </span>

                                    <?php else: ?>

                                        <span class="badge badge-danger">
                                            Pending
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>
                                    <div class="table-actions">

                                    <a
                                        href="/user/view-report.php?type=found&id=<?= $report['id'] ?>&tab=found"
                                        class="action-btn view"
                                        title="View Report"
                                        aria-label="View Report">
                                        <i class="fas fa-eye"></i>

                                    </a>

                                    <a
                                        href="/user/edit-report.php?type=found&id=<?= $report['id'] ?>&tab=found"
                                        class="action-btn edit-btn"
                                        title="Edit Report"
                                        aria-label="Edit Report">
                                        <i class="fas fa-edit"></i>

                                    </a>

                                    <a
                                        href="/user/delete-report.php?type=found&id=<?= $report['id'] ?>&tab=found"
                                        class="action-btn delete-btn"
                                        title="Delete Report"
                                        aria-label="Delete Report">
                                        <i class="fas fa-trash"></i>

                                    </a>
                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

                </div>

            </div>

            <br>

            <div class="card">

                <h2>Quick Actions</h2>

                <br>

                <div class="action-grid">

                    <a href="/user/report-lost.php"
                       class="action-btn">

                        Report Lost Item

                    </a>

                    <a href="/user/search.php"
                       class="action-btn">

                        Search Items

                    </a>

                    <a href="/user/matches.php"
                       class="action-btn">

                        View Matches

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="/assets/js/sidebar.js"></script>
<script>
function activateReportTab(tabName, updateUrl){
    const targetPanel = tabName === 'found' ? 'foundReports' : 'lostReports';

    document.querySelectorAll('.report-tab').forEach(function(item){
        item.classList.toggle('active', item.dataset.tab === targetPanel);
    });

    document.querySelectorAll('.report-tab-panel').forEach(function(panel){
        panel.classList.toggle('active', panel.id === targetPanel);
    });

    if(updateUrl){
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName === 'found' ? 'found' : 'lost');
        history.pushState({}, '', url);
    }
}

document.querySelectorAll('.report-tab').forEach(function(tab){
    tab.addEventListener('click', function(){
        const tabName = tab.dataset.tab === 'foundReports' ? 'found' : 'lost';
        activateReportTab(tabName, true);
    });
});

window.addEventListener('popstate', function(){
    const params = new URLSearchParams(window.location.search);
    activateReportTab(params.get('tab') === 'found' ? 'found' : 'lost', false);
});

document.addEventListener('DOMContentLoaded', function(){
    const params = new URLSearchParams(window.location.search);
    activateReportTab(params.get('tab') === 'found' ? 'found' : 'lost', false);
});
</script>
</body>

</html>
