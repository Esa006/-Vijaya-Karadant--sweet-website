<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/inventory-report.php
 * Description: JSON API — Inventory Report Table Data
 * =============================================================
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/config.php';
require_once REPOS_PATH . '/InventoryRepository.php';

try {
    $repo   = new InventoryRepository();
    $search = trim($_GET['search'] ?? '');
    $status = trim($_GET['status'] ?? '');

    // Whitelist status values
    $allowed = ['', 'healthy', 'low', 'out_of_stock'];
    if (!in_array($status, $allowed, true)) {
        $status = '';
    }

    $allRows = $repo->getInventoryReport($search, '');
    $movement = $repo->getWeeklyMovement();

    // Build summary counts based on ALL rows (filtered by search but NOT status)
    $total     = count($allRows);
    $healthy   = count(array_filter($allRows, fn($r) => $r['status_key'] === 'healthy'));
    $low       = count(array_filter($allRows, fn($r) => $r['status_key'] === 'low'));
    $outOfStock = count(array_filter($allRows, fn($r) => $r['status_key'] === 'out_of_stock'));

    // Apply the status filter for the table data
    $rows = $allRows;
    if ($status !== '') {
        $rows = array_values(array_filter($allRows, fn($r) => $r['status_key'] === $status));
    }

    echo json_encode([
        'status' => 'success',
        'summary' => [
            'total_skus'    => $total,
            'healthy'       => $healthy,
            'low_critical'  => $low,
            'out_of_stock'  => $outOfStock,
        ],
        'movement' => $movement,
        'data' => $rows,
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
