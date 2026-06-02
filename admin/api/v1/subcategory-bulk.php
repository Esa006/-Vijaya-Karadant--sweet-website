<?php
/**
 * Sweets Website
 * =============================================================
 * File: subcategory-bulk.php
 * Description: Production-grade Bulk Update API for Subcategories
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once '../../includes/auth.php';
require_once REPOS_PATH . '/SubcategoryRepository.php';

// 1. Headers & Security
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Note: In production, wrap this in authentication middleware
// require_once '../../includes/auth_check.php';

// 2. Input Validation
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['ids']) || !is_array($input['ids'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or empty IDs array']);
    exit;
}

if (count($input['ids']) > 100) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Maximum 100 items allowed per bulk action']);
    exit;
}

$action = $input['action'] ?? null;
$value = $input['value'] ?? null;

$validActions = ['status', 'delete', 'category'];
if (!in_array($action, $validActions)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid bulk action requested']);
    exit;
}

try {
    // 3. Initialize Repository
    $repo = new SubcategoryRepository();

    // 4. Transaction Safety
    $repo->beginTransaction();
    
    $updatedCount = $repo->bulkUpdate($input['ids'], $action, $value);
    
    $repo->commit();

    // 5. Response
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'updated_count' => $updatedCount,
        'message' => "Successfully processed $updatedCount items"
    ]);

} catch (Exception $e) {
    if (isset($repo)) $repo->rollBack();
    
    error_log('[SubcategoryBulkAPI] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Internal Server Error during bulk update',
        'debug' => (defined('DEBUG') && DEBUG) ? $e->getMessage() : null
    ]);
}
