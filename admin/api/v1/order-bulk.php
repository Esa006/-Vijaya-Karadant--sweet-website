<?php
/**
 * Sweets Website
 * =============================================================
 * File: order-bulk.php
 * Description: API for bulk updating order statuses
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once REPOS_PATH . '/OrderRepository.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['ids']) || !is_array($input['ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No orders selected']);
    exit;
}

$status = $input['status'] ?? '';
$validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];

if (!in_array($status, $validStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid status: ' . $status]);
    exit;
}

try {
    $repo = new OrderRepository();
    $updatedCount = $repo->bulkUpdate($input['ids'], $status);

    echo json_encode([
        'success' => true,
        'updated_count' => $updatedCount,
        'message' => "Successfully updated $updatedCount orders to " . ucfirst($status)
    ]);
} catch (Exception $e) {
    error_log('[OrderBulkAPI] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Internal Server Error']);
}
