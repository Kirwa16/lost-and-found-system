<?php

require_once '../backend/config/database.php';

$db = new Database();
$conn = $db->connect();

echo "Database Connected Successfully";
