<?php

session_start();

require_once __DIR__ . '/../models/Claim.php';

if(isset($_POST['submit_claim']))
{
    $claim = new Claim();

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

