<?php
require_once 'config/config.php';
use App\Core\Database;

try {
    $stmt = Database::query("DESCRIBE permissions");
    echo "Table 'permissions' structure:\n";
    while ($row = $stmt->fetch()) {
        echo " - {$row['Field']} ({$row['Type']})\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
