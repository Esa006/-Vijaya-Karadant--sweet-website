<?php
/**
 * Sweets Website
 * =============================================================
 * File: services/SubcategoryService.php
 * Description: Business logic for Subcategories
 * =============================================================
 */

require_once REPOS_PATH . '/SubcategoryRepository.php';

class SubcategoryService {
    private SubcategoryRepository $repo;

    public function __construct() {
        $this->repo = new SubcategoryRepository();
    }

    public function getSubcategories(?int $categoryId = null): array {
        try {
            return $this->repo->getAllExtended($categoryId);
        } catch (Exception $e) {
            error_log("SubcategoryService::getSubcategories Error: " . $e->getMessage());
            return [];
        }
    }

    public function getPaginatedSubcategories(int $limit, int $offset, ?int $categoryId = null): array {
        try {
            return $this->repo->getPaginatedExtended($limit, $offset, $categoryId);
        } catch (Exception $e) {
            error_log("SubcategoryService::getPaginatedSubcategories Error: " . $e->getMessage());
            return [];
        }
    }

    public function countSubcategories(?int $categoryId = null): int {
        try {
            return $this->repo->countExtended($categoryId);
        } catch (Exception $e) {
            error_log("SubcategoryService::countSubcategories Error: " . $e->getMessage());
            return 0;
        }
    }

    public function getSubcategoryById(int $id): ?array {
        return $this->repo->getById($id);
    }

    public function createSubcategory(array $data, ?array $files = null): int {
        if (empty($data['name']) || empty($data['category_id'])) {
            throw new Exception("Name and Parent Category are required.");
        }

        $fileService = new FileService('subcategories');
        
        // Handle Hero Image
        if (isset($files['hero_image']) && $files['hero_image']['error'] === UPLOAD_ERR_OK) {
            $data['hero_image'] = $fileService->upload($files['hero_image']);
        }
        
        // Handle Thumbnail Image
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $data['image_path'] = $fileService->upload($files['image']);
        }

        return $this->repo->create($data);
    }

    public function updateSubcategory(int $id, array $data, ?array $files = null): bool {
        // Fetch existing if partial update
        $existing = $this->repo->getById($id);
        if (!$existing) throw new Exception("Subcategory not found.");

        $fileService = new FileService('subcategories');
        
        // Handle Hero Image
        if (isset($files['hero_image']) && $files['hero_image']['error'] === UPLOAD_ERR_OK) {
            $newHero = $fileService->upload($files['hero_image']);
            if ($newHero) {
                if (!empty($existing['hero_image'])) $fileService->delete($existing['hero_image']);
                $data['hero_image'] = $newHero;
            }
        }
        
        // Handle Thumbnail Image
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $newThumb = $fileService->upload($files['image']);
            if ($newThumb) {
                if (!empty($existing['image_path'])) $fileService->delete($existing['image_path']);
                $data['image_path'] = $newThumb;
            }
        }
        
        $updateData = array_merge($existing, $data);
        return $this->repo->update($id, $updateData);
    }

    public function deleteSubcategory(int $id): bool {
        return $this->repo->delete($id);
    }
}
