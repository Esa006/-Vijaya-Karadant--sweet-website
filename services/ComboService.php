<?php
/**
 * Sweets Website
 * =============================================================
 * File: services/ComboService.php
 * Description: Business Logic and Pricing for Combos (with Admin CRUD)
 * =============================================================
 */

require_once __DIR__ . '/../repositories/ComboRepository.php';
require_once __DIR__ . '/FileService.php';

class ComboService {
    private ComboRepository $repo;

    public function __construct() {
        $this->repo = new ComboRepository();
    }

    // ── Read Methods ─────────────────────────────────────────────────────────

    /**
     * Get all active combos enriched with pricing logic (for frontend)
     */
    public function getAllCombos(): array {
        $combos = $this->repo->getAllCombos();
        return array_map([$this, 'calculatePricing'], $combos);
    }

    /**
     * Get all combos including inactive (for admin listing)
     */
    public function getAllCombosAdmin(): array {
        $combos = $this->repo->getAllCombos(false);
        return array_map([$this, 'calculatePricing'], $combos);
    }

    /**
     * Get combos by category
     */
    public function getCombosByCategory(string $category): array {
        $combos = $this->repo->getCombosByCategory($category);
        return array_map([$this, 'calculatePricing'], $combos);
    }

    /**
     * Get a combo by ID (with pricing)
     */
    public function getComboById(int $id): ?array {
        $combo = $this->repo->getById($id);
        return $combo ? $this->calculatePricing($combo) : null;
    }

    /**
     * Get a combo by Slug (with pricing)
     */
    public function getComboBySlug(string $slug): ?array {
        $combo = $this->repo->getBySlug($slug);
        return $combo ? $this->calculatePricing($combo) : null;
    }

    /**
     * Admin stats overview
     */
    public function getComboStats(): array {
        return $this->repo->getStats();
    }

    // ── Admin Write Methods ─────────────────────────────────────────────────

    /**
     * Create a new combo with optional image upload and product items
     */
    public function createCombo(array $data, ?array $imageFile = null): int {
        // 1. Image upload
        $imagePath = null;
        if ($imageFile && !empty($imageFile['tmp_name'])) {
            $fs = new FileService('combos');
            $imagePath = $fs->upload($imageFile);
        }

        // 2. Generate slug
        $slug = $this->generateUniqueSlug($data['name'] ?? 'combo');

        // 3. Build row
        $row = [
            'name'        => strip_tags(trim($data['name'] ?? '')),
            'slug'        => $slug,
            'description' => strip_tags(trim($data['description'] ?? '')),
            'category'    => strtolower(trim($data['category'] ?? '')),
            'price'       => (float)($data['price'] ?? 0),
            'image'       => $imagePath,
            'is_active'   => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ];

        $comboId = $this->repo->create($row);

        // 4. Sync items
        if ($comboId > 0 && !empty($data['items'])) {
            $this->repo->syncItems($comboId, $data['items']);
        }

        return $comboId;
    }

    /**
     * Update an existing combo with optional image upload and item changes
     */
    public function updateCombo(int $id, array $data, ?array $imageFile = null): bool {
        $updateRow = [
            'name'        => strip_tags(trim($data['name'] ?? '')),
            'description' => strip_tags(trim($data['description'] ?? '')),
            'category'    => strtolower(trim($data['category'] ?? '')),
            'price'       => (float)($data['price'] ?? 0),
            'is_active'   => isset($data['is_active']) ? (int)$data['is_active'] : 1,
        ];

        // Update image only if a new file is uploaded
        if ($imageFile && !empty($imageFile['tmp_name'])) {
            $fs = new FileService('combos');
            $newPath = $fs->upload($imageFile);
            if ($newPath) {
                $updateRow['image'] = $newPath;
            }
        }

        $ok = $this->repo->update($id, $updateRow);

        // Always resync items when provided
        if (isset($data['items'])) {
            $this->repo->syncItems($id, $data['items']);
        }

        return $ok;
    }

    /**
     * Soft-delete a combo (set inactive)
     */
    public function deleteCombo(int $id): bool {
        return $this->repo->delete($id);
    }

    // ── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Apply dynamic pricing and calculate savings
     */
    private function calculatePricing(array $combo): array {
        $originalPrice = 0.0;
        $derivedPrice  = 0.0;
        $isOutOfStock  = false;

        foreach ($combo['items'] as $item) {
            $qty  = (int)$item['quantity'];
            $base = (float)($item['base_price'] ?: $item['sale_price'] ?: 0);
            $sale = (float)($item['sale_price'] ?: $item['base_price'] ?: 0);

            $originalPrice += ($base * $qty);
            $derivedPrice  += ($sale * $qty);

            // Stock Check
            $availableStock = (int)($item['stock'] ?? 0);
            $itemStatus     = $item['status'] ?? 'published';
            if ($itemStatus === 'out_of_stock' || $availableStock < $qty) {
                $isOutOfStock = true;
            }
        }

        // If a fixed price is set in DB, use it; otherwise use derived sum
        if ($combo['price'] !== null && $combo['price'] > 0) {
            $finalPrice = (float)$combo['price'];
        } else {
            $finalPrice = $derivedPrice;
        }

        $combo['original_price'] = $originalPrice;
        $combo['final_price']    = $finalPrice;
        $combo['savings_amount'] = max(0, $originalPrice - $finalPrice);
        $combo['stock_status']   = $isOutOfStock ? 'out_of_stock' : 'in_stock';

        return $combo;
    }

    /**
     * Generate a unique slug from a name
     */
    private function generateUniqueSlug(string $name): string {
        $base  = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug  = $base;
        $i     = 1;
        while ($this->repo->slugExists($slug)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
