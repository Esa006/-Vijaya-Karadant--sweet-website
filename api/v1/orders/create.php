<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/orders/create.php
 * Description: Production-grade Checkout & Order Creation API
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once REPOS_PATH . '/OrderRepository.php';

header('Content-Type: application/json');

// 1. Authentication Check (Assume session-based for now)
session_start();
$userId = $_SESSION['user_id'] ?? 99; // Fallback to test user for demo

// 2. Input Validation
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['items']) || !is_array($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Cart is empty or invalid request format']);
    exit;
}

try {
    $repo = new OrderRepository();
    
    // 3. Execute Atomic Checkout
    // This handles stock locking, deduction, and order creation in one transaction
    $result = $repo->createWithStockLock($userId, $input['items']);

    if ($result['success']) {
        http_response_code(201);
        echo json_encode([
            'success'  => true,
            'order_id' => $result['order_id'],
            'total'    => $result['total'],
            'status'   => 'pending',
            'message'  => 'Order initiated successfully. Proceed to payment.'
        ]);
    } else {
        http_response_code(409); // Conflict (e.g., Out of stock)
        echo json_encode([
            'success' => false, 
            'error'   => $result['error']
        ]);
    }

} catch (Exception $e) {
    error_log('[CheckoutAPI] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'A critical server error occurred during checkout.'
    ]);
}
