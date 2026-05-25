<?php
/**
 * Sweets Website
 * =============================================================
 * File: CustomerService.php
 * Description: Service layer for high-fidelity Customer CRM
 * Author: Antigravity - Senior Frontend Architect
 * Version: 2.1.0
 * =============================================================
 */

require_once REPOS_PATH . '/CustomerRepository.php';
require_once REPOS_PATH . '/OrderRepository.php';

class CustomerService {
    private CustomerRepository $repo;
    private OrderRepository $orderRepo;

    public function __construct() {
        $this->repo = new CustomerRepository();
        $this->orderRepo = new OrderRepository();
    }

    /**
     * Get comprehensive customer profile for detailed view
     */
    public function getCustomerProfile(int $id): ?array {
        $user = $this->repo->getById($id);
        if (!$user) return null;

        $stats = $this->repo->getDetailedStats($id);
        $addresses = $this->repo->getAddressesByUserId($id);
        $recentOrders = $this->orderRepo->getOrdersByUserId($id, 5);

        // Process addresses
        $billing = null;
        $shipping = null;

        foreach ($addresses as $addr) {
            if ($addr['is_default']) {
                $billing = $addr;
                $shipping = $addr; // Default to same as billing
            }
        }

        return [
            'info' => $user,
            'stats' => $stats,
            'billing' => $billing,
            'shipping' => $shipping,
            'recent_orders' => $recentOrders,
            'initials' => $this->getInitials($user['full_name'])
        ];
    }

    /**
     * Get detailed info for the logged-in user profile
     */
    public function getProfileData(int $userId): ?array {
        return $this->repo->getCustomerDetails($userId);
    }

    /**
     * Update profile data
     */
    public function updateProfile(int $userId, array $data): bool {
        return $this->repo->updateProfile($userId, $data);
    }

    private function getInitials(string $name): string {
        $words = explode(' ', $name);
        $initials = '';
        foreach ($words as $w) {
            if (!empty($w)) {
                $initials .= strtoupper(substr($w, 0, 1));
            }
        }
        return substr($initials, 0, 2);
    }
}
