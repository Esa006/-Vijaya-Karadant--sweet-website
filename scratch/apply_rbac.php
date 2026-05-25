<?php
require_once dirname(__DIR__) . '/config/config.php';
use App\Core\Database;

try {
    $sqlFile = dirname(__DIR__) . '/database/create_rbac_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: " . $sqlFile);
    }
    $sql = file_get_contents($sqlFile);
    
    // Split by semicolon
    $queries = explode(';', $sql);
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (!empty($query)) {
            Database::getConnection()->exec($query);
        }
    }
    
    echo "RBAC tables created and seeded successfully.\n";
} catch (Exception $e) {
    echo "Error applying RBAC migration: " . $e->getMessage() . "\n";
}
