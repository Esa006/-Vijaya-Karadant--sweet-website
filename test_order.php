<?php
require_once 'config/config.php';
$db = Database::getInstance();
$stmt = $db->query("SELECT * FROM orders WHERE id=4091");
$order = $stmt->fetch(PDO::FETCH_ASSOC);
echo "Order exists: " . ($order ? "Yes" : "No") . "\n";
if ($order) {
    print_r($order);
}

require_once 'repositories/OrderRepository.php';
$repo = new OrderRepository();
$res = $repo->getById(4091);
echo "OrderRepo getById: " . ($res ? "Yes" : "No") . "\n";
