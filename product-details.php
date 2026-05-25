<?php
/**
 * Sweets Website
 * =============================================================
 * File: product-details.php
 * Description: Bridge route for id/slug to product-detail page
 * =============================================================
 */

require_once 'config/config.php';
require_once SERVICES_PATH . '/ProductService.php';

$productService = new ProductService();

$id = (int)($_GET['id'] ?? 0);
$slug = trim((string)($_GET['slug'] ?? ''));

if ($id > 0) {
    $product = $productService->getProductById($id);
    if ($product && !empty($product['slug'])) {
        header('Location: product-detail.php?slug=' . urlencode((string)$product['slug']));
        exit;
    }
}

if ($slug !== '') {
    header('Location: product-detail.php?slug=' . urlencode($slug));
    exit;
}

header('Location: product-detail.php');
exit;
