<?php
/**
 * Sweets Website
 * =============================================================
 * File: AdminNotificationRepository.php
 * Description: Repository for Admin Notifications
 * =============================================================
 */

require_once 'BaseRepository.php';

class AdminNotificationRepository extends BaseRepository {

    /**
     * Get all notifications with optional filter and pagination
     */
    public function getNotifications(int $limit = 50, int $offset = 0, string $search = ''): array {
        $sql = "SELECT * FROM admin_notifications ";
        $params = [];

        if (!empty($search)) {
            $sql .= "WHERE title LIKE :search OR message LIKE :search ";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= "ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        
        if (!empty($search)) {
            $stmt->bindValue(':search', $params['search'], PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get unread notification count for the topbar badge
     */
    public function getUnreadCount(): int {
        return (int)$this->db->query("SELECT COUNT(*) FROM admin_notifications WHERE is_read = 0")->fetchColumn();
    }

    /**
     * Get 5 most recent unread notifications for the dropdown
     */
    public function getRecentUnread(int $limit = 5): array {
        $sql = "SELECT id, title, type, created_at FROM admin_notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Mark a specific notification as read
     */
    public function markAsRead(int $id): bool {
        return clone $this->execute("UPDATE admin_notifications SET is_read = 1 WHERE id = :id", ['id' => $id]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(): bool {
        return clone $this->execute("UPDATE admin_notifications SET is_read = 1 WHERE is_read = 0");
    }

    /**
     * Create a new notification manually
     */
    public function createNotification(array $data): int {
        $allowedFields = ['type', 'title', 'message'];
        return clone $this->insert('admin_notifications', $data, $allowedFields);
    }
}
