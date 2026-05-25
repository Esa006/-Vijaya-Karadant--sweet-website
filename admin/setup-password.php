<?php
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/src/Autoloader.php';
require_once ROOT_PATH . '/config/Database.php';

use App\Core\Database;

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$invite = null;

if (empty($token)) {
    $error = "Invalid or missing invitation token.";
} else {
    // Validate token
    $stmt = Database::query("SELECT * FROM admin_invites WHERE token = :token AND used_at IS NULL", ['token' => $token]);
    $invite = $stmt->fetch();
    
    if (!$invite) {
        $error = "This invitation link is invalid or has already been used.";
    } elseif (strtotime($invite['expires_at']) < time()) {
        $error = "This invitation link has expired. Please contact the administrator for a new one.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        try {
            Database::beginTransaction();
            
            // Hash new password
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            
            // Update user
            Database::query("UPDATE users SET password = :pass, status = 'Active' WHERE email = :email", [
                'pass' => $hashed,
                'email' => $invite['email']
            ]);
            
            // Mark invite as used
            Database::query("UPDATE admin_invites SET used_at = NOW() WHERE id = :id", [
                'id' => $invite['id']
            ]);
            
            Database::commit();
            $success = "Your password has been set successfully! You can now log in.";
        } catch (Exception $e) {
            Database::rollBack();
            $error = "An error occurred while saving your password. Please try again.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Admin Password - Sweets Website</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --admin-primary: #7a1f1f;
            --admin-secondary: #fdf5f2;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--admin-secondary);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            background: white;
        }
        .btn-primary {
            background-color: var(--admin-primary);
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        .btn-primary:hover {
            background-color: #5a1414;
            transform: translateY(-2px);
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #eee;
            background: #fafafa;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: var(--admin-primary);
            background: white;
        }
        .alert {
            font-size: 0.9rem;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">Setup Admin Access</h4>
            <p class="text-muted small">Create a secure password for your account</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div><?= htmlspecialchars($error) ?></div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <div><?= htmlspecialchars($success) ?></div>
            </div>
            <a href="<?= BASE_URL ?>admin/login.php" class="btn btn-primary w-100 mt-3">Go to Login</a>
        <?php elseif (!$error && $invite): ?>
            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Email Address</label>
                    <input type="email" class="form-control" value="<?= htmlspecialchars($invite['email']) ?>" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">New Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8" placeholder="At least 8 characters">
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="8" placeholder="Retype password">
                </div>
                <button type="submit" class="btn btn-primary w-100">Set Password</button>
            </form>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="mt-4 text-center">
                <a href="<?= BASE_URL ?>admin/login.php" class="text-decoration-none text-muted small">
                    <i class="bi bi-arrow-left me-1"></i> Back to Login
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
