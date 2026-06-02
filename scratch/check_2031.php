<?php
require_once __DIR__ . '/../config/config.php';
$db = Database::getInstance();
$r = $db->query("SELECT * FROM products WHERE id = 2031")->fetch(PDO::FETCH_ASSOC);
print_r($r);
