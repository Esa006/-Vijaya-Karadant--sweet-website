<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/Database.php';

$db = Database::getInstance();

$combos = [
    [
        'name' => 'Mega Sweet Combo',
        'slug' => 'mega-sweet-combo',
        'description' => 'A delightful mix of our premium Karadant and Laddu.',
        'category' => 'karadant',
        'price' => 1200.00,
        'image' => 'assets/images/combos/karadant-combo.png',
        'items' => [
            ['product_id' => 1001, 'quantity' => 2],
            ['product_id' => 1009, 'quantity' => 1]
        ]
    ],
    [
        'name' => 'Festive Namkeen Mix',
        'slug' => 'festive-namkeen-mix',
        'description' => 'Spicy and crunchy namkeen assortment.',
        'category' => 'namkeen',
        'price' => 450.00,
        'image' => 'assets/images/combos/namkeen-combo.png',
        'items' => [
            ['product_id' => 2, 'quantity' => 1],
            ['product_id' => 1010, 'quantity' => 2]
        ]
    ],
    [
        'name' => 'Premium Laddu Box',
        'slug' => 'premium-laddu-box',
        'description' => 'The best selection of assorted laddus.',
        'category' => 'laddu',
        'price' => 800.00,
        'image' => 'assets/images/combos/laddu-combo.png',
        'items' => [
            ['product_id' => 3, 'quantity' => 2],
            ['product_id' => 1009, 'quantity' => 2]
        ]
    ],
    [
        'name' => 'Family Festival Pack',
        'slug' => 'family-festival-pack',
        'description' => 'Something for everyone in the family.',
        'category' => 'karadant',
        'price' => 2500.00,
        'image' => 'assets/images/combos/family-combo.png',
        'items' => [
            ['product_id' => 1001, 'quantity' => 3],
            ['product_id' => 3, 'quantity' => 2],
            ['product_id' => 2, 'quantity' => 1]
        ]
    ],
    [
        'name' => 'Classic Karadant Pair',
        'slug' => 'classic-karadant-pair',
        'description' => 'Two of our best selling Karadants.',
        'category' => 'karadant',
        'price' => 1100.00,
        'image' => 'assets/images/combos/classic-karadant.png',
        'items' => [
            ['product_id' => 1001, 'quantity' => 1],
            ['product_id' => 1002, 'quantity' => 1]
        ]
    ],
    [
        'name' => 'Healthy Bites',
        'slug' => 'healthy-bites',
        'description' => 'Nutritious Ragi Laddu and Dink Karadant.',
        'category' => 'karadant',
        'price' => 900.00,
        'image' => 'assets/images/combos/healthy-bites.png',
        'items' => [
            ['product_id' => 1010, 'quantity' => 2],
            ['product_id' => 1005, 'quantity' => 1]
        ]
    ],
    [
        'name' => 'Ultimate Gift Box',
        'slug' => 'ultimate-gift-box',
        'description' => 'The perfect gift for any occasion.',
        'category' => 'gifting',
        'price' => 3000.00,
        'image' => 'assets/images/combos/gifting-combo.png',
        'items' => [
            ['product_id' => 1003, 'quantity' => 2],
            ['product_id' => 1004, 'quantity' => 2]
        ]
    ],
    [
        'name' => 'Mini Snack Pack',
        'slug' => 'mini-snack-pack',
        'description' => 'A small pack of joy.',
        'category' => 'namkeen',
        'price' => 300.00,
        'image' => 'assets/images/combos/mini-snack.png',
        'items' => [
            ['product_id' => 1009, 'quantity' => 1],
            ['product_id' => 2, 'quantity' => 1]
        ]
    ]
];

foreach ($combos as $comboData) {
    // Check if exists
    $stmt = $db->prepare("SELECT id FROM combos WHERE slug = ?");
    $stmt->execute([$comboData['slug']]);
    $existingId = $stmt->fetchColumn();

    if ($existingId) {
        // Update combo items (simple approach: delete and recreate)
        $db->prepare("DELETE FROM combo_items WHERE combo_id = ?")->execute([$existingId]);
        $comboId = $existingId;
        // Also update price, category, etc.
        $db->prepare("UPDATE combos SET price = ?, category = ?, name = ?, description = ?, image = ? WHERE id = ?")
           ->execute([$comboData['price'], $comboData['category'], $comboData['name'], $comboData['description'], $comboData['image'], $existingId]);
    } else {
        $stmt = $db->prepare("INSERT INTO combos (name, slug, description, category, price, image) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $comboData['name'], 
            $comboData['slug'], 
            $comboData['description'], 
            $comboData['category'], 
            $comboData['price'], 
            $comboData['image']
        ]);
        $comboId = $db->lastInsertId();
    }

    foreach ($comboData['items'] as $item) {
        $stmt = $db->prepare("INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (?, ?, ?)");
        $stmt->execute([$comboId, $item['product_id'], $item['quantity']]);
    }
}

echo "Seeded " . count($combos) . " combos successfully.\n";
