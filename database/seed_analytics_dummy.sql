/**
 * Sweets Website - Analytics Dummy Data
 * =============================================================
 * File: database/seed_analytics_dummy.sql
 * Description: Populates the DB with 30 days of high-fidelity sales data
 * =============================================================
 */

USE sweets_db;

-- 1. Ensure we have at least one user
INSERT IGNORE INTO users (id, full_name, email, password, role, status)
VALUES (99, 'Test Customer', 'customer@example.com', 'password', 'customer', 'Active');

-- 2. Clear old test orders to avoid confusion (Optional - comment out if not needed)
-- DELETE FROM order_items WHERE order_id IN (SELECT id FROM orders WHERE order_number LIKE 'DUMMY-%');
-- DELETE FROM orders WHERE order_number LIKE 'DUMMY-%';

-- 3. Insert Dummy Orders for the last 30 days
-- Using a loop-like structure for variety
INSERT INTO orders (user_id, order_number, total_amount, status, payment_status, created_at) VALUES
(99, 'DUMMY-001', 1250.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(99, 'DUMMY-002', 2400.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(99, 'DUMMY-003', 850.00,  'shipped',   'paid', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(99, 'DUMMY-004', 3200.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(99, 'DUMMY-005', 1500.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(99, 'DUMMY-006', 4200.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(99, 'DUMMY-007', 950.00,  'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 6 DAY)),
(99, 'DUMMY-008', 2100.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 7 DAY)),
(99, 'DUMMY-009', 1800.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(99, 'DUMMY-010', 3600.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(99, 'DUMMY-011', 1200.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 12 DAY)),
(99, 'DUMMY-012', 2800.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 15 DAY)),
(99, 'DUMMY-013', 5500.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 20 DAY)),
(99, 'DUMMY-014', 1400.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 25 DAY)),
(99, 'DUMMY-015', 3100.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 30 DAY));

-- 4. Insert Order Items for these orders
-- Linking to existing products (assuming IDs 1001, 1002, 1003, 1004 exist from fix_categories_and_products.sql)
INSERT INTO order_items (order_id, product_id, quantity, price_at_time)
SELECT id, 1001, 2, 600.00 FROM orders WHERE order_number LIKE 'DUMMY-%';

INSERT INTO order_items (order_id, product_id, quantity, price_at_time)
SELECT id, 1009, 1, 450.00 FROM orders WHERE order_number LIKE 'DUMMY-%' AND id % 2 = 0;

INSERT INTO order_items (order_id, product_id, quantity, price_at_time)
SELECT id, 1018, 3, 300.00 FROM orders WHERE order_number LIKE 'DUMMY-%' AND id % 3 = 0;
