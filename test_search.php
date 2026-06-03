<?php
require_once 'config/config.php';
require_once 'services/ProductService.php';

$service = new ProductService();
$results = $service->getFilteredProducts(['search' => 'Dry Fruit Honey']);

echo "Results count: " . count($results) . "\n";
foreach ($results as $r) {
    echo "ID: " . ($r['id'] ?? 'null') . ", Name: " . ($r['name'] ?? 'null') . "\n";
}

$results2 = $service->getFilteredProducts(['search' => 'Dry Fruit']);
echo "Results count (Dry Fruit): " . count($results2) . "\n";
foreach ($results2 as $r) {
    echo "ID: " . ($r['id'] ?? 'null') . ", Name: " . ($r['name'] ?? 'null') . "\n";
}
