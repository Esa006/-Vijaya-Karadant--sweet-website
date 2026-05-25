<?php
/**
 * Sweets Website
 * =============================================================
 * File: change_password.php
 * Description: Secure API endpoint to change admin password
 * =============================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/Database.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

// CSRF validation
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Invalid security token. Please refresh the page.']);
    exit;
}

// Auth check
if (empty($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit;
}

try {
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword     = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // --- Input Validation ---
    if (empty($currentPassword)) {
        throw new InvalidArgumentException('Current password is required.');
    }
    if (strlen($newPassword) < 8) {
        throw new InvalidArgumentException('New password must be at least 8 characters.');
    }
    if ($newPassword !== $confirmPassword) {
        throw new InvalidArgumentException('New password and confirmation do not match.');
    }
    if ($currentPassword === $newPassword) {
        throw new InvalidArgumentException('New password must be different from your current password.');
    }

    $pdo    = Database::getInstance();
    $userId = (int)$_SESSION['user_id'];

    // Fetch current stored hash
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $userId]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Account not found.']);
        exit;
    }

    // Verify current password
    if (!password_verify($currentPassword, $row['password'])) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect.']);
        exit;
    }

    // Hash new password with bcrypt (cost 12)
    $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

    // Persist the new hash
    $update = $pdo->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id");
    $update->execute([':password' => $newHash, ':id' => $userId]);

    http_response_code(200);
    echo json_encode([
        'status'  => 'success',
        'message' => 'Password updated successfully!'
    ]);

} catch (InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Exception $e) {
    error_log('[Admin Change Password API] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'An internal server error occurred.']);
}
