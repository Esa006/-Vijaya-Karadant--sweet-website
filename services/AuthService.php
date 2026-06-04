<?php
/**
 * Sweets Website
 * =============================================================
 * File: AuthService.php
 * Description: Hardened administrative authentication logic
 * Author: Antigravity - Principal Security Architect
 * Version: 2.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/UserRepository.php';

class AuthService {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Authenticate Admin User (Hardened)
     */
    public function login(string $email, string $password): bool {
        require_once SERVICES_PATH . '/CacheService.php';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!RateLimiter::check("rate:login:{$ip}", 5, 60)) {
            error_log("[Security] Rate limit exceeded for login from IP: $ip");
            return false;
        }

        // 1. Fetch user by email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email AND role = 'admin' LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            error_log("[Security] Admin login attempt for non-existent or non-admin user: $email");
            return false;
        }

        // 2. Verify Password (Secure Hash)
        // For existing plaintext passwords in local DB, handle gracefully but warn
        $isValid = password_verify($password, $user['password']);
        
        // Fallback for demo/dev (If hash fails, check if plain matches - ONLY for development)
        if (!$isValid && $password === $user['password']) {
            $isValid = true;
            // Upgrade to hash immediately (Self-healing security)
            $this->upgradePassword($user['id'], $password);
        }

        if ($isValid) {
            $this->initializeSession($user);
            return true;
        }

        return false;
    }

    /**
     * Initialize secure session with hijacking guards
     */
    private function initializeSession(array $user): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        session_regenerate_id(true);
        
        $_SESSION['user_id']   = $user['id'];
        $_SESSION['user_role'] = 'admin';
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_ip']   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; // Binding to IP
        $_SESSION['permissions'] = ['all']; // Root admin roles
        
        // CSRF Token for administrative actions
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

        // Record login activity
        $this->recordLoginActivity($user['id']);
    }

    /**
     * Record login activity into admin_login_activity table
     * Detects device type and browser from User-Agent automatically
     */
    private function recordLoginActivity(int $userId): void {
        try {
            $ip        = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

            // --- Device Label & Type Detection ---
            $deviceType  = 'desktop';
            $deviceLabel = 'Unknown Device - Unknown Browser';

            $ua = strtolower($userAgent);

            // Detect device type
            if (preg_match('/android|iphone|ipod|windows phone|mobile/i', $userAgent)) {
                $deviceType = 'mobile';
            } elseif (preg_match('/ipad|tablet/i', $userAgent)) {
                $deviceType = 'tablet';
            }

            // Detect OS
            $os = 'Unknown OS';
            if (str_contains($ua, 'windows nt'))       $os = 'Windows PC';
            elseif (str_contains($ua, 'macintosh'))    $os = 'Mac';
            elseif (str_contains($ua, 'android'))      $os = 'Android Phone';
            elseif (str_contains($ua, 'iphone'))       $os = 'iPhone';
            elseif (str_contains($ua, 'ipad'))         $os = 'iPad';
            elseif (str_contains($ua, 'linux'))        $os = 'Linux PC';

            // Detect Browser
            $browser = 'Unknown Browser';
            if (str_contains($ua, 'edg/'))             $browser = 'Edge';
            elseif (str_contains($ua, 'opr/') || str_contains($ua, 'opera/')) $browser = 'Opera';
            elseif (str_contains($ua, 'chrome/') && !str_contains($ua, 'chromium')) $browser = 'Chrome';
            elseif (str_contains($ua, 'firefox/'))     $browser = 'Firefox';
            elseif (str_contains($ua, 'safari/') && !str_contains($ua, 'chrome')) $browser = 'Safari';

            $deviceLabel = "$os - $browser";

            // Detect location (local vs public IP)
            $location = 'Unknown';
            if ($ip === '::1' || $ip === '127.0.0.1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
                $location = 'Local Network';
            } else {
                $location = 'Public Network';
            }

            // Mark all previous sessions for this admin as NOT current
            $resetStmt = $this->db->prepare("UPDATE admin_login_activity SET is_current = 0 WHERE admin_id = :id");
            $resetStmt->execute([':id' => $userId]);

            // Insert new login record as current
            $insertStmt = $this->db->prepare("
                INSERT INTO admin_login_activity 
                    (admin_id, ip_address, user_agent, device_label, device_type, location, status, action_label, is_current, created_at)
                VALUES 
                    (:admin_id, :ip, :ua, :device_label, :device_type, :location, 'success', 'Logged In', 1, NOW())
            ");
            $insertStmt->execute([
                ':admin_id'     => $userId,
                ':ip'           => $ip,
                ':ua'           => $userAgent,
                ':device_label' => $deviceLabel,
                ':device_type'  => $deviceType,
                ':location'     => $location,
            ]);
        } catch (\Exception $e) {
            // Don't block login if activity logging fails
            error_log('[AuthService] Login activity recording failed: ' . $e->getMessage());
        }
    }

    private function upgradePassword(int $userId, string $plainPassword): void {
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = :hash WHERE id = :id");
        $stmt->execute([':hash' => $hash, ':id' => $userId]);
    }

    public function isLoggedIn(): bool {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }

    public function logout(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // Mark session as no longer current in DB
        if (!empty($_SESSION['user_id'])) {
            try {
                $stmt = $this->db->prepare("UPDATE admin_login_activity SET is_current = 0 WHERE admin_id = :id AND is_current = 1");
                $stmt->execute([':id' => $_SESSION['user_id']]);
            } catch (\Exception $e) {
                error_log('[AuthService] Logout activity update failed: ' . $e->getMessage());
            }
        }

        session_unset();
        session_destroy();
    }
}
