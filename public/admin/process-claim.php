<?php

session_save_path(__DIR__ . '/../../sessions');
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: /login.php");
    exit;
}

if($_SESSION['role'] !== 'admin')
{
    header("Location: /user/dashboard.php");
    exit;
}

require_once __DIR__ . '/../../backend/models/Claim.php';
require_once __DIR__ . '/../../backend/config/database.php';

if(
    !isset($_GET['id']) ||
    !isset($_GET['action'])
)
{
    header("Location: /admin/claims.php");
    exit;
}

$id = (int)$_GET['id'];
$action = $_GET['action'];

if(
    $action !== 'approve' &&
    $action !== 'reject'
)
{
    header("Location: /admin/claims.php");
    exit;
}

$status =
    ($action === 'approve')
    ? 'approved'
    : 'rejected';

$claimModel = new Claim();

$claim = $claimModel->getClaimById($id);

if(!$claim)
{
    die("Claim not found.");
}

/*
|--------------------------------------------------------------------------
| Update Claim Status
|--------------------------------------------------------------------------
*/

$claimModel->updateStatus(
    $id,
    $status
);

$db = new Database();
$conn = $db->getConnection();

/*
|--------------------------------------------------------------------------
| Update Match Status
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "UPDATE matches
     SET status = :status
     WHERE id = :match_id"
);

$stmt->execute([
    ':status'   => $status,
    ':match_id' => $claim['match_id']
]);

/*
|--------------------------------------------------------------------------
| Notification
|--------------------------------------------------------------------------
*/

$message =
    ($status === 'approved')
    ? 'Your claim has been approved.'
    : 'Your claim has been rejected.';

$claimModel->createNotification(
    $claim['user_id'],
    $message
);

header("Location: /admin/claims.php");
exit;