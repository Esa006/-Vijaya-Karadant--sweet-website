<?php
/**
 * Sweets Website - Admin API
 * =============================================================
 * File: admin/api/v1/offer-details.php
 * Description: Aggregates configuration and performance data for coupons
 * Author: Antigravity
 * =============================================================
 */

header('Content-Type: application/json');
require_once '../../../config/config.php';
require_once ROOT_PATH . '/config/Database.php';
require_once REPOS_PATH . '/CouponRepository.php';

$couponId = (int)($_GET['id'] ?? 0);

if (!$couponId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Offer ID is required']);
    exit;
}

try {
    $db = Database::getInstance();
    $repo = new CouponRepository($db);
    
    $offer = $repo->getById($couponId);
    
    if (!$offer) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Offer not found']);
        exit;
    }

    // Convert JSON categories to array
    if ($offer['applicable_categories']) {
        $offer['applicable_categories'] = json_decode($offer['applicable_categories'], true);
    } else {
        $offer['applicable_categories'] = [];
    }

    $metrics = $repo->getOfferMetrics($couponId);

    echo json_encode([
        'success' => true,
        'data' => [
            'config' => $offer,
            'performance' => $metrics
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
