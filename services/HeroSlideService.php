<?php
/**
 * Sweets Website
 * =============================================================
 * File: services/HeroSlideService.php
 * Description: Business logic for hero slider management
 * =============================================================
 */

declare(strict_types=1);

require_once REPOS_PATH . '/HeroSlideRepository.php';

class HeroSlideService {

    private HeroSlideRepository $repo;

    public function __construct() {
        $this->repo = new HeroSlideRepository();
    }

    /* ── FALLBACK DATA ─────────────────────────────────────── */

    private function getFallbackSlides(): array {
        return [
            [
                'id'             => 0,
                'title_line1'    => 'Celebrate Every Occasion',
                'title_accent'   => 'Timeless Sweetness',
                'tagline'        => 'ARTISANAL & TRADITIONAL',
                'button_text'    => 'Explore Karadant',
                'button_url'     => '#bestsellers',
                'desktop_image'  => 'assets/images/banners/home-banner  (5).png',
                'mobile_image'   => 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228075 (2).png',
                'sort_order'     => 1,
                'is_active'      => 1,
            ],
            [
                'id'             => 0,
                'title_line1'    => 'Experience the Taste of',
                'title_accent'   => 'Traditional Laddus',
                'tagline'        => 'ARTISANAL & TRADITIONAL',
                'button_text'    => 'Explore Laddu',
                'button_url'     => '#bestsellers',
                'desktop_image'  => 'assets/images/banners/home-banner  (2).png',
                'mobile_image'   => 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228127 (1).png',
                'sort_order'     => 2,
                'is_active'      => 1,
            ],
            [
                'id'             => 0,
                'title_line1'    => 'Made with Pure Ghee',
                'title_accent'   => 'Handcrafted with Love',
                'tagline'        => '100% NATURAL & PURE',
                'button_text'    => 'Shop Now',
                'button_url'     => '#bestsellers',
                'desktop_image'  => 'assets/images/banners/home-banner  (3).png',
                'mobile_image'   => 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228128.png',
                'sort_order'     => 3,
                'is_active'      => 1,
            ],
            [
                'id'             => 0,
                'title_line1'    => 'Savor the Crunch of',
                'title_accent'   => 'Delicious Namkeens',
                'tagline'        => 'ARTISANAL & TRADITIONAL',
                'button_text'    => 'Explore Namkeen',
                'button_url'     => '#bestsellers',
                'desktop_image'  => 'assets/images/banners/home-banner  (4).png',
                'mobile_image'   => 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228075 (2).png',
                'sort_order'     => 4,
                'is_active'      => 1,
            ],
            [
                'id'             => 0,
                'title_line1'    => 'Pure and Natural',
                'title_accent'   => 'Traditional Karadant',
                'tagline'        => '100% PURE & NATURAL',
                'button_text'    => 'Browse Collection',
                'button_url'     => '#bestsellers',
                'desktop_image'  => 'assets/images/banners/home-banner  (1).png',
                'mobile_image'   => 'assets/images/banners/demo-phone-screen-img/Property 1=Frame 2147228127 (1).png',
                'sort_order'     => 5,
                'is_active'      => 1,
            ],
        ];
    }

    /* ── PUBLIC API ────────────────────────────────────────── */

    /**
     * Get active slides for frontend display; falls back to static if DB fails.
     */
    public function getActiveSlides(): array {
        try {
            $slides = $this->repo->getActiveSlides();
            return !empty($slides) ? $slides : $this->getFallbackSlides();
        } catch (Exception $e) {
            error_log('[HeroSlideService] getActiveSlides failed: ' . $e->getMessage());
            return $this->getFallbackSlides();
        }
    }

    /**
     * Get all slides for admin management.
     */
    public function getAllSlides(): array {
        try {
            $slides = $this->repo->getAllSlides();
            return !empty($slides) ? $slides : $this->getFallbackSlides();
        } catch (Exception $e) {
            error_log('[HeroSlideService] getAllSlides failed: ' . $e->getMessage());
            return $this->getFallbackSlides();
        }
    }

    /**
     * Get one slide by ID.
     */
    public function getById(int $id): ?array {
        try {
            return $this->repo->getById($id);
        } catch (Exception $e) {
            error_log('[HeroSlideService] getById failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new slide. Returns new ID or 0 on failure.
     */
    public function create(array $data): int {
        try {
            return $this->repo->create($data);
        } catch (Exception $e) {
            error_log('[HeroSlideService] create failed: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update an existing slide.
     */
    public function update(int $id, array $data): bool {
        try {
            return $this->repo->update($id, $data);
        } catch (Exception $e) {
            error_log('[HeroSlideService] update failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a slide. Also removes image file if it's a local upload.
     */
    public function delete(int $id): bool {
        try {
            $slide = $this->repo->getById($id);
            if ($slide) {
                foreach (['desktop_image', 'mobile_image'] as $field) {
                    $path = $slide[$field] ?? '';
                    if ($path && str_contains($path, 'assets/images/hero-slides/') && file_exists(ROOT_PATH . '/' . $path)) {
                        @unlink(ROOT_PATH . '/' . $path);
                    }
                }
            }
            return $this->repo->delete($id);
        } catch (Exception $e) {
            error_log('[HeroSlideService] delete failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reorder slides.
     */
    public function reorder(array $orderedIds): bool {
        try {
            return $this->repo->reorder($orderedIds);
        } catch (Exception $e) {
            error_log('[HeroSlideService] reorder failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Handle image upload for hero slides.
     * Returns relative path or empty string on failure.
     */
    public function uploadImage(array $file, string $suffix = ''): string {
        $uploadDir = ROOT_PATH . '/assets/images/hero-slides/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        if (!in_array($file['type'], $allowed, true)) {
            return '';
        }

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'hero_' . $suffix . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dest     = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return '';
        }

        return 'assets/images/hero-slides/' . $fileName;
    }
}
