<?php
/**
 * Sweets Website
 * =============================================================
 * File: get_shipments.php
 * Description: Fetch shipment rows for admin delivery dashboard
 * =============================================================
 */

// Buffer output so PHP notices/warnings cannot corrupt the JSON response
ob_start();

require_once __DIR__ . '/db.php';          // loads config.php → session_start()
require_once __DIR__ . '/includes/auth.php'; // now $_SESSION is populated

header('Content-Type: application/json');
ob_clean(); // discard any warnings buffered so far

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

try {
    $pdo = getPDOConnection();
    ensureShipmentTrackingSchema($pdo);

    $hasOrderReference = columnExists($pdo, 'orders', 'order_reference');
    $hasOrderNumber = columnExists($pdo, 'orders', 'order_number');
    $hasCustomerName = columnExists($pdo, 'orders', 'customer_name');
    $hasUserId = columnExists($pdo, 'orders', 'user_id') && tableExists($pdo, 'users') && columnExists($pdo, 'users', 'full_name');

    if ($hasOrderReference) {
        $orderReferenceExpr = 'o.order_reference';
    } elseif ($hasOrderNumber) {
        $orderReferenceExpr = 'o.order_number';
    } else {
        $orderReferenceExpr = "CONCAT('ORD-', o.id)";
    }

    if ($hasCustomerName) {
        $customerExpr = 'o.customer_name';
    } elseif ($hasUserId) {
        $customerExpr = "COALESCE(u.full_name, 'Guest')";
    } else {
        $customerExpr = "'Guest'";
    }

    $joinUsers = $hasUserId ? 'LEFT JOIN users u ON u.id = o.user_id' : '';

    $sql = "SELECT
            o.id AS order_id,
            {$orderReferenceExpr} AS order_reference,
            {$customerExpr} AS customer_name,
            o.total_amount,
            o.created_at,
            s.destination,
            s.status,
            s.updated_at
        FROM orders o
        INNER JOIN shipments s ON s.order_id = o.id
        {$joinUsers}
        ORDER BY s.updated_at DESC, o.id DESC";

    $stmt = $pdo->query($sql);

    $rows = $stmt->fetchAll();

    foreach ($rows as &$row) {
        $row['destination'] = trim((string)($row['destination'] ?? '')) !== ''
            ? $row['destination']
            : 'N/A';
    }
    unset($row);

    echo json_encode([
        'success' => true,
        'data' => $rows
    ]);
} catch (Throwable $e) {
    error_log('[get_shipments] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch shipments'
    ]);
}
