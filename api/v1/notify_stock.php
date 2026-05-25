<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/notify_stock.php
 * Description: API endpoint to receive restock notifications
 * =============================================================
 */

require_once '../../config/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_SANITIZE_STRING);
$product_type = filter_input(INPUT_POST, 'product_type', FILTER_SANITIZE_STRING);

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Valid email address is required']);
    exit;
}

if (!$product_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
    exit;
}

$product_type = in_array($product_type, ['product', 'combo']) ? $product_type : 'product';

try {
    $db = Database::getInstance();
    
    // Check if a pending notification already exists for this email and product to prevent spam
    $checkSql = "SELECT id FROM stock_notifications WHERE email = :email AND product_id = :product_id AND product_type = :product_type AND status = 'pending'";
    $stmt = $db->prepare($checkSql);
    $stmt->execute([
        ':email' => $email,
        ':product_id' => $product_id,
        ':product_type' => $product_type
    ]);
    
    if ($stmt->fetch()) {
        // Already registered, return success anyway so user feels acknowledged
        echo json_encode(['status' => 'success', 'message' => 'You are already on the notification list for this item!']);
        exit;
    }

    $sql = "INSERT INTO stock_notifications (email, product_id, product_type, status, created_at) VALUES (:email, :product_id, :product_type, 'pending', NOW())";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':email' => $email,
        ':product_id' => $product_id,
        ':product_type' => $product_type
    ]);

    echo json_encode(['status' => 'success', 'message' => 'We will notify you as soon as it is back in stock!']);
} catch (PDOException $e) {
    error_log("Stock Notification Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}
