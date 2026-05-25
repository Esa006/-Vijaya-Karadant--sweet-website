<?php
require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

class SyncService extends ProductService {
    public function doSync() {
        $db = Database::getInstance();
        $fallbacks = $this->fallbackProducts();
        $db->beginTransaction();
        try {
            foreach ($fallbacks as $slug => $product) {
                $id = $product['id'] ?? null;
                if (!$id) continue;
                
                $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
                $stmt->execute([$id]);
                if ($stmt->fetchColumn()) continue;
                
                echo "Inserting missing fallback product: {$product['name']} (ID: $id)\n";
                
                $catId = $product['category_id'] ?? 1;
                $stmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
                $stmt->execute([$catId]);
                if (!$stmt->fetchColumn()) {
                    $stmt = $db->prepare("INSERT INTO categories (id, name, slug) VALUES (?, ?, ?)");
                    $stmt->execute([$catId, $product['category_slug'] ?? 'cat', $product['category_slug'] ?? 'cat']);
                }
                
                $stmt = $db->prepare("INSERT INTO products (id, category_id, name, slug, short_description, base_price, sale_price, stock_quantity, status) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'published')");
                $stmt->execute([
                    $id,
                    $catId,
                    $product['name'],
                    $product['slug'],
                    $product['short_description'] ?? '',
                    $product['base_price'] ?? 0,
                    $product['sale_price'] ?? null,
                    $product['stock_quantity'] ?? 100
                ]);
                
                // Don't insert into product_variants unless we explicitly need it for combos, wait, we do need it for combos!
                // Some fallback products are used as child products in combos. The OrderRepository checks product_variants for combo children.
                $stmt = $db->prepare("INSERT INTO product_variants (product_id, weight, label, price, stock) VALUES (?, '500g', '500g Standard Pack', ?, ?)");
                $stmt->execute([$id, $product['sale_price'] ?? $product['base_price'] ?? 0, 100]);
            }
            $db->commit();
            echo "Sync complete.\n";
        } catch (Exception $e) {
            $db->rollBack();
            echo "Error: " . $e->getMessage() . "\n";
        }
    }
}

$sync = new SyncService();
$sync->doSync();
