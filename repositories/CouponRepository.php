<?php
/**
 * Sweets Website
 * =============================================================
 * File: CouponRepository.php
 * Description: Data access layer for Coupons and Promotional tools
 * Author: Antigravity - Senior Backend Engineer
 * Version: 3.0.0
 * =============================================================
 */

require_once 'BaseRepository.php';

class CouponRepository extends BaseRepository {

    /**
     * Get coupon by code (Secure Lookup)
     */
    public function getByCode(string $code): ?array {
        return $this->fetchOne(
            "SELECT * FROM coupons WHERE code = :code AND is_active = 1", 
            ['code' => $code]
        );
    }

    /**
     * Get coupon by ID with Creator details
     */
    public function getById(int $id): ?array {
        $sql = "SELECT c.*, u.full_name as creator_name 
                FROM coupons c 
                LEFT JOIN users u ON c.created_by = u.id 
                WHERE c.id = :id";
        return $this->fetchOne($sql, ['id' => $id]);
    }

    /**
     * Get Comprehensive Offer Analytics (Performance Insights)
     */
    public function getOfferMetrics(int $couponId): array {
        $sql = "SELECT 
                    COUNT(id) as total_orders,
                    SUM(discount_amount) as total_discount,
                    (SELECT SUM(total_amount) FROM orders WHERE id IN (SELECT order_id FROM coupon_usages WHERE coupon_id = :id1)) as total_revenue
                FROM coupon_usages 
                WHERE coupon_id = :id2";
        
        $metrics = $this->fetchOne($sql, [':id1' => $couponId, ':id2' => $couponId]) ?: [
            'total_orders' => 0,
            'total_discount' => 0,
            'total_revenue' => 0
        ];
        
        // Calculate AOV
        $metrics['aov'] = $metrics['total_orders'] > 0 ? $metrics['total_revenue'] / $metrics['total_orders'] : 0;
        
        return $metrics;
    }

    /**
     * Track coupon usage count for a user
     */
    public function getUserUsageCount(int $couponId, int $userId): int {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM coupon_usages WHERE coupon_id = :cid AND user_id = :uid");
        $stmt->execute(['cid' => $couponId, 'uid' => $userId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Add usage entry (Should be in same transaction as Order placement)
     */
    public function addUsage(int $couponId, int $userId, int $orderId, float $discount): bool {
        $stmt = $this->db->prepare("INSERT INTO coupon_usages (coupon_id, user_id, order_id, discount_amount) VALUES (:cid, :uid, :oid, :disc)");
        return $stmt->execute(['cid' => $couponId, 'uid' => $userId, 'oid' => $orderId, 'disc' => $discount]);
    }

    /**
     * Get all active coupons for admin listing
     */
    public function getAllCoupons(): array {
        $sql = "SELECT c.*, 
                (SELECT COUNT(*) FROM coupon_usages WHERE coupon_id = c.id) as usage_used
                FROM coupons c 
                ORDER BY c.created_at DESC";
        return $this->fetchAll($sql);
    }

    /**
     * Update existing coupon
     */
    public function update(int $id, array $data): bool {
        $sql = "UPDATE coupons SET 
                description = :desc,
                type = :type,
                value = :value,
                min_cart_total = :min_total,
                usage_limit = :limit,
                limit_per_user = :u_limit,
                expires_at = :expires,
                is_active = :active
                WHERE id = :id";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'id'        => $id,
            'desc'      => $data['description'] ?? null,
            'type'      => $data['type'],
            'value'     => $data['value'],
            'min_total' => $data['min_cart_total'] ?? 0,
            'limit'     => $data['usage_limit'] ?? 1,
            'u_limit'   => $data['limit_per_user'] ?? 1,
            'expires'   => $data['expires_at'] ?? null,
            'active'    => $data['is_active'] ?? 1
        ]);
    }

    /**
     * Delete coupon
     */
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM coupons WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Create new coupon (Updated for v3.0 schema)
     */
    public function create(array $data): int {
        $sql = "INSERT INTO coupons (code, description, type, value, min_cart_total, usage_limit, limit_per_user, applicable_categories, is_active, created_by) 
                VALUES (:code, :desc, :type, :value, :min_total, :limit, :u_limit, :cats, :active, :creator)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'code'      => $data['code'],
            'desc'      => $data['description'] ?? null,
            'type'      => $data['type'],
            'value'     => $data['value'],
            'min_total' => $data['min_cart_total'] ?? 0,
            'limit'     => $data['usage_limit'] ?? 1,
            'u_limit'   => $data['limit_per_user'] ?? 1,
            'cats'      => $data['applicable_categories'] ?? null,
            'active'    => $data['is_active'] ?? 1,
            'creator'   => $data['created_by'] ?? null
        ]);
        
        return (int)$this->db->lastInsertId();
    }
}
