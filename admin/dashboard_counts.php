<?php
/**
 * Sweets Website
 * =============================================================
 * File: dashboard_counts.php
 * Description: Shipment dashboard counters endpoint
 * =============================================================
 */

// Buffer so PHP notices/warnings cannot corrupt the JSON response
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
    $stmt = $pdo->query(
        "SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN status = 'in_transit' THEN 1 ELSE 0 END) AS in_transit,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) AS delivered
        FROM shipments"
    );

    $counts = $stmt->fetch() ?: [
        'total' => 0,
        'pending' => 0,
        'in_transit' => 0,
        'delivered' => 0,
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'total' => (int)$counts['total'],
            'pending' => (int)$counts['pending'],
            'in_transit' => (int)$counts['in_transit'],
            'delivered' => (int)$counts['delivered'],
        ]
    ]);
} catch (Throwable $e) {
    error_log('[dashboard_counts] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load dashboard counts'
    ]);
}
