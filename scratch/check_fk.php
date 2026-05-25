<?php
require_once 'config/config.php';
$db = Database::getInstance();
$stmt = $db->query("SHOW CREATE TABLE order_items");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
