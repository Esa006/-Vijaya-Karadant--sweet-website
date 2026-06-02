<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

$ids = [1040, 1041, 1042, 1043];
foreach ($ids as $id) {
    echo "Product #{$id} variants:\n";
    $vars = $db->query("SELECT * FROM product_variants WHERE product_id = {$id}")->fetchAll(PDO::FETCH_ASSOC);
    print_r($vars);
}
