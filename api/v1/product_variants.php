<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/product_variants.php
 * Description: CRUD API for product weight variants (Admin only)
 * =============================================================
 */

error_reporting(0);
require_once '../../config/config.php';

header('Content-Type: application/json');

// Admin auth check
if (empty($_SESSION['admin_id']) && empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$db     = Database::getInstance();

// ── GET: List all variants for a product ───────────────────────
if ($method === 'GET') {
    $productId = (int)($_GET['product_id'] ?? 0);
    if (!$productId) {
        echo json_encode(['status' => 'error', 'message' => 'product_id required']);
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM product_variants WHERE product_id = :pid ORDER BY id ASC");
    $stmt->execute([':pid' => $productId]);
    echo json_encode(['status' => 'success', 'variants' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// ── POST: Add a new variant ────────────────────────────────────
if ($method === 'POST') {
    $productId = (int)($_POST['product_id'] ?? 0);
    $weight    = trim((string)($_POST['weight']    ?? ''));
    $label     = trim((string)($_POST['label']     ?? ''));
    $price     = (float)($_POST['price']   ?? 0);
    $stock     = (int)($_POST['stock']     ?? 0);

    if (!$productId || !$weight || $price <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'product_id, weight, and price are required.']);
        exit;
    }

    // Auto-generate label if empty
    if (!$label) $label = $weight . ' Pack';

    // Check duplicate weight for same product
    $dup = $db->prepare("SELECT id FROM product_variants WHERE product_id = :pid AND weight = :w LIMIT 1");
    $dup->execute([':pid' => $productId, ':w' => $weight]);
    if ($dup->fetch()) {
        echo json_encode(['status' => 'error', 'message' => "A variant with weight '$weight' already exists for this product."]);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO product_variants (product_id, weight, label, price, stock) VALUES (:pid, :w, :l, :p, :s)");
    $stmt->execute([':pid' => $productId, ':w' => $weight, ':l' => $label, ':p' => $price, ':s' => $stock]);
    $newId = $db->lastInsertId();

    echo json_encode(['status' => 'success', 'message' => 'Variant added successfully.', 'id' => $newId]);
    exit;
}

// ── PUT: Update a variant ──────────────────────────────────────
if ($method === 'PUT') {
    parse_str(file_get_contents('php://input'), $data);
    $id    = (int)($data['id']    ?? 0);
    $price = (float)($data['price'] ?? 0);
    $stock = (int)($data['stock'] ?? 0);
    $label = trim((string)($data['label'] ?? ''));
    $weight = trim((string)($data['weight'] ?? ''));

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Variant ID required.']);
        exit;
    }

    $stmt = $db->prepare("UPDATE product_variants SET price = :p, stock = :s, label = :l, weight = :w WHERE id = :id");
    $stmt->execute([':p' => $price, ':s' => $stock, ':l' => $label, ':w' => $weight, ':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Variant updated.']);
    exit;
}

// ── DELETE: Remove a variant ───────────────────────────────────
if ($method === 'DELETE') {
    parse_str(file_get_contents('php://input'), $data);
    $id = (int)($data['id'] ?? 0);

    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Variant ID required.']);
        exit;
    }

    $stmt = $db->prepare("DELETE FROM product_variants WHERE id = :id");
    $stmt->execute([':id' => $id]);

    echo json_encode(['status' => 'success', 'message' => 'Variant deleted.']);
    exit;
}

http_response_code(405);
echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
