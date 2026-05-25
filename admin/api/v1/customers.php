<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/v1/customers.php
 * Description: API for General Customer Operations (Edit/Delete)
 * Author: Antigravity - Senior Full-Stack Architect
 * Version: 3.0.0
 * =============================================================
 */

require_once '../../../config/config.php';
require_once REPOS_PATH . '/CustomerRepository.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

try {
    $repo = new CustomerRepository();

    if ($method === 'POST') {
        // Handle Action based requests (Form Data or JSON)
        $input = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $action = $input['action'] ?? '';

        if ($action === 'edit_customer') {
            $userId = (int)($input['user_id'] ?? 0);
            if (!$userId) throw new Exception('Invalid User ID');

            $data = [
                'name'   => $input['full_name'] ?? '',
                'email'  => $input['email'] ?? '',
                'phone'  => $input['phone'] ?? '',
                'status' => $input['status'] ?? 'active'
            ];

            if (empty($data['name']) || empty($data['email'])) {
                throw new Exception('Name and Email are required');
            }

            $success = $repo->updateProfile($userId, $data);
            
            if ($success) {
                echo json_encode(['status' => 'success', 'message' => 'Customer updated successfully']);
            } else {
                throw new Exception('Database update failed');
            }
            exit;
        }
    }

    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Action not supported']);

} catch (Exception $e) {
    error_log('[CustomersAPI] ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
