<?php
/**
 * Sweets Website
 * =============================================================
 * File: update_status.php
 * Description: Update shipment status for admin delivery dashboard
 * =============================================================
 */

// Buffer so PHP notices/warnings cannot corrupt the JSON response
ob_start();

require_once __DIR__ . '/db.php';          // loads config.php → session_start()
require_once __DIR__ . '/includes/auth.php'; // now $_SESSION is populated

header('Content-Type: application/json');
ob_clean(); // discard any warnings buffered so far

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

verifyCSRF();

$input = $_POST;

if (empty($input)) {
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    if (is_array($decoded)) {
        $input = $decoded;
    }
}

$orderId = filter_var($input['order_id'] ?? null, FILTER_VALIDATE_INT);
$status = strtolower(trim((string)($input['status'] ?? '')));

$allowedStatuses = ['pending', 'in_transit', 'delivered'];

if (!$orderId) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid or missing order_id'
    ]);
    exit;
}

if (!in_array($status, $allowedStatuses, true)) {
    http_response_code(422);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit;
}

try {
    $pdo = getPDOConnection();
    ensureShipmentTrackingSchema($pdo);

    $updateStmt = $pdo->prepare(
        'UPDATE shipments SET status = :status, updated_at = NOW() WHERE order_id = :order_id'
    );
    $updateStmt->execute([
        ':status' => $status,
        ':order_id' => $orderId
    ]);

    if ($updateStmt->rowCount() === 0) {
        $existsStmt = $pdo->prepare('SELECT id FROM shipments WHERE order_id = :order_id LIMIT 1');
        $existsStmt->execute([':order_id' => $orderId]);
        if (!$existsStmt->fetch()) {
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Shipment record not found for this order'
            ]);
            exit;
        }
    }

    $timeStmt = $pdo->prepare('SELECT updated_at FROM shipments WHERE order_id = :order_id LIMIT 1');
    $timeStmt->execute([':order_id' => $orderId]);
    $timeRow = $timeStmt->fetch();

    echo json_encode([
        'success' => true,
        'message' => 'Shipment status updated successfully',
        'data' => [
            'order_id' => (int)$orderId,
            'status' => $status,
            'updated_at' => $timeRow['updated_at'] ?? date('Y-m-d H:i:s')
        ]
    ]);
} catch (Throwable $e) {
    error_log('[update_status] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update shipment status'
    ]);
}
