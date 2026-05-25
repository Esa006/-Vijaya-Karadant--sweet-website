<?php
/**
 * Sweets Website - Secure Payment API v1
 * =============================================================
 * Endpoint: /api/v1/payment.php
 * Methods: POST (initiate), POST (webhook)
 * Author: Antigravity - Senior Backend Engineer
 * =============================================================
 */

require_once '../../config/config.php';
require_once SERVICES_PATH . '/OrderService.php';
require_once SERVICES_PATH . '/PaymentService.php'; 
require_once SERVICES_PATH . '/AuditService.php';

// Hardened Headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$method = $_SERVER['REQUEST_METHOD'];
$paymentService = new PaymentService();

try {
    // 1. Webhook Handler (Public but Hardened)
    if (isset($_GET['source']) && $_GET['source'] === 'webhook') {
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
            exit;
        }

        // Security: Get RAW payload for signature verification
        $rawPayload = file_get_contents('php://input');
        $signature  = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        if (!$paymentService->verifyWebhook($rawPayload, $signature)) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized Signature']);
            exit;
        }

        // Signature valid -> Parse and process
        $data = json_decode($rawPayload, true);
        $success = $paymentService->processWebhookEvent($data);
        
        echo json_encode(['success' => $success]);
        exit;
    }

    // 2. Client Side: Initiate Payment (Protected)
    if ($method !== 'POST') {
        throw new Exception("Method Not Allowed", 405);
    }
    
    $json = file_get_contents('php://input');
    $request = json_decode($json, true);
    $orderId = (int)($request['order_id'] ?? 0);

    if (!$orderId) {
        throw new Exception("Invalid Order ID", 400);
    }

    $result = $paymentService->initiatePayment($orderId);
    
    if (!$result['success']) {
        throw new Exception($result['message'], 422);
    }

    echo json_encode(['success' => true, 'data' => $result]);

} catch (Exception $e) {
    $code = $e->getCode() ?: 500;
    http_response_code($code > 599 ? 500 : $code);
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
