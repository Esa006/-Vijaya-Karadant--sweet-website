<?php
require_once 'config/config.php';
$db = Database::getInstance();
$stmt = $db->query("SELECT id, order_number, total_amount, created_at FROM orders ORDER BY id DESC LIMIT 5");
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
print_r($orders);
