<?php
/**
 * Sweets Website
 * =============================================================
 * File: ProductRepository.php
 * Description: Data access layer for Products and Categories
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.2.0
 * =============================================================
 */

require_once 'BaseRepository.php';

class ProductRepository extends BaseRepository {

    private function hasTable(string $table): bool {
        return in_array($table, ['products', 'categories', 'product_images', 'product_variants', 'inventory']);
    }

    private function hasColumn(string $table, string $column): bool {
        if ($table === 'products' && $column === 'featured') return true;
        return false;
    }
    private function supportsInventory(): bool {
        return true;
    }

    private function supportsProductImages(): bool {
        return true;
    }

    private function supportsProductImagePath(): bool {
        return false;
    }

    private function supportsSoftDeletes(): bool {
        return true;
    }

    private function softDeleteClause(string $prefix = 'p', bool $asWhere = false): string {
        if (!$this->supportsSoftDeletes()) {
            return '';
        }

        $column = $prefix !== '' ? "{$prefix}.deleted_at" : 'deleted_at';
        return $asWhere ? " WHERE {$column} IS NULL" : " AND {$column} IS NULL";
    }

    private function getStockSelector(): string {
        return 'COALESCE(i.stock, p.stock_quantity, 0) as stock_quantity';
    }

    private function getImageSelector(): string {
        if ($this->supportsProductImages()) {
            if ($this->supportsProductImagePath()) {
                return "COALESCE(pi.image_path, p.image_path) as image_path";
            }
            return "pi.image_path as image_path";
        }

        if ($this->supportsProductImagePath()) {
            return "p.image_path as image_path";
        }

        return "NULL as image_path";
    }

    private function getImageJoin(): string {
        if ($this->supportsProductImages()) {
            return "LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1";
        }
        return "";
    }

    private function getInventoryJoin(): string {
        if ($this->supportsInventory()) {
            return "LEFT JOIN inventory i ON p.id = i.product_id";
        }
        return "";
    }

    /**
     * Get all products with category names and stock
     */
    public function getAllProducts(int $limit = 100): array {
        $stockSelect = $this->getStockSelector();
        $imageSelect = $this->getImageSelector();
        $inventoryJoin = $this->getInventoryJoin();
        $imageJoin = $this->getImageJoin();

        $softDeleteWhere = $this->softDeleteClause('p', true);

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                       s.name as subcategory_name, s.slug as subcategory_slug,
                       pc.name as parent_category_name, pc.slug as parent_category_slug,
                       {$stockSelect}, {$imageSelect} 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN subcategories s ON p.subcategory_id = s.id
                LEFT JOIN categories pc ON c.parent_id = pc.id
                {$inventoryJoin}
                {$imageJoin}
                {$softDeleteWhere}
                ORDER BY p.created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get product by ID
     */
    public function getById(int $id): ?array {
        $stockSelect = $this->getStockSelector();
        $imageSelect = $this->getImageSelector();
        $inventoryJoin = $this->getInventoryJoin();
        $imageJoin = $this->getImageJoin();

        $softDeleteCondition = $this->softDeleteClause('p');

        return $this->fetchOne(
            "SELECT p.*, c.name as category_name, c.slug as category_slug, 
                    s.name as subcategory_name, s.slug as subcategory_slug,
                    {$stockSelect}, {$imageSelect} 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             LEFT JOIN subcategories s ON p.subcategory_id = s.id
             {$inventoryJoin}
             {$imageJoin}
             WHERE p.id = :id{$softDeleteCondition}", 
            ['id' => $id]
        );
    }

    public function getProductById(int $id): ?array {
        return $this->getById($id);
    }

    /**
     * Get all images for a product
     */
    public function getProductImages(int $productId): array {
        if (!$this->supportsProductImages()) {
            return [];
        }

        $sql = "SELECT id, image_path, is_main FROM product_images WHERE product_id = :pid ORDER BY is_main DESC, id ASC";
        return $this->fetchAll($sql, ['pid' => $productId]);
    }

    /**
     * Delete a specific product image
     */
    public function deleteImage(int $imageId): bool {
        if (!$this->supportsProductImages()) return false;
        
        $stmt = $this->db->prepare("DELETE FROM product_images WHERE id = :id");
        return $stmt->execute(['id' => $imageId]);
    }

    /**
     * Add a new image to the product gallery
     */
    public function addProductImage(int $productId, string $path, bool $isMain = false): bool {
        if (!$this->supportsProductImages()) return false;

        $sql = "INSERT INTO product_images (product_id, image_path, is_main) VALUES (:pid, :path, :main)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'pid'  => $productId,
            'path' => $path,
            'main' => $isMain ? 1 : 0
        ]);
    }

    /**
     * Set a specific gallery image as the primary image
     */
    public function setPrimaryImage(int $productId, int $imageId, string $path): bool {
        try {
            $this->db->beginTransaction();
            
            // 1. Set all images for this product to NOT main
            $stmt1 = $this->db->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = :pid");
            $stmt1->execute(['pid' => $productId]);
            
            // 2. Set the chosen image to main
            $stmt2 = $this->db->prepare("UPDATE product_images SET is_main = 1 WHERE id = :id");
            $stmt2->execute(['id' => $imageId]);
            
            // 3. Update products table for legacy compatibility if column exists
            if ($this->supportsProductImagePath()) {
                $stmt3 = $this->db->prepare("UPDATE products SET image_path = :path WHERE id = :id");
                $stmt3->execute(['path' => $path, 'id' => $productId]);
            }
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Get all variants for a product
     */
    public function getVariantsByProductId(int $productId): array {
        if (!$this->hasTable('product_variants')) {
            return [];
        }

        $sql = "SELECT * FROM product_variants WHERE product_id = :pid";

        if ($this->hasColumn('product_variants', 'is_active')) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY id ASC";

        return $this->fetchAll($sql, ['pid' => $productId]);
    }

    /**
     * Get variants for many products in a single query
     */
    public function getVariantsByProductIds(array $productIds): array {
        if (!$this->hasTable('product_variants') || empty($productIds)) {
            return [];
        }

        $productIds = array_values(array_unique(array_map('intval', $productIds)));
        $productIds = array_values(array_filter($productIds, static function (int $id): bool {
            return $id > 0;
        }));

        if (empty($productIds)) {
            return [];
        }

        $placeholders = [];
        $params = [];
        foreach ($productIds as $index => $productId) {
            $key = ':pid' . $index;
            $placeholders[] = $key;
            $params[$key] = $productId;
        }

        $sql = "SELECT * FROM product_variants WHERE product_id IN (" . implode(', ', $placeholders) . ")";

        if ($this->hasColumn('product_variants', 'is_active')) {
            $sql .= " AND is_active = 1";
        }

        $sql .= " ORDER BY product_id ASC, id ASC";

        return $this->fetchAll($sql, $params);
    }

    /**
     * Get product by Slug
     */
    public function getBySlug(string $slug): ?array {
        $stockSelect = $this->getStockSelector();
        $imageSelect = $this->getImageSelector();
        $inventoryJoin = $this->getInventoryJoin();
        $imageJoin = $this->getImageJoin();

        $softDeleteCondition = $this->softDeleteClause('p');

        return $this->fetchOne(
            "SELECT p.*, c.name as category_name, c.slug as category_slug,
                    s.name as subcategory_name, s.slug as subcategory_slug,
                    {$stockSelect}, {$imageSelect} 
             FROM products p 
             LEFT JOIN categories c ON p.category_id = c.id 
             LEFT JOIN subcategories s ON p.subcategory_id = s.id
             {$inventoryJoin}
             {$imageJoin}
             WHERE p.slug = :slug{$softDeleteCondition}", 
            ['slug' => $slug]
        );
    }

    /**
     * Create product record
     */
    public function create(array $data): int {
        $columns = ['category_id', 'subcategory_id', 'name', 'slug', 'short_description', 'description', 'base_price', 'sale_price', 'tax_rate', 'sku', 'status', 'featured'];
        $placeholders = [':cid', ':sid', ':name', ':slug', ':short_desc', ':desc', ':price', ':sale_price', ':tax_rate', ':sku', ':status', ':featured'];
        $params = [
            'cid'         => $data['category_id'] ?? null,
            'sid'         => $data['subcategory_id'] ?? null,
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'short_desc'  => $data['short_description'] ?? null,
            'desc'        => $data['description'] ?? null,
            'price'       => $data['base_price'],
            'sale_price'  => $data['sale_price'] ?? null,
            'tax_rate'    => $data['tax_rate'] ?? 0.00,
            'sku'         => $data['sku'] ?? null,
            'status'      => $data['status'] ?? 'published',
            'featured'    => isset($data['featured']) ? (int)$data['featured'] : 0
        ];

        if ($this->supportsProductImagePath()) {
            $columns[] = 'image_path';
            $placeholders[] = ':image_path';
            $params['image_path'] = $data['image_path'] ?? 'assets/images/placeholders/product-placeholder.png';
        }

        $sql = "INSERT INTO products (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $productId = (int)$this->db->lastInsertId();

        // Handle Image
        if ($productId > 0) {
            $imagePath = $data['image_path'] ?? 'assets/images/placeholders/product-placeholder.png';
            if ($this->supportsProductImages()) {
                $stmtImg = $this->db->prepare("INSERT INTO product_images (product_id, image_path, is_main) VALUES (:pid, :img, 1)");
                $stmtImg->execute(['pid' => $productId, 'img' => $imagePath]);
            }

            $stock = (int)($data['stock_quantity'] ?? 0);
            if ($this->supportsInventory()) {
                $stmtInv = $this->db->prepare("INSERT INTO inventory (product_id, stock) VALUES (:pid, :stock)");
                $stmtInv->execute([
                    'pid'   => $productId,
                    'stock' => $stock
                ]);
            } elseif ($this->hasColumn('products', 'stock_quantity')) {
                $stmtStock = $this->db->prepare("UPDATE products SET stock_quantity = :stock WHERE id = :pid");
                $stmtStock->execute(['stock' => $stock, 'pid' => $productId]);
            } elseif ($this->hasColumn('products', 'stock_qty')) {
                $stmtStock = $this->db->prepare("UPDATE products SET stock_qty = :stock WHERE id = :pid");
                $stmtStock->execute(['stock' => $stock, 'pid' => $productId]);
            }
        }

        return $productId;
    }

    /**
     * Update product details
     */
    public function update(int $id, array $data): bool {
        if (empty($data)) return true;

        $fields = [];
        $params = [':id' => $id];

        foreach ($data as $key => $value) {
            // Special handling for inventory if passed here
            if ($key === 'stock_quantity') {
                if ($this->supportsInventory()) {
                    $stmtInv = $this->db->prepare("INSERT INTO inventory (product_id, stock) VALUES (:id, :stock) ON DUPLICATE KEY UPDATE stock = VALUES(stock)");
                    $stmtInv->execute(['stock' => $value, 'id' => $id]);
                }
                
                if ($this->hasColumn('products', 'stock_quantity')) {
                    $stmtInv2 = $this->db->prepare("UPDATE products SET stock_quantity = :stock WHERE id = :id");
                    $stmtInv2->execute(['stock' => $value, 'id' => $id]);
                } elseif ($this->hasColumn('products', 'stock_qty')) {
                    $stmtInv2 = $this->db->prepare("UPDATE products SET stock_qty = :stock WHERE id = :id");
                    $stmtInv2->execute(['stock' => $value, 'id' => $id]);
                }
                continue;
            }

            // Special handling for main image
            if ($key === 'image_path') {
                if ($this->supportsProductImagePath()) {
                    $stmtProdImg = $this->db->prepare("UPDATE products SET image_path = :img WHERE id = :id");
                    $stmtProdImg->execute(['img' => $value, 'id' => $id]);
                }

                if ($this->supportsProductImages()) {
                    // Correct approach: Set all others to 0, then ensure this one exists as 1
                    // or just update the existing one if it exists.
                    $this->db->prepare("UPDATE product_images SET is_main = 0 WHERE product_id = :id")->execute(['id' => $id]);
                    
                    $stmtImg = $this->db->prepare(
                        "INSERT INTO product_images (product_id, image_path, is_main) VALUES (:id, :img, 1) 
                         ON DUPLICATE KEY UPDATE image_path = VALUES(image_path), is_main = 1"
                    );
                    $stmtImg->execute(['img' => $value, 'id' => $id]);
                }
                continue;
            }

            $fields[] = "{$key} = :{$key}";
            $params[":{$key}"] = $value;
        }

        if (empty($fields)) return true;

        $sql = "UPDATE products SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Upsert a single product variant (insert if id=0, update otherwise)
     */
    public function upsertVariant(int $productId, array $data): bool {
        $variantId = (int)($data['id'] ?? 0);
        $weight    = trim((string)($data['weight'] ?? ''));
        $label     = trim((string)($data['label'] ?? ($weight . ' Pack')));
        $price     = (float)($data['price'] ?? 0);
        $stock     = (int)($data['stock'] ?? 0);

        if ($weight === '' || $price <= 0) return false;

        if ($variantId > 0) {
            $stmt = $this->db->prepare(
                "UPDATE product_variants SET weight=:w, label=:l, price=:p, stock=:s WHERE id=:id AND product_id=:pid"
            );
            return $stmt->execute([':w'=>$weight, ':l'=>$label, ':p'=>$price, ':s'=>$stock, ':id'=>$variantId, ':pid'=>$productId]);
        } else {
            // Upsert by weight so duplicate weights are updated, not duplicated
            $stmt = $this->db->prepare(
                "INSERT INTO product_variants (product_id, weight, label, price, stock)
                 VALUES (:pid, :w, :l, :p, :s)
                 ON DUPLICATE KEY UPDATE label=VALUES(label), price=VALUES(price), stock=VALUES(stock)"
            );
            return $stmt->execute([':pid'=>$productId, ':w'=>$weight, ':l'=>$label, ':p'=>$price, ':s'=>$stock]);
        }
    }

    /**
     * Delete a single variant by id (must belong to product)
     */
    public function deleteVariant(int $productId, int $variantId): bool {
        $stmt = $this->db->prepare("DELETE FROM product_variants WHERE id=:id AND product_id=:pid");
        return $stmt->execute([':id'=>$variantId, ':pid'=>$productId]);
    }

    /**
     * Delete product
     */
    public function delete(int $id): bool {
        if ($this->supportsSoftDeletes()) {
            $stmt = $this->db->prepare("UPDATE products SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id");
            return $stmt->execute(['id' => $id]);
        }

        $stmt = $this->db->prepare("DELETE FROM products WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /* ── LEGACY & HELPER METHODS ── */

    public function getFeaturedProducts(int $limit = 0): array {
        $stockSelect = $this->getStockSelector();
        $imageSelect = $this->getImageSelector();
        $imageJoin   = $this->getImageJoin();
        $inventoryJoin = $this->getInventoryJoin();
        $softDeleteCondition = $this->softDeleteClause('p');

        // Only filter by featured=1 when the column exists
        $featuredCondition = $this->hasColumn('products', 'featured')
            ? " AND p.featured = 1"
            : '';

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       pc.slug as parent_category_slug,
                       COALESCE(pc.slug, c.slug) as effective_category_slug,
                       {$stockSelect}, {$imageSelect}
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN categories pc ON c.parent_id = pc.id
                {$imageJoin}
                {$inventoryJoin}
                WHERE p.status = 'published'{$featuredCondition}{$softDeleteCondition}
                GROUP BY p.id
                ORDER BY p.id DESC";
        if ($limit > 0) $sql .= " LIMIT " . (int)$limit;
        return $this->fetchAll($sql);
    }

    public function getCollectionProducts(int $limit = 6): array {
        return $this->getFeaturedProducts($limit);
    }

    public function getProductsByCategory(string $catSlug): array {
        $stockSelect = $this->getStockSelector();
        $imageSelect = $this->getImageSelector();
        $imageJoin   = $this->getImageJoin();
        $inventoryJoin = $this->getInventoryJoin();
        $softDeleteCondition = $this->softDeleteClause('p');

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                       pc.slug as parent_category_slug,
                       COALESCE(pc.slug, c.slug) as effective_category_slug,
                       {$stockSelect}, {$imageSelect}
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN categories pc ON c.parent_id = pc.id
                {$imageJoin}
                {$inventoryJoin}
                WHERE (c.slug = :slug OR pc.slug = :pslug) AND p.status = 'published'{$softDeleteCondition}
                GROUP BY p.id
                ORDER BY p.id DESC";
        return $this->fetchAll($sql, [':slug' => $catSlug, ':pslug' => $catSlug]);
    }

    public function getFilteredProducts(array $filters = [], string $sortBy = 'newest'): array {
        $stockSelect = $this->getStockSelector();
        $imageSelect = $this->getImageSelector();
        $imageJoin = $this->getImageJoin();
        $inventoryJoin = $this->getInventoryJoin();
        
        $whereConditions = [];
        $params = [];
        
        if ($this->supportsSoftDeletes()) {
            $whereConditions[] = "p.deleted_at IS NULL";
        }

        // Frontend catalog should only show actively published products.
        $whereConditions[] = "p.status = 'published'";

        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            // Check both direct category and parent category
            $whereConditions[] = "(c.slug = :cat1 OR pc.slug = :cat2)";
            $params['cat1'] = $filters['category'];
            $params['cat2'] = $filters['category'];
        }

        if (isset($filters['min_price']) && $filters['min_price'] !== '') {
            $whereConditions[] = "p.base_price >= :min_price";
            $params['min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price']) && $filters['max_price'] !== '') {
            $whereConditions[] = "p.base_price <= :max_price";
            $params['max_price'] = $filters['max_price'];
        }

        if (!empty($filters['search'])) {
            $searchStr = trim((string)$filters['search']);
            $whereConditions[] = "(MATCH(p.name, p.slug, p.short_description) AGAINST(:search_query IN BOOLEAN MODE) OR MATCH(c.name, c.slug) AGAINST(:search_query IN BOOLEAN MODE))";
            
            $words = explode(' ', $searchStr);
            $boolQuery = '';
            foreach ($words as $word) {
                $word = trim($word);
                if (!empty($word)) {
                    $boolQuery .= '+' . $word . '* ';
                }
            }
            $params['search_query'] = trim($boolQuery);
        }

        $whereSql = !empty($whereConditions) ? " WHERE " . implode(" AND ", $whereConditions) : "";

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, {$stockSelect}, {$imageSelect} 
                FROM products p 
                JOIN categories c ON p.category_id = c.id 
                LEFT JOIN categories pc ON c.parent_id = pc.id
                {$imageJoin}
                {$inventoryJoin}
                {$whereSql}
                GROUP BY p.id";

        if ($sortBy === 'price_low') {
            $sql .= " ORDER BY p.base_price ASC";
        } elseif ($sortBy === 'price_high') {
            $sql .= " ORDER BY p.base_price DESC";
        } elseif ($sortBy === 'name') {
            $sql .= " ORDER BY p.name ASC";
        } else {
            $sql .= " ORDER BY p.created_at DESC";
        }

        return $this->fetchAll($sql, $params);
    }

    public function getProductStats(): array {
        $softDeleteWhere = $this->supportsSoftDeletes() ? " WHERE deleted_at IS NULL" : '';
        $softDeleteAnd = $this->supportsSoftDeletes() ? " AND p.deleted_at IS NULL" : '';

        // Total count should always be from products table directly
        $total = (int)$this->db->query("SELECT COUNT(*) FROM products{$softDeleteWhere}")->fetchColumn();

        if ($this->supportsInventory()) {
            $stockCol = $this->getStockSelector(); // Already calculates COALESCE fallback
            
            // Subquery approach or direct LEFT JOIN to ensure we count products even without inventory records
            $sqlBase = "SELECT COUNT(*) FROM products p 
                       LEFT JOIN inventory i ON p.id = i.product_id 
                       WHERE 1=1{$softDeleteAnd}";
            
            $inStock = (int)$this->db->query("{$sqlBase} AND COALESCE(i.stock, p.stock_quantity, 0) > 10")->fetchColumn();
            $lowStock = (int)$this->db->query("{$sqlBase} AND COALESCE(i.stock, p.stock_quantity, 0) > 0 AND COALESCE(i.stock, p.stock_quantity, 0) <= 10")->fetchColumn();
            $outStock = (int)$this->db->query("{$sqlBase} AND COALESCE(i.stock, p.stock_quantity, 0) <= 0")->fetchColumn();
        } elseif ($this->hasColumn('products', 'stock_quantity')) {
            $inStock = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock_quantity > 10" . ($softDeleteWhere ? " AND deleted_at IS NULL" : ''))->fetchColumn();
            $lowStock = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock_quantity > 0 AND stock_quantity <= 10" . ($softDeleteWhere ? " AND deleted_at IS NULL" : ''))->fetchColumn();
            $outStock = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock_quantity <= 0" . ($softDeleteWhere ? " AND deleted_at IS NULL" : ''))->fetchColumn();
        } elseif ($this->hasColumn('products', 'stock_qty')) {
            $inStock = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock_qty > 10" . ($softDeleteWhere ? " AND deleted_at IS NULL" : ''))->fetchColumn();
            $lowStock = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock_qty > 0 AND stock_qty <= 10" . ($softDeleteWhere ? " AND deleted_at IS NULL" : ''))->fetchColumn();
            $outStock = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock_qty <= 0" . ($softDeleteWhere ? " AND deleted_at IS NULL" : ''))->fetchColumn();
        } else {
            $inStock = $lowStock = $outStock = 0;
        }

        return [
            'total' => (int)$total,
            'in_stock' => (int)$inStock,
            'low_stock' => (int)$lowStock,
            'out_of_stock' => (int)$outStock
        ];
    }
}
