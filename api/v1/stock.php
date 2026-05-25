<?php
declare(strict_types=1);
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/stock.php
 * Description: Public-facing stock status + notify-me API
 *
 * GET  ?action=check&product_id=X
 *      → {"id":X,"name":"…","price":Y,"stock_status":"in_stock","stock_quantity":12}
 *
 * POST action=notify  product_id=X  email=user@example.com
 *      → {"success":true,"message":"…"}
 * =============================================================
 */

require_once __DIR__ . '/../../config/config.php';
require_once REPOS_PATH . '/StockRepository.php';
require_once REPOS_PATH . '/ProductRepository.php';

header('Content-Type: application/json');
header('Cache-Control: no-store');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'check';

$stockRepo   = new StockRepository();
$productRepo = new ProductRepository();

// ── GET /api/v1/stock.php?action=check&product_id=X ─────────
if ($method === 'GET' && $action === 'check') {
    $id = (int)($_GET['product_id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'product_id is required']);
        exit;
    }

    $product = $productRepo->getById($id);
    if (!$product) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    $payload = $stockRepo->getStockPayload($id);

    echo json_encode([
        'id'             => $id,
        'name'           => $product['name'],
        'price'          => (float)($product['sale_price'] ?? $product['base_price'] ?? 0),
        'stock_status'   => $payload['stock_status'],
        'stock_quantity' => $payload['stock_quantity'],
    ]);
    exit;
}

// ── GET ?action=bulk&ids=1,2,3 ───────────────────────────────
if ($method === 'GET' && $action === 'bulk') {
    $raw = $_GET['ids'] ?? '';
    $ids = array_filter(array_map('intval', explode(',', $raw)));

    if (empty($ids)) {
        echo json_encode([]);
        exit;
    }

    $result = [];
    foreach ($ids as $id) {
        $result[] = $stockRepo->getStockPayload($id);
    }
    echo json_encode($result);
    exit;
}

// ── POST action=notify ───────────────────────────────────────
if ($method === 'POST' && $action === 'notify') {
    $id    = (int)($_POST['product_id'] ?? 0);
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);

    if ($id <= 0 || !$email) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Valid product_id and email are required.']);
        exit;
    }

    // Only save if actually out-of-stock
    $payload = $stockRepo->getStockPayload($id);
    if ($payload['stock_status'] !== 'out_of_stock') {
        echo json_encode(['success' => false, 'message' => 'Product is currently in stock.']);
        exit;
    }

    $ok = $stockRepo->saveNotifyRequest($id, $email);
    echo json_encode([
        'success' => $ok,
        'message' => $ok
            ? 'We\'ll notify you as soon as it\'s back in stock!'
            : 'Could not save request. Please try again.',
    ]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'Invalid action or method']);
