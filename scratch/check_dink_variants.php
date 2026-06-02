<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();

echo "=== VARIANTS FOR DINK LADDU (ID: 1009) ===\n";
$stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = 1009");
$stmt->execute();
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($variants as $v) {
    echo sprintf("ID: %d | Weight: %s | Label: %s | Price: %.2f | Stock: %d\n",
        $v['id'], $v['weight'], $v['label'], $v['price'], $v['stock']);
}

echo "\n=== VARIANTS FOR DINK KARADANT (ID: 1005) ===\n";
$stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = 1005");
$stmt->execute();
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($variants as $v) {
    echo sprintf("ID: %d | Weight: %s | Label: %s | Price: %.2f | Stock: %d\n",
        $v['id'], $v['weight'], $v['label'], $v['price'], $v['stock']);
}
