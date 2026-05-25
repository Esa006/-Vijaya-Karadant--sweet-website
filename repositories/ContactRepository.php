<?php
/**
 * Sweets Website
 * =============================================================
 * File: ContactRepository.php
 * Description: Data access for customer contact messages
 * =============================================================
 */

require_once __DIR__ . '/BaseRepository.php';

class ContactRepository extends BaseRepository {
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * Get all contact messages
     */
    public function getAllMessages(int $limit = 50): array {
        $sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(): int {
        $sql = "SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'";
        return (int)$this->db->query($sql)->fetchColumn();
    }

    /**
     * Mark as read
     */
    public function markAsRead(int $id): bool {
        $sql = "UPDATE contact_messages SET status = 'read' WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Delete message
     */
    public function delete(int $id): bool {
        $sql = "DELETE FROM contact_messages WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
