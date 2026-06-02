<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/v1/combos.php
 * Description: Admin Combo CRUD API Controller (with Gallery)
 * =============================================================
 */

require_once '../../../config/config.php';
require_once '../../includes/auth.php';
verifyCSRF();
require_once SERVICES_PATH . '/ComboService.php';

header('Content-Type: application/json');

$method  = $_SERVER['REQUEST_METHOD'];
$action  = $_POST['action'] ?? ($_GET['action'] ?? '');
$service = new ComboService();

// ── GET: load combo items and images for edit panel ───────────────────────
if ($method === 'GET' && $action === 'get_items') {
    $id = (int)($_GET['combo_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
        exit;
    }
    $combo = $service->getComboById($id);
    if (!$combo) {
        // Try including inactive via repo directly
        require_once REPOS_PATH . '/ComboRepository.php';
        $repo  = new ComboRepository();
        $combo = $repo->getById($id);
        $combo['gallery'] = $repo->getImagesForCombo($id);
    }
    echo json_encode([
        'status'  => 'success',
        'items'   => $combo['items']   ?? [],
        'gallery' => $combo['gallery'] ?? [],
    ]);
    exit;
}

// ── GET: list gallery images for a combo ─────────────────────────────────
if ($method === 'GET' && $action === 'get_images') {
    $id = (int)($_GET['combo_id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
        exit;
    }
    $images = $service->getComboImages($id);
    echo json_encode(['status' => 'success', 'images' => $images]);
    exit;
}

try {

    if ($method !== 'POST') throw new Exception('Method Not Allowed', 405);

    switch ($action) {
        case 'create':
            $items = json_decode($_POST['items_json'] ?? '[]', true) ?: [];
            $data  = array_merge($_POST, ['items' => $items]);
            $id    = $service->createCombo($data, $_FILES['combo_image'] ?? null);
            if ($id <= 0) throw new Exception('Failed to create combo.', 500);
            echo json_encode(['status' => 'success', 'message' => 'Combo created successfully.', 'id' => $id]);
            break;

        case 'update':
            $id = (int)($_POST['combo_id'] ?? 0);
            if ($id <= 0) throw new Exception('Missing combo ID.', 400);
            $items = json_decode($_POST['items_json'] ?? '[]', true) ?: [];
            $data  = array_merge($_POST, ['items' => $items]);
            $ok    = $service->updateCombo($id, $data, $_FILES['combo_image'] ?? null);
            if (!$ok) throw new Exception('Failed to update combo.', 500);
            echo json_encode(['status' => 'success', 'message' => 'Combo updated successfully.']);
            break;

        case 'delete':
            $id = (int)($_POST['combo_id'] ?? 0);
            if ($id <= 0) throw new Exception('Missing combo ID.', 400);
            $service->deleteCombo($id);
            echo json_encode(['status' => 'success', 'message' => 'Combo deleted.']);
            break;

        case 'toggle_status':
            $id     = (int)($_POST['combo_id'] ?? 0);
            $status = (int)($_POST['is_active'] ?? 0);
            if ($id <= 0) throw new Exception('Missing combo ID.', 400);
            require_once REPOS_PATH . '/ComboRepository.php';
            (new ComboRepository())->update($id, ['is_active' => $status]);
            echo json_encode(['status' => 'success', 'message' => 'Status updated.']);
            break;

        // ── Gallery Actions ──────────────────────────────────────────────────

        case 'upload_image':
            $comboId    = (int)($_POST['combo_id'] ?? 0);
            $makePrimary = !empty($_POST['make_primary']);
            if ($comboId <= 0) throw new Exception('Missing combo ID.', 400);
            if (empty($_FILES['gallery_image']['tmp_name'])) throw new Exception('No image file received.', 400);
            $result = $service->uploadComboImage($comboId, $_FILES['gallery_image'], $makePrimary);
            if (!$result['success']) throw new Exception($result['message'], 500);
            echo json_encode([
                'status'     => 'success',
                'message'    => 'Image uploaded.',
                'id'         => $result['id'],
                'image_path' => $result['image_path'],
                'is_primary' => $result['is_primary'],
            ]);
            break;

        case 'delete_image':
            $imageId = (int)($_POST['image_id'] ?? 0);
            if ($imageId <= 0) throw new Exception('Missing image ID.', 400);
            $service->deleteComboImage($imageId);
            echo json_encode(['status' => 'success', 'message' => 'Image deleted.']);
            break;

        case 'set_primary_image':
            $comboId = (int)($_POST['combo_id'] ?? 0);
            $imageId = (int)($_POST['image_id'] ?? 0);
            if ($comboId <= 0 || $imageId <= 0) throw new Exception('Missing IDs.', 400);
            $service->setPrimaryComboImage($comboId, $imageId);
            echo json_encode(['status' => 'success', 'message' => 'Primary image updated.']);
            break;

        default:
            throw new Exception('Invalid action.', 400);
    }

} catch (Exception $e) {
    $code = (int)$e->getCode();
    http_response_code(($code >= 100 && $code < 600) ? $code : 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
