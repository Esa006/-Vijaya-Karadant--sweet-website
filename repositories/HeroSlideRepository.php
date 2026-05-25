<?php
/**
 * Sweets Website
 * =============================================================
 * File: repositories/HeroSlideRepository.php
 * Description: Data access layer for hero_slides table
 * =============================================================
 */

require_once 'BaseRepository.php';

class HeroSlideRepository extends BaseRepository {

    /**
     * Get all active slides ordered by sort_order
     */
    public function getActiveSlides(): array {
        try {
            $sql = "SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY sort_order ASC";
            return $this->fetchAll($sql);
        } catch (Exception $e) {
            error_log('[HeroSlideRepository] getActiveSlides failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all slides (admin view)
     */
    public function getAllSlides(): array {
        try {
            $sql = "SELECT * FROM hero_slides ORDER BY sort_order ASC";
            return $this->fetchAll($sql);
        } catch (Exception $e) {
            error_log('[HeroSlideRepository] getAllSlides failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single slide by ID
     */
    public function getById(int $id): ?array {
        try {
            return $this->fetchOne("SELECT * FROM hero_slides WHERE id = :id", ['id' => $id]);
        } catch (Exception $e) {
            error_log('[HeroSlideRepository] getById failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create new slide
     */
    public function create(array $data): int {
        $sql = "INSERT INTO hero_slides 
                (title_line1, title_accent, tagline, button_text, button_url, desktop_image, mobile_image, sort_order, is_active)
                VALUES 
                (:title_line1, :title_accent, :tagline, :button_text, :button_url, :desktop_image, :mobile_image, :sort_order, :is_active)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'title_line1'    => $data['title_line1']    ?? '',
            'title_accent'   => $data['title_accent']   ?? '',
            'tagline'        => $data['tagline']         ?? '',
            'button_text'    => $data['button_text']     ?? 'Shop Now',
            'button_url'     => $data['button_url']      ?? '#bestsellers',
            'desktop_image'  => $data['desktop_image']   ?? '',
            'mobile_image'   => $data['mobile_image']    ?? '',
            'sort_order'     => (int)($data['sort_order'] ?? 0),
            'is_active'      => (int)($data['is_active']  ?? 1),
        ]);
        return (int)$this->db->lastInsertId();
    }

    /**
     * Update an existing slide
     */
    public function update(int $id, array $data): bool {
        $fields = [];
        $params = ['id' => $id];
        $allowed = ['title_line1', 'title_accent', 'tagline', 'button_text', 'button_url',
                    'desktop_image', 'mobile_image', 'sort_order', 'is_active'];

        foreach ($allowed as $key) {
            if (array_key_exists($key, $data)) {
                $fields[] = "`{$key}` = :{$key}";
                $params[$key] = $data[$key];
            }
        }

        if (empty($fields)) return true;

        $sql = "UPDATE hero_slides SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete slide by ID
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM hero_slides WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Reorder slides in bulk
     */
    public function reorder(array $orderedIds): bool {
        try {
            foreach ($orderedIds as $order => $id) {
                $stmt = $this->db->prepare("UPDATE hero_slides SET sort_order = :order WHERE id = :id");
                $stmt->execute(['order' => $order + 1, 'id' => (int)$id]);
            }
            return true;
        } catch (Exception $e) {
            error_log('[HeroSlideRepository] reorder failed: ' . $e->getMessage());
            return false;
        }
    }
}
