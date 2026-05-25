<?php
// Mock test script
require 'config/config.php';
// require 'admin/includes/auth.php'; // Bypass auth
require 'repositories/PermissionRepository.php';

use App\Repositories\PermissionRepository;

$_SERVER['REQUEST_METHOD'] = 'POST';
$input = ['action' => 'create_admin', 'name' => 'Test', 'email' => 'test88@gmail.com', 'password' => '123', 'role_id' => 2];

        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? '';
        $roleId = (int)($input['role_id'] ?? 0);

        if (empty($name) || empty($email) || empty($password) || $roleId <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide Name, Email, Password, and select a Role.']);
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
            exit;
        }

        try {
            $data = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role_id' => $roleId
            ];
            $repo = new PermissionRepository();
            $repo->createOrUpgradeAdmin($data);
            
            echo json_encode(['status' => 'success', 'message' => 'Admin successfully created or upgraded.']);
        } catch (\PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
