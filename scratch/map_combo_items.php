<?php
require_once 'config/config.php';
require_once 'repositories/ComboRepository.php';

try {
    $repo = new ComboRepository();
    
    // Combo 11
    $repo->syncItems(11, [
        ['product_id' => 1, 'quantity' => 1],
        ['product_id' => 3, 'quantity' => 1]
    ]);
    
    // Combo 12
    $repo->syncItems(12, [
        ['product_id' => 2, 'quantity' => 1],
        ['product_id' => 1009, 'quantity' => 1]
    ]);
    
    // Combo 13
    $repo->syncItems(13, [
        ['product_id' => 1001, 'quantity' => 1],
        ['product_id' => 1002, 'quantity' => 1]
    ]);
    
    // Combo 14
    $repo->syncItems(14, [
        ['product_id' => 1003, 'quantity' => 1],
        ['product_id' => 1004, 'quantity' => 1]
    ]);
    
    echo "Combo items mapped successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
