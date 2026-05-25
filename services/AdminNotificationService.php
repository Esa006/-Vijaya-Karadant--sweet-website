<?php
/**
 * Sweets Website
 * =============================================================
 * File: AdminNotificationService.php
 * Description: Logic layer for handling admin notifications
 * =============================================================
 */

require_once ROOT_PATH . '/repositories/AdminNotificationRepository.php';

class AdminNotificationService {
    
    private AdminNotificationRepository $repo;

    public function __construct() {
        $this->repo = new AdminNotificationRepository();
    }

    public function getAllNotifications(int $limit = 50, $offset = 0, $search = ''): array {
        try {
            return $this->repo->getNotifications($limit, $offset, $search);
        } catch (Exception $e) {
            error_log('[AdminNotificationService] getAllNotifications failed: ' . $e->getMessage());
            return [];
        }
    }

    public function getTopbarData(): array {
        try {
            $count = $this->repo->getUnreadCount();
            $recent = $this->repo->getRecentUnread(5);
            return [
                'count' => $count,
                'recent' => $recent
            ];
        } catch (Exception $e) {
            error_log('[AdminNotificationService] getTopbarData failed: ' . $e->getMessage());
            return ['count' => 0, 'recent' => []];
        }
    }

    public function markAsRead(int $id): bool {
        try {
            return $this->repo->markAsRead($id);
        } catch (Exception $e) {
            error_log('[AdminNotificationService] markAsRead failed: ' . $e->getMessage());
            return false;
        }
    }

    public function markAllAsRead(): bool {
        try {
            return $this->repo->markAllAsRead();
        } catch (Exception $e) {
            error_log('[AdminNotificationService] markAllAsRead failed: ' . $e->getMessage());
            return false;
        }
    }

    public function logOrder(string $orderNumber, float $amount, string $customerName): bool {
        $data = [
            'type' => 'order',
            'title' => 'New order received',
            'message' => "Order #$orderNumber was placed by $customerName for ₹ $amount."
        ];
        return $this->repo->createNotification($data) > 0;
    }

    public function logStockWarning(string $productName, int $remaining): bool {
        $data = [
            'type' => 'stock',
            'title' => 'Low stock Alert',
            'message' => "$productName is running low on stock. only $remaining units remaining in inventory"
        ];
        return $this->repo->createNotification($data) > 0;
    }
}
