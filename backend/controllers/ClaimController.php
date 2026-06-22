<?php

session_start();

require_once __DIR__ . '/../models/Claim.php';

$claim = new Claim();

if(isset($_POST['submit_claim']))
{
    $claim->createClaim(
        $_SESSION['user_id'],
        $_POST['match_id'],
        trim($_POST['claim_message'])
    );

    $_SESSION['success'] =
        "Claim submitted successfully.";

    header("Location: /user/claims.php");
    exit;
}