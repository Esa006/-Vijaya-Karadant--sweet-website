<?php
/**
 * Sweets Website
 * =============================================================
 * File: CategoryService.php
 * Description: Business logic for Category management (V3 Schema)
 * Author: Sweets Website Team
 * Version: 3.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/CategoryRepository.php';
require_once SERVICES_PATH . '/FileService.php';

class CategoryService {

    private CategoryRepository $repo;
    private FileService $fileService;

    public function __construct() {
        $this->repo = new CategoryRepository();
        $this->fileService = new FileService('categories');
    }

    /**
     * Create a new category 
     */
    public function createCategory(array $data, ?array $imageFile = null, ?array $heroImageFile = null): array {
        try {
            $name = trim($data['name'] ?? '');
            if (empty($name)) {
                throw new Exception("Category name is required.");
            }

            $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

            // Generate slug and ensure uniqueness
            $slug = $this->generateSlug($name);

            $imagePath = null;
            if ($imageFile && isset($imageFile['error']) && $imageFile['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->fileService->upload($imageFile);
            }

            $heroImagePath = null;
            if ($heroImageFile && isset($heroImageFile['error']) && $heroImageFile['error'] === UPLOAD_ERR_OK) {
                // You could use a separate directory or same. FileService logic appends unique ID.
                $heroImagePath = $this->fileService->upload($heroImageFile);
            }

            // Encode highlights to JSON if array
            $highlights = null;
            if (!empty($data['highlights']) && is_array($data['highlights'])) {
                $highlights = json_encode(array_values(array_filter($data['highlights'])));
            }

            $saveData = [
                'parent_id'   => $parentId,
                'name'        => $name,
                'slug'        => $slug,
                'sku'         => $data['sku'] ?? null,
                'description' => $data['description'] ?? null,
                'image_path'  => $imagePath,
                'hero_image'  => $heroImagePath,
                'regular_price' => isset($data['regular_price']) && $data['regular_price'] !== '' ? (float)$data['regular_price'] : null,
                'discount_price' => isset($data['discount_price']) && $data['discount_price'] !== '' ? (float)$data['discount_price'] : null,
                'tax_rate'      => $data['tax_rate'] ?? null,
                'weight'        => $data['weight'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'highlights'    => $highlights,
                'ingredients'   => $data['ingredients'] ?? null,
                'benefits'      => $data['benefits'] ?? null,
                'storage_instructions' => $data['storage_instructions'] ?? null,
                'status'      => $data['status'] ?? 'active'
            ];

            $id = $this->repo->create($saveData);

            return [
                'success' => true,
                'message' => 'Category created successfully.',
                'data'    => ['id' => $id]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    /**
     * Update an existing category
     */
    public function updateCategory(int $id, array $data, ?array $imageFile = null, ?array $heroImageFile = null): array {
        try {
            $category = $this->repo->getById($id);
            if (!$category) {
                throw new Exception("Category not found.");
            }

            $name = trim($data['name'] ?? $category['name']);
            if (empty($name)) {
                throw new Exception("Category name is required.");
            }

            $parentId = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;

            // Prevent circular reference: category cannot be its own parent
            if ($parentId === $id) {
                throw new Exception("A category cannot be its own parent.");
            }

            // Handle optional slug override
            $slug = $category['slug'];
            if (!empty($data['slug']) && $data['slug'] !== $category['slug']) {
                 $slug = $this->generateSlug(trim($data['slug']), $id);
            }

            $imagePath = $category['image_path'];
            if ($imageFile && isset($imageFile['error']) && $imageFile['error'] === UPLOAD_ERR_OK) {
                $imagePath = $this->fileService->upload($imageFile);
            }

            $heroImagePath = $category['hero_image'];
            if ($heroImageFile && isset($heroImageFile['error']) && $heroImageFile['error'] === UPLOAD_ERR_OK) {
                $heroImagePath = $this->fileService->upload($heroImageFile);
            }

            // Encode highlights to JSON if array
            $highlights = $category['highlights'];
            if (isset($data['highlights'])) {
                if (is_array($data['highlights'])) {
                    // Filter empty entries
                    $highlights = json_encode(array_values(array_filter($data['highlights'])));
                } else {
                    $highlights = null; // Cleared
                }
            }

            $updateData = [
                'name'        => $name,
                'parent_id'   => $parentId,
                'description' => isset($data['description']) ? $data['description'] : $category['description'],
                'slug'        => $slug,
                'sku'         => isset($data['sku']) ? $data['sku'] : $category['sku'],
                'image_path'  => $imagePath,
                'hero_image'  => $heroImagePath,
                'regular_price' => isset($data['regular_price']) ? ($data['regular_price'] === '' ? null : (float)$data['regular_price']) : $category['regular_price'],
                'discount_price' => isset($data['discount_price']) ? ($data['discount_price'] === '' ? null : (float)$data['discount_price']) : $category['discount_price'],
                'tax_rate'      => isset($data['tax_rate']) ? $data['tax_rate'] : $category['tax_rate'],
                'weight'        => isset($data['weight']) ? $data['weight'] : $category['weight'],
                'short_description' => isset($data['short_description']) ? $data['short_description'] : $category['short_description'],
                'highlights'    => $highlights,
                'ingredients'   => isset($data['ingredients']) ? $data['ingredients'] : $category['ingredients'],
                'benefits'      => isset($data['benefits']) ? $data['benefits'] : $category['benefits'],
                'storage_instructions' => isset($data['storage_instructions']) ? $data['storage_instructions'] : $category['storage_instructions'],
                'status'      => $data['status'] ?? $category['status']
            ];


            $this->repo->update($id, $updateData);

            return [
                'success' => true,
                'message' => 'Category updated successfully.',
                'data'    => ['id' => $id]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    /**
     * Delete a category safely
     */
    public function deleteCategory(int $id): array {
        try {
            $category = $this->repo->getById($id);
            if (!$category) {
                throw new Exception("Category not found.");
            }

            $subcatCount = $this->repo->countSubcategories($id);
            if ($subcatCount > 0) {
                throw new Exception("Cannot delete a category that has subcategories. Delete or move subcategories first.");
            }

            $this->repo->delete($id);

            return [
                'success' => true,
                'message' => 'Category deleted successfully.',
                'data'    => []
            ];

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return [
                    'success' => false,
                    'message' => 'Cannot delete this category because there are products linked to it.',
                    'data'    => []
                ];
            }
            return [
                'success' => false,
                'message' => 'Database error during deletion.',
                'data'    => []
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data'    => []
            ];
        }
    }

    /**
     * Generate unique slug
     */
    private function generateSlug(string $name, int $excludeId = 0): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->repo->existsBySlug($slug, $excludeId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Get root categories (parent_id IS NULL)
     */
    public function getRootCategories(): array {
        return $this->repo->getRootCategories();
    }

    /**
     * Get tree
     */
    public function getCategoriesTree(): array {
        return $this->repo->getTree();
    }
}
