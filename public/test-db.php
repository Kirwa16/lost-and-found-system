<?php

require_once '../backend/config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "Database Connected Successfully";
