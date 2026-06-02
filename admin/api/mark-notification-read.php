<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/mark-notification-read.php
 * Description: AJAX endpoint — mark notification(s) as read
 * =============================================================
 */

require_once dirname(__DIR__, 2) . '/config/config.php';
require_once ROOT_PATH . '/services/AdminNotificationService.php';

header('Content-Type: application/json');

require_once dirname(__DIR__) . '/includes/auth.php';

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$id    = isset($input['id'])     ? (int)$input['id']     : 0;
$all   = isset($input['all'])    ? (bool)$input['all']   : false;

$service = new AdminNotificationService();

try {
    if ($all) {
        $service->markAllAsRead();
        echo json_encode(['success' => true, 'all' => true]);
    } elseif ($id > 0) {
        $service->markAsRead($id);
        echo json_encode(['success' => true, 'id' => $id]);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No id provided']);
    }
} catch (\Throwable $e) {
    error_log('[mark-notification-read] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Server error']);
}
