<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/v1/customer-details.php
 * Description: Aggregated CRM API for Customer Details
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once REPOS_PATH . '/CustomerRepository.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$userId = (int)($_GET['id'] ?? 0);

if (!$userId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Valid User ID is required']);
    exit;
}

try {
    $repo = new CustomerRepository();

    // --- GET: Fetch All Aggregated Data ---
    if ($method === 'GET') {
        $data = $repo->getCustomerDetails($userId);
        if (!$data['profile']) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'Customer not found']);
            exit;
        }
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    // --- POST: Handle Mutations (Notes, Status, Tags) ---
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        switch ($action) {
            case 'add_note':
                if (empty($input['note'])) throw new Exception('Note content is empty');
                $repo->addNote($userId, $input['note']);
                echo json_encode(['success' => true, 'message' => 'Note added successfully']);
                break;

            case 'update_status':
                if (empty($input['status'])) throw new Exception('Status is required');
                $repo->updateStatus($userId, $input['status']);
                echo json_encode(['success' => true, 'message' => 'Status updated to ' . $input['status']]);
                break;

            default:
                throw new Exception('Invalid action specified');
        }
        exit;
    }

    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);

} catch (Exception $e) {
    error_log('[CustomerDetailsAPI] Error for ID ' . $userId . ': ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'debug_info' => 'User ID requested: ' . $userId
    ]);
}
