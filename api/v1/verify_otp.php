<?php
/**
 * Sweets Website
 * =============================================================
 * File: api/v1/verify_otp.php
 * Description: Verify 6-digit OTP for password reset
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
$otp = trim($_POST['otp'] ?? '');

if (empty($email) || empty($otp)) {
    echo json_encode(['success' => false, 'message' => 'Email and OTP are required']);
    exit;
}

try {
    $db = Database::getInstance();

    // Check if OTP matches and is not expired
    $stmt = $db->prepare("SELECT token_hash FROM password_resets 
                          WHERE email = :email 
                          AND otp = :otp 
                          AND used_at IS NULL 
                          AND expires_at > NOW() 
                          ORDER BY created_at DESC LIMIT 1");
    
    $stmt->execute([':email' => $email, ':otp' => $otp]);
    $reset = $stmt->fetch();

    if ($reset) {
        echo json_encode([
            'success' => true, 
            'message' => 'OTP verified successfully.',
            'token' => $reset['token_hash'] // We pass this back to the UI to use in the next step
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid or expired OTP.']);
    }

} catch (Exception $e) {
    error_log("[VerifyOTP] Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again.']);
}
