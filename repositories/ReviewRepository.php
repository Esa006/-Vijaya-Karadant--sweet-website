<?php
/**
 * Sweets Website
 * =============================================================
 * File: repositories/ReviewRepository.php
 * Description: Data access layer for product_reviews table
 * =============================================================
 */
declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

class ReviewRepository extends BaseRepository {

    public function __construct() {
        parent::__construct();
    }

    /**
     * Get approved reviews for a product, newest first.
     */
    public function getByProductId(int $productId, int $limit = 20): array {
        try {
            $sql = "SELECT r.*, u.full_name AS reviewer_name
                    FROM product_reviews r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.product_id = :pid AND r.status = 'approved'
                    ORDER BY r.created_at DESC
                    LIMIT :lim";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':pid', $productId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit,     PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('[ReviewRepository] getByProductId: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get approved reviews for a combo.
     */
    public function getByComboId(int $comboId, int $limit = 20): array {
        try {
            $sql = "SELECT r.*, u.full_name AS reviewer_name
                    FROM product_reviews r
                    LEFT JOIN users u ON r.user_id = u.id
                    WHERE r.combo_id = :cid AND r.status = 'approved'
                    ORDER BY r.created_at DESC
                    LIMIT :lim";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':cid', $comboId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit,   PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log('[ReviewRepository] getByComboId: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Aggregate rating stats for a product.
     * Returns ['avg' => float, 'count' => int, 'breakdown' => [5=>int, ...]]
     */
    public function getStatsByProductId(int $productId): array {
        return $this->buildStats(
            "SELECT rating, COUNT(*) AS cnt
             FROM product_reviews
             WHERE product_id = :id AND status = 'approved'
             GROUP BY rating",
            [':id' => $productId]
        );
    }

    /**
     * Aggregate rating stats for a combo.
     */
    public function getStatsByComboId(int $comboId): array {
        return $this->buildStats(
            "SELECT rating, COUNT(*) AS cnt
             FROM product_reviews
             WHERE combo_id = :id AND status = 'approved'
             GROUP BY rating",
            [':id' => $comboId]
        );
    }

    private function buildStats(string $sql, array $params): array {
        try {
            // Explicitly request ASSOC so 'rating'/'cnt' keys are always present
            $stmt = $this->db->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v, PDO::PARAM_INT);
            }
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $breakdown = [5=>0, 4=>0, 3=>0, 2=>0, 1=>0];
            $total = 0; $weightedSum = 0;
            foreach ($rows as $row) {
                $r = (int)($row['rating'] ?? 0);
                $c = (int)($row['cnt']    ?? 0);
                if ($r >= 1 && $r <= 5) { $breakdown[$r] = $c; }
                $total       += $c;
                $weightedSum += $r * $c;
            }
            return [
                'avg'       => $total > 0 ? round($weightedSum / $total, 1) : 0.0,
                'count'     => $total,
                'breakdown' => $breakdown,
            ];
        } catch (\PDOException $e) {
            error_log('[ReviewRepository] buildStats: ' . $e->getMessage());
            return ['avg' => 0.0, 'count' => 0, 'breakdown' => [5=>0,4=>0,3=>0,2=>0,1=>0]];
        }
    }

    /**
     * Check if a user has already reviewed a product.
     */
    public function userHasReviewed(int $userId, ?int $productId, ?int $comboId): bool {
        try {
            if ($productId) {
                $sql = "SELECT 1 FROM product_reviews WHERE user_id = :uid AND product_id = :pid LIMIT 1";
                return (bool)$this->fetchOne($sql, [':uid' => $userId, ':pid' => $productId]);
            }
            if ($comboId) {
                $sql = "SELECT 1 FROM product_reviews WHERE user_id = :uid AND combo_id = :cid LIMIT 1";
                return (bool)$this->fetchOne($sql, [':uid' => $userId, ':cid' => $comboId]);
            }
            return false;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Check if a user has a delivered order containing a product.
     * Returns the order_id, or 0 if none found.
     */
    public function getDeliveredOrderIdForProduct(int $userId, ?int $productId, ?int $comboId): int {
        try {
            // Accept both 'delivered' and 'paid' statuses — some shops mark
            // fulfilled orders as 'paid' without advancing to 'delivered'.
            if ($productId) {
                $sql = "SELECT o.id
                        FROM orders o
                        JOIN order_items oi ON oi.order_id = o.id
                        WHERE o.user_id = :uid
                          AND oi.product_id = :pid
                          AND LOWER(o.status) IN ('delivered', 'paid')
                        LIMIT 1";
                $row = $this->fetchOne($sql, [':uid' => $userId, ':pid' => $productId]);
            } elseif ($comboId) {
                $sql = "SELECT o.id
                        FROM orders o
                        JOIN order_items oi ON oi.order_id = o.id
                        WHERE o.user_id = :uid
                          AND oi.combo_id = :cid
                          AND LOWER(o.status) IN ('delivered', 'paid')
                        LIMIT 1";
                $row = $this->fetchOne($sql, [':uid' => $userId, ':cid' => $comboId]);
            } else {
                return 0;
            }
            return (int)($row['id'] ?? 0);
        } catch (\PDOException $e) {
            error_log('[ReviewRepository] getDeliveredOrderIdForProduct: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Insert a new review.
     */
    public function create(array $data): int {
        $sql = "INSERT INTO product_reviews
                    (product_id, combo_id, user_id, order_id, rating, title, body, status)
                VALUES
                    (:pid, :cid, :uid, :oid, :rating, :title, :body, 'approved')";
        try {
            return $this->executeInsert($sql, [
                ':pid'    => $data['product_id'] ?? null,
                ':cid'    => $data['combo_id']   ?? null,
                ':uid'    => $data['user_id'],
                ':oid'    => $data['order_id'],
                ':rating' => $data['rating'],
                ':title'  => $data['title'],
                ':body'   => $data['body'],
            ]);
        } catch (\PDOException $e) {
            error_log('[ReviewRepository] create: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Increment helpful count atomically.
     */
    public function incrementHelpful(int $reviewId): bool {
        try {
            return $this->execute(
                "UPDATE product_reviews SET helpful_count = helpful_count + 1 WHERE id = :id",
                [':id' => $reviewId]
            );
        } catch (\PDOException $e) {
            return false;
        }
    }
}
