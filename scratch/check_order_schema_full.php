<?php
require_once 'config/config.php';
$db = Database::getInstance();
echo "ALL COLUMNS IN ORDERS:\n";
$cols = $db->query("DESCRIBE orders")->fetchAll(PDO::FETCH_ASSOC);
foreach($cols as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";
