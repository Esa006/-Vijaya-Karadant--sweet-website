<?php
require_once __DIR__ . '/../config/config.php';

$db = Database::getInstance();

echo "\n=== USER ROLES ===\n";
$stmt = $db->query("SELECT ur.*, u.full_name, u.email, r.slug as role_slug FROM user_roles ur JOIN users u ON ur.user_id = u.id JOIN roles r ON ur.role_id = r.id");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
