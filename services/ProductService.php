<?php
/**
 * Sweets Website
 * =============================================================
 * File: ProductService.php
 * Description: Business logic layer — sits between controller & repository
 * Pattern: Service Layer Pattern
 * Author: Sweets Website Team
 * Version: 2.0.0
 * =============================================================
 */

require_once ROOT_PATH . '/repositories/ProductRepository.php';
require_once ROOT_PATH . '/config/Database.php';
require_once ROOT_PATH . '/services/FileService.php';

class ProductService {

    private ProductRepository $repo;
    private ?ComboRepository $comboRepo = null;
    private ?AuditService $auditService = null;
    private PDO $db;

    private ?FileService $fileService = null;

    public function __construct(
        ?ProductRepository $repo = null,
        ?PDO $db = null,
        ?FileService $fileService = null,
        ?AuditService $auditService = null,
        ?ComboRepository $comboRepo = null
    ) {
        $this->db = $db ?? Database::getInstance();
        $this->repo = $repo ?? new ProductRepository($this->db);
        $this->fileService = $fileService;
        $this->auditService = $auditService;
        $this->comboRepo = $comboRepo;
    }

    private function getComboRepo(): ComboRepository {
        if (!$this->comboRepo) {
            require_once ROOT_PATH . '/repositories/ComboRepository.php';
            $this->comboRepo = new ComboRepository();
        }
        return $this->comboRepo;
    }

    private function getAuditService(): AuditService {
        if (!$this->auditService) {
            require_once ROOT_PATH . '/services/AuditService.php';
            $this->auditService = new AuditService();
        }
        return $this->auditService;
    }

    /**
     * Get featured best seller products for the homepage.
     */
    public function getFeaturedProducts(int $limit = 0): array {
        try {
            $products = $this->repo->getFeaturedProducts($limit);
            return !empty($products) ? $products : [];
        } catch (Exception $e) {
            error_log('[ProductService] getFeaturedProducts failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get products for the signature collection slider.
     */
    public function getCollectionProducts(int $limit = 6): array {
        try {
            $products = $this->repo->getCollectionProducts($limit);
            return !empty($products) ? $products : [];
        } catch (Exception $e) {
            error_log('[ProductService] getCollectionProducts failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get products filtered by category slug.
     */
    public function getProductsByCategory(string $categorySlug): array {
        try {
            $categorySlug = strtolower((string)$categorySlug);
            
            // Special handling for Combos
            if ($categorySlug === 'combos' || $categorySlug === 'combo') {
                $combos = $this->getComboRepo()->getAllCombos(true);
                return $this->mapCombosToProducts($combos);
            }

            $products = $this->repo->getProductsByCategory($categorySlug);
            return !empty($products) ? $products : [];
        } catch (Exception $e) {
            error_log('[ProductService] getProductsByCategory failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Map combo records to the standard product format for UI compatibility
     */
    private function mapCombosToProducts(array $combos): array {
        $mapped = [];
        foreach ($combos as $combo) {
            // Calculate pricing if not already present (ComboService usually does this, 
            // but we're calling repo directly to avoid circular dependency or heavy service overhead)
            $originalPrice = 0.0;
            $derivedPrice  = 0.0;
            $isOutOfStock  = false;

            if (!empty($combo['items'])) {
                foreach ($combo['items'] as $item) {
                    $qty  = (int)($item['quantity'] ?? 1);
                    $base = (float)($item['base_price'] ?: $item['sale_price'] ?: 0);
                    $sale = (float)($item['sale_price'] ?: $item['base_price'] ?: 0);

                    $originalPrice += ($base * $qty);
                    $derivedPrice  += ($sale * $qty);

                    $availableStock = (int)($item['stock'] ?? 0);
                    $itemStatus     = $item['status'] ?? 'published';
                    if ($itemStatus === 'out_of_stock' || $availableStock < $qty) {
                        $isOutOfStock = true;
                    }
                }
            }

            $finalPrice = ($combo['price'] !== null && $combo['price'] > 0) ? (float)$combo['price'] : $derivedPrice;

            $mapped[] = [
                'id'                => 0, // Set to 0 to force slug-based routing in detail pages
                'combo_id'          => (int)$combo['id'],
                'name'              => $combo['name'],
                'slug'              => $combo['slug'],
                'image_path'        => $combo['image'] ?? 'assets/images/placeholders/product-placeholder.png',
                'base_price'        => $originalPrice,
                'sale_price'        => $finalPrice,
                'short_description' => $combo['description'] ?? '',
                'stock_quantity'    => $isOutOfStock ? 0 : 100,
                'category_slug'     => 'combos',
                'is_combo'          => true,
                'items'             => $combo['items'] ?? [], // Include items for gallery usage
                'rating'            => 4.8, 
                'reviews_count'     => 45
            ];
        }
        return $mapped;
    }

    /**
     * Get a single product by slug (for detail pages).
     */
    public function getProductBySlug(string $slug): ?array {
        try {
            $product = $this->repo->getBySlug($slug);
            if ($product) {
                return $this->mergeWithFallbackProduct($product, $slug);
            }

            // Check if it's a combo
            $combo = $this->getComboRepo()->getBySlug($slug);
            if ($combo) {
                return $this->mapComboToSingleProduct($combo);
            }

            return $this->getFallbackProductBySlug($slug);
        } catch (Exception $e) {
            error_log('[ProductService] getProductBySlug failed: ' . $e->getMessage());
            return $this->getFallbackProductBySlug($slug);
        }
    }

    /**
     * Map a single combo record to standard product format for the detail page
     */
    private function mapComboToSingleProduct(array $combo): array {
        // Reuse mapping logic but for a single item
        $mapped = $this->mapCombosToProducts([$combo]);
        return reset($mapped) ?: null;
    }

    /**
     * Get a single product by id.
     */
    public function getProductById(int $id): ?array {
        try {
            $product = $this->repo->getProductById($id);
            if ($product) {
                $slug = (string)($product['slug'] ?? '');
                return $slug !== '' ? $this->mergeWithFallbackProduct($product, $slug) : $product;
            }

            return $this->getFallbackProductById($id);
        } catch (Exception $e) {
            error_log('[ProductService] getProductById failed: ' . $e->getMessage());
            return $this->getFallbackProductById($id);
        }
    }

    /**
     * Get all images for a product (for gallery views).
     */
    public function getProductImages(int $productId): array {
        try {
            return $this->repo->getProductImages($productId);
        } catch (Exception $e) {
            error_log('[ProductService] getProductImages failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all images for a product by slug (for sliders like Box of Joy).
     */
    public function getSliderImages(string $slug): array {
        // Fallback to main image for now
        $product = $this->getProductBySlug($slug);
        if ($product && !empty($product['image_path'])) {
            return [['image_path' => $product['image_path']]];
        }
        return [];
    }

    /**
     * Get all active variants for a single product.
     */
    public function getProductVariants(int $productId): array {
        try {
            if ($productId <= 0) {
                return [];
            }

            $dbVariants = $this->repo->getVariantsByProductId($productId);
            
            // Standardize variants
            $standardWeights = ['250g', '500g', '1kg'];
            $existingWeights = array_column($dbVariants, 'weight');
            
            $basePrice = 0;
            if (!empty($dbVariants)) {
                // Try to find 500g price as base
                foreach ($dbVariants as $v) {
                    if ($v['weight'] === '500g') {
                        $basePrice = (float)$v['price'];
                        break;
                    }
                }
                if ($basePrice === 0) $basePrice = (float)$dbVariants[0]['price'];
            }
            
            // If DB variants are empty, maybe fallback product
            if ($basePrice === 0) {
                // Try to fetch base price from fallback
                $f = $this->getFallbackProductById($productId);
                if ($f) {
                    $basePrice = (float)($f['sale_price'] ?? $f['base_price'] ?? 500);
                }
                if ($basePrice === 0) $basePrice = 500;
            }

            // Always append standard weights if missing so UI can show them as crossed-out (Ajio style)
            foreach ($standardWeights as $weight) {
                if (!in_array($weight, $existingWeights)) {
                    $multiplier = 1;
                    if ($weight === '250g') $multiplier = 0.55; 
                    if ($weight === '1kg') $multiplier = 1.95; 
                    
                    // Set simulated stock to 0 so it gets crossed out in UI
                    $simulatedStock = 0;
                    $simulatedStatus = 'published'; // Must be 'published' to avoid being filtered out in product-detail.php
                    
                    $dbVariants[] = [
                        'id' => 0,
                        'product_id' => $productId,
                        'weight' => $weight,
                        'label' => $weight . ' Standard Pack',
                        'price' => $basePrice * $multiplier,
                        'stock' => $simulatedStock,
                        'sku' => '',
                        'status' => $simulatedStatus,
                        'restock_eta' => '',
                        'preorder_enabled' => 0
                    ];
                }
            }

            // Sort variants logically
            usort($dbVariants, function($a, $b) {
                $order = ['250g' => 1, '500g' => 2, '1kg' => 3];
                $valA = $order[$a['weight'] ?? ''] ?? 99;
                $valB = $order[$b['weight'] ?? ''] ?? 99;
                return $valA <=> $valB;
            });



            return $dbVariants;
        } catch (Exception $e) {
            error_log('[ProductService] getProductVariants failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get luxury gift boxes from DB (consolidated from 'gift-box' and 'gifting')
     */
    public function getGiftBoxes(): array {
        $giftBoxProducts = $this->getProductsByCategory('gift-box');
        $giftingProducts = $this->getProductsByCategory('gifting');
        
        return array_merge($giftBoxProducts, $giftingProducts);
    }

    /**
     * Get customer testimonials.
     * Hardcoded for now until testimonials table is created.
     */
    public function getTestimonials(): array {
        return $this->getFallbackTestimonials();
    }

    /**
     * Get products filtered by various criteria and sorted by preference.
     * 
     * @param array $filters
     * @param string $sortBy
     * @return array
     */
    public function getFilteredProducts(array $filters = [], string $sortBy = 'newest'): array {
        try {
            if (!empty($filters['category'])) {
                $filters['category'] = strtolower((string)$filters['category']);
            }
            if (isset($filters['search'])) {
                $filters['search'] = trim((string)$filters['search']);
            }
            $products = $this->repo->getFilteredProducts($filters, $sortBy);

            foreach ($products as &$product) {
                $slug = (string)($product['slug'] ?? '');
                if ($slug !== '') {
                    $product = $this->mergeWithFallbackProduct($product, $slug);
                }
            }
            unset($product);

            if (!empty($filters['search'])) {
                $products = $this->mergeUniqueProducts(
                    $products,
                    $this->searchFallbackProducts((string)$filters['search'], (string)($filters['category'] ?? ''))
                );
            }

            return $products;
        } catch (Exception $e) {
            error_log('[ProductService] getFilteredProducts failed: ' . $e->getMessage());
            if (!empty($filters['search'])) {
                return $this->searchFallbackProducts((string)$filters['search'], (string)($filters['category'] ?? ''));
            }
            return [];
        }
    }

    public function getRelatedProducts(array $currentProduct, int $limit = 4): array {
        $currentSlug = (string)($currentProduct['slug'] ?? '');
        $currentId   = (int)($currentProduct['id'] ?? 0);
        $categorySlug = strtolower((string)(
            $currentProduct['category_slug']
            ?? $currentProduct['effective_category_slug']
            ?? $currentProduct['parent_category_slug']
            ?? ''
        ));

        $related = [];

        if ($categorySlug !== '') {
            try {
                $related = $this->repo->getFilteredProducts(['category' => $categorySlug], 'name');
            } catch (Exception $e) {
                error_log('[ProductService] getRelatedProducts DB failed: ' . $e->getMessage());
                $related = [];
            }

            foreach ($related as &$product) {
                $slug = (string)($product['slug'] ?? '');
                if ($slug !== '') {
                    $product = $this->mergeWithFallbackProduct($product, $slug);
                }
            }
            unset($product);

            $related = $this->mergeUniqueProducts($related, $this->getFallbackProductsByCategory($categorySlug));
        }

        if (count($related) < $limit) {
            $related = $this->mergeUniqueProducts($related, $this->getFallbackProductsByCategory('karadant'));
            $related = $this->mergeUniqueProducts($related, $this->getFallbackProductsByCategory('laddu'));
            $related = $this->mergeUniqueProducts($related, $this->getFallbackProductsByCategory('namkeen'));
            $related = $this->mergeUniqueProducts($related, $this->getFallbackProductsByCategory('gift-box'));
        }

        // Remove current product by slug OR by id (catches slug mismatch cases)
        $related = array_values(array_filter($related, static function (array $product) use ($currentSlug, $currentId): bool {
            $slug = (string)($product['slug'] ?? '');
            $id   = (int)($product['id']   ?? 0);
            if ($slug === '' ) return false;
            if ($slug === $currentSlug) return false;
            if ($currentId > 0 && $id === $currentId) return false;
            return true;
        }));

        // Final dedup by normalised name (catches same product under two slugs)
        $seen = []; $unique = [];
        foreach ($related as $product) {
            $nameKey = strtolower(trim((string)($product['name'] ?? '')));
            $slugKey = (string)($product['slug'] ?? '');
            $key = $nameKey ?: $slugKey;
            if ($key !== '' && isset($seen[$key])) continue;
            $seen[$key] = true;
            $unique[] = $product;
        }

        return array_slice($unique, 0, $limit);
    }

    private function searchFallbackProducts(string $query, string $categorySlug = ''): array {
        $query = strtolower(trim($query));
        $categorySlug = strtolower(trim($categorySlug));
        if ($query === '') {
            return [];
        }

        $matches = [];
        foreach ($this->getFallbackProductSlugs() as $slug) {
            $product = $this->getFallbackProductBySlug($slug);
            if (!$product) {
                continue;
            }

            $productCategory = strtolower((string)($product['category_slug'] ?? ''));
            if ($categorySlug !== '' && $categorySlug !== 'all' && $productCategory !== $categorySlug) {
                continue;
            }

            $haystack = strtolower(trim(
                (string)($product['name'] ?? '') . ' ' .
                (string)($product['slug'] ?? '') . ' ' .
                (string)($product['short_description'] ?? '') . ' ' .
                $productCategory
            ));

            $searchWords = array_filter(explode(' ', $query));
            $matchAll = true;
            foreach ($searchWords as $word) {
                if (strpos($haystack, $word) === false) {
                    $matchAll = false;
                    break;
                }
            }

            if ($matchAll) {
                $matches[] = $product;
            }
        }

        return $matches;
    }

    private function getFallbackProductsByCategory(string $categorySlug): array {
        $categorySlug = strtolower(trim($categorySlug));
        if ($categorySlug === '') {
            return [];
        }

        $products = [];
        foreach ($this->getFallbackProductSlugs() as $slug) {
            $product = $this->getFallbackProductBySlug($slug);
            if (!$product) {
                continue;
            }

            if (strtolower((string)($product['category_slug'] ?? '')) === $categorySlug) {
                $products[] = $product;
            }
        }

        return $products;
    }

    private function mergeUniqueProducts(array $primary, array $secondary): array {
        $seen = [];
        foreach ($primary as $product) {
            $slug = (string)($product['slug'] ?? '');
            if ($slug !== '') {
                $seen[$slug] = true;
            }
        }

        foreach ($secondary as $product) {
            $slug = (string)($product['slug'] ?? '');
            if ($slug !== '' && isset($seen[$slug])) {
                continue;
            }
            if ($slug !== '') {
                $seen[$slug] = true;
            }
            $primary[] = $product;
        }

        return $primary;
    }

    private function getFallbackProductSlugs(): array {
        return [
            'premium-vijaya-karadant',
            'classic-vijaya-karadant',
            'supreme-vijaya-karadant',
            'supreme-vijaya-karadant-offer',
            'premium-karadant-pack',
            'premium-karadant-special',
            'regal-anjeer-karadant',
            'gandahagiri-laddu-premium',
            'dink-laddu',
            'ragi-laddu',
            'besan-laddu',
            'premium-ladagi-laddu',
            'otts-laddu',
            'til-laddu',
            'peanut-laddu',
            'gandahagiri-laddu',
            'gandhagiri-laddu',
            'spicy-mix-namkeen',
            'golden-sev',
            'masala-peanuts',
            'premium-mixture',
            'all-in-one-mix',
            'bengaluru-mix',
            'butter-muruku',
            'rice-kodubale',
            'garlic-ribbon',
            'nippattu',
            'onion-kodubale',
            'ribbon-pakoda',
            'tilkut-vijaya-karadant',
            'raga-anjeer-karadant',
            'premium-dink-laddu',
            'mawa-vijaya-karadant',
            'royal-vijaya-karadant',
            'premium-gift-box',
            'tilkut-gift-box',
            'supreme-gift-box',
            'anjeer-gift-box',
            'dink-laddu-gift-box',
            'mawa-gift-box'
        ];
    }

    /**
     * Get product statistics for the dashboard.
     */
    public function getProductStats(): array {
        try {
            return $this->repo->getProductStats();
        } catch (Exception $e) {
            error_log('[ProductService] getProductStats failed: ' . $e->getMessage());
            return ['total' => 0, 'in_stock' => 0, 'low_stock' => 0, 'out_of_stock' => 0];
        }
    }

    /**
     * Create a new product with image and inventory initialization
     */
    public function createProduct(array $data, ?array $imageFile = null): int {
        // 1. Image Upload
        if ($imageFile) {
            $fileService = $this->fileService ?? new FileService();
            $data['image_path'] = $fileService->upload($imageFile);
        }

        // 2. Slug Generation
        $data['slug'] = $this->generateUniqueSlug($data['name']);

        // 3. Data Normalization
        $repoData = [
            'category_id'       => (!empty($data['category_id']) && (int)$data['category_id'] > 0) ? (int)$data['category_id'] : null,
            'subcategory_id'    => (!empty($data['subcategory_id']) && (int)$data['subcategory_id'] > 0) ? (int)$data['subcategory_id'] : null,
            'name'              => strip_tags($data['name']),
            'slug'              => $data['slug'],
            'weight'            => !empty($data['weight']) ? strip_tags((string)$data['weight']) : null,
            'short_description' => strip_tags($data['short_description'] ?? ''),
            'description'       => $data['description'] ?? '',
            'base_price'        => (float)($data['base_price'] ?? 0),
            'sale_price'        => (!empty($data['sale_price']) && (float)$data['sale_price'] > 0) ? (float)$data['sale_price'] : null,
            'tax_rate'          => (float)($data['tax_rate'] ?? 0.00),
            'sku'               => !empty($data['sku']) ? strip_tags($data['sku']) : null,
            'image_path'        => $data['image_path'] ?? 'assets/images/placeholders/product-placeholder.png',
            'status'            => $data['status'] ?? 'published',
            'stock_quantity'    => (int)($data['stock_quantity'] ?? 0),
            'featured'          => isset($data['featured']) ? (int)$data['featured'] : 0
        ];

        $this->db->beginTransaction();
        try {
            $productId = $this->repo->create($repoData);
            if ($productId > 0) {
                $this->getAuditService()->log('product', $productId, 'create', $_SESSION['user_id'] ?? null, $repoData);
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }
            return $productId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'sku') !== false) {
                throw new Exception("The SKU '" . $repoData['sku'] . "' is already in use by another product.", 409);
            }
            error_log('[ProductService] createProduct SQL failed: ' . $e->getMessage());
            throw new Exception("Database error: " . $e->getMessage(), 500);
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[ProductService] createProduct failed: ' . $e->getMessage());
            throw new Exception("Product creation failed: " . $e->getMessage(), 500);
        }
    }

    /**
     * Update an existing product
     */
    public function updateProduct(int $id, array $data, ?array $imageFile = null, ?array $galleryFiles = null): bool {
        $existing = $this->repo->getById($id);
        if (!$existing) return false;

        $fileService = $this->fileService ?? new FileService();

        // 1. Handle Main Image
        if ($imageFile && !empty($imageFile['tmp_name'])) {
            $newPath = $fileService->upload($imageFile);
            if ($newPath) {
                // Delete old image if not placeholder
                if (!empty($existing['image_path']) && strpos($existing['image_path'], 'placeholder') === false) {
                    $fileService->delete($existing['image_path']);
                }
                $data['image_path'] = $newPath;
            }
        }

        // 2. Handle Gallery Images (Multipart array)
        if ($galleryFiles && !empty($galleryFiles['name'][0])) {
            $uploadedCount = 0;
            foreach ($galleryFiles['name'] as $key => $name) {
                if ($galleryFiles['error'][$key] === UPLOAD_ERR_OK) {
                    $singleFile = [
                        'name'     => $galleryFiles['name'][$key],
                        'type'     => $galleryFiles['type'][$key],
                        'tmp_name' => $galleryFiles['tmp_name'][$key],
                        'error'    => $galleryFiles['error'][$key],
                        'size'     => $galleryFiles['size'][$key]
                    ];
                    
                    $path = $fileService->upload($singleFile);
                    if ($path) {
                        // If current image is placeholder or empty, promote this first upload to main
                        $currentMain = $data['image_path'] ?? $existing['image_path'] ?? '';
                        $isPlaceholder = empty($currentMain) || strpos($currentMain, 'placeholder') !== false;
                        
                        if ($isPlaceholder && $uploadedCount === 0 && !isset($data['image_path'])) {
                            $data['image_path'] = $path;
                            $this->repo->addProductImage($id, $path, true); // Set as main in gallery too
                        } else {
                            $this->repo->addProductImage($id, $path, false);
                        }
                        $uploadedCount++;
                    }
                }
            }
        }

        // 3. Data Normalization
        $updateData = [];
        $allowedFields = [
            'category_id', 'subcategory_id', 'name', 'short_description', 'description', 
            'base_price', 'sale_price', 'tax_rate', 'sku', 
            'image_path', 'status', 'stock_quantity', 'featured'
        ];

        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $val = $data[$field];
                
                // Normalization rules for optional/relational fields
                if ($field === 'category_id' || $field === 'subcategory_id') {
                    $updateData[$field] = (!empty($val) && (int)$val > 0) ? (int)$val : null;
                } elseif ($field === 'sku') {
                    $updateData[$field] = !empty($val) ? strip_tags($val) : null;
                } elseif ($field === 'sale_price') {
                    $updateData[$field] = (!empty($val) && (float)$val > 0) ? (float)$val : null;
                } elseif ($field === 'featured') {
                    $updateData[$field] = ($val === '1' || $val === 1 || $val === true) ? 1 : 0;
                } else {
                    $updateData[$field] = $val;
                }
            }
        }

        $this->db->beginTransaction();
        try {
            $success = $this->repo->update($id, $updateData);
            if ($success) {
                $this->getAuditService()->log('product', $id, 'update', $_SESSION['user_id'] ?? null, $updateData);
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }
            return $success;
        } catch (PDOException $e) {
            $this->db->rollBack();
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'sku') !== false) {
                throw new Exception("The SKU '" . ($updateData['sku'] ?? 'unknown') . "' is already in use by another product.", 409);
            }
            error_log('[ProductService] updateProduct SQL failed: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[ProductService] updateProduct failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a specific gallery image
     */
    public function deleteProductImage(int $productId, int $imageId): bool {
        try {
            // Get image details first to delete file
            $images = $this->repo->getProductImages($productId);
            $target = null;
            foreach ($images as $img) {
                if ((int)$img['id'] === $imageId) {
                    $target = $img;
                    break;
                }
            }

            if ($target) {
                $fileService = $this->fileService ?? new FileService();
                $fileService->delete($target['image_path']);
                return $this->repo->deleteImage($imageId);
            }
            return false;
        } catch (Exception $e) {
            error_log('[ProductService] deleteProductImage failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a product (Soft Delete)
     */
    public function setProductMainImage(int $productId, int $imageId): bool {
        try {
            $images = $this->repo->getProductImages($productId);
            $newMainPath = '';
            
            foreach ($images as $img) {
                if ((int)$img['id'] === $imageId) {
                    $newMainPath = $img['image_path'];
                    break;
                }
            }
            
            if (empty($newMainPath)) return false;
            
            // 1. Update repository: sets all other is_main=0 and this one=1
            // But wait, the repo doesn't have a specific method for this transition yet.
            // I'll use the update method with special image_path handling or add a new repo method.
            // Since repo->update handles image_path by updating products table AND product_images (ON DUPLICATE),
            // it's better to add a clean method to the repo.
            
            return $this->repo->setPrimaryImage($productId, $imageId, $newMainPath);
        } catch (Exception $e) {
            error_log('[ProductService] Error setting main image: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteProduct(int $id): bool {
        $this->db->beginTransaction();
        try {
            $success = $this->repo->delete($id);
            if ($success) {
                $this->getAuditService()->log('product', $id, 'delete', $_SESSION['user_id'] ?? null);
                $this->db->commit();
            } else {
                $this->db->rollBack();
            }
            return $success;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('[ProductService] deleteProduct failed: ' . $e->getMessage());
            return false;
        }
    }
    private function generateUniqueSlug(string $name): string {
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = $baseSlug;
        $counter = 1;

        while ($this->repo->getBySlug($slug)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Format a MySQL DATETIME string into a human-readable relative time string.
     */
    private function formatRelativeTime(string $datetime): string {
        try {
            $now  = new DateTime();
            $past = new DateTime($datetime);
            $diff = $now->diff($past);

            $totalDays = (int)$diff->days;

            if ($totalDays === 0) {
                $hours = (int)$diff->h;
                if ($hours === 0) {
                    $mins = (int)$diff->i;
                    return $mins <= 1 ? 'Just now' : $mins . ' mins ago';
                }
                return $hours === 1 ? '1 hour ago' : $hours . ' hours ago';
            }
            if ($totalDays === 1) return 'Yesterday';
            if ($totalDays < 7)  return $totalDays . ' days ago';
            if ($totalDays < 14) return '1 week ago';
            if ($totalDays < 30) return (int)($totalDays / 7) . ' weeks ago';
            if ($totalDays < 60) return '1 month ago';
            if ($totalDays < 365) return (int)($totalDays / 30) . ' months ago';
            return (int)($totalDays / 365) . ' yr ago';
        } catch (Exception $e) {
            return 'Recently';
        }
    }

    /**
     * Get all products for the admin panel.
     */
    public function getAllProducts(): array {
        try {
            $products = $this->repo->getAllProducts();
            
            if (empty($products)) {
                return [];
            }

            foreach ($products as &$product) {
                // Ensure price is set
                $product['price'] = (float)($product['sale_price'] ?? $product['base_price'] ?? 0);
                
                // Formatted category name
                $product['category'] = $product['category_name'] ?? 'General';
                
                // Main image or fallback
                $imagePath = $product['image_path'] ?? '';
                if (empty($imagePath) || !file_exists(ROOT_PATH . '/' . $imagePath)) {
                    $product['image'] = 'assets/images/placeholders/product-placeholder.png';
                } else {
                    $product['image'] = $imagePath;
                }

                // Stock Status Logic (Respect explicit out_of_stock status OR calculate from quantity)
                $stock = (int)($product['stock_quantity'] ?? 0);
                $dbStatus = strtolower($product['status'] ?? '');
                
                if ($dbStatus === 'out_of_stock') {
                    $product['status_label'] = 'Out of Stock';
                    $product['status_class'] = 'products-status-out';
                } elseif ($stock > 10) {
                    $product['status_label'] = 'In Stock';
                    $product['status_class'] = 'products-status-in';
                } elseif ($stock > 0) {
                    $product['status_label'] = 'Low Stock';
                    $product['status_class'] = 'products-status-low';
                } else {
                    $product['status_label'] = 'Out of Stock';
                    $product['status_class'] = 'products-status-out';
                }
            }
            unset($product);

            return $products;
        } catch (Exception $e) {
            error_log('[ProductService] getAllProducts failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get grouped inventory data with variant-aware total stock.
     */
    public function getInventoryData(): array {
        try {
            $products = $this->repo->getAllProducts();
            if (empty($products)) {
                return [];
            }

            $productIds = array_map(static function (array $product): int {
                return (int)($product['id'] ?? 0);
            }, $products);

            $variants = $this->repo->getVariantsByProductIds($productIds);
            $variantsByProduct = [];
            foreach ($variants as $variant) {
                $pid = (int)($variant['product_id'] ?? 0);
                if ($pid <= 0) {
                    continue;
                }
                if (!isset($variantsByProduct[$pid])) {
                    $variantsByProduct[$pid] = [];
                }
                $variantsByProduct[$pid][] = $variant;
            }

            foreach ($products as &$product) {
                $productId = (int)($product['id'] ?? 0);
                $productVariants = $variantsByProduct[$productId] ?? [];
                $variantStockTotal = 0;

                foreach ($productVariants as &$variant) {
                    $variant['stock'] = (int)($variant['stock'] ?? 0);
                    $variantStockTotal += $variant['stock'];
                }
                unset($variant);

                $product['variants'] = $productVariants;
                $product['has_variants'] = !empty($productVariants);
                $product['total_stock'] = $product['has_variants']
                    ? $variantStockTotal
                    : (int)($product['stock_quantity'] ?? 0);

                // Keep backward-compatible key used by table + status calculation.
                $product['stock_quantity'] = $product['total_stock'];

                $product['price'] = (float)($product['sale_price'] ?? $product['base_price'] ?? 0);
                $product['category'] = $product['category_name'] ?? 'General';

                $imagePath = $product['image_path'] ?? '';
                if (empty($imagePath) || !file_exists(ROOT_PATH . '/' . $imagePath)) {
                    $product['image'] = 'assets/images/placeholders/product-placeholder.png';
                } else {
                    $product['image'] = $imagePath;
                }

                $stock = (int)$product['total_stock'];
                $dbStatus = strtolower($product['status'] ?? '');

                if ($dbStatus === 'out_of_stock') {
                    $product['status_label'] = 'Out of Stock';
                    $product['status_class'] = 'products-status-out';
                } elseif ($stock > 10) {
                    $product['status_label'] = 'In Stock';
                    $product['status_class'] = 'products-status-in';
                } elseif ($stock > 0) {
                    $product['status_label'] = 'Low Stock';
                    $product['status_class'] = 'products-status-low';
                } else {
                    $product['status_label'] = 'Out of Stock';
                    $product['status_class'] = 'products-status-out';
                }

                // Format updated_at as a human-readable relative time
                $rawTime = $product['updated_at'] ?? null;
                if ($rawTime && $rawTime !== '0000-00-00 00:00:00') {
                    $product['updated_at'] = $this->formatRelativeTime($rawTime);
                } else {
                    $product['updated_at'] = 'Recently';
                }
            }
            unset($product);

            return $products;
        } catch (Exception $e) {
            error_log('[ProductService] getInventoryData failed: ' . $e->getMessage());
            return [];
        }
    }

    // ── Static Component Data ──────────────────────────────────────────────────

    private function getFallbackTestimonials(): array {
        return [
            [
                'author' => 'Anjali Rao',
                'date' => '10/11/25',
                'text' => 'The traditional freshness was evident from the moment I opened the box. Perfect for occasions.',
                'rating' => 5,
                'type' => 'text'
            ],
            [
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'poster' => 'assets/images/homepage/Sub Container.png',
                'type' => 'video'
            ],
            [
                'author' => 'Vijay Deshmukh',
                'date' => '14/11/25',
                'text' => 'I really enjoyed the authentic flavour. It felt fresh and perfectly balanced. The quality was consistent in every bite.',
                'rating' => 5,
                'type' => 'text'
            ],
            [
                'video_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'poster' => 'assets/images/homepage/Sub Container.png',
                'type' => 'video'
            ]
        ];
    }

    private function getFallbackProductBySlug(string $slug): ?array {
        // Category Details Templates
        $karadantDetails = [
            'detailed_description' => 'Our signature Karadant is a nutrient-rich traditional sweet made with organic jaggery, premium nuts, and pure edible gum. A legacy of health and taste passed down through generations.',
            'ingredients' => 'Organic Jaggery, Cashews, Almonds, Pistachios, Edible Gum (Antu), Pure Cow Ghee, Dry Dates, Poppy Seeds, Cardamom.',
            'nutrition' => 'Energy: 450 kcal, Protein: 8g, Fat: 22g, Carbohydrates: 55g, Natural Sugars: 40g.',
            'storage' => 'Store in a cool, dry place. Best before 60 days from packaging.'
        ];

        $ladduDetails = [
            'detailed_description' => 'Handcrafted with traditional recipes and pure ingredients for a wholesome bite.',
            'ingredients' => 'Grain Flour (Besan/Ragi), Pure Cow Ghee, Organic Jaggery/Sugar, Cardamom, Roasted Nuts.',
            'nutrition' => 'Energy: 420 kcal, Protein: 6g, Fat: 18g, Carbohydrates: 60g, Sugars: 35g.',
            'storage' => 'Keep in an airtight container for lasting freshness up to 45 days.'
        ];

        $namkeenDetails = [
            'detailed_description' => 'A crisp and savory snack made with signature house spices and heritage recipes.',
            'ingredients' => 'Gram Flour (Besan), Cold Pressed Oil, Signature Spices, Curry Leaves, Peanuts, Lentils, Salt.',
            'nutrition' => 'Energy: 380 kcal, Protein: 12g, Fat: 25g, Carbohydrates: 40g, Sugars: 2g.',
            'storage' => 'Store in a dry, airtight jar to maintain crispness for 90 days.'
        ];

        $fallbackProducts = [
            'premium-vijaya-karadant' => array_merge($karadantDetails, [
                'id' => 1001,
                'name' => 'Premium Vijaya Karadant',
                'slug' => 'premium-vijaya-karadant',
                'short_description' => 'Our signature Karadant made with premium nuts and jaggery.',
                'base_price' => 720,
                'sale_price' => 650,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/New folder/karant/bestseeler karadant (1).png'
            ]),
            'classic-vijaya-karadant' => array_merge($karadantDetails, [
                'id' => 1002,
                'name' => 'Classic Vijaya Karadant',
                'slug' => 'classic-vijaya-karadant',
                'short_description' => 'Traditional Vijaya Karadant with authentic taste and texture.',
                'base_price' => 600,
                'sale_price' => 540,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/New folder/karant/bestseeler karadant (2).png'
            ]),
            'supreme-vijaya-karadant' => array_merge($karadantDetails, [
                'id' => 1003,
                'name' => 'Supreme Vijaya Karadant',
                'slug' => 'supreme-vijaya-karadant',
                'short_description' => 'Richer blend of nuts and jaggery for a premium bite.',
                'base_price' => 420,
                'sale_price' => 380,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/The Karadant Range (1).png'
            ]),
            'supreme-vijaya-karadant-offer' => array_merge($karadantDetails, [
                'id' => 1004,
                'name' => 'Supreme Vijaya Karadant Special',
                'slug' => 'supreme-vijaya-karadant-offer',
                'short_description' => 'Special offer pack of Supreme Vijaya Karadant.',
                'base_price' => 950,
                'sale_price' => 820,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/New folder/karant/bestseeler karadant (4).png'
            ]),
            'premium-karadant-pack' => array_merge($karadantDetails, [
                'id' => 1005,
                'name' => 'Premium Karadant Pack',
                'slug' => 'premium-karadant-pack',
                'short_description' => 'Premium Karadant family pack for festive moments.',
                'base_price' => 780,
                'sale_price' => 699,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/New folder/karant/bestseeler karadant (5).png'
            ]),
            'premium-karadant-special' => array_merge($karadantDetails, [
                'id' => 1006,
                'name' => 'Premium Karadant Special',
                'slug' => 'premium-karadant-special',
                'short_description' => 'Special edition premium Karadant with rich dry fruits.',
                'base_price' => 820,
                'sale_price' => 760,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/New folder/karant/bestseeler karadant (6).png'
            ]),
            'regal-anjeer-karadant' => array_merge($karadantDetails, [
                'id' => 1007,
                'name' => 'Regal Anjeer Karadant',
                'slug' => 'regal-anjeer-karadant',
                'short_description' => 'Anjeer-infused Karadant with a naturally rich sweetness.',
                'base_price' => 880,
                'sale_price' => 799,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/New folder/karant/bestseeler karadant (7).png'
            ]),
            'dink-laddu' => array_merge($ladduDetails, [
                'id' => 1009,
                'name' => 'Classic Dink Laddu',
                'slug' => 'dink-laddu',
                'short_description' => 'Traditional dink laddu for daily nourishment.',
                'base_price' => 480,
                'sale_price' => 430,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu 1.png'
            ]),
            'ragi-laddu' => array_merge($ladduDetails, [
                'id' => 1010,
                'name' => 'Healthy Ragi Laddu',
                'slug' => 'ragi-laddu',
                'short_description' => 'Wholesome ragi laddus with a roasted nutty taste.',
                'base_price' => 450,
                'sale_price' => 399,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu 2.png'
            ]),
            'besan-laddu' => array_merge($ladduDetails, [
                'id' => 1011,
                'name' => 'Signature Besan Laddu',
                'slug' => 'besan-laddu',
                'short_description' => 'Classic besan laddu made with pure ghee and gram flour.',
                'base_price' => 420,
                'sale_price' => 380,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu 3.png'
            ]),
            'premium-ladagi-laddu' => array_merge($ladduDetails, [
                'id' => 1012,
                'name' => 'Premium Ladagi Laddu',
                'slug' => 'premium-ladagi-laddu',
                'short_description' => 'Premium laddu assortment with rich dry fruits.',
                'base_price' => 550,
                'sale_price' => 499,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu 4.png'
            ]),
            'otts-laddu' => array_merge($ladduDetails, [
                'id' => 1013,
                'name' => 'Premium Otts Laddu',
                'slug' => 'otts-laddu',
                'short_description' => 'Soft and flavorful laddus with a traditional finish.',
                'base_price' => 500,
                'sale_price' => 450,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu 5.png'
            ]),
            'til-laddu' => array_merge($ladduDetails, [
                'id' => 1014,
                'name' => 'Healthy Til Laddu',
                'slug' => 'til-laddu',
                'short_description' => 'Sesame laddus with a warm jaggery sweetness.',
                'base_price' => 400,
                'sale_price' => 360,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu6.png'
            ]),
            'spicy-mix-namkeen' => array_merge($namkeenDetails, [
                'id' => 1018,
                'name' => 'Spicy Mix Namkeen',
                'slug' => 'spicy-mix-namkeen',
                'short_description' => 'A bold namkeen mix with signature house spices.',
                'base_price' => 320,
                'sale_price' => 280,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/homepage/Best Sellers (1).png'
            ]),
            'golden-sev' => array_merge($namkeenDetails, [
                'id' => 1019,
                'name' => 'Golden Sev',
                'slug' => 'golden-sev',
                'short_description' => 'Crispy golden sev, light and perfectly seasoned.',
                'base_price' => 280,
                'sale_price' => 250,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/homepage/Best Sellers (2).png'
            ]),
            'masala-peanuts' => array_merge($namkeenDetails, [
                'id' => 1020,
                'name' => 'Masala Peanuts',
                'slug' => 'masala-peanuts',
                'short_description' => 'Crunchy masala-coated peanuts with balanced heat.',
                'base_price' => 250,
                'sale_price' => 220,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/homepage/Best Sellers (5).png'
            ]),
            'premium-mixture' => array_merge($namkeenDetails, [
                'id' => 1021,
                'name' => 'High-Protein Premium Mixture',
                'slug' => 'premium-mixture',
                'short_description' => 'Premium crunchy mixture perfect for tea-time snacking.',
                'base_price' => 350,
                'sale_price' => 315,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/homepage/Best Sellers (7).png'
            ]),
            'all-in-one-mix' => array_merge($namkeenDetails, [
                'id' => 1022,
                'name' => 'All-in-One Signature Mix',
                'slug' => 'all-in-one-mix',
                'short_description' => 'A crunchy all-in-one namkeen blend with rich flavors.',
                'base_price' => 280,
                'sale_price' => 250,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (1).png'
            ]),
            'bengaluru-mix' => array_merge($namkeenDetails, [
                'id' => 1023,
                'name' => 'Authentic Bengaluru Mix',
                'slug' => 'bengaluru-mix',
                'short_description' => 'Regional style namkeen mix inspired by Bengaluru flavors.',
                'base_price' => 250,
                'sale_price' => 220,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (5).png'
            ]),
            'tilkut-vijaya-karadant' => array_merge($karadantDetails, [
                'id' => 1030,
                'name' => 'Tilkut Vijaya Karadant',
                'slug' => 'tilkut-vijaya-karadant',
                'short_description' => 'Traditional Tilkut Vijaya Karadant packed with nutrients.',
                'base_price' => 950,
                'sale_price' => 720,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (2).png'
            ]),
            'raga-anjeer-karadant' => array_merge($karadantDetails, [
                'id' => 1031,
                'name' => 'Raga Anjeer Karadant',
                'slug' => 'raga-anjeer-karadant',
                'short_description' => 'Exotic Raga Anjeer Karadant with the goodness of figs.',
                'base_price' => 950,
                'sale_price' => 720,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (4).png'
            ]),
            'premium-dink-laddu' => array_merge($ladduDetails, [
                'id' => 1032,
                'name' => 'Dink Laddu Gift Box',
                'slug' => 'premium-dink-laddu',
                'short_description' => 'Healthy Dink Laddu Gift Box for wellness gifting.',
                'base_price' => 720,
                'sale_price' => 650,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (5).png'
            ]),
            'mawa-vijaya-karadant' => array_merge($karadantDetails, [
                'id' => 1033,
                'name' => 'Mawa Gift Box',
                'slug' => 'mawa-vijaya-karadant',
                'short_description' => 'Rich Mawa Gift Box for traditional celebrations.',
                'base_price' => 720,
                'sale_price' => 650,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (6).png'
            ]),
            'royal-vijaya-karadant' => array_merge($karadantDetails, [
                'id' => 1034,
                'name' => 'Royal Vijaya Karadant',
                'slug' => 'royal-vijaya-karadant',
                'short_description' => 'Royal edition Vijaya Karadant for special occasions.',
                'base_price' => 760,
                'sale_price' => 690,
                'category_slug' => 'karadant',
                'image_path' => 'assets/images/homepage/The Karadant Range (2).png'
            ]),
            'peanut-laddu' => array_merge($ladduDetails, [
                'id' => 1015,
                'name' => 'Peanut Laddu',
                'slug' => 'peanut-laddu',
                'short_description' => 'Crunchy peanut laddus with classic jaggery notes.',
                'base_price' => 440,
                'sale_price' => 390,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu  7.png'
            ]),
            'gandhagiri-laddu' => array_merge($ladduDetails, [
                'id' => 1017,
                'name' => 'Gandahagiri Laddu',
                'slug' => 'gandhagiri-laddu',
                'short_description' => 'Heritage-style Gandahagiri laddu made in pure ghee.',
                'base_price' => 950,
                'sale_price' => 890,
                'category_slug' => 'laddu',
                'image_path' => 'assets/images/homepage/New folder/bestseller-laddu 8.png'
            ]),
            'butter-muruku' => array_merge($namkeenDetails, [
                'id' => 1024,
                'name' => 'Butter Muruku',
                'slug' => 'butter-muruku',
                'short_description' => 'Traditional butter muruku with a crisp bite.',
                'base_price' => 320,
                'sale_price' => 290,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (6).png'
            ]),
            'rice-kodubale' => array_merge($namkeenDetails, [
                'id' => 1025,
                'name' => 'Rice Kodubale',
                'slug' => 'rice-kodubale',
                'short_description' => 'Rice flour kodubale with classic spice blend.',
                'base_price' => 320,
                'sale_price' => 290,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (7).png'
            ]),
            'garlic-ribbon' => array_merge($namkeenDetails, [
                'id' => 1026,
                'name' => 'Garlic Ribbon',
                'slug' => 'garlic-ribbon',
                'short_description' => 'Ribbon snack with rich garlic flavor and crunch.',
                'base_price' => 320,
                'sale_price' => 290,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (8).png'
            ]),
            'nippattu' => array_merge($namkeenDetails, [
                'id' => 1027,
                'name' => 'Nippattu',
                'slug' => 'nippattu',
                'short_description' => 'Crisp nippattu with roasted spice notes.',
                'base_price' => 290,
                'sale_price' => 260,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (9).png'
            ]),
            'onion-kodubale' => array_merge($namkeenDetails, [
                'id' => 1028,
                'name' => 'Onion Kodubale',
                'slug' => 'onion-kodubale',
                'short_description' => 'Onion-flavored kodubale with spicy crisp texture.',
                'base_price' => 320,
                'sale_price' => 290,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (10).png'
            ]),
            'ribbon-pakoda' => array_merge($namkeenDetails, [
                'id' => 1029,
                'name' => 'Ribbon Pakoda',
                'slug' => 'ribbon-pakoda',
                'short_description' => 'Classic ribbon pakoda with crunchy savory finish.',
                'base_price' => 320,
                'sale_price' => 290,
                'category_slug' => 'namkeen',
                'image_path' => 'assets/images/banners/namkeen-page/our signature  (11).png'
            ]),
            'premium-gift-box' => array_merge($karadantDetails, [
                'id' => 1040,
                'name' => 'Premium Gift Box',
                'slug' => 'premium-gift-box',
                'short_description' => 'A luxurious assortment of our finest Karadant varieties.',
                'base_price' => 950,
                'sale_price' => 880,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (1).png'
            ]),
            'tilkut-gift-box' => array_merge($karadantDetails, [
                'id' => 1041,
                'name' => 'Tilkut Gift Box',
                'slug' => 'tilkut-gift-box',
                'short_description' => 'Traditional Tilkut sweets in a premium festive box.',
                'base_price' => 950,
                'sale_price' => 880,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (2).png'
            ]),
            'supreme-gift-box' => array_merge($karadantDetails, [
                'id' => 1042,
                'name' => 'Supreme Gift Box',
                'slug' => 'supreme-gift-box',
                'short_description' => 'Our most popular festive collection in a vibrant gift pack.',
                'base_price' => 950,
                'sale_price' => 880,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (3).png'
            ]),
            'anjeer-gift-box' => array_merge($karadantDetails, [
                'id' => 1043,
                'name' => 'Anjeer Gift Box',
                'slug' => 'anjeer-gift-box',
                'short_description' => 'Exotic Anjeer sweets beautifully packed for special occasions.',
                'base_price' => 950,
                'sale_price' => 880,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (4).png'
            ]),
            'dink-laddu-gift-box' => array_merge($ladduDetails, [
                'id' => 1044,
                'name' => 'Dink Laddu Gift Box',
                'slug' => 'dink-laddu-gift-box',
                'short_description' => 'Nutritious and delicious Dink Laddus in a premium gift set.',
                'base_price' => 950,
                'sale_price' => 880,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (5).png'
            ]),
            'mawa-gift-box' => array_merge($karadantDetails, [
                'id' => 1045,
                'name' => 'Mawa Gift Box',
                'slug' => 'mawa-gift-box',
                'short_description' => 'Rich Mawa-infused delicacies in a royal presentation box.',
                'base_price' => 950,
                'sale_price' => 880,
                'category_slug' => 'gift-box',
                'image_path' => 'assets/images/banners/gifing/Featured Gifting Specials (6).png'
            ])
        ];

        return $fallbackProducts[$slug] ?? null;
    }

    private function getFallbackProductById(int $id): ?array {
        $fallbackProducts = [
            'premium-vijaya-karadant' => [
                'id' => 1001,
                'slug' => 'premium-vijaya-karadant'
            ],
            'classic-vijaya-karadant' => [
                'id' => 1002,
                'slug' => 'classic-vijaya-karadant'
            ],
            'supreme-vijaya-karadant' => [
                'id' => 1003,
                'slug' => 'supreme-vijaya-karadant'
            ],
            'supreme-vijaya-karadant-offer' => [
                'id' => 1004,
                'slug' => 'supreme-vijaya-karadant-offer'
            ],
            'premium-karadant-pack' => [
                'id' => 1005,
                'slug' => 'premium-karadant-pack'
            ],
            'premium-karadant-special' => [
                'id' => 1006,
                'slug' => 'premium-karadant-special'
            ],
            'regal-anjeer-karadant' => [
                'id' => 1007,
                'slug' => 'regal-anjeer-karadant'
            ],
            'gandahagiri-laddu-premium' => [
                'id' => 1008,
                'slug' => 'gandahagiri-laddu-premium'
            ],
            'dink-laddu' => [
                'id' => 1009,
                'slug' => 'dink-laddu'
            ],
            'ragi-laddu' => [
                'id' => 1010,
                'slug' => 'ragi-laddu'
            ],
            'besan-laddu' => [
                'id' => 1011,
                'slug' => 'besan-laddu'
            ],
            'premium-ladagi-laddu' => [
                'id' => 1012,
                'slug' => 'premium-ladagi-laddu'
            ],
            'otts-laddu' => [
                'id' => 1013,
                'slug' => 'otts-laddu'
            ],
            'til-laddu' => [
                'id' => 1014,
                'slug' => 'til-laddu'
            ],
            'peanut-laddu' => [
                'id' => 1015,
                'slug' => 'peanut-laddu'
            ],
            'gandahagiri-laddu' => [
                'id' => 1016,
                'slug' => 'gandahagiri-laddu'
            ],
            'gandhagiri-laddu' => [
                'id' => 1017,
                'slug' => 'gandhagiri-laddu'
            ],
            'spicy-mix-namkeen' => [
                'id' => 1018,
                'slug' => 'spicy-mix-namkeen'
            ],
            'golden-sev' => [
                'id' => 1019,
                'slug' => 'golden-sev'
            ],
            'masala-peanuts' => [
                'id' => 1020,
                'slug' => 'masala-peanuts'
            ],
            'premium-mixture' => [
                'id' => 1021,
                'slug' => 'premium-mixture'
            ],
            'all-in-one-mix' => [
                'id' => 1022,
                'slug' => 'all-in-one-mix'
            ],
            'bengaluru-mix' => [
                'id' => 1023,
                'slug' => 'bengaluru-mix'
            ],
            'butter-muruku' => [
                'id' => 1024,
                'slug' => 'butter-muruku'
            ],
            'rice-kodubale' => [
                'id' => 1025,
                'slug' => 'rice-kodubale'
            ],
            'garlic-ribbon' => [
                'id' => 1026,
                'slug' => 'garlic-ribbon'
            ],
            'nippattu' => [
                'id' => 1027,
                'slug' => 'nippattu'
            ],
            'onion-kodubale' => [
                'id' => 1028,
                'slug' => 'onion-kodubale'
            ],
            'ribbon-pakoda' => [
                'id' => 1029,
                'slug' => 'ribbon-pakoda'
            ],
            'tilkut-vijaya-karadant' => [
                'id' => 1030,
                'slug' => 'tilkut-vijaya-karadant'
            ],
            'raga-anjeer-karadant' => [
                'id' => 1031,
                'slug' => 'raga-anjeer-karadant'
            ],
            'premium-dink-laddu' => [
                'id' => 1032,
                'slug' => 'premium-dink-laddu'
            ],
            'mawa-vijaya-karadant' => [
                'id' => 1033,
                'slug' => 'mawa-vijaya-karadant'
            ],
            'royal-vijaya-karadant' => [
                'id' => 1034,
                'slug' => 'royal-vijaya-karadant'
            ]
        ];

        foreach ($fallbackProducts as $slug => $meta) {
            if ((int)$meta['id'] === $id) {
                return $this->getFallbackProductBySlug($slug);
            }
        }

        return null;
    }

    private function mergeWithFallbackProduct(array $product, string $slug): array {
        $fallback = $this->getFallbackProductBySlug($slug);
        if (!$fallback) {
            return $product;
        }

        // Only backfill fields that are genuinely empty/null in the DB record.
        // Never overwrite a populated DB value with a static fallback.
        foreach ($fallback as $key => $value) {
            $current = $product[$key] ?? null;
            $isEmpty = ($current === null || $current === '' || $current === false);
            if ($isEmpty) {
                $product[$key] = $value;
            }
        }

        return $product;
    }

}
