<?php
/**
 * Sweets Website
 * =============================================================
 * File: services/ComboService.php
 * Description: Business Logic and Pricing for Combos (with Admin CRUD + Gallery)
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
     * Get all active combos enriched with pricing and gallery (for frontend)
     */
    public function getAllCombos(): array {
        $combos = $this->repo->getAllCombos();
        return array_map([$this, 'enrichWithGallery'], $combos);
    }

    /**
     * Get all combos including inactive (for admin listing — no gallery for speed)
     */
    public function getAllCombosAdmin(): array {
        $combos = $this->repo->getAllCombos(false);
        return array_map([$this, 'calculatePricing'], $combos);
    }

    /**
     * Get combos by category (with gallery)
     */
    public function getCombosByCategory(string $category): array {
        $combos = $this->repo->getCombosByCategory($category);
        return array_map([$this, 'enrichWithGallery'], $combos);
    }

    /**
     * Get a combo by ID (with pricing + gallery)
     */
    public function getComboById(int $id): ?array {
        $combo = $this->repo->getById($id);
        return $combo ? $this->enrichWithGallery($combo) : null;
    }

    /**
     * Get a combo by Slug (with pricing + gallery)
     */
    public function getComboBySlug(string $slug): ?array {
        $combo = $this->repo->getBySlug($slug);
        return $combo ? $this->enrichWithGallery($combo) : null;
    }

    /**
     * Admin stats overview
     */
    public function getComboStats(): array {
        return $this->repo->getStats();
    }

    // ── Gallery Read ──────────────────────────────────────────────────────────

    /**
     * Get all gallery images for a combo
     */
    public function getComboImages(int $comboId): array {
        return $this->repo->getImagesForCombo($comboId);
    }

    // ── Admin Write Methods ─────────────────────────────────────────────────

    /**
     * Create a new combo with optional image upload and product items
     */
    public function createCombo(array $data, ?array $imageFile = null): int {
        // 1. Image upload
        $imagePath = null;
        if ($imageFile && !empty($imageFile['tmp_name'])) {
            $fs        = new FileService('combos');
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

        // 5. Seed combo_images with the uploaded primary image
        if ($comboId > 0 && $imagePath) {
            $this->repo->addImage($comboId, $imagePath, true, 0);
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
            $fs      = new FileService('combos');
            $newPath = $fs->upload($imageFile);
            if ($newPath) {
                $updateRow['image'] = $newPath;
                // Add to gallery as new primary
                $this->repo->addImage($id, $newPath, true, 0);
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
     * Upload a gallery image for a combo (AJAX call from admin)
     */
    public function uploadComboImage(int $comboId, array $file, bool $makePrimary = false): array {
        if (empty($file['tmp_name'])) {
            return ['success' => false, 'message' => 'No file received.'];
        }

        $fs   = new FileService('combos');
        $path = $fs->upload($file);
        if (!$path) {
            return ['success' => false, 'message' => 'File upload failed.'];
        }

        // If this is the first image for the combo, force it primary
        $isFirst = ($this->repo->countImages($comboId) === 0);
        $imgId   = $this->repo->addImage($comboId, $path, $makePrimary || $isFirst, 0);

        // Keep combos.image in sync if making primary
        if ($makePrimary || $isFirst) {
            $this->repo->update($comboId, ['image' => $path]);
        }

        return [
            'success'    => true,
            'id'         => $imgId,
            'image_path' => $path,
            'is_primary' => ($makePrimary || $isFirst) ? 1 : 0,
        ];
    }

    /**
     * Delete a gallery image
     */
    public function deleteComboImage(int $imageId): bool {
        return $this->repo->deleteImage($imageId);
    }

    /**
     * Set primary gallery image for a combo
     */
    public function setPrimaryComboImage(int $comboId, int $imageId): bool {
        return $this->repo->setPrimaryImage($comboId, $imageId);
    }

    /**
     * Soft-delete a combo (set inactive)
     */
    public function deleteCombo(int $id): bool {
        return $this->repo->delete($id);
    }

    // ── Private Helpers ─────────────────────────────────────────────────────

    /**
     * Enrich a combo with gallery images and pricing
     */
    private function enrichWithGallery(array $combo): array {
        $combo            = $this->calculatePricing($combo);
        $combo['gallery'] = $this->repo->getImagesForCombo((int)$combo['id']);
        return $combo;
    }

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
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = $base;
        $i    = 1;
        while ($this->repo->slugExists($slug)) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }
}
