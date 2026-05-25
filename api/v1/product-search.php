<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/product-search.php
 * Description: Lightweight product search suggestions endpoint
 * =============================================================
 */

require_once __DIR__ . '/../../config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

$query = trim((string)($_GET['q'] ?? $_GET['search'] ?? ''));
$limit = (int)($_GET['limit'] ?? 6);
$limit = max(1, min($limit, 10));

if (strlen($query) < 2) {
    echo json_encode([
        'success' => true,
        'query' => $query,
        'results' => []
    ]);
    exit;
}

try {
    $productService = new ProductService();
    $products = $productService->getFilteredProducts(['search' => $query], 'name');
    $results = [];

    foreach ($products as $product) {
        if (count($results) >= $limit) {
            break;
        }

        $slug = (string)($product['slug'] ?? '');
        $id = (int)($product['id'] ?? 0);
        if ($slug === '' && $id <= 0) {
            continue;
        }

        $image = trim((string)($product['image_path'] ?? 'assets/images/placeholders/product-placeholder.png'));
        if ($image === '') {
            $image = 'assets/images/placeholders/product-placeholder.png';
        }

        $basePrice = (float)($product['base_price'] ?? 0);
        $salePrice = $product['sale_price'] ?? null;
        $displayPrice = $salePrice !== null && $salePrice !== '' ? (float)$salePrice : $basePrice;
        $detailUrl = $id > 0
            ? BASE_URL . 'product-details.php?id=' . $id
            : BASE_URL . 'product-details.php?slug=' . urlencode($slug);

        $results[] = [
            'name' => (string)($product['name'] ?? 'Product'),
            'slug' => $slug,
            'category' => (string)($product['category_name'] ?? ''),
            'image' => BASE_URL . ltrim(str_replace('\\', '/', $image), '/'),
            'price' => $displayPrice,
            'price_formatted' => 'Rs. ' . number_format($displayPrice, 2),
            'url' => $detailUrl
        ];
    }

    echo json_encode([
        'success' => true,
        'query' => $query,
        'results' => $results
    ]);
} catch (Throwable $e) {
    error_log('[Product Search API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Search is temporarily unavailable.',
        'results' => []
    ]);
}
