<?php
require_once 'config/config.php';
$db = Database::getInstance();
$stmt = $db->query("SELECT id, name FROM products");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
