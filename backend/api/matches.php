<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, PUT, OPTIONS");
require_once __DIR__ . '/../controllers/MatchController.php';

$controller = new MatchController();
$controller->handleRequest();
?>