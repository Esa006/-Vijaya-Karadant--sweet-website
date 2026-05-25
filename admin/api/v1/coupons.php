<?php
/**
 * Sweets Website - Admin API
 * =============================================================
 * File: admin/api/v1/coupons.php
 * Description: CRUD operations for Coupons
 * Author: Antigravity
 * =============================================================
 */

header('Content-Type: application/json');
require_once '../../../config/config.php';
require_once ROOT_PATH . '/config/Database.php';
require_once REPOS_PATH . '/CouponRepository.php';

$method = $_SERVER['REQUEST_METHOD'];
$db = Database::getInstance();
$repo = new CouponRepository($db);

try {
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
        if ($id) {
            $coupon = $repo->getById($id);
            if ($coupon) {
                $coupon['metrics'] = $repo->getOfferMetrics($id);
            }
            echo json_encode(['success' => true, 'data' => $coupon]);
        } else {
            $coupons = $repo->getAllCoupons();
            echo json_encode(['success' => true, 'data' => $coupons]);
        }
    } 
    
    else if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['code']) || empty($data['value'])) {
            throw new Exception('Code and Value are required');
        }

        // Check if code exists
        if ($repo->getByCode($data['code'])) {
            throw new Exception('Coupon code already exists');
        }

        $couponId = $repo->create([
            'code' => strtoupper($data['code']),
            'description' => $data['description'] ?? '',
            'type' => $data['type'],
            'value' => (float)$data['value'],
            'min_cart_total' => (float)($data['min_cart_total'] ?? 0),
            'usage_limit' => (int)($data['usage_limit'] ?? 1),
            'limit_per_user' => (int)($data['limit_per_user'] ?? 1),
            'expires_at' => $data['expires_at'] ?: null,
            'is_active' => 1,
            'created_by' => 1 // Demo Admin ID
        ]);

        echo json_encode(['success' => true, 'id' => $couponId]);
    } 
    
    else if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) throw new Exception('ID is required');

        $success = $repo->update((int)$data['id'], [
            'description' => $data['description'] ?? '',
            'type' => $data['type'],
            'value' => (float)$data['value'],
            'min_cart_total' => (float)($data['min_cart_total'] ?? 0),
            'usage_limit' => (int)($data['usage_limit'] ?? 1),
            'limit_per_user' => (int)($data['limit_per_user'] ?? 1),
            'expires_at' => $data['expires_at'] ?: null,
            'is_active' => isset($data['is_active']) ? (int)$data['is_active'] : 1
        ]);

        echo json_encode(['success' => $success]);
    }

    else if ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (empty($data['id'])) throw new Exception('ID is required');
        
        $success = $repo->delete((int)$data['id']);
        echo json_encode(['success' => $success]);
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
