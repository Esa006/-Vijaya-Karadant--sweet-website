<?php
require_once 'config/config.php';
require_once 'config/Database.php';

try {
    $db = Database::getInstance();
    
    // 1. Show current distribution
    echo "--- CURRENT CATEGORIES ---\n";
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM combos GROUP BY category");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
    // 2. Mapping logic to the 4 target categories
    $mapping = [
        'traditional' => 'karadant',
        'festive'     => 'gifting',
        'gourmet'     => 'karadant',
        'luxury'      => 'gifting',
        'karadant'    => 'karadant',
        'classic'     => 'karadant',
        'healthy'     => 'laddu',
        'royal'       => 'gifting',
        'signature'   => 'karadant',
        'heritage'    => 'karadant',
        'laddu'       => 'laddu',
        'namkeen'     => 'namkeen',
        'mixed'       => 'gifting',
        'gifting'     => 'gifting',
        'special'     => 'karadant',
        'bestseller'  => 'karadant',
        'sweet'       => 'karadant',
        'snack'       => 'namkeen'
    ];
    
    foreach ($mapping as $old => $new) {
        $stmt = $db->prepare("UPDATE combos SET category = :new WHERE LOWER(category) = :old");
        $stmt->execute([':new' => $new, ':old' => $old]);
    }
    
    echo "\n--- UPDATED CATEGORIES ---\n";
    $stmt = $db->query("SELECT category, COUNT(*) as count FROM combos GROUP BY category");
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
    
} catch (Exception $e) {
    echo $e->getMessage();
}
