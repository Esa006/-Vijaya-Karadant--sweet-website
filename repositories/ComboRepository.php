<?php
/**
 * Sweets Website
 * =============================================================
 * File: repositories/ComboRepository.php
 * Description: Data Access for Combos
 * =============================================================
 */

require_once __DIR__ . '/BaseRepository.php';

class ComboRepository extends BaseRepository {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all combos with their items (avoids N+1)
     */
    public function getAllCombos(bool $activeOnly = true): array {
        $where = $activeOnly ? "WHERE c.is_active = 1" : "";
        $sql = "SELECT c.*, 
                       ci.product_id, ci.quantity, 
                       p.name as product_name, p.slug as product_slug, 
                       p.base_price, p.sale_price, p.stock_quantity, p.status as product_status,
                       pi.image_path as product_image
                FROM combos c
                LEFT JOIN combo_items ci ON c.id = ci.combo_id
                LEFT JOIN products p ON ci.product_id = p.id AND p.deleted_at IS NULL
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                $where
                ORDER BY c.created_at DESC";
        
        $rows = $this->fetchAll($sql);
        return $this->formatCombos($rows);
    }

    /**
     * Get combos by category
     */
    public function getCombosByCategory(string $category): array {
        $sql = "SELECT c.*, 
                       ci.product_id, ci.quantity, 
                       p.name as product_name, p.slug as product_slug, 
                       p.base_price, p.sale_price, p.stock_quantity, p.status as product_status,
                       pi.image_path as product_image
                FROM combos c
                LEFT JOIN combo_items ci ON c.id = ci.combo_id
                LEFT JOIN products p ON ci.product_id = p.id AND p.deleted_at IS NULL
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                WHERE c.is_active = 1 AND LOWER(c.category) = :category
                ORDER BY c.created_at DESC";
        
        $rows = $this->fetchAll($sql, [':category' => strtolower($category)]);
        return $this->formatCombos($rows);
    }

    /**
     * Get a single combo by ID
     */
    public function getById(int $id): ?array {
        $sql = "SELECT c.*, 
                       ci.product_id, ci.quantity, 
                       p.name as product_name, p.slug as product_slug, 
                       p.base_price, p.sale_price, p.stock_quantity, p.status as product_status,
                       pi.image_path as product_image
                FROM combos c
                LEFT JOIN combo_items ci ON c.id = ci.combo_id
                LEFT JOIN products p ON ci.product_id = p.id AND p.deleted_at IS NULL
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                WHERE c.id = :id AND c.is_active = 1";
        
        $rows = $this->fetchAll($sql, [':id' => $id]);
        if (empty($rows)) return null;
        
        $combos = $this->formatCombos($rows);
        return reset($combos) ?: null;
    }

    /**
     * Get a single combo by Slug
     */
    public function getBySlug(string $slug): ?array {
        $sql = "SELECT c.*, 
                       ci.product_id, ci.quantity, 
                       p.name as product_name, p.slug as product_slug, 
                       p.base_price, p.sale_price, p.stock_quantity, p.status as product_status,
                       pi.image_path as product_image
                FROM combos c
                LEFT JOIN combo_items ci ON c.id = ci.combo_id
                LEFT JOIN products p ON ci.product_id = p.id AND p.deleted_at IS NULL
                LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                WHERE c.slug = :slug AND c.is_active = 1";
        
        $rows = $this->fetchAll($sql, [':slug' => $slug]);
        if (empty($rows)) return null;
        
        $combos = $this->formatCombos($rows);
        return reset($combos) ?: null;
    }

    /**
     * Group flat rows into structured combo arrays
     */
    private function formatCombos(array $rows): array {
        $combos = [];
        foreach ($rows as $row) {
            $comboId = $row['id'];
            if (!isset($combos[$comboId])) {
                $combos[$comboId] = [
                    'id'          => $row['id'],
                    'name'        => $row['name'],
                    'slug'        => $row['slug'],
                    'description' => $row['description'],
                    'category'    => $row['category'],
                    'price'       => $row['price'],
                    'image'       => $row['image'], // resolved below
                    'is_active'   => $row['is_active'],
                    'created_at'  => $row['created_at'] ?? null,
                    'item_count'  => 0,
                    'items'       => []
                ];
            }
            if ($row['product_id'] && $row['product_name'] !== null) {
                $combos[$comboId]['items'][] = [
                    'product_id' => $row['product_id'],
                    'quantity'   => $row['quantity'],
                    'name'       => $row['product_name'],
                    'slug'       => $row['product_slug'],
                    'base_price' => $row['base_price'],
                    'sale_price' => $row['sale_price'],
                    'stock'      => $row['stock_quantity'],
                    'status'     => $row['product_status'],
                    'image'      => $row['product_image']
                ];
                $combos[$comboId]['item_count']++;
            }
        }

        // Resolve combo image: use combo's own image; if missing, fall back to first item's product image
        foreach ($combos as &$combo) {
            if (empty($combo['image']) && !empty($combo['items'])) {
                foreach ($combo['items'] as $item) {
                    if (!empty($item['image'])) {
                        $combo['image'] = $item['image'];
                        break;
                    }
                }
            }
            // Final fallback to a known placeholder
            if (empty($combo['image'])) {
                $combo['image'] = 'assets/images/placeholders/product-placeholder.png';
            }
        }
        unset($combo);

        return array_values($combos);
    }

    // ── Admin CRUD Methods ──────────────────────────────────────────────────

    /**
     * Create a new combo record
     */
    public function create(array $data): int {
        $sql = "INSERT INTO combos (name, slug, description, category, price, image, is_active, created_at)
                VALUES (:name, :slug, :description, :category, :price, :image, :is_active, NOW())";
        $this->execute($sql, [
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':description' => $data['description'] ?? null,
            ':category'    => $data['category'] ?? null,
            ':price'       => $data['price'] > 0 ? $data['price'] : null,
            ':image'       => $data['image'] ?? null,
            ':is_active'   => $data['is_active'] ?? 1,
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing combo record
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = [':id' => $id];

        $allowed = ['name', 'slug', 'description', 'category', 'price', 'image', 'is_active'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $fields[] = "$col = :$col";
                $params[":$col"] = $data[$col];
            }
        }

        if (empty($fields)) return false;

        $sql = "UPDATE combos SET " . implode(', ', $fields) . " WHERE id = :id";
        $this->execute($sql, $params);
        return true;
    }

    /**
     * Soft-delete a combo (set is_active = 0)
     */
    public function delete(int $id): bool {
        $this->execute("UPDATE combos SET is_active = 0 WHERE id = :id", [':id' => $id]);
        return true;
    }

    /**
     * Sync combo items: wipe existing and re-insert
     */
    public function syncItems(int $comboId, array $items): void {
        $this->execute("DELETE FROM combo_items WHERE combo_id = :combo_id", [':combo_id' => $comboId]);

        if (empty($items)) return;

        $stmt = $this->db->prepare(
            "INSERT INTO combo_items (combo_id, product_id, quantity) VALUES (:combo_id, :product_id, :quantity)"
        );
        foreach ($items as $item) {
            $productId = (int)($item['product_id'] ?? 0);
            $qty       = (int)($item['quantity'] ?? 1);
            if ($productId > 0 && $qty > 0) {
                $stmt->execute([
                    ':combo_id'   => $comboId,
                    ':product_id' => $productId,
                    ':quantity'   => $qty,
                ]);
            }
        }
    }

    /**
     * Check if a slug is already used (optionally excluding one ID)
     */
    public function slugExists(string $slug, int $excludeId = 0): bool {
        $sql    = "SELECT COUNT(*) FROM combos WHERE slug = :slug AND id != :id";
        $result = $this->fetchOne($sql, [':slug' => $slug, ':id' => $excludeId]);
        return (int)($result['COUNT(*)'] ?? 0) > 0;
    }

    /**
     * Quick stats for admin dashboard
     */
    public function getStats(): array {
        $total  = $this->fetchOne("SELECT COUNT(*) AS cnt FROM combos")['cnt'] ?? 0;
        $active = $this->fetchOne("SELECT COUNT(*) AS cnt FROM combos WHERE is_active = 1")['cnt'] ?? 0;
        $items  = $this->fetchOne("SELECT COUNT(*) AS cnt FROM combo_items")['cnt'] ?? 0;
        return [
            'total'  => (int)$total,
            'active' => (int)$active,
            'items'  => (int)$items,
        ];
    }

    /**
     * Get all combos (including inactive) for admin listing
     */
    public function getAllCombosAdmin(): array {
        return $this->getAllCombos(false);
    }

    // ── Gallery / Multi-Image Methods ────────────────────────────────────────

    /**
     * Fetch all gallery images for a combo, ordered by sort_order then id
     */
    public function getImagesForCombo(int $comboId): array {
        $sql = "SELECT id, combo_id, image_path, is_primary, sort_order, created_at
                FROM combo_images
                WHERE combo_id = :combo_id
                ORDER BY sort_order ASC, id ASC";
        return $this->fetchAll($sql, [':combo_id' => $comboId]);
    }

    /**
     * Insert a new gallery image; if is_primary, clear existing primary first
     */
    public function addImage(int $comboId, string $imagePath, bool $isPrimary = false, int $sortOrder = 0): int {
        if ($isPrimary) {
            $this->execute(
                "UPDATE combo_images SET is_primary = 0 WHERE combo_id = :combo_id",
                [':combo_id' => $comboId]
            );
        }
        $this->execute(
            "INSERT INTO combo_images (combo_id, image_path, is_primary, sort_order)
             VALUES (:combo_id, :image_path, :is_primary, :sort_order)",
            [
                ':combo_id'   => $comboId,
                ':image_path' => $imagePath,
                ':is_primary' => $isPrimary ? 1 : 0,
                ':sort_order' => $sortOrder,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    /**
     * Delete a single gallery image by its own id
     */
    public function deleteImage(int $imageId): bool {
        $row = $this->fetchOne(
            "SELECT combo_id, is_primary FROM combo_images WHERE id = :id",
            [':id' => $imageId]
        );
        if (!$row) return false;

        $this->execute("DELETE FROM combo_images WHERE id = :id", [':id' => $imageId]);

        // If the deleted image was primary, promote the next image automatically
        if ($row['is_primary']) {
            $this->execute(
                "UPDATE combo_images SET is_primary = 1
                 WHERE combo_id = :combo_id
                 ORDER BY sort_order ASC, id ASC
                 LIMIT 1",
                [':combo_id' => $row['combo_id']]
            );
            // Sync combos.image
            $next = $this->fetchOne(
                "SELECT image_path FROM combo_images WHERE combo_id = :combo_id AND is_primary = 1",
                [':combo_id' => $row['combo_id']]
            );
            $this->execute(
                "UPDATE combos SET image = :image WHERE id = :id",
                [':image' => $next['image_path'] ?? null, ':id' => $row['combo_id']]
            );
        }
        return true;
    }

    /**
     * Set a specific gallery image as primary (clears others, syncs combos.image)
     */
    public function setPrimaryImage(int $comboId, int $imageId): bool {
        $this->execute(
            "UPDATE combo_images SET is_primary = 0 WHERE combo_id = :combo_id",
            [':combo_id' => $comboId]
        );
        $this->execute(
            "UPDATE combo_images SET is_primary = 1 WHERE id = :id AND combo_id = :combo_id",
            [':id' => $imageId, ':combo_id' => $comboId]
        );
        // Keep combos.image in sync for backward compatibility
        $row = $this->fetchOne(
            "SELECT image_path FROM combo_images WHERE id = :id",
            [':id' => $imageId]
        );
        if ($row) {
            $this->execute(
                "UPDATE combos SET image = :image WHERE id = :id",
                [':image' => $row['image_path'], ':id' => $comboId]
            );
        }
        return true;
    }

    /**
     * Count images for a combo
     */
    public function countImages(int $comboId): int {
        $row = $this->fetchOne(
            "SELECT COUNT(*) AS cnt FROM combo_images WHERE combo_id = :combo_id",
            [':combo_id' => $comboId]
        );
        return (int)($row['cnt'] ?? 0);
    }
}
