<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/reset_password_with_otp.php
 * Description: Finalize password reset using verified token
 * =============================================================
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$token = trim($_POST['token'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($token) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    $db = Database::getInstance();

    // Verify token one last time
    $stmt = $db->prepare("SELECT id FROM password_resets 
                          WHERE email = :email 
                          AND token_hash = :token 
                          AND used_at IS NULL 
                          AND expires_at > NOW() 
                          LIMIT 1");
    
    $stmt->execute([':email' => $email, ':token' => $token]);
    $reset = $stmt->fetch();

    if (!$reset) {
        echo json_encode(['success' => false, 'message' => 'Security token expired or invalid.']);
        exit;
    }

    $db->beginTransaction();

    // 1. Update user password
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $stmtUser = $db->prepare("UPDATE users SET password = :password WHERE email = :email LIMIT 1");
    $stmtUser->execute([
        ':password' => $passwordHash,
        ':email' => $email
    ]);

    // 2. Mark reset token as used
    $stmtMarkUsed = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
    $stmtMarkUsed->execute([':id' => (int)$reset['id']]);

    // 3. Cleanup other unused tokens for this email
    $stmtCleanup = $db->prepare("DELETE FROM password_resets WHERE email = :email AND used_at IS NULL");
    $stmtCleanup->execute([':email' => $email]);

    $db->commit();

    echo json_encode(['success' => true, 'message' => 'Password has been reset successfully.']);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    error_log("[ResetPasswordOTP] Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again.']);
}
