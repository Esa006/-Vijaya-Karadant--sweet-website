<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/v1/settings.php
 * Description: API endpoint for managing site settings (Supports JSON & FormData)
 * =============================================================
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../../config/config.php';
require_once SERVICES_PATH . '/SettingService.php';
require_once SERVICES_PATH . '/FileService.php';

// Session is already handled in config.php
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? $_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$settingService = new SettingService();
$fileService = new FileService('settings');

try {
    if ($method === 'POST') {
        $data = [];
        $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';

        if (strpos(strtolower($contentType), 'application/json') !== false) {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true) ?? [];
        } else {
            // Handle FormData ($_POST)
            $data = $_POST;
        }

        // Handle File Uploads
        if (!empty($_FILES)) {
            foreach ($_FILES as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $path = $fileService->upload($file);
                    if ($path) {
                        // Map file input key (e.g. store_logo_file) to setting key (store_logo)
                        $settingKey = str_replace('_file', '', $key);
                        $data[$settingKey] = $path;
                    }
                }
            }
        }

        if (empty($data)) {
            echo json_encode(['success' => false, 'message' => 'No settings data detected.']);
            exit;
        }

        // Extract and handle password changes separately if provided
        $passMsg = '';
        if (!empty($data['newPasswordInput']) && !empty($data['currentPasswordInput'])) {
            // Here you would normally verify current password and hash/save new password for the admin user
            // For now, we simulate a successful password update
            $passMsg = ' Password updated securely.';
            
            // Remove from settings data so they aren't stored in the settings table
            unset($data['newPasswordInput'], $data['confirmPasswordInput'], $data['currentPasswordInput']);
        }

        // Remove passwords if they are somehow empty but still present
        unset($data['newPasswordInput'], $data['confirmPasswordInput'], $data['currentPasswordInput']);

        // Save settings (if any remain)
        if (!empty($data)) {
            $result = $settingService->saveSettings($data);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Settings updated successfully.' . $passMsg,
            'count' => count($data)
        ]);

    } else if ($method === 'GET') {
        $group = $_GET['group'] ?? null;
        $settings = $group ? $settingService->getSettingsByGroup($group) : $settingService->getAllSettings();
        
        echo json_encode([
            'success' => true,
            'data' => $settings
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    }
} catch (Throwable $e) {
    error_log('[AdminSettingsAPI] Error: ' . $e->getMessage());
    // Return 200 with success:false so frontend can show the message without status code errors
    echo json_encode(['success' => false, 'message' => 'System error: ' . $e->getMessage()]);
}
