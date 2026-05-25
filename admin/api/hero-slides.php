<?php
/**
 * Sweets Website
 * =============================================================
 * File: admin/api/hero-slides.php
 * Description: API handler for Hero Slide CRUD and file uploads
 * =============================================================
 */

require_once dirname(dirname(__DIR__)) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/auth.php'; // Enforces admin login
verifyCSRF(); // Checks for CSRF token on POST

require_once SERVICES_PATH . '/HeroSlideService.php';

header('Content-Type: application/json');

$heroService = new HeroSlideService();
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $action = $_GET['action'] ?? '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($action === 'get' && $id > 0) {
            $slide = $heroService->getById($id);
            if ($slide) {
                echo json_encode(['success' => true, 'slide' => $slide]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Slide not found']);
            }
        } else {
            $slides = $heroService->getAllSlides();
            echo json_encode(['success' => true, 'slides' => $slides]);
        }
    } 
    elseif ($method === 'POST') {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $data = [
                'title_line1'   => $_POST['title_line1'] ?? '',
                'title_accent'  => $_POST['title_accent'] ?? '',
                'tagline'       => $_POST['tagline'] ?? '',
                'button_text'   => $_POST['button_text'] ?? 'Shop Now',
                'button_url'    => $_POST['button_url'] ?? '#bestsellers',
                'desktop_image' => $_POST['desktop_image'] ?? '',
                'mobile_image'  => $_POST['mobile_image'] ?? '',
                'sort_order'    => (int)($_POST['sort_order'] ?? 1),
                'is_active'     => (int)($_POST['is_active'] ?? 1),
            ];

            // Handle file uploads if present
            if (!empty($_FILES['desktop_file']) && $_FILES['desktop_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $heroService->uploadImage($_FILES['desktop_file'], 'desktop');
                if ($uploaded) {
                    $data['desktop_image'] = $uploaded;
                }
            }
            if (!empty($_FILES['mobile_file']) && $_FILES['mobile_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $heroService->uploadImage($_FILES['mobile_file'], 'mobile');
                if ($uploaded) {
                    $data['mobile_image'] = $uploaded;
                }
            }

            // Fallback to placeholders or existing paths if not set
            if (empty($data['desktop_image'])) {
                $data['desktop_image'] = 'assets/images/banners/home-banner  (5).png';
            }
            if (empty($data['mobile_image'])) {
                $data['mobile_image'] = 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228075 (2).png';
            }

            $newId = $heroService->create($data);
            if ($newId > 0) {
                echo json_encode(['success' => true, 'message' => 'Slide created successfully', 'id' => $newId]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create slide in database']);
            }
        } 
        elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $data = [
                'title_line1'   => $_POST['title_line1'] ?? '',
                'title_accent'  => $_POST['title_accent'] ?? '',
                'tagline'       => $_POST['tagline'] ?? '',
                'button_text'   => $_POST['button_text'] ?? 'Shop Now',
                'button_url'    => $_POST['button_url'] ?? '#bestsellers',
                'sort_order'    => (int)($_POST['sort_order'] ?? 1),
                'is_active'     => (int)($_POST['is_active'] ?? 1),
            ];

            // If manual paths were set/updated
            if (isset($_POST['desktop_image'])) {
                $data['desktop_image'] = $_POST['desktop_image'];
            }
            if (isset($_POST['mobile_image'])) {
                $data['mobile_image'] = $_POST['mobile_image'];
            }

            // Handle file uploads if present
            if (!empty($_FILES['desktop_file']) && $_FILES['desktop_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $heroService->uploadImage($_FILES['desktop_file'], 'desktop');
                if ($uploaded) {
                    $data['desktop_image'] = $uploaded;
                }
            }
            if (!empty($_FILES['mobile_file']) && $_FILES['mobile_file']['error'] === UPLOAD_ERR_OK) {
                $uploaded = $heroService->uploadImage($_FILES['mobile_file'], 'mobile');
                if ($uploaded) {
                    $data['mobile_image'] = $uploaded;
                }
            }

            $success = $heroService->update($id, $data);
            echo json_encode(['success' => $success, 'message' => $success ? 'Slide updated successfully' : 'No changes or update failed']);
        }
        elseif ($action === 'toggle') {
            $id = (int)($_POST['id'] ?? 0);
            $isActive = (int)($_POST['is_active'] ?? 1);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $success = $heroService->update($id, ['is_active' => $isActive]);
            echo json_encode(['success' => $success]);
        }
        elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID']);
                exit;
            }

            $success = $heroService->delete($id);
            echo json_encode(['success' => $success]);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } else {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
