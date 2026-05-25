<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../repositories/OrderRepository.php';

$repo = new OrderRepository();

try {
    $repo->beginTransaction();

    // 1. Check current stock of product 2029
    $product = $repo->fetchOne("SELECT stock_quantity FROM products WHERE id = 2029");
    echo "Current Stock of Prod 2029: " . $product['stock_quantity'] . "\n";

    // 2. Insert fake pending order 15 minutes ago
    $orderSql = "INSERT INTO orders (user_id, order_number, total_amount, subtotal, status, created_at)
                 VALUES (1, 'SW-FAKE-TEST', 500, 500, 'pending', DATE_SUB(NOW(), INTERVAL 15 MINUTE))";
    $orderId = $repo->executeInsert($orderSql);

    // 3. Insert order item
    $repo->execute("INSERT INTO order_items (order_id, product_id, item_type, quantity, price, price_at_time) 
                    VALUES ($orderId, 2029, 'product', 5, 100, 100)");

    // 4. Deduct stock manually to simulate what checkout does
    $repo->execute("UPDATE products SET stock_quantity = stock_quantity - 5 WHERE id = 2029");

    $repo->commit();

    echo "Successfully created fake abandoned order #$orderId and deducted 5 stock.\n";
    echo "Wait for the background worker to expire it and restore stock!\n";

} catch (Exception $e) {
    $repo->rollBack();
    echo "Error: " . $e->getMessage();
}
