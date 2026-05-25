<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    
    echo "--- Database Table Audit ---\n";
    
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        $countStmt = $db->query("SELECT COUNT(*) FROM `$table` ");
        $count = $countStmt->fetchColumn();
        echo "[OK] $table ($count rows)\n";
    }

    // List of expected tables from schema.sql analysis
    $expected = [
        'activity_logs', 'addresses', 'admin_notifications', 'analytics_events', 'audit_logs',
        'cart', 'cart_items', 'carts', 'categories', 'company_info', 'coupon_usages', 'coupons',
        'customer_activity', 'customer_addresses', 'customer_metrics', 'customer_notes',
        'customer_profiles', 'customer_tags', 'customers', 'delivery_tracking', 'failed_orders',
        'inventory', 'invoices', 'news_updates', 'order_items', 'orders', 'product_images',
        'product_variants', 'products', 'promotions', 'refunds', 'reviews', 'shipments',
        'site_settings', 'stock_activity', 'subcategories', 'users'
    ];

    echo "\n--- Missing Tables Check ---\n";
    $missing = array_diff($expected, $tables);
    if (empty($missing)) {
        echo "All expected core tables are present.\n";
    } else {
        foreach ($missing as $m) {
            echo "[MISSING] $m\n";
        }
    }

    echo "\n--- Table Specific Column Checks ---\n";
    // Check order_items for our recent fix
    $stmt = $db->query("DESCRIBE order_items");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('variant_id', $columns)) {
        echo "[OK] order_items.variant_id exists.\n";
    } else {
        echo "[WARN] order_items.variant_id is missing!\n";
    }

    if (in_array('price', $columns)) {
        echo "[OK] order_items.price exists.\n";
    } else {
        echo "[WARN] order_items.price is missing!\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
