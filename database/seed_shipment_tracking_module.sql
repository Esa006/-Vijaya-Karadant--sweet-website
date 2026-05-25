-- Shipment Tracking Module Demo Seed
-- Run this after importing database/shipment_tracking_module.sql

INSERT INTO orders (order_reference, customer_name, total_amount, created_at)
VALUES
    ('ORD-2026-1001', 'Aarav Mehta', 1249.00, '2026-04-20 10:15:00'),
    ('ORD-2026-1002', 'Isha Sharma', 890.50, '2026-04-20 11:42:00'),
    ('ORD-2026-1003', 'Rohan Verma', 2450.00, '2026-04-21 09:08:00'),
    ('ORD-2026-1004', 'Neha Kapoor', 599.00, '2026-04-21 14:30:00'),
    ('ORD-2026-1005', 'Kabir Singh', 1799.75, '2026-04-22 16:55:00'),
    ('ORD-2026-1006', 'Ananya Rao', 320.00, '2026-04-23 08:25:00'),
    ('ORD-2026-1007', 'Vivaan Nair', 4100.20, '2026-04-23 18:05:00'),
    ('ORD-2026-1008', 'Diya Joshi', 760.00, '2026-04-24 12:11:00'),
    ('ORD-2026-1009', 'Aditya Kulkarni', 1399.99, '2026-04-24 19:42:00'),
    ('ORD-2026-1010', 'Mira Chawla', 520.00, '2026-04-25 07:50:00')
ON DUPLICATE KEY UPDATE
    customer_name = VALUES(customer_name),
    total_amount = VALUES(total_amount);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'Mumbai, Maharashtra', 'pending', '2026-04-25 09:00:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1001'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'Ahmedabad, Gujarat', 'in_transit', '2026-04-25 10:15:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1002'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'Bengaluru, Karnataka', 'delivered', '2026-04-25 11:05:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1003'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, '', 'pending', '2026-04-25 11:30:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1004'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'Jaipur, Rajasthan', 'in_transit', '2026-04-25 12:20:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1005'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'Pune, Maharashtra', 'delivered', '2026-04-25 13:45:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1006'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'New Delhi, Delhi', 'in_transit', '2026-04-25 14:40:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1007'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, NULL, 'pending', '2026-04-25 15:10:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1008'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'Lucknow, Uttar Pradesh', 'delivered', '2026-04-25 16:05:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1009'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);

INSERT INTO shipments (order_id, destination, status, updated_at)
SELECT o.id, 'Kochi, Kerala', 'pending', '2026-04-25 17:35:00'
FROM orders o
WHERE o.order_reference = 'ORD-2026-1010'
ON DUPLICATE KEY UPDATE
    destination = VALUES(destination),
    status = VALUES(status),
    updated_at = VALUES(updated_at);
