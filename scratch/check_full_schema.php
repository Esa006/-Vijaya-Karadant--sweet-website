<?php
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/config/Database.php';

$db = Database::getInstance();
$tables = ['users', 'orders', 'order_items', 'inventory', 'products', 'categories'];

foreach ($tables as $table) {
    echo "--- $table ---\n";
    try {
        $stmt = $db->query("DESCRIBE $table");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Field']} ({$row['Type']})\n";
        }
    } catch (Exception $e) {
        echo "Error: Table $table does not exist or accessible.\n";
    }
    echo "\n";
}
