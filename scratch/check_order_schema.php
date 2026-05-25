<?php
require_once 'config/config.php';
$db = Database::getInstance();
echo "ORDERS TABLE:\n";
print_r($db->query("DESCRIBE orders")->fetchAll(PDO::FETCH_ASSOC));
echo "\nORDER_ITEMS TABLE:\n";
print_r($db->query("DESCRIBE order_items")->fetchAll(PDO::FETCH_ASSOC));
echo "\nPRODUCTS TABLE:\n";
print_r($db->query("DESCRIBE products")->fetchAll(PDO::FETCH_ASSOC));
