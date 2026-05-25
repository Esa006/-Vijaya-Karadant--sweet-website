<?php
/**
 * Sweets Website
 * =============================================================
 * File: repositories/SubcategoryRepository.php
 * Description: Data access for Subcategories
 * =============================================================
 */

require_once __DIR__ . '/BaseRepository.php';

class SubcategoryRepository extends BaseRepository {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all subcategories with parent category name and product count
     */
    public function getAllExtended(?int $categoryId = null): array {
        $sql = "SELECT s.*, c.name as category_name, c.image_path as category_image,
                (SELECT pi.image_path FROM products p 
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 WHERE p.subcategory_id = s.id AND pi.image_path IS NOT NULL AND pi.image_path != '' LIMIT 1) as product_image,
                (SELECT COUNT(*) FROM products WHERE subcategory_id = s.id) as product_count
                FROM subcategories s
                JOIN categories c ON s.category_id = c.id";
        
        if ($categoryId) {
            $sql .= " WHERE s.category_id = :cat_id";
            return $this->fetchAll($sql, [':cat_id' => $categoryId]);
        }
        
        $sql .= " ORDER BY c.name ASC, s.name ASC";
        return $this->fetchAll($sql);
    }

    public function countExtended(?int $categoryId = null): int {
        $sql = "SELECT COUNT(*) FROM subcategories s";
        if ($categoryId) {
            $sql .= " WHERE s.category_id = :cat_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':cat_id' => $categoryId]);
            return (int)$stmt->fetchColumn();
        }
        return (int)$this->db->query($sql)->fetchColumn();
    }

    public function getPaginatedExtended(int $limit, int $offset, ?int $categoryId = null): array {
        $sql = "SELECT s.*, c.name as category_name, c.image_path as category_image,
                (SELECT pi.image_path FROM products p 
                 LEFT JOIN product_images pi ON p.id = pi.product_id AND pi.is_main = 1
                 WHERE p.subcategory_id = s.id AND pi.image_path IS NOT NULL AND pi.image_path != '' LIMIT 1) as product_image,
                (SELECT COUNT(*) FROM products WHERE subcategory_id = s.id) as product_count
                FROM subcategories s
                JOIN categories c ON s.category_id = c.id";
        
        $params = [];
        if ($categoryId) {
            $sql .= " WHERE s.category_id = :cat_id";
            $params[':cat_id'] = $categoryId;
        }
        
        $sql .= " ORDER BY c.name ASC, s.name ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): ?array {
        $sql = "SELECT s.*, c.name as category_name 
                FROM subcategories s
                JOIN categories c ON s.category_id = c.id
                WHERE s.id = :id LIMIT 1";
        return $this->fetchOne($sql, [':id' => $id]);
    }

    public function create(array $data): int {
        $sql = "INSERT INTO subcategories (
                    category_id, name, slug, description, short_description, 
                    regular_price, discount_price, tax_rate, weight, 
                    highlights, ingredients, benefits, storage_instructions, 
                    image_path, hero_image, status
                ) VALUES (
                    :category_id, :name, :slug, :description, :short_description, 
                    :regular_price, :discount_price, :tax_rate, :weight, 
                    :highlights, :ingredients, :benefits, :storage_instructions, 
                    :image_path, :hero_image, :status
                )";
        
        $params = [
            ':category_id' => (int)$data['category_id'],
            ':name'        => $data['name'],
            ':slug'        => $data['slug'] ?? $this->slugify($data['name']),
            ':description' => $data['description'] ?? null,
            ':short_description' => $data['short_description'] ?? null,
            ':regular_price' => $data['regular_price'] ?? null,
            ':discount_price' => $data['discount_price'] ?? null,
            ':tax_rate'      => $data['tax_rate'] ?? null,
            ':weight'        => $data['weight'] ?? null,
            ':highlights'    => isset($data['highlights']) ? (is_array($data['highlights']) ? json_encode($data['highlights']) : $data['highlights']) : null,
            ':ingredients'   => $data['ingredients'] ?? null,
            ':benefits'      => $data['benefits'] ?? null,
            ':storage_instructions' => $data['storage_instructions'] ?? null,
            ':image_path'    => $data['image_path'] ?? null,
            ':hero_image'     => $data['hero_image'] ?? null,
            ':status'      => $data['status'] ?? 'active'
        ];

        return $this->executeInsert($sql, $params);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE subcategories SET 
                category_id = :category_id,
                name = :name,
                slug = :slug,
                description = :description,
                short_description = :short_description,
                regular_price = :regular_price,
                discount_price = :discount_price,
                tax_rate = :tax_rate,
                weight = :weight,
                highlights = :highlights,
                ingredients = :ingredients,
                benefits = :benefits,
                storage_instructions = :storage_instructions,
                image_path = :image_path,
                hero_image = :hero_image,
                status = :status
                WHERE id = :id";
        
        $params = [
            ':id'          => $id,
            ':category_id' => (int)$data['category_id'],
            ':name'        => $data['name'],
            ':slug'        => $data['slug'] ?? $this->slugify($data['name']),
            ':description' => $data['description'] ?? null,
            ':short_description' => $data['short_description'] ?? null,
            ':regular_price' => $data['regular_price'] ?? null,
            ':discount_price' => $data['discount_price'] ?? null,
            ':tax_rate'      => $data['tax_rate'] ?? null,
            ':weight'        => $data['weight'] ?? null,
            ':highlights'    => isset($data['highlights']) ? (is_array($data['highlights']) ? json_encode($data['highlights']) : $data['highlights']) : null,
            ':ingredients'   => $data['ingredients'] ?? null,
            ':benefits'      => $data['benefits'] ?? null,
            ':storage_instructions' => $data['storage_instructions'] ?? null,
            ':image_path'    => $data['image_path'] ?? null,
            ':hero_image'     => $data['hero_image'] ?? null,
            ':status'      => $data['status'] ?? 'active'
        ];

        return $this->execute($sql, $params);
    }

    /**
     * Perform high-performance bulk updates (SINGLE QUERY)
     */
    public function bulkUpdate(array $ids, string $action, $value = null): int {
        if (empty($ids)) return 0;
        
        $placeholders = str_repeat('?,', count($ids) - 1) . '?';
        $params = [];
        
        switch ($action) {
            case 'status':
                $sql = "UPDATE subcategories SET status = ? WHERE id IN ($placeholders)";
                $params[] = $value;
                break;
            case 'delete':
                $sql = "UPDATE subcategories SET is_deleted = 1 WHERE id IN ($placeholders)";
                break;
            case 'category':
                $sql = "UPDATE subcategories SET category_id = ? WHERE id IN ($placeholders)";
                $params[] = (int)$value;
                break;
            default:
                throw new Exception("Invalid bulk action: $action");
        }

        // Add all IDs to params
        foreach ($ids as $id) $params[] = (int)$id;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    private function slugify(string $text): string {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }
}
