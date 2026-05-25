<?php
/**
 * Sweets Website
 * =============================================================
 * File: PromotionRepository.php
 * Description: Data access layer for promotions/hero sections
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

class PromotionRepository {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get a promotion by its section ID.
     *
     * @param string $sectionId
     * @return array|null
     */
    public function getPromotionBySectionId(string $sectionId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT * FROM promotions 
            WHERE section_id = :section_id AND is_active = 1
            LIMIT 1
        ");
        $stmt->execute([':section_id' => $sectionId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    /**
     * Update timer_end for a promotion section.
     *
     * @param string $sectionId
     * @param string|null $timerEnd  ISO datetime or null to clear
     * @return bool
     */
    public function updateTimerEnd(string $sectionId, ?string $timerEnd): bool {
        $stmt = $this->pdo->prepare("
            UPDATE promotions
            SET timer_end = :timer_end
            WHERE section_id = :section_id
        ");
        return $stmt->execute([':timer_end' => $timerEnd, ':section_id' => $sectionId]);
    }
}
