<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/get_variants.php
 * Description: Returns product variants as JSON for the admin edit page
 * =============================================================
 */
require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php';
require_once REPOS_PATH . '/ProductRepository.php';

header('Content-Type: application/json');

$productId = (int)($_GET['product_id'] ?? 0);
if ($productId <= 0) {
    echo json_encode(['success' => false, 'variants' => []]);
    exit;
}

$repo     = new ProductRepository();
$variants = $repo->getVariantsByProductId($productId);

echo json_encode(['success' => true, 'variants' => $variants]);
