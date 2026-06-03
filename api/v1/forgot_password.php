<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';

header('Content-Type: application/json');

function ensurePasswordResetTable(PDO $db): void {
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL,
                token_hash CHAR(64) NOT NULL,
                otp CHAR(6) DEFAULT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_password_resets_email (email),
                INDEX idx_password_resets_expires (expires_at),
                UNIQUE KEY uniq_password_resets_token_hash (token_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $db->exec($sql);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'Email address is required']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

try {
    $db = Database::getInstance();
    ensurePasswordResetTable($db);

    // Check if user exists
    $stmt = $db->prepare("SELECT id, full_name FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    $response = [
        'success' => true,
        'message' => 'If an account with that email exists, we have sent a verification code.'
    ];

    if (!$user) {
        echo json_encode($response);
        exit;
    }

    $db->prepare("DELETE FROM password_resets WHERE email = :email AND used_at IS NULL")
        ->execute([':email' => $email]);

    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);
    $otp = (string)random_int(100000, 999999);

    $stmtInsert = $db->prepare("INSERT INTO password_resets (email, token_hash, otp, expires_at) VALUES (:email, :token_hash, :otp, DATE_ADD(NOW(), INTERVAL 15 MINUTE))");
    $stmtInsert->execute([
        ':email' => $email,
        ':token_hash' => $tokenHash,
        ':otp' => $otp
    ]);

    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $isLocal = strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false;

    $resetLink = BASE_URL . 'reset-password.php?token=' . urlencode($token);
    
    // Always log for debugging
    error_log('[ForgotPassword] Reset link generated for ' . $email . ' - ' . $resetLink);

    // 1. Send Real Email
    $emailSvc = new \App\Service\EmailService();
    $mailSent = $emailSvc->sendPasswordResetOTP($email, $otp, $resetLink);

    // 2. Local fallback: return OTP in JSON for developer convenience
    if ($isLocal) {
        $response['otp'] = $otp;
        $response['reset_link'] = $resetLink;
        $response['mail_sent'] = $mailSent;
    }

    echo json_encode($response);


} catch (\Throwable $e) {
    error_log("[ForgotPassword] Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'System error. Please try again later.']);
}
