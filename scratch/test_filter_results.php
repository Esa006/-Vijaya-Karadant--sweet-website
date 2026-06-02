<?php
require_once __DIR__ . '/../config/config.php';
require_once SERVICES_PATH . '/ComboService.php';

$comboService = new ComboService();

$categories = ['all', 'karadant', 'namkeen', 'laddu', 'gifting', 'mixed'];

echo "=== COMBO FILTER RESULTS BY CATEGORY ===\n";
foreach ($categories as $cat) {
    if ($cat === 'all') {
        $results = $comboService->getAllCombos();
    } else {
        $results = $comboService->getCombosByCategory($cat);
    }
    echo "Category: " . str_pad($cat, 10) . " | Count: " . count($results) . "\n";
    
    // Print first 2 combo names for sanity check
    $names = [];
    for ($i = 0; $i < min(2, count($results)); $i++) {
        $names[] = "#" . $results[$i]['id'] . " " . $results[$i]['name'];
    }
    if (!empty($names)) {
        echo "  Examples: " . implode(" | ", $names) . "\n";
    }
}
