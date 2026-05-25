<?php
require_once 'config/config.php';
use App\Core\Database;

try {
    $tables = ['roles', 'permissions', 'role_permissions', 'user_roles', 'user_permissions'];
    foreach ($tables as $table) {
        $stmt = Database::query("SHOW TABLES LIKE ?", [$table]);
        if ($stmt->fetch()) {
            echo "Table '$table' exists.\n";
            $count = Database::query("SELECT COUNT(*) FROM $table")->fetchColumn();
            echo " - Row count: $count\n";
        } else {
            echo "Table '$table' DOES NOT exist.\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
