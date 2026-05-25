<?php
/**
 * Sweets Website - API
 * =============================================================
 * File: admin/api/v1/subcategories.php
 * Description: REST API for Subcategory management
 * =============================================================
 */

header('Content-Type: application/json');
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once SERVICES_PATH . '/SubcategoryService.php';
require_once SERVICES_PATH . '/FileService.php';

$method = $_SERVER['REQUEST_METHOD'];
$service = new SubcategoryService();

try {
    if ($method === 'GET') {
        $categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
        $results = $service->getSubcategories($categoryId);
        echo json_encode(['success' => true, 'data' => $results]);
    } 
    elseif ($method === 'POST') {
        // verifyCSRF(); // This usually checks $_POST['csrf_token']
        
        $action = $_POST['action'] ?? 'create';
        
        if ($action === 'create') {
            $id = $service->createSubcategory($_POST, $_FILES);
            echo json_encode(['success' => true, 'id' => $id, 'message' => 'Subcategory created successfully']);
        } 
        elseif ($action === 'update') {
            $id = (int)$_POST['id'];
            $service->updateSubcategory($id, $_POST, $_FILES);
            echo json_encode(['success' => true, 'message' => 'Subcategory updated successfully']);
        } 
        elseif ($action === 'toggle_status') {
            $id = (int)$_POST['id'];
            $status = $_POST['status'] ?? 'active';
            $service->updateSubcategory($id, ['status' => $status], null);
            echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
        }
        elseif ($action === 'delete') {
            $id = (int)$_POST['id'];
            $service->deleteSubcategory($id);
            echo json_encode(['success' => true, 'message' => 'Subcategory deleted successfully']);
        }
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
