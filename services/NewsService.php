<?php
/**
 * Sweets Website
 * =============================================================
 * File: NewsService.php
 * Description: Business logic layer for Latest News
 * =============================================================
 */

require_once ROOT_PATH . '/repositories/NewsRepository.php';
require_once ROOT_PATH . '/services/FileService.php';

class NewsService {

    private NewsRepository $repo;

    public function __construct() {
        $this->repo = new NewsRepository();
    }

    /**
     * Get all active news for the frontend.
     */
    public function getActiveNews(): array {
        try {
            $news = $this->repo->getAllActive();
            return !empty($news) ? $news : $this->getFallbackNews();
        } catch (Exception $e) {
            error_log('[NewsService] getActiveNews failed: ' . $e->getMessage());
            return $this->getFallbackNews();
        }
    }

    /**
     * Get all news (active and inactive) for admin panel.
     */
    public function getAllNews(): array {
        try {
            $news = $this->repo->getAll();
            return !empty($news) ? $news : $this->getFallbackNews(true);
        } catch (Exception $e) {
            error_log('[NewsService] getAllNews failed: ' . $e->getMessage());
            return $this->getFallbackNews(true);
        }
    }

    /**
     * Get a specific news item by ID.
     */
    public function getNewsById(int $id): ?array {
        try {
            return $this->repo->getById($id);
        } catch (Exception $e) {
            error_log('[NewsService] getNewsById failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create news post from admin panel
     */
    public function createNews(array $data, ?array $imageFile = null): bool {
        try {
            if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
                // Ensure uploaded to correct directory
                $fileService = new FileService('news');
                $uploadedPath = $fileService->upload($imageFile);
                if ($uploadedPath) {
                    $data['image_path'] = $uploadedPath;
                }
            }
            
            // Set fallback only if no image_path exists at all
            if (empty($data['image_path'])) {
                $data['image_path'] = 'assets/images/placeholders/product-placeholder.png';
            }

            return $this->repo->create($data) > 0;
        } catch (Exception $e) {
            error_log('[NewsService] createNews failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update an existing news post
     */
    public function updateNews(int $id, array $data, ?array $imageFile = null): bool {
        try {
            $existing = $this->repo->getById($id);
            if (!$existing) return false;

            if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
                $fileService = new FileService('news');
                $uploadedPath = $fileService->upload($imageFile);
                if ($uploadedPath) {
                    // Optional: Delete old image if needed
                    $data['image_path'] = $uploadedPath;
                }
            } else {
                $data['image_path'] = $existing['image_path'];
            }

            return $this->repo->update($id, $data);
        } catch (Exception $e) {
            error_log('[NewsService] updateNews failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete news post
     */
    public function deleteNews(int $id): bool {
        try {
            return $this->repo->delete($id);
        } catch (Exception $e) {
            error_log('[NewsService] deleteNews failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Hardcoded fallback if DB is empty
     */
    private function getFallbackNews(bool $withIds = false): array {
        $fallbacks = [
            [
                'title' => 'Honoring Excellence in Traditional Sweets',
                'publish_date' => '2025-03-15',
                'description' => 'Vijaya Karadant recognized for preserving authentic taste and cultural heritage.',
                'image_path' => 'assets/images/homepage/news/news-visit-1.jpeg',
                'status' => 'active'
            ],
            [
                'title' => 'Meeting with Industry Leaders',
                'publish_date' => '2025-03-15',
                'description' => 'Team Vijaya Karadant collaborates with key leaders to expand growth and innovation.',
                'image_path' => 'assets/images/homepage/news/news-visit-2.jpeg',
                'status' => 'active'
            ],
            [
                'title' => 'Behind the Scenes at Our Production Unit',
                'publish_date' => '2025-03-15',
                'description' => 'A glimpse into our quality process, team efforts, and traditional preparation methods.',
                'image_path' => 'assets/images/homepage/news/news-visit-3.jpeg',
                'status' => 'active'
            ],
            [
                'title' => 'Expanding the Legacy of Vijaya Karadant',
                'publish_date' => '2025-03-15',
                'description' => 'Reaching new horizons with our authentic sweets and commitment to quality.',
                'image_path' => 'assets/images/homepage/news/news-visit-1.jpeg',
                'status' => 'active'
            ]
        ];

        if ($withIds) {
            foreach ($fallbacks as $index => &$item) {
                $item['id'] = -($index + 1); // Negative IDs for fallbacks
            }
        }

        return $fallbacks;
    }
}
