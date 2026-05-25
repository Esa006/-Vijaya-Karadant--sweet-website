<?php
/**
 * Sweets Website
 * =============================================================
 * File: update_profile.php
 * Description: Secure API Endpoint to update Admin Profile
 * =============================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/Database.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// CSRF & Authentication validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid security token. Please refresh the page.']);
    exit;
}

if (empty($_SESSION['user_id']) || empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $pdo = Database::getInstance();
    $userId = (int)$_SESSION['user_id'];
    
    // Sanitize inputs
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $language = trim($_POST['language'] ?? 'English (US)');
    $timezone = trim($_POST['timezone'] ?? '(UTC+05:30) Asia/Kolkata');

    // Handle optional avatar upload
    $avatarUrl = null;
    if (!empty($_FILES['avatar']['tmp_name'])) {
        $file = $_FILES['avatar'];
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!in_array($mime, $allowed)) {
            throw new InvalidArgumentException('Invalid image type. Only JPG, PNG, WebP, or GIF allowed.');
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new InvalidArgumentException('Image is too large. Max 2MB allowed.');
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $avatarDir = __DIR__ . '/../../../assets/images/avatars/';
        if (!is_dir($avatarDir)) mkdir($avatarDir, 0755, true);
        $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $avatarDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            throw new RuntimeException('Failed to save the uploaded image.');
        }
        $avatarUrl = 'assets/images/avatars/' . $filename;
    }

    if (empty($fullName) || empty($email)) {
        throw new InvalidArgumentException('Full Name and Email are required.');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Invalid email format.');
    }

    // Check if email exists for another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $stmt->execute([':email' => $email, ':id' => $userId]);
    if ($stmt->fetch()) {
        throw new InvalidArgumentException('This email is already in use by another account.');
    }

    // Update User
    if ($avatarUrl !== null) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = :full_name, email = :email, phone = :phone,
                language = :language, timezone = :timezone,
                avatar = :avatar, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':full_name' => $fullName, ':email' => $email, ':phone' => $phone,
                        ':language' => $language, ':timezone' => $timezone,
                        ':avatar' => $avatarUrl, ':id' => $userId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = :full_name, email = :email, phone = :phone,
                language = :language, timezone = :timezone, updated_at = NOW()
            WHERE id = :id
        ");
        $stmt->execute([':full_name' => $fullName, ':email' => $email, ':phone' => $phone,
                        ':language' => $language, ':timezone' => $timezone, ':id' => $userId]);
    }

    // Update Session so UI updates dynamically
    $_SESSION['user_name'] = $fullName;
    $_SESSION['user_email'] = $email;
    if ($avatarUrl !== null) {
        $_SESSION['user_avatar'] = $avatarUrl;
    }
    
    http_response_code(200);
    echo json_encode([
        'status' => 'success', 
        'message' => 'Profile updated successfully!',
        'data' => [
            'full_name' => $fullName,
            'email'     => $email,
            'avatar_url' => $avatarUrl ? (BASE_URL . $avatarUrl) : null
        ]
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log("[Admin Profile API] " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred.']);
}
