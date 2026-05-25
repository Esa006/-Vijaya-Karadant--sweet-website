<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/customers.php
 * Description: API handler for Customer CRUD operations
 * =============================================================
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php'; // Enforces requireAdmin()
verifyCSRF(); // Enforces CSRF token check for POST/DELETE
require_once ROOT_PATH . '/config/Database.php';
require_once REPOS_PATH . '/CustomerRepository.php';

header('Content-Type: application/json');

$customerRepo = new CustomerRepository(Database::getInstance());
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($id) {
            $customer = $customerRepo->getById($id);
            echo json_encode(['success' => true, 'customer' => $customer]);
        } else {
            $customers = $customerRepo->getAllCustomers();
            echo json_encode(['success' => true, 'customers' => $customers]);
        }
    } 
    elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $action = $data['action'] ?? '';

        if ($action === 'create') {
            $userData = [
                'full_name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? '',
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'role' => 'customer',
                'status' => $data['status'] ? 'Active' : 'Inactive'
            ];

            $addressData = [
                'address_line1' => $data['address'] ?? '',
                'city' => $data['city'] ?? '',
                'state' => $data['state'] ?? '',
                'zip_code' => $data['pincode'] ?? '',
                'country' => 'India',
                'phone' => $data['phone'] ?? '',
                'is_default' => 1
            ];

            $userId = $customerRepo->createCustomer($userData, $addressData);
            echo json_encode(['success' => true, 'id' => $userId]);
        } 
        elseif ($action === 'update') {
            $id = (int)$data['id'];
            
            // Get existing data first
            $existing = $customerRepo->getById($id);
            if (!$existing) {
                echo json_encode(['success' => false, 'error' => 'Customer not found']);
                exit;
            }
            
            $userData = [
                'full_name' => isset($data['name']) ? $data['name'] : $existing['full_name'],
                'email' => isset($data['email']) ? $data['email'] : $existing['email'],
                'phone' => isset($data['phone']) ? $data['phone'] : $existing['phone'],
                'status' => isset($data['status']) ? (($data['status'] === true || $data['status'] === 'Active') ? 'Active' : 'Inactive') : $existing['status'],
                'admin_notes' => isset($data['notes']) ? $data['notes'] : $existing['admin_notes']
            ];

            $addressData = [];
            if (isset($data['address'])) {
                $addressData = [
                    'address_line1' => $data['address'] ?? '',
                    'city' => $data['city'] ?? '',
                    'state' => $data['state'] ?? '',
                    'zip_code' => $data['pincode'] ?? '',
                    'country' => 'India',
                    'phone' => $data['phone'] ?? '',
                ];
            }

            $success = $customerRepo->updateFullProfile($id, $userData, $addressData);
            echo json_encode(['success' => $success]);
        }
    } 
    elseif ($method === 'DELETE') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);
        
        if ($id) {
            $success = $customerRepo->deleteCustomer($id);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'No ID provided']);
        }
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
