-- Sweets Website - CRM Dummy Data Seed (Expanded Set)
-- Adding even more varied data for deep testing

USE sweets_db;

-- 1. Additional Customers
INSERT IGNORE INTO users (full_name, email, password, role, created_at) VALUES 
('Sunita Gupta', 'sunita.gupta@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'customer', DATE_SUB(NOW(), INTERVAL 365 DAY)),
('Vikram Singh', 'vikram.singh@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'customer', DATE_SUB(NOW(), INTERVAL 120 DAY)),
('Ananya Rao', 'ananya.rao@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'customer', DATE_SUB(NOW(), INTERVAL 60 DAY)),
('Karan Mehra', 'karan.mehra@example.com', '$2y$10$abcdefghijklmnopqrstuv', 'customer', DATE_SUB(NOW(), INTERVAL 15 DAY));

INSERT IGNORE INTO customers (user_id, name, phone, dob, status, created_at) VALUES 
((SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'Sunita Gupta', '+91 93344 55667', '1978-05-20', 'active', DATE_SUB(NOW(), INTERVAL 365 DAY)),
((SELECT id FROM users WHERE email = 'vikram.singh@example.com'), 'Vikram Singh', '+91 95566 77889', '1982-12-15', 'suspended', DATE_SUB(NOW(), INTERVAL 120 DAY)),
((SELECT id FROM users WHERE email = 'ananya.rao@example.com'), 'Ananya Rao', '+91 97788 99001', '1995-01-30', 'active', DATE_SUB(NOW(), INTERVAL 60 DAY)),
((SELECT id FROM users WHERE email = 'karan.mehra@example.com'), 'Karan Mehra', '+91 99900 11223', '1990-08-10', 'active', DATE_SUB(NOW(), INTERVAL 15 DAY));

-- 2. More Addresses
INSERT IGNORE INTO customer_addresses (user_id, type, address_line, city, state, pincode, phone) VALUES 
((SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'billing', 'Plot 45, Civil Lines', 'Delhi', 'Delhi', '110001', '+91 93344 55667'),
((SELECT id FROM users WHERE email = 'ananya.rao@example.com'), 'shipping', 'Apartment 201, Marine Drive', 'Chennai', 'Tamil Nadu', '600001', '+91 97788 99001');

-- 3. More Orders with varied statuses
INSERT IGNORE INTO orders (user_id, order_number, total_amount, status, payment_status, created_at) VALUES 
((SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'ORD-2024-010', 5600.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 45 DAY)),
((SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'ORD-2024-015', 2100.00, 'delivered', 'paid', DATE_SUB(NOW(), INTERVAL 5 DAY)),
((SELECT id FROM users WHERE email = 'ananya.rao@example.com'), 'ORD-2024-020', 450.00, 'cancelled', 'refunded', DATE_SUB(NOW(), INTERVAL 2 DAY)),
((SELECT id FROM users WHERE email = 'karan.mehra@example.com'), 'ORD-2024-025', 1800.00, 'processing', 'paid', DATE_SUB(NOW(), INTERVAL 1 DAY));

-- 4. More Tags
INSERT IGNORE INTO customer_tags (user_id, tag) VALUES 
((SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'High Value'),
((SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'Loyal Customer'),
((SELECT id FROM users WHERE email = 'vikram.singh@example.com'), 'Payment Issues'),
((SELECT id FROM users WHERE email = 'ananya.rao@example.com'), 'Social Media Influencer');

-- 5. More Notes
INSERT IGNORE INTO customer_notes (user_id, note, created_at) VALUES 
((SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'Requested eco-friendly packaging only.', NOW()),
((SELECT id FROM users WHERE email = 'vikram.singh@example.com'), 'Account suspended due to repeated chargeback attempts.', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- 6. More Timeline Data
INSERT IGNORE INTO activity_logs (entity_type, entity_id, action, meta, created_at) VALUES 
('customer', (SELECT id FROM users WHERE email = 'sunita.gupta@example.com'), 'note_added', '{"note": "Packaging preference"}', NOW()),
('customer', (SELECT id FROM users WHERE email = 'vikram.singh@example.com'), 'status_changed', '{"new_status": "suspended"}', DATE_SUB(NOW(), INTERVAL 5 DAY));
