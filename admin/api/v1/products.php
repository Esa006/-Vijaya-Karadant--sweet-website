<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/products.php
 * Description: Admin Product Controller (Add/Update/Delete)
 * Author: Antigravity - Principal Staff Engineer
 * Version: 2.1.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once '../../includes/auth.php'; // Enforces requireAdmin()
verifyCSRF(); // Enforces CSRF token check for POST
require_once SERVICES_PATH . '/ProductService.php';
require_once SERVICES_PATH . '/AuditService.php';

header('Content-Type: application/json');

// 1. HTTP Method Context
$method = $_SERVER['REQUEST_METHOD'];
$action = $_POST['action'] ?? ($_GET['action'] ?? '');

$productService = new ProductService();
$auditService   = new AuditService();
$adminId        = $_SESSION['user_id'] ?? 0;

try {
    switch ($method) {
        case 'GET':
            $id = (int)($_GET['id'] ?? 0);
            if ($id > 0) {
                $product = $productService->getProductById($id);
                if ($product) {
                    echo json_encode(['status' => 'success', 'data' => $product]);
                } else {
                    throw new Exception('Product not found.', 404);
                }
            } else {
                throw new Exception('Invalid product ID.', 400);
            }
            break;

        case 'POST':
            if ($action === 'create') {
                handleCreate($productService, $auditService, $adminId);
            } elseif ($action === 'update') {
                handleUpdate($productService, $auditService, $adminId);
            } elseif ($action === 'delete') {
                handleDelete($productService, $auditService, $adminId);
            } elseif ($action === 'delete_gallery_image') {
                handleDeleteGalleryImage($productService, $auditService, $adminId);
            } elseif ($action === 'set_primary_image') {
                handleSetPrimaryImage($productService, $auditService, $adminId);
            } elseif ($action === 'toggle_status') {
                handleToggleStatus($productService, $auditService, $adminId);
            } else {
                throw new Exception('Invalid administrative action requested.', 400);
            }
            break;

        default:
            throw new Exception('Method Not Allowed', 405);
    }
} catch (Exception $e) {
    $code = $e->getCode();
    $httpCode = (is_numeric($code) && $code >= 100 && $code < 600) ? (int)$code : 500;
    http_response_code($httpCode);
    echo json_encode([
        'status'  => 'error',
        'message' => $e->getMessage()
    ]);
}

/**
 * Handle Product Creation
 */
function handleCreate(ProductService $service, AuditService $audit, int $adminId) {
    // 1. Data Normalization
    $data = $_POST;
    $data['admin_id'] = $adminId;
    $image = $_FILES['product_image'] ?? null;

    // 2. Execution
    $productId = $service->createProduct($data, $image);

    if ($productId > 0) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Product created successfully.',
            'id' => $productId
        ]);
    } else {
        throw new Exception('Failed to create product. Check server logs.', 500);
    }
}

/**
 * Handle Product Update
 */
function handleUpdate(ProductService $service, AuditService $audit, int $adminId) {
    $id = (int)($_POST['product_id'] ?? 0);
    if ($id <= 0) throw new Exception('Missing product ID.', 400);

    $data = $_POST;
    $data['admin_id'] = $adminId;
    // Explicitly handle boolean checkbox which is omitted from POST when unchecked
    $data['featured'] = isset($_POST['featured']) ? 1 : 0;
    
    $mainImage = $_FILES['product_image'] ?? null;
    $galleryImages = $_FILES['product_images'] ?? null;

    $success = $service->updateProduct($id, $data, $mainImage, $galleryImages);

    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Product updated successfully.']);
    } else {
        throw new Exception('Failed to update product details.', 500);
    }
}

/**
 * Handle Gallery Image Deletion
 */
function handleDeleteGalleryImage(ProductService $service, AuditService $audit, int $adminId) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $imageId = (int)($_POST['image_id'] ?? 0);

    if ($productId <= 0 || $imageId <= 0) {
        throw new Exception('Invalid parameters for image deletion.', 400);
    }

    if ($service->deleteProductImage($productId, $imageId)) {
        $audit->log('product', $productId, 'delete_image', $adminId, ['image_id' => $imageId]);
        echo json_encode(['status' => 'success', 'message' => 'Image removed from gallery.']);
    } else {
        throw new Exception('Failed to delete image.', 500);
    }
}

/**
 * Handle Primary Image Selection
 */
function handleSetPrimaryImage(ProductService $service, AuditService $audit, int $adminId) {
    $productId = (int)($_POST['product_id'] ?? 0);
    $imageId = (int)($_POST['image_id'] ?? 0);

    if ($productId <= 0 || $imageId <= 0) {
        throw new Exception('Invalid parameters for primary image selection.', 400);
    }

    if ($service->setProductMainImage($productId, $imageId)) {
        $audit->log('product', $productId, 'set_primary_image', $adminId, ['image_id' => $imageId]);
        echo json_encode(['status' => 'success', 'message' => 'Primary image updated successfully.']);
    } else {
        throw new Exception('Failed to set primary image.', 500);
    }
}

/**
 * Handle Product Deletion
 */
function handleDelete(ProductService $service, AuditService $audit, int $adminId) {
    $id = (int)($_POST['product_id'] ?? 0);
    if ($id <= 0) throw new Exception('Missing product ID.', 400);

    if ($service->deleteProduct($id)) {
        echo json_encode(['status' => 'success', 'message' => 'Product deleted successfully.']);
    } else {
        throw new Exception('Failed to delete product.', 500);
    }
}

/**
 * Handle Product Status Toggle
 */
function handleToggleStatus(ProductService $service, AuditService $audit, int $adminId) {
    $id = (int)($_POST['product_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    if ($id <= 0 || empty($status)) throw new Exception('Invalid parameters for status toggle.', 400);

    $success = $service->updateProduct($id, ['status' => $status]);

    if ($success) {
        $audit->log('product', $id, 'toggle_status', $adminId, ['new_status' => $status]);
        echo json_encode(['status' => 'success', 'message' => 'Status updated.']);
    } else {
        throw new Exception('Failed to toggle status.', 500);
    }
}
