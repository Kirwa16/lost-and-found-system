<?php

require_once "backend/controllers/ClaimController.php";

$controller = new ClaimController();

echo "<pre>";

print_r($controller->index());

echo "</pre>";