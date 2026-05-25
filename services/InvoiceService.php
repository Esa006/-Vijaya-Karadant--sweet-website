<?php
/**
 * Sweets Website
 * =============================================================
 * File: InvoiceService.php
 * Description: Orchestrator for Invoice workflows
 * Author: Antigravity - Senior Backend Engineer
 * Version: 1.0.0
 * =============================================================
 */

require_once REPOS_PATH . '/InvoiceRepository.php';
require_once REPOS_PATH . '/OrderRepository.php';

class InvoiceService {
    private InvoiceRepository $invoiceRepo;
    private OrderRepository $orderRepo;

    public function __construct() {
        $this->invoiceRepo = new InvoiceRepository();
        $this->orderRepo   = new OrderRepository();
    }

    /**
     * Get full data for an invoice by Order ID.
     * If invoice doesn't exist, it creates one (auto-generation).
     */
    public function getInvoiceDataByOrder(int $orderId): ?array {
        // 1. Fetch Order
        $order = $this->orderRepo->getById($orderId);
        if (!$order) return null;

        // 2. Fetch/Create Invoice record
        $invoice = $this->invoiceRepo->getByOrderId($orderId);
        if (!$invoice) {
            $invoiceNumber = $this->invoiceRepo->getNextInvoiceNumber();
            $invoiceId = $this->invoiceRepo->create([
                'invoice_number' => $invoiceNumber,
                'order_id'       => $orderId,
                'invoice_date'   => date('Y-m-d'),
                'status'         => 'sent'
            ]);
            $invoice = $this->invoiceRepo->getByOrderId($orderId);
        }

        // 3. Fetch Items
        $items = $this->orderRepo->getItemsByOrderId($orderId);

        // 4. Fetch Company Info
        $company = $this->invoiceRepo->getCompanyInfo();

        return [
            'order'    => $order,
            'invoice'  => $invoice,
            'items'    => $items,
            'company'  => $company
        ];
    }

    /**
     * Helper to calculate totals if not already stored in DB.
     * (Backward compatibility for old orders)
     */
    public function calculateTotals(array &$data): void {
        $subtotal = 0;
        foreach ($data['items'] as $item) {
            $subtotal += (float)$item['price_at_time'] * (int)$item['quantity'];
        }
        
        // If order missing enriched fields, populate them in-memory
        if (empty($data['order']['subtotal']) || (float)$data['order']['subtotal'] == 0) {
            $data['order']['subtotal'] = $subtotal;
            $data['order']['tax_rate'] = 5.00;
            $data['order']['tax_amount'] = $subtotal * 0.05;
            $data['order']['discount_amount'] = 0.00;
            $data['order']['shipping_charges'] = 0.00;
            $data['order']['total_amount'] = $subtotal + ($subtotal * 0.05);
        }
    }
}
