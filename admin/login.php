<?php
/**
 * Sweets Website
 * =============================================================
 * File: login.php
 * Description: Secure administrative login interface
 * Author: Antigravity - Principal Security Architect
 * Version: 2.1.0
 * =============================================================
 */

require_once '../config/config.php';
require_once SERVICES_PATH . '/AuthService.php';

if (session_status() === PHP_SESSION_NONE)
    session_start();

$auth = new AuthService();
$error = '';

/**
 * Handle Login POST
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $token = $_POST['csrf_token'] ?? '';

    // 1. Basic CSRF check (if token exists)
    if (isset($_SESSION['csrf_token']) && $token !== $_SESSION['csrf_token']) {
        $error = 'Invalid security token. Please try again.';
    } else {
        // 2. Authenticate
        if ($auth->login($email, $password)) {
            header('Location: ' . BASE_URL . 'admin/index.php');
            exit;
        } else {
            $error = 'Invalid administrative credentials or unauthorized role.';
            // Delay to mitigate brute force
            sleep(1);
        }
    }
}

// Generate new CSRF token for the form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sweets Website</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --admin-primary: #ae4b3a;
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
            max-width: 400px;
            padding: 2.5rem;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            background: white;
        }

        .login-logo {
            width: 100px;
            margin-bottom: 1.5rem;
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
            background-color: #8e3d2f;
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
        <div class="text-center">
            <img src="<?php echo BASE_URL . SITE_LOGO; ?>" alt="<?php echo SITE_NAME; ?> Logo" class="login-logo">
            <h4 class="fw-bold mb-1">Administrative Access</h4>
            <p class="text-muted small mb-4">Secure Portal v2.1</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <div>
                    <?php echo $error; ?>
                </div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="mb-3">
                <label class="form-label small fw-bold text-muted">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted ps-3"><i
                            class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control border-start-0 ps-1"
                        placeholder="admin@sweets.com" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-bold text-muted">Management Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0 text-muted ps-3"><i
                            class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control border-start-0 ps-1"
                        placeholder="••••••••" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">Authenticate session</button>
        </form>

        <div class="mt-4 text-center">
            <a href="<?php echo BASE_URL; ?>" class="text-decoration-none text-muted small">
                <i class="bi bi-arrow-left me-1"></i> Back to website
            </a>
        </div>
    </div>

</body>

</html>