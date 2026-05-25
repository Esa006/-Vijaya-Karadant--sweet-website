<?php
/**
 * Sweets Website
 * =============================================================
 * File: InvoiceRepository.php
 * Description: Data access layer for invoices and business info
 * Author: Antigravity - Senior Backend Engineer
 * Version: 1.0.0
 * =============================================================
 */

require_once 'BaseRepository.php';

class InvoiceRepository extends BaseRepository {

    /**
     * Create a new invoice
     */
    public function create(array $data): int {
        $sql = "INSERT INTO invoices (invoice_number, order_id, invoice_date, status, due_date) 
                VALUES (:invoice_number, :order_id, :invoice_date, :status, :due_date)";
        
        $params = [
            ':invoice_number' => $data['invoice_number'],
            ':order_id'       => $data['order_id'],
            ':invoice_date'   => $data['invoice_date'] ?? date('Y-m-d'),
            ':status'         => $data['status'] ?? 'sent',
            ':due_date'       => $data['due_date'] ?? null
        ];

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int)$this->db->lastInsertId();
    }

    /**
     * Get invoice by Order ID
     */
    public function getByOrderId(int $orderId): ?array {
        $sql = "SELECT * FROM invoices WHERE order_id = :order_id";
        return $this->fetchOne($sql, ['order_id' => $orderId]);
    }

    /**
     * Get invoice by Invoice Number
     */
    public function getByNumber(string $number): ?array {
        $sql = "SELECT * FROM invoices WHERE invoice_number = :number";
        return $this->fetchOne($sql, ['number' => $number]);
    }

    /**
     * Get business company info
     */
    public function getCompanyInfo(): ?array {
        $sql = "SELECT * FROM company_info ORDER BY id DESC LIMIT 1";
        return $this->fetchOne($sql);
    }

    /**
     * Update invoice status
     */
    public function updateStatus(int $id, string $status): bool {
        $sql = "UPDATE invoices SET status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':status' => $status]);
    }

    /**
     * Get the next invoice number (simple sequence)
     */
    public function getNextInvoiceNumber(): string {
        $sql = "SELECT MAX(id) as last_id FROM invoices";
        $row = $this->fetchOne($sql);
        $nextId = ($row['last_id'] ?? 0) + 1;
        return 'INV-' . date('Y') . '-' . str_pad((string)$nextId, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get all orders with invoice info if exists
     */
    public function getAllInvoicesWithOrders(int $limit = 100): array {
        $sql = "SELECT o.id as order_id, o.order_number, o.total_amount, o.status as order_status, 
                       o.created_at, o.payment_method, o.user_id,
                       u.full_name as customer_name, u.email as customer_email,
                       i.invoice_number, i.invoice_date, i.status as invoice_status, i.id as invoice_id
                FROM orders o
                LEFT JOIN users u ON o.user_id = u.id
                LEFT JOIN invoices i ON o.id = i.order_id
                ORDER BY o.created_at DESC
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
