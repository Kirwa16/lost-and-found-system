<?php
/**
 * admin/generated-reports.php
 * Analytics report generation hub.
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: /user/dashboard.php");
    exit;
}

$reportType = $_GET['report_type'] ?? '';
$dateFrom   = $_GET['date_from'] ?? '';
$dateTo     = $_GET['date_to'] ?? '';
$format     = $_GET['format'] ?? 'pdf';

$reportTypes = [
    'system-summary' => 'System Summary',
    'lost-items' => 'Lost Items Report',
    'found-items' => 'Found Items Report',
    'claims' => 'Claims Report',
    'matches' => 'Matches Report',
    'recovery' => 'Recovery Report',
    'user-activity' => 'User Activity Report',
    'monthly' => 'Monthly Report',
    'yearly' => 'Yearly Report',
    'custom' => 'Custom Date Range Report'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generated Reports</title>

<link rel="stylesheet" href="/assets/css/dashboard.css">
<link rel="stylesheet" href="/assets/css/admin.css">
<link rel="stylesheet" href="/assets/css/sidebar.css">
<link rel="stylesheet" href="/assets/css/topbar.css">
<link rel="stylesheet" href="/assets/css/reports.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
.report-grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
gap:20px;
margin:25px 0;
}
.report-card{
background:#fff;
padding:20px;
border-radius:12px;
box-shadow:0 2px 10px rgba(0,0,0,.08);
}
.report-card i{
font-size:32px;
color:#2563eb;
margin-bottom:15px;
}
.generate-form{
background:#fff;
padding:25px;
border-radius:12px;
box-shadow:0 2px 10px rgba(0,0,0,.08);
}
.generate-form .row{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:18px;
margin-bottom:18px;
}
.generate-form label{
display:block;
font-weight:600;
margin-bottom:6px;
}
.generate-form input,
.generate-form select{
width:100%;
padding:10px;
border:1px solid #ddd;
border-radius:8px;
}
.generate-btn{
background:#2563eb;
color:#fff;
padding:12px 22px;
border:none;
border-radius:8px;
cursor:pointer;
}
</style>

</head>

<body>

<div class="admin-layout">

<?php include __DIR__.'/../components/sidebar.php'; ?>

<div class="main">

<?php include __DIR__.'/../components/topbar.php'; ?>

<div class="content">

<h1>Generated Reports</h1>

<p>Create printable and exportable management reports.</p>

<div class="report-grid">

<?php foreach($reportTypes as $key=>$title): ?>

<div class="report-card">
<i class="fas fa-chart-bar"></i>
<h3><?= htmlspecialchars($title) ?></h3>
<p>Generate and export this report.</p>
</div>

<?php endforeach; ?>

</div>

<div class="generate-form">

<h2>Generate Report</h2>

<form method="GET">

<div class="row">

<div>
<label>Report Type</label>
<select name="report_type" required>
<option value="">Select Report</option>
<?php foreach($reportTypes as $key=>$title): ?>
<option value="<?= $key ?>" <?= $reportType==$key?'selected':'' ?>>
<?= htmlspecialchars($title) ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div>
<label>Date From</label>
<input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
</div>

<div>
<label>Date To</label>
<input type="date" name="date_to" value="<?= htmlspecialchars($dateTo) ?>">
</div>

<div>
<label>Export Format</label>
<select name="format">
<option value="pdf">PDF</option>
<option value="csv">CSV</option>
<option value="xlsx">Excel</option>
<option value="print">Print</option>
</select>
</div>

</div>

<button class="generate-btn" type="submit">
<i class="fas fa-file-export"></i>
Generate Report
</button>

</form>

<?php if($reportType): ?>

<hr style="margin:30px 0;">

<h2>Preview</h2>

<table class="table">

<tr><th>Report</th><td><?= htmlspecialchars($reportTypes[$reportType] ?? '') ?></td></tr>
<tr><th>Date From</th><td><?= htmlspecialchars($dateFrom ?: 'Not specified') ?></td></tr>
<tr><th>Date To</th><td><?= htmlspecialchars($dateTo ?: 'Not specified') ?></td></tr>
<tr><th>Format</th><td><?= strtoupper(htmlspecialchars($format)) ?></td></tr>

</table>

<p style="margin-top:20px;">
This page is ready to be connected to your report generation logic. Based on the selected report type and date range, you can query the database and export the results as PDF, CSV, Excel, or a print-friendly page.
</p>

<?php endif; ?>

</div>

</div>

</div>

</div>

</body>
</html>
