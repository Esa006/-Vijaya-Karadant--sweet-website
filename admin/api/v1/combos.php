<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/v1/combos.php
 * Description: Admin Combo CRUD API Controller
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

// ── GET: load combo items for edit panel ──────────────────────────────────
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
    }
    echo json_encode([
        'status' => 'success',
        'items'  => $combo['items'] ?? [],
    ]);
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

        default:
            throw new Exception('Invalid action.', 400);
    }

} catch (Exception $e) {
    $code = (int)$e->getCode();
    http_response_code(($code >= 100 && $code < 600) ? $code : 500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
