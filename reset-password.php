<?php
/**
 * Sweets Website
 * =============================================================
 * File: reset-password.php
 * Description: Customer password reset form using token flow
 * =============================================================
 */
require_once 'config/config.php';
require_once 'config/Database.php';

$token = trim((string)($_GET['token'] ?? $_POST['token'] ?? ''));
$errorMessage = '';
$successMessage = '';

function ensurePasswordResetTable(PDO $db): void {
    $sql = "CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL,
                token_hash CHAR(64) NOT NULL,
                expires_at DATETIME NOT NULL,
                used_at DATETIME DEFAULT NULL,
                created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_password_resets_email (email),
                INDEX idx_password_resets_expires (expires_at),
                UNIQUE KEY uniq_password_resets_token_hash (token_hash)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

    $db->exec($sql);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = (string)($_POST['csrf_token'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errorMessage = 'Security validation failed. Please refresh and try again.';
    } elseif ($token === '') {
        $errorMessage = 'Missing reset token.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Password and confirm password do not match.';
    } else {
        try {
            $db = Database::getInstance();
            ensurePasswordResetTable($db);

            $tokenHash = hash('sha256', $token);

            $stmtReset = $db->prepare("SELECT id, email FROM password_resets WHERE token_hash = :token_hash AND used_at IS NULL AND expires_at > NOW() LIMIT 1");
            $stmtReset->execute([':token_hash' => $tokenHash]);
            $resetRow = $stmtReset->fetch(PDO::FETCH_ASSOC);

            if (!$resetRow) {
                $errorMessage = 'This reset link is invalid or expired. Please request a new one.';
            } else {
                $db->beginTransaction();

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmtUser = $db->prepare("UPDATE users SET password = :password WHERE email = :email LIMIT 1");
                $stmtUser->execute([
                    ':password' => $passwordHash,
                    ':email' => $resetRow['email']
                ]);

                $stmtMarkUsed = $db->prepare("UPDATE password_resets SET used_at = NOW() WHERE id = :id");
                $stmtMarkUsed->execute([':id' => (int)$resetRow['id']]);

                $stmtCleanup = $db->prepare("DELETE FROM password_resets WHERE email = :email AND used_at IS NULL");
                $stmtCleanup->execute([':email' => $resetRow['email']]);

                $db->commit();
                $successMessage = 'Your password has been reset successfully. You can now sign in.';
            }
        } catch (Exception $e) {
            if (isset($db) && $db instanceof PDO && $db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[ResetPassword] Error: ' . $e->getMessage());
            $errorMessage = 'System error. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | <?php echo htmlspecialchars(SITE_NAME); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #fff9f3 0%, #f9efe3 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 1rem;
        }
        .rp-card {
            width: 100%;
            max-width: 460px;
            border: 1px solid #ecd8c2;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 50px rgba(85, 34, 12, 0.08);
        }
        .rp-btn {
            background: linear-gradient(90deg, #7b1d1d 0%, #c15500 100%);
            border: 0;
            color: #fff;
            font-weight: 600;
        }
        .rp-btn:hover { color: #fff; opacity: 0.95; }
    </style>
</head>
<body>
    <div class="rp-card p-4 p-md-5">
        <h1 class="h4 mb-2" style="color:#7b1d1d;">Reset Your Password</h1>
        <p class="text-muted mb-4">Enter your new password below.</p>

        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if ($successMessage !== ''): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
            <a href="login.php" class="btn rp-btn w-100">Go to Sign In</a>
        <?php elseif ($token === ''): ?>
            <div class="alert alert-warning">Reset token missing. Please use the full link from forgot password.</div>
            <a href="login.php" class="btn btn-outline-secondary w-100">Back to Sign In</a>
        <?php else: ?>
            <form method="post" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars((string)$_SESSION['csrf_token']); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="mb-3">
                    <label for="password" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="password" name="password" required minlength="6" autocomplete="new-password">
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6" autocomplete="new-password">
                </div>

                <button type="submit" class="btn rp-btn w-100">Reset Password</button>
            </form>

            <a href="login.php" class="btn btn-link w-100 mt-3" style="color:#7b1d1d;">Back to Sign In</a>
        <?php endif; ?>
    </div>
</body>
</html>
