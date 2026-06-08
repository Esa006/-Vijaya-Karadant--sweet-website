<?php
/**
 * Sweets Website
 * =============================================================
 * File: PromotionService.php
 * Description: Business logic layer for promotions/hero sections
 * Author: Sweets Website Team
 * Version: 1.0.0
 * =============================================================
 */

require_once ROOT_PATH . '/repositories/PromotionRepository.php';
require_once ROOT_PATH . '/config/Database.php';

class PromotionService {
    private PromotionRepository $repo;

    public function __construct(?PromotionRepository $repo = null) {
        $this->repo = $repo ?? new PromotionRepository(Database::getInstance());
    }

    /**
     * Get a dynamic heroic/promotion section by its ID.
     * Returns fallback data if DB fails.
     *
     * @param string $sectionId
     * @return array
     */
    public function getPromotion(string $sectionId): array {
        try {
            $promo = $this->repo->getPromotionBySectionId($sectionId);
            return !empty($promo) ? $promo : $this->getFallbackPromo($sectionId);
        } catch (Exception $e) {
            error_log('[PromotionService] getPromotion failed: ' . $e->getMessage());
            return $this->getFallbackPromo($sectionId);
        }
    }

    private function getFallbackPromo(string $sectionId): array {
        if ($sectionId === 'curated-combos') {
            return [
                'title' => 'Curated Combos for Every Celebration',
                'description' => 'Thoughtfully crafted selections designed for gifting and festive moments. Discover the perfect harmony of traditional flavors and modern luxury.',
                'image_path' => 'assets/images/homepage/Celebration.png',
                'btn1_text' => 'View Offers', 'btn1_link' => '#',
                'btn2_text' => 'View Catalogue', 'btn2_link' => '#',
                'stat1_val' => '50+', 'stat1_label' => 'Varieties',
                'stat2_val' => 'SINCE 1907', 'stat2_label' => 'Handcrafted with Care',
                'stat3_val' => '4.9/5', 'stat3_label' => 'rating',
            ];
        }
        
        if ($sectionId === 'festival-offers') {
            return [
                'title' => 'Vibrant Festival Offers',
                'subtitle' => 'Celebrate with Sweet Savings',
                'description' => 'Experience the joy of gifting with our exclusive festival discounts. Handcrafted sweets, premium packaging, and timeless traditions delivered to your doorstep.',
                'image_path' => 'assets/images/homepage/FestivalOffer.png',
                'btn_text' => 'Explore Offers',
                'btn_link' => 'category-products.php?slug=gifting',
                'discount_badge' => 'UP TO 30% OFF',
                'timer_end' => date('Y-m-d H:i:s', strtotime('+7 days'))
            ];
        }
        return [];
    }
}
