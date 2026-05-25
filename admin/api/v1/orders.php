<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/v1/orders.php
 * Description: API endpoint for Order status transitions
 * Author: Antigravity - Senior Backend Engineer
 * Version: 2.2.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once '../../includes/auth.php';
verifyCSRF();
require_once SERVICES_PATH . '/OrderService.php';

header('Content-Type: application/json');

// Auth guard already called by auth.php
$action = $_POST['action'] ?? '';

// ── update_status ────────────────────────────────────────────
if ($action === 'update_status') {
    $orderId   = (int)($_POST['order_id'] ?? 0);
    $newStatus = trim($_POST['status'] ?? '');

    if ($orderId <= 0 || !$newStatus) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Order ID and status are required.']);
        exit;
    }

    $service = new OrderService();
    $result  = $service->transitionStatus($orderId, $newStatus);

    if ($result['success']) {
        echo json_encode([
            'status'  => 'success',
            'message' => "Order #$orderId updated to $newStatus."
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'status'  => 'error',
            'message' => $result['message'] ?? 'Status transition failed.'
        ]);
    }
    exit;
}

// ── edit_order ───────────────────────────────────────────────
if ($action === 'edit_order') {
    $orderId   = (int)($_POST['order_id'] ?? 0);
    $status    = trim($_POST['status'] ?? '');
    $payStatus = trim($_POST['payment_status'] ?? '');

    if ($orderId <= 0 || !$status || !$payStatus) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Order ID, status, and payment status are required.']);
        exit;
    }

    $service = new OrderService();
    $result  = $service->updateOrder($orderId, [
        'status'                  => $status,
        'payment_status'          => $payStatus,
        'tracking_id'             => trim($_POST['tracking_id'] ?? ''),
        'delivery_partner'        => trim($_POST['delivery_partner'] ?? ''),
        'estimated_delivery_date' => $_POST['estimated_delivery_date'] ?? null,
        'admin_notes'             => trim($_POST['admin_notes'] ?? '')
    ]);

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => "Order #$orderId updated successfully."]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Database update failed.']);
    }
    exit;
}

// ── delete_order ─────────────────────────────────────────────
if ($action === 'delete_order') {
    $orderId = (int)($_POST['order_id'] ?? 0);

    if ($orderId <= 0) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Valid Order ID is required.']);
        exit;
    }

    $service = new OrderService();
    $result  = $service->deleteOrder($orderId);

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => "Order #$orderId deleted permanently."]);
    } else {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete order.']);
    }
    exit;
}

// ── fallback ─────────────────────────────────────────────────
http_response_code(400);
echo json_encode(['status' => 'error', 'message' => 'Invalid action.']);
