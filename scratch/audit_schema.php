<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

try {
    $db = Database::getInstance();
    
    $tables = ['inventory', 'stock_activity', 'products', 'users', 'orders', 'order_items'];
    
    foreach ($tables as $table) {
        echo "\nTable: $table\n";
        try {
            $stmt = $db->query("DESCRIBE $table");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo sprintf("  %-15s | %-15s | %s\n", $row['Field'], $row['Type'], $row['Null'] === 'YES' ? 'NULL' : 'NOT NULL');
            }
        } catch (Exception $e) {
            echo "  [ERROR] Table missing or inaccessible: " . $e->getMessage() . "\n";
        }
    }
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
