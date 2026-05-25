<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/categories.php
 * Description: API endpoint for Category operations
 * Author: Sweets Website Team
 * Version: 2.0.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once '../../includes/auth.php';
verifyCSRF();
require_once SERVICES_PATH . '/CategoryService.php';

header('Content-Type: application/json');

// Strict Response Format Helper
function sendResponse(bool $success, string $message, array $data = [], int $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ]);
    exit;
}

$action = $_REQUEST['action'] ?? '';
$service = new CategoryService();

if ($action === 'create') {
    $categoryData = [
        'name'        => $_POST['name'] ?? '',
        'parent_id'   => $_POST['parent_id'] ?? null,
        'description' => $_POST['description'] ?? '',
        'regular_price' => $_POST['regular_price'] ?? null,
        'discount_price' => $_POST['discount_price'] ?? null,
        'tax_rate'      => $_POST['tax_rate'] ?? null,
        'weight'        => $_POST['weight'] ?? null,
        'short_description' => $_POST['short_description'] ?? null,
        'highlights'    => $_POST['highlights'] ?? null,
        'ingredients'   => $_POST['ingredients'] ?? null,
        'benefits'      => $_POST['benefits'] ?? null,
        'storage_instructions' => $_POST['storage_instructions'] ?? null,
        'status'      => $_POST['status'] ?? 'active'
    ];

    $imageFile = $_FILES['image'] ?? null;
    $heroImageFile = $_FILES['hero_image'] ?? null;
    $result = $service->createCategory($categoryData, $imageFile, $heroImageFile);
    
    $status = $result['success'] ? 200 : 400;
    sendResponse($result['success'], $result['message'], $result['data'], $status);
}

if ($action === 'update') {
    verifyCSRF();
    $id = (int)($_POST['id'] ?? 0);
    
    if ($id <= 0) {
        sendResponse(false, 'Invalid Category ID.', [], 400);
    }

    $categoryData = [
        'name'        => $_POST['name'] ?? '',
        'parent_id'   => $_POST['parent_id'] ?? null,
        'description' => $_POST['description'] ?? '',
        'regular_price' => $_POST['regular_price'] ?? null,
        'discount_price' => $_POST['discount_price'] ?? null,
        'tax_rate'      => $_POST['tax_rate'] ?? null,
        'weight'        => $_POST['weight'] ?? null,
        'short_description' => $_POST['short_description'] ?? null,
        'highlights'    => $_POST['highlights'] ?? null,
        'ingredients'   => $_POST['ingredients'] ?? null,
        'benefits'      => $_POST['benefits'] ?? null,
        'storage_instructions' => $_POST['storage_instructions'] ?? null,
        'status'      => $_POST['status'] ?? 'active',
        'slug'        => $_POST['slug'] ?? '' // Optional manual override
    ];

    $imageFile = $_FILES['image'] ?? null;
    $heroImageFile = $_FILES['hero_image'] ?? null;
    $result = $service->updateCategory($id, $categoryData, $imageFile, $heroImageFile);
    
    $status = $result['success'] ? 200 : 400;
    sendResponse($result['success'], $result['message'], $result['data'], $status);
}

if ($action === 'delete') {
    // Determine method (could be GET or POST depending on frontend implementation, typically POST for APIs)
    $id = (int)($_REQUEST['id'] ?? 0);
    
    if ($id <= 0) {
        sendResponse(false, 'Invalid Category ID.', [], 400);
    }
    
    $result = $service->deleteCategory($id);
    
    $status = $result['success'] ? 200 : 400;
    sendResponse($result['success'], $result['message'], $result['data'], $status);
}

if ($action === 'toggle_status') {
    $id = (int)($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    if ($id <= 0 || empty($status)) {
        sendResponse(false, 'Invalid parameters.', [], 400);
    }
    
    $result = $service->updateCategory($id, ['status' => $status]);
    
    $httpStatus = $result['success'] ? 200 : 400;
    sendResponse($result['success'], $result['message'], $result['data'], $httpStatus);
}

if ($action === 'list') {
    $repo = new CategoryRepository();
    $categories = $repo->getTree();
    sendResponse(true, 'Categories fetched successfully.', $categories, 200);
}

sendResponse(false, 'Invalid action.', [], 400);
