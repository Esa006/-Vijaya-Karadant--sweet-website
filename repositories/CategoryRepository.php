<?php
/**
 * Sweets Website
 * =============================================================
 * File: CategoryRepository.php
 * Description: Data access layer for Categories (Tree & Flat)
 * Pattern: Repository Pattern
 * Author: Sweets Website Team
 * Version: 3.0.0
 * =============================================================
 */

require_once REPOS_PATH . '/BaseRepository.php';

class CategoryRepository extends BaseRepository {

    /**
     * Get all categories as a flat list
     */
    public function getAllFlat(): array {
        return $this->fetchAll("
            SELECT id, parent_id, name, slug, sku, description, image_path, hero_image, regular_price, discount_price, tax_rate, weight, short_description, highlights, ingredients, benefits, storage_instructions, status, created_at, updated_at
            FROM categories
            ORDER BY parent_id ASC, name ASC
        ");
    }

    /**
     * Get root level categories (parent_id IS NULL) with product counts
     */
    public function getRootCategories(): array {
        return $this->fetchAll("
            SELECT c.*, 
                   (CASE WHEN c.slug IN ('combos', 'combo') THEN
                        (SELECT image FROM combos WHERE image IS NOT NULL AND image != '' LIMIT 1)
                   ELSE
                       (SELECT pi.image_path FROM products p 
                        LEFT JOIN categories sub ON p.category_id = sub.id
                        LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                        WHERE (p.category_id = c.id OR sub.parent_id = c.id) 
                          AND (pi.image_path IS NOT NULL AND pi.image_path != '')
                        LIMIT 1)
                   END) as product_image,
                   (CASE WHEN c.slug IN ('combos', 'combo') THEN
                        (SELECT COUNT(*) FROM combos)
                   ELSE
                       (SELECT COUNT(*) FROM products p 
                        LEFT JOIN categories sub ON p.category_id = sub.id
                        WHERE p.category_id = c.id OR sub.parent_id = c.id)
                   END) as product_count
            FROM categories c
            WHERE c.parent_id IS NULL OR c.parent_id = 0
            ORDER BY (CASE WHEN c.name IN ('Combos', 'combo') THEN 1 ELSE 0 END) ASC, c.name ASC
        ");
    }

    /**
     * Get product counts for all categories
     */
    public function getCategoryProductCounts(): array {
        return $this->fetchAll("
            SELECT c.id, c.name, 
                   (CASE WHEN c.slug IN ('combos', 'combo') THEN
                        (SELECT COUNT(*) FROM combos)
                   ELSE
                       (SELECT COUNT(*) FROM products p 
                        LEFT JOIN categories sub ON p.category_id = sub.id
                        WHERE p.category_id = c.id OR sub.parent_id = c.id)
                   END) as product_count
            FROM categories c
        ");
    }

    /**
     * Get all active categories with tree structure recursive
     */
    public function getTree(): array {
        $flat = $this->getAllFlat();
        return $this->buildTree($flat);
    }

    private function buildTree(array $elements, $parentId = null) {
        $branch = array();
        foreach ($elements as $element) {
            // Match strict null or 0 for root
            $currentParent = $element['parent_id'] ?: null;
            if ($currentParent == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                } else {
                    $element['children'] = [];
                }
                $branch[] = $element;
            }
        }
        return $branch;
    }

    /**
     * Get a single category by id
     */
    public function getById(int $id): ?array {
        return $this->fetchOne("
            SELECT id, parent_id, name, slug, sku, description, image_path, hero_image, regular_price, discount_price, tax_rate, weight, short_description, highlights, ingredients, benefits, storage_instructions, status, created_at, updated_at
            FROM categories
            WHERE id = :id
            LIMIT 1
        ", [':id' => $id]);
    }

    /**
     * Get category with aggregated stats (product count, revenue, subcategory count)
     */
    public function getWithStats(int $id): ?array {
        $category = $this->getById($id);
        if (!$category) return null;

        // Product count & revenue for this category
        $stats = $this->fetchOne("
            SELECT
                COUNT(p.id) AS product_count,
                COALESCE(SUM(
                    COALESCE(p.sale_price, p.base_price, 0) *
                    COALESCE(i.stock, p.stock_quantity, 0)
                ), 0) AS stock_value,
                SUM(COALESCE(i.stock, p.stock_quantity, 0)) AS total_stock
            FROM products p
            LEFT JOIN inventory i ON p.id = i.product_id
            LEFT JOIN categories sub ON p.category_id = sub.id
            WHERE p.category_id = :cid1 OR sub.parent_id = :cid2
        ", [':cid1' => $id, ':cid2' => $id]);

        // Revenue from orders
        $revenue = $this->fetchOne("
            SELECT COALESCE(SUM(oi.price_at_time * oi.quantity), 0) AS total_revenue
            FROM order_items oi
            JOIN orders o ON oi.order_id = o.id
            JOIN products p ON oi.product_id = p.id
            LEFT JOIN categories sub ON p.category_id = sub.id
            WHERE (p.category_id = :cid1 OR sub.parent_id = :cid2) AND o.status != 'cancelled'
        ", [':cid1' => $id, ':cid2' => $id]);

        // Subcategory count
        $subCount = $this->fetchOne("
            SELECT COUNT(id) AS cnt FROM categories WHERE parent_id = :cid
        ", [':cid' => $id]);

        // Parent category (if any)
        $parent = null;
        if (!empty($category['parent_id'])) {
            $parent = $this->fetchOne("SELECT id, name, slug FROM categories WHERE id = :pid", [':pid' => $category['parent_id']]);
        }

        $category['product_count']     = (int)($stats['product_count'] ?? 0);
        $category['stock_value']       = (float)($stats['stock_value'] ?? 0);
        $category['total_stock']       = (int)($stats['total_stock'] ?? 0);
        $category['total_revenue']     = (float)($revenue['total_revenue'] ?? 0);
        $category['subcategory_count'] = (int)($subCount['cnt'] ?? 0);
        $category['parent']            = $parent;

        return $category;
    }

    /**
     * Get recent products in a category
     */
    public function getRecentProducts(int $categoryId, int $limit = 8): array {
        return $this->fetchAll("
            SELECT p.id, p.name, p.slug, p.base_price, p.sale_price, p.status,
                   pi.image_path AS image_path,
                   COALESCE(i.stock, p.stock_quantity, 0) AS stock_quantity
            FROM products p
            LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
            LEFT JOIN inventory i ON p.id = i.product_id
            LEFT JOIN categories sub ON p.category_id = sub.id
            WHERE p.category_id = :cid1 OR sub.parent_id = :cid2
            ORDER BY p.created_at DESC
            LIMIT :lim
        ", [':cid1' => $categoryId, ':cid2' => $categoryId, ':lim' => $limit], [':lim' => PDO::PARAM_INT]);
    }

    /**
     * Get a single category by slug
     */
    public function getBySlug(string $slug): ?array {
        return $this->fetchOne("
            SELECT c.*,
                   (SELECT COUNT(p.id) FROM products p WHERE p.category_id = c.id) AS product_count
            FROM categories c
            WHERE c.slug = :slug
            LIMIT 1
        ", [':slug' => $slug]);
    }

    /**
     * Check if a category exists by slug (excluding a specific ID)
     */
    public function existsBySlug(string $slug, int $excludeId = 0): bool {
        $query = "SELECT COUNT(id) AS cnt FROM categories WHERE slug = :slug";
        $params = [':slug' => $slug];
        
        if ($excludeId > 0) {
            $query .= " AND id != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $res = $this->fetchOne($query, $params);
        return (int)($res['cnt'] ?? 0) > 0;
    }

    /**
     * Create a new category
     */
    public function create(array $data): int {
        $sql = "INSERT INTO categories (
                    parent_id, name, slug, sku, description, image_path, hero_image, regular_price, discount_price, tax_rate, weight, short_description, highlights, ingredients, benefits, storage_instructions, status
                ) VALUES (
                    :parent_id, :name, :slug, :sku, :description, :image_path, :hero_image, :regular_price, :discount_price, :tax_rate, :weight, :short_description, :highlights, :ingredients, :benefits, :storage_instructions, :status
                )";
        
        $params = [
            ':parent_id'   => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            ':name'        => $data['name'],
            ':slug'        => $data['slug'],
            ':sku'         => $data['sku'] ?? null,
            ':description' => $data['description'] ?? null,
            ':image_path'  => $data['image_path'] ?? null,
            ':hero_image'  => $data['hero_image'] ?? null,
            ':regular_price' => $data['regular_price'] ?? null,
            ':discount_price' => $data['discount_price'] ?? null,
            ':tax_rate'      => $data['tax_rate'] ?? null,
            ':weight'        => $data['weight'] ?? null,
            ':short_description' => $data['short_description'] ?? null,
            ':highlights'    => $data['highlights'] ?? null,
            ':ingredients'   => $data['ingredients'] ?? null,
            ':benefits'      => $data['benefits'] ?? null,
            ':storage_instructions' => $data['storage_instructions'] ?? null,
            ':sku'         => $data['sku'] ?? null,
            ':status'      => $data['status'] ?? 'active'
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing category
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE categories SET 
                    parent_id = :parent_id,
                    name = :name,
                    description = :description,
                    regular_price = :regular_price,
                    discount_price = :discount_price,
                    tax_rate = :tax_rate,
                    weight = :weight,
                    short_description = :short_description,
                    highlights = :highlights,
                    ingredients = :ingredients,
                    benefits = :benefits,
                    storage_instructions = :storage_instructions,
                    sku = :sku,
                    status = :status,
                    updated_at = CURRENT_TIMESTAMP";

        // Keep slug immutable unless explicitly passed correctly, but requirement:
        // "Slug MUST NOT change on update (preserve existing slug)" -> so we skip slug update.
        // Wait, "Allow optional manual override": Let's include slug conditionally.
        $params = [
            ':id'          => $id,
            ':parent_id'   => !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            ':name'        => $data['name'],
            ':description' => $data['description'] ?? null,
            ':regular_price' => $data['regular_price'] ?? null,
            ':discount_price' => $data['discount_price'] ?? null,
            ':tax_rate'      => $data['tax_rate'] ?? null,
            ':weight'        => $data['weight'] ?? null,
            ':short_description' => $data['short_description'] ?? null,
            ':highlights'    => $data['highlights'] ?? null,
            ':ingredients'   => $data['ingredients'] ?? null,
            ':benefits'      => $data['benefits'] ?? null,
            ':storage_instructions' => $data['storage_instructions'] ?? null,
            ':sku'         => $data['sku'] ?? null,
            ':status'      => $data['status'] ?? 'active'
        ];

        if (array_key_exists('slug', $data) && $data['slug'] !== null && $data['slug'] !== '') {
            $sql .= ", slug = :slug";
            $params[':slug'] = $data['slug'];
        }

        if (array_key_exists('image_path', $data)) {
            $sql .= ", image_path = :image_path";
            $params[':image_path'] = $data['image_path']; // allow null
        }
        
        if (array_key_exists('hero_image', $data)) {
            $sql .= ", hero_image = :hero_image";
            $params[':hero_image'] = $data['hero_image']; // allow null
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Count subcategories for a specific parent.
     */
    public function countSubcategories(int $parentId): int {
        $res = $this->fetchOne("SELECT COUNT(id) AS cnt FROM categories WHERE parent_id = :parent_id", [':parent_id' => $parentId]);
        return (int)($res['cnt'] ?? 0);
    }

    /**
     * Get paginated subcategories for a parent
     */
    public function getSubcategories(int $parentId, int $limit, int $offset): array {
        return $this->fetchAll("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
            FROM categories c
            WHERE c.parent_id = :parent_id
            ORDER BY c.name ASC
            LIMIT :limit OFFSET :offset
        ", [
            ':parent_id' => $parentId,
            ':limit'     => $limit,
            ':offset'    => $offset
        ], [
            ':limit'     => PDO::PARAM_INT,
            ':offset'    => PDO::PARAM_INT
        ]);
    }

    /**
     * Delete a category
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

     /**
     * Count all subcategories (parent_id is not null/0).
     */
    public function countAllSubcategories(): int {
        return (int)$this->fetchOne("
            SELECT COUNT(id) as cnt FROM categories WHERE parent_id IS NOT NULL AND parent_id != 0
        ")['cnt'];
    }

    /**
     * Get all paginated subcategories.
     */
    public function getAllSubcategories(int $limit, int $offset): array {
        return $this->fetchAll("
            SELECT c.*, 
                   (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id) as product_count
            FROM categories c
            WHERE c.parent_id IS NOT NULL AND c.parent_id != 0
            ORDER BY c.name ASC
            LIMIT :limit OFFSET :offset
        ", [
            ':limit'  => $limit,
            ':offset' => $offset
        ], [
            ':limit'  => PDO::PARAM_INT,
            ':offset' => PDO::PARAM_INT
        ]);
    }
}
