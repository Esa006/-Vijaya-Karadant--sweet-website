<?php

namespace App\Modules\Auth\Controllers;

use App\Core\BaseController;
use App\Modules\Auth\Services\AuthService;

/**
 * Auth Controller
 * Handles login and logout requests
 */
class AuthController extends BaseController {
    
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    /**
     * Handle Login Request
     */
    public function login(): void {
        $email    = $this->input('email');
        $password = $this->input('password');

        if (empty($email) || empty($password)) {
            $this->error("Email and password are required", 422);
        }

        $user = $this->authService->authenticate($email, $password);

        if ($user) {
            $this->success("Login successful", [
                'user' => [
                    'id' => $user['id'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ],
                'redirect' => $user['role'] === 'admin' ? '/public/admin/dashboard' : '/'
            ]);
        } else {
            $this->error("Invalid email or password", 401);
        }
    }

    /**
     * Handle Logout Request
     */
    public function logout(): void {
        $this->authService->logout();
        $this->success("Logout successful");
    }
}
