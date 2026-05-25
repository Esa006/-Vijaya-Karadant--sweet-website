<?php
/**
 * Sweets Website
 * =============================================================
 * File: services/ReviewService.php
 * Description: Business logic for product reviews
 *              – purchase verification, CRUD, stats
 * =============================================================
 */
declare(strict_types=1);

require_once ROOT_PATH . '/repositories/ReviewRepository.php';

class ReviewService {

    private ReviewRepository $repo;

    public function __construct() {
        $this->repo = new ReviewRepository();
    }

    /**
     * Get all approved reviews + live rating stats for a product/combo.
     * Falls back to an empty array if table doesn't exist yet.
     *
     * @param int|null  $productId  Regular product ID (null for combos)
     * @param int|null  $comboId    Combo ID (null for regular products)
     * @return array ['reviews' => [...], 'stats' => ['avg'=>float, 'count'=>int, 'breakdown'=>[]]]
     */
    public function getReviewsWithStats(?int $productId, ?int $comboId): array {
        try {
            if ($productId && $productId > 0) {
                $reviews = $this->repo->getByProductId($productId);
                $stats   = $this->repo->getStatsByProductId($productId);
            } elseif ($comboId && $comboId > 0) {
                $reviews = $this->repo->getByComboId($comboId);
                $stats   = $this->repo->getStatsByComboId($comboId);
            } else {
                return $this->emptyResult();
            }

            // Normalize each review for the view
            foreach ($reviews as &$rev) {
                $rev['reviewer_name'] = $this->maskName((string)($rev['reviewer_name'] ?? 'Anonymous'));
                $rev['date_label']    = date('d M Y', strtotime((string)($rev['created_at'] ?? 'now')));
                $rev['verified']      = true; // always true — only delivered buyers can review
                $rev['helpful']       = (int)($rev['helpful_count'] ?? 0);
            }
            unset($rev);

            return ['reviews' => $reviews, 'stats' => $stats];
        } catch (\Throwable $e) {
            error_log('[ReviewService] getReviewsWithStats: ' . $e->getMessage());
            return $this->emptyResult();
        }
    }

    /**
     * Check if the logged-in user can review this product:
     *  – Must be logged in
     *  – Must have a delivered order containing the product
     *  – Must not have already reviewed it
     *
     * Returns ['can_review' => bool, 'order_id' => int, 'reason' => string]
     */
    public function canUserReview(int $userId, ?int $productId, ?int $comboId): array {
        if ($userId <= 0) {
            return ['can_review' => false, 'order_id' => 0, 'reason' => 'not_logged_in'];
        }
        try {
            if ($this->repo->userHasReviewed($userId, $productId, $comboId)) {
                return ['can_review' => false, 'order_id' => 0, 'reason' => 'already_reviewed'];
            }
            $orderId = $this->repo->getDeliveredOrderIdForProduct($userId, $productId, $comboId);
            if (!$orderId) {
                return ['can_review' => false, 'order_id' => 0, 'reason' => 'not_purchased'];
            }
            return ['can_review' => true, 'order_id' => $orderId, 'reason' => ''];
        } catch (\Throwable $e) {
            error_log('[ReviewService] canUserReview: ' . $e->getMessage());
            return ['can_review' => false, 'order_id' => 0, 'reason' => 'error'];
        }
    }

    /**
     * Submit a review after server-side validation.
     *
     * @return array ['success' => bool, 'message' => string, 'review_id' => int]
     */
    public function submitReview(
        int    $userId,
        ?int   $productId,
        ?int   $comboId,
        int    $orderId,
        int    $rating,
        string $title,
        string $body
    ): array {
        // Validate
        if ($rating < 1 || $rating > 5) {
            return ['success' => false, 'message' => 'Rating must be between 1 and 5.', 'review_id' => 0];
        }
        $title = trim(strip_tags($title));
        $body  = trim(strip_tags($body));
        if (mb_strlen($title) < 3) {
            return ['success' => false, 'message' => 'Please add a short review title (min 3 chars).', 'review_id' => 0];
        }
        if (mb_strlen($body) < 10) {
            return ['success' => false, 'message' => 'Review body must be at least 10 characters.', 'review_id' => 0];
        }
        $title = mb_substr($title, 0, 120);
        $body  = mb_substr($body,  0, 2000);

        // Re-verify eligibility
        $check = $this->canUserReview($userId, $productId, $comboId);
        if (!$check['can_review']) {
            $msgs = [
                'not_logged_in'    => 'You must be logged in to submit a review.',
                'already_reviewed' => 'You have already submitted a review for this product.',
                'not_purchased'    => 'Only customers who purchased and received this product can review it.',
            ];
            return ['success' => false, 'message' => $msgs[$check['reason']] ?? 'Unable to submit review.', 'review_id' => 0];
        }

        $reviewId = $this->repo->create([
            'product_id' => $productId,
            'combo_id'   => $comboId,
            'user_id'    => $userId,
            'order_id'   => $orderId,
            'rating'     => $rating,
            'title'      => $title,
            'body'       => $body,
        ]);

        if ($reviewId > 0) {
            return ['success' => true, 'message' => 'Thank you! Your review has been published.', 'review_id' => $reviewId];
        }
        return ['success' => false, 'message' => 'Could not save your review. Please try again.', 'review_id' => 0];
    }

    /**
     * Increment helpful count for a review.
     */
    public function markHelpful(int $reviewId): bool {
        return $this->repo->incrementHelpful($reviewId);
    }

    // ── Helpers ──────────────────────────────────────────────

    /** Mask "Ravi Kumar" → "Ravi K." for privacy */
    private function maskName(string $fullName): string {
        $parts = preg_split('/\s+/', trim($fullName));
        if (count($parts) >= 2) {
            return $parts[0] . ' ' . mb_strtoupper(mb_substr($parts[1], 0, 1)) . '.';
        }
        return $fullName;
    }

    private function emptyResult(): array {
        return [
            'reviews' => [],
            'stats'   => ['avg' => 0.0, 'count' => 0, 'breakdown' => [5=>0,4=>0,3=>0,2=>0,1=>0]],
        ];
    }
}
