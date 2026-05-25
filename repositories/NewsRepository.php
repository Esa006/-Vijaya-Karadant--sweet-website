<?php
/**
 * Sweets Website
 * =============================================================
 * File: NewsRepository.php
 * Description: Data access layer for news updates
 * Pattern: Repository Pattern
 * =============================================================
 */

require_once REPOS_PATH . '/BaseRepository.php';

class NewsRepository extends BaseRepository {

    public function getAllActive(): array {
        return $this->fetchAll("
            SELECT id, title, publish_date, description, image_path, status, created_at
            FROM news_updates
            WHERE status = 'active'
            ORDER BY publish_date DESC
        ");
    }

    public function getAll(): array {
        return $this->fetchAll("
            SELECT id, title, publish_date, description, image_path, status, created_at
            FROM news_updates
            ORDER BY publish_date DESC
        ");
    }

    public function getById(int $id): ?array {
        return $this->fetchOne("
            SELECT id, title, publish_date, description, image_path, status, created_at
            FROM news_updates
            WHERE id = :id
            LIMIT 1
        ", [':id' => $id]);
    }

    public function create(array $data): int {
        $sql = "INSERT INTO news_updates (
                    title, publish_date, description, image_path, status
                ) VALUES (
                    :title, :publish_date, :description, :image_path, :status
                )";
        
        $params = [
            ':title'        => $data['title'],
            ':publish_date' => $data['publish_date'],
            ':description'  => $data['description'],
            ':image_path'   => $data['image_path'],
            ':status'       => $data['status'] ?? 'active'
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE news_updates SET 
                    title = :title,
                    publish_date = :publish_date,
                    description = :description,
                    image_path = :image_path,
                    status = :status
                WHERE id = :id";

        $params = [
            ':id'           => $id,
            ':title'        => $data['title'],
            ':publish_date' => $data['publish_date'],
            ':description'  => $data['description'],
            ':image_path'   => $data['image_path'],
            ':status'       => $data['status'] ?? 'active'
        ];

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    public function delete(int $id): bool {
        $sql = "DELETE FROM news_updates WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
