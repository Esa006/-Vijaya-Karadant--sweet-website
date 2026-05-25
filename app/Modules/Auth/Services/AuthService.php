<?php

namespace App\Modules\Auth\Services;

use App\Core\Database;
use PDO;

/**
 * Enterprise Auth Service
 * Handles user authentication, session management, and password security
 */
class AuthService {
    
    /**
     * Authenticate a user
     */
    public function authenticate(string $email, string $password): ?array {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = Database::query($sql, ['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Check if account is active (if status column exists)
            if (isset($user['status']) && strtolower($user['status']) !== 'active') {
                return null;
            }
            
            $this->createSession($user);
            return $user;
        }

        return null;
    }

    /**
     * Create a secure session
     */
    private function createSession(array $user): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'] ?? 'customer';
        $_SESSION['last_activity'] = time();
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Check if user is logged in
     */
    public static function isLoggedIn(): bool {
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }

    /**
     * Check if user has a specific role
     */
    public static function hasRole(string $role): bool {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
    }

    /**
     * Logout user
     */
    public function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }
}
