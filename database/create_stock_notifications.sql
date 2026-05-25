CREATE TABLE IF NOT EXISTS `stock_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_email` varchar(255) NOT NULL,
  `product_id` varchar(255) NOT NULL,
  `product_type` enum('product','combo') NOT NULL DEFAULT 'product',
  `status` enum('pending','notified') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
