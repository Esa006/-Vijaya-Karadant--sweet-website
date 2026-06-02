<?php
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance();
$stmt = $db->query("SELECT id, name, slug FROM products ORDER BY id");
echo "=== ALL PRODUCTS ===\n";
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | Name: {$r['name']} | Slug: {$r['slug']}\n";
}
