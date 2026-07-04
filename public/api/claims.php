<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
require_once __DIR__ . '/../../backend/controllers/ClaimController.php';

$controller = new ClaimController();
$controller->handleRequest();
?>
