<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../backend/controllers/ClaimController.php';

$controller = new ClaimController();

$claims = $controller->index();

echo "<pre>";
print_r($claims);
echo "</pre>";