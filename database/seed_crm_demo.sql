-- ==========================================================
-- CRM DEMO DATA (For Client Presentation)
-- ==========================================================
-- Run this file in your MySQL / phpMyAdmin to insert dummy 
-- data. This allows your REAL PHP queries to show beautiful 
-- metrics (LTV, AOV, Returning Users) on the dashboard without 
-- hardcoding any numbers in the code.

-- 1. Insert 5 Dummy Customers
INSERT INTO users (id, full_name, email, phone, password, role, status, created_at) VALUES 
(9001, 'Rajiv Sharma', 'rajiv.demo@example.com', '9876543210', '$2y$10$dummy', 'customer', 'active', DATE_SUB(NOW(), INTERVAL 120 DAY)),
(9002, 'Priya Patel', 'priya.demo@example.com', '9876543211', '$2y$10$dummy', 'customer', 'active', DATE_SUB(NOW(), INTERVAL 45 DAY)),
(9003, 'Amit Kumar', 'amit.demo@example.com', '9876543212', '$2y$10$dummy', 'customer', 'active', DATE_SUB(NOW(), INTERVAL 10 DAY)),
(9004, 'Sneha Reddy', 'sneha.demo@example.com', '9876543213', '$2y$10$dummy', 'customer', 'active', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(9005, 'Vikram Singh', 'vikram.demo@example.com', '9876543214', '$2y$10$dummy', 'customer', 'inactive', DATE_SUB(NOW(), INTERVAL 200 DAY))
ON DUPLICATE KEY UPDATE full_name=VALUES(full_name), status=VALUES(status);

-- 2. Insert Profiles
INSERT IGNORE INTO customer_profiles (customer_id, full_name, gender, dob) VALUES 
(9001, 'Rajiv Sharma', 'Male', '1985-06-15'),
(9002, 'Priya Patel', 'Female', '1990-08-22'),
(9003, 'Amit Kumar', 'Male', '1992-11-05'),
(9004, 'Sneha Reddy', 'Female', '1995-02-14'),
(9005, 'Vikram Singh', 'Male', '1988-09-30');

-- 3. Insert Addresses
INSERT IGNORE INTO addresses (user_id, address_line1, city, state) VALUES
(9001, 'Flat 402, Sunshine Apts', 'Bengaluru', 'Karnataka'),
(9002, 'Villa 15, Green Meadows', 'Mumbai', 'Maharashtra'),
(9003, 'Plot 45, Tech Park', 'Hyderabad', 'Telangana'),
(9004, 'Door 12, Main Road', 'Chennai', 'Tamil Nadu'),
(9005, 'Sector 4, Dwarka', 'New Delhi', 'Delhi');

-- 4. Insert Dummy Orders (To generate Revenue, AOV, and Returning Users)
INSERT IGNORE INTO orders (id, user_id, total_amount, status, created_at) VALUES
(90001, 9001, 1500.00, 'COMPLETED', DATE_SUB(NOW(), INTERVAL 100 DAY)),
(90002, 9001, 2500.50, 'COMPLETED', DATE_SUB(NOW(), INTERVAL 15 DAY)), -- Rajiv is a returning user
(90003, 9002, 4200.00, 'COMPLETED', DATE_SUB(NOW(), INTERVAL 40 DAY)),
(90004, 9002, 1100.00, 'COMPLETED', DATE_SUB(NOW(), INTERVAL 5 DAY)),  -- Priya is a returning user
(90005, 9003, 850.00, 'COMPLETED', DATE_SUB(NOW(), INTERVAL 8 DAY)),
(90006, 9004, 5400.00, 'PENDING', DATE_SUB(NOW(), INTERVAL 1 DAY)),     -- Sneha is a high spender
(90007, 9005, 300.00, 'CANCELLED', DATE_SUB(NOW(), INTERVAL 190 DAY));  -- Vikram is inactive/cancelled

-- 5. Insert Customer Activity Timeline
INSERT IGNORE INTO customer_activity (user_id, action_type, description, created_at) VALUES
(9001, 'login', 'Logged into the system via web', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(9002, 'purchase', 'Placed Order #90004', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(9003, 'support', 'Contacted support for delivery tracking', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(9005, 'account_locked', 'Account marked inactive due to prolonged absence', DATE_SUB(NOW(), INTERVAL 10 DAY));
