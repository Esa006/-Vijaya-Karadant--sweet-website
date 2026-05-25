-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 16, 2026 at 06:45 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sweets_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `entity_type` enum('customer','order','account') NOT NULL,
  `entity_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `entity_type`, `entity_id`, `action`, `meta`, `created_at`) VALUES
(1, 'customer', 116, 'profile_updated', '{\"field\": \"phone\"}', '2026-04-15 11:21:57'),
(2, 'customer', 116, 'note_added', '{\"note_id\": 1}', '2026-04-20 11:21:57'),
(3, 'customer', 125, 'note_added', '{\"note\": \"Packaging preference\"}', '2026-04-20 11:30:27'),
(4, 'customer', 126, 'status_changed', '{\"new_status\": \"suspended\"}', '2026-04-15 11:30:27'),
(5, 'order', 90037, 'payment_success', '{\"gateway\":\"razorpay\",\"txn_id\":\"pay_SirApqraLAKZs6\",\"amount\":3569,\"status\":\"success\",\"raw\":\"{\\\"razorpay_payment_id\\\":\\\"pay_SirApqraLAKZs6\\\",\\\"razorpay_order_id\\\":\\\"order_SirAjITGkdf8Td\\\",\\\"razorpay_signature\\\":\\\"b3d1c549649a7c7d4e843551827cd0416327fcd41352c807aa4cf6c86c03dd9f\\\"}\"}', '2026-04-28 09:04:24'),
(6, 'order', 90038, 'payment_success', '{\"gateway\":\"razorpay\",\"txn_id\":\"pay_Sit6a78UhoF3gG\",\"amount\":340,\"status\":\"success\",\"raw\":\"{\\\"razorpay_payment_id\\\":\\\"pay_Sit6a78UhoF3gG\\\",\\\"razorpay_order_id\\\":\\\"order_Sit66tNSQOJ2yh\\\",\\\"razorpay_signature\\\":\\\"2596a82ed4bc0718fb55b75223cc0813555e291be0d5e5864f8642a92a2b63e5\\\"}\"}', '2026-04-28 10:57:46'),
(7, 'order', 90039, 'payment_success', '{\"gateway\":\"razorpay\",\"txn_id\":\"pay_SixPhtSg1Qoinw\",\"amount\":5429,\"status\":\"success\",\"raw\":\"{\\\"razorpay_payment_id\\\":\\\"pay_SixPhtSg1Qoinw\\\",\\\"razorpay_order_id\\\":\\\"order_SixPS0mhHEaNrv\\\",\\\"razorpay_signature\\\":\\\"319376f0ccc126c3d80a5531086ab71a0e16e23512d3895d8509d4cfb7bddf2d\\\"}\"}', '2026-04-28 15:10:50'),
(8, 'order', 90040, 'payment_success', '{\"gateway\":\"razorpay\",\"txn_id\":\"pay_SjHCkLWi1Xxj7k\",\"amount\":700,\"status\":\"success\",\"raw\":\"{\\\"razorpay_payment_id\\\":\\\"pay_SjHCkLWi1Xxj7k\\\",\\\"razorpay_order_id\\\":\\\"order_SjHCb1X7cXn65s\\\",\\\"razorpay_signature\\\":\\\"403809238df7afc3c69a1ed994f0bc583a20022b802b972ffe804cf874ab041c\\\"}\"}', '2026-04-29 10:32:14'),
(9, 'order', 90041, 'payment_success', '{\"gateway\":\"razorpay\",\"txn_id\":\"pay_SjZoXZQdgEcK8y\",\"amount\":700,\"status\":\"success\",\"raw\":\"{\\\"razorpay_payment_id\\\":\\\"pay_SjZoXZQdgEcK8y\\\",\\\"razorpay_order_id\\\":\\\"order_SjZoFqHvdH4m8H\\\",\\\"razorpay_signature\\\":\\\"2c0c1a59d7a68e2de7e2be4c55c4b2672329e8909d07b408d2b94c68930e302e\\\"}\"}', '2026-04-30 04:44:34'),
(10, 'order', 90042, 'payment_success', '{\"gateway\":\"razorpay\",\"txn_id\":\"pay_SjaCIz2eVxqUKB\",\"amount\":365,\"status\":\"success\",\"raw\":\"{\\\"razorpay_payment_id\\\":\\\"pay_SjaCIz2eVxqUKB\\\",\\\"razorpay_order_id\\\":\\\"order_SjaCCXCuoFHtam\\\",\\\"razorpay_signature\\\":\\\"d995f184d9dec245b0144b70e33d9cafb6bef4fbe8d0fbfffdd9bfa82b65b1d0\\\"}\"}', '2026-04-30 05:07:01');

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  `type` enum('shipping','billing') DEFAULT 'shipping',
  `address_line1` varchar(255) NOT NULL,
  `address_line2` varchar(255) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `zip_code` varchar(20) NOT NULL,
  `country` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `recipient_name`, `type`, `address_line1`, `address_line2`, `city`, `state`, `zip_code`, `country`, `phone`, `is_default`) VALUES
(1, 1, 'Kevin Rajput', 'shipping', 'Plot No. 42, Heritage Enclave, Indiranagar', NULL, 'Bengaluru', 'Karnataka', '560038', 'India', NULL, 1),
(3, 9001, NULL, 'shipping', 'Flat 402, Sunshine Apts', NULL, 'Bengaluru', 'Karnataka', '', '', NULL, 0),
(4, 9002, NULL, 'shipping', 'Villa 15, Green Meadows', NULL, 'Mumbai', 'Maharashtra', '', '', NULL, 0),
(5, 9003, NULL, 'shipping', 'Plot 45, Tech Park', NULL, 'Hyderabad', 'Telangana', '', '', NULL, 0),
(6, 9004, NULL, 'shipping', 'Door 12, Main Road', NULL, 'Chennai', 'Tamil Nadu', '', '', NULL, 0),
(7, 9005, NULL, 'shipping', 'Sector 4, Dwarka', NULL, 'New Delhi', 'Delhi', '', '', NULL, 0),
(9, 9008, 'esakiraj raja', 'shipping', 'wqdwqfwqfwq', NULL, 'sivakasi', 'TAMIL NADU', '658625', 'India', '9047478886', 0),
(10, 9008, 'esakiraj raj', 'shipping', 'wqdwqfwqfwq', NULL, 'sivakasi', 'TAMIL NADU', '658625', 'India', '9047478886', 0),
(11, 9008, 'esakiraj raj', 'shipping', 'wqdwqfwqfwq', NULL, 'sivakasi', 'TAMIL NADU', '658625', 'India', '9047478886', 0),
(12, 9008, 'esakiraj raj', 'shipping', 'wqdwqfwqfwq', NULL, 'sivakasi', 'TAMIL NADU', '658625', 'India', '9047478886', 0),
(13, 9008, 'esakiraj raj', 'shipping', 'wqdwqfwqfwq', NULL, 'sivakasi', 'TAMIL NADU', '658625', 'India', '9047478886', 0),
(14, 9010, 'john raja', 'shipping', 'wqdwqfwqfwq', NULL, 'sivakasi', 'TAMIL NADU', '658623', 'India', '8221633689', 0),
(15, 9010, 'john raja', 'shipping', 'weegewgegweg', NULL, 'sivakasi', 'TAMIL NADU', '658622', 'India', '8221633689', 0),
(16, 9010, 'john raja', 'shipping', 'wqdwqfwqfwq', NULL, 'sivakasi', 'TAMIL NADU', '658623', 'India', '8221633689', 0),
(17, 9007, 'esakiraj', 'shipping', 'eededwdw', NULL, 'madurai', 'TAMIL NADU', ' 625001', 'India', '9047478886', 0),
(18, 9008, 'esakiraj raja', 'shipping', 'scscscs', NULL, 'sivakasi', 'TAMIL NADU', '625001', 'India', '9047478886', 0),
(19, 9013, 'raj', 'shipping', 'eededwdw', NULL, 'madurai', 'TAMIL NADU', ' 625001', 'India', '9047478888', 0),
(20, 9013, 'raj', 'shipping', 'eededwdw', NULL, 'madurai', 'TAMIL NADU', '625001', 'India', '9047478888', 0),
(21, 9008, 'esakiraj raja', 'shipping', 'wqdwqfwqfwq', NULL, 'madurai', 'TAMIL NADU', '625001', 'India', '9047478886', 0),
(22, 9008, 'esakiraj raja', 'shipping', 'wqdwqfwqfwq', NULL, 'madurai', 'TAMIL NADU', '625001', 'India', '9047478886', 0),
(23, 9008, 'esakiraj raja', 'shipping', 'hhhtjrtsjjrtjtjj', NULL, 'sivakasi', 'TAMIL NADU', ' 625001', 'India', '9685741122', 0),
(24, 9008, 'esakiraj raja', 'shipping', 'wqdwqfwqfwq', NULL, 'madurai', 'TAMIL NADU', '625001', 'India', '9047478886', 0),
(25, 9008, 'esakiraj', 'shipping', 'wqdwqfwqfwq', NULL, 'madurai', 'TAMIL NADU', ' 625001', 'India', '9047478886', 0),
(26, 2, 'esakiraj raja', 'shipping', 'wqdwqfwqfwq', NULL, 'madurai', 'TAMIL NADU', ' 625001', 'India', '9047478886', 0),
(27, 9008, 'esakiraj raja', 'shipping', 'wqdwqfwqfwq', NULL, 'madurai', 'TAMIL NADU', '254125', 'India', '9047478886', 0),
(28, 9014, 'nalej raja', 'shipping', 'wqdwqfwqfwq', NULL, 'Hyderabad', ' Andhra Pradesh', '858567', 'India', '9085858541', 0),
(29, 9014, 'nalej raja', 'shipping', 'wqdwqfwqfwq', NULL, 'Hyderabad', ' Andhra Pradesh', '858567', 'India', '9085858541', 0),
(30, 9015, 'nalej raja', 'shipping', 'wqdwqfwqfwq', NULL, 'Hyderabad', ' Andhra Pradesh', '589625', 'India', '8525654215', 0),
(31, 9015, 'nalej raja', 'shipping', 'wqdwqfwqfwq', NULL, 'Hyderabad', ' Andhra Pradesh', '589625', 'India', '8525654215', 0);

-- --------------------------------------------------------

--
-- Table structure for table `admin_login_activity`
--

CREATE TABLE `admin_login_activity` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL DEFAULT '',
  `user_agent` text DEFAULT NULL,
  `device_label` varchar(150) DEFAULT NULL,
  `device_type` enum('desktop','mobile','tablet','unknown') DEFAULT 'unknown',
  `location` varchar(150) DEFAULT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'failed',
  `action_label` varchar(50) DEFAULT 'Pending',
  `is_current` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_login_activity`
--

INSERT INTO `admin_login_activity` (`id`, `admin_id`, `ip_address`, `user_agent`, `device_label`, `device_type`, `location`, `status`, `action_label`, `is_current`, `created_at`) VALUES
(1, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'Windows PC - Chrome', 'desktop', 'Local Network', 'success', 'Pending', 0, '2026-04-29 09:23:50'),
(2, 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'Windows PC - Chrome', 'desktop', 'Local Network', 'success', 'Pending', 1, '2026-04-30 05:07:48');

-- --------------------------------------------------------

--
-- Table structure for table `admin_notifications`
--

CREATE TABLE `admin_notifications` (
  `id` int(11) NOT NULL,
  `type` enum('order','stock','payment','shipment','system') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_notifications`
--

INSERT INTO `admin_notifications` (`id`, `type`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 'order', 'New order received', 'order #VK1042 was placed by aman rathore for ? 720 Includes premium sorted karadant.', 0, '2026-04-12 17:05:34'),
(2, 'stock', 'Low stock Alert', 'Karadant (500g) is running low on stock. only 4 units remaining in inventory', 0, '2026-04-12 16:49:34'),
(3, 'payment', 'Payment Successful', 'Payment of ? 3,400 received for order #VK141 via Razorpay', 0, '2026-04-12 16:07:34'),
(4, 'shipment', 'Shipment delivered', 'Order #VK1082 has been successfully to the customer in bangalore', 0, '2026-04-11 17:07:34'),
(5, 'order', 'order cancelled', 'order #VK1035 was cancelled by the user. reason change of mind refund has been initiated automatically', 0, '2026-04-11 17:07:34');

-- --------------------------------------------------------

--
-- Table structure for table `analytics_events`
--

CREATE TABLE `analytics_events` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(100) NOT NULL,
  `event_type` enum('page_view','add_to_cart','begin_checkout','purchase') NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`payload`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `entity_type`, `entity_id`, `action`, `performed_by`, `payload`, `ip_address`, `created_at`) VALUES
(1, 'product', 1016, 'create', NULL, '{\"category_id\":1,\"name\":\"SyncTest Product 1775808853\",\"slug\":\"synctest-product-1775808853\",\"short_description\":\"\",\"description\":\"\",\"base_price\":100,\"sale_price\":null,\"tax_rate\":0,\"sku\":null,\"image_path\":\"assets\\/images\\/placeholders\\/product-placeholder.png\",\"status\":\"published\",\"stock_quantity\":50}', '0.0.0.0', '2026-04-10 08:14:13'),
(2, 'product', 1016, 'delete', NULL, NULL, '0.0.0.0', '2026-04-10 08:14:13'),
(3, 'product', 1, 'update', 2, '{\"category_id\":3,\"name\":\"Premium Vijaya Karadant\",\"short_description\":\"Vijaya Karadant stands out as one of the finest and most delicious sweets...\",\"description\":\"ffgsfgfhsfhsffsdsdh\",\"base_price\":\"350\",\"sale_price\":650,\"tax_rate\":\"0\",\"sku\":\"#j\",\"status\":\"published\"}', '::1', '2026-04-10 08:30:16'),
(4, 'product', 1000, 'update', 2, '{\"category_id\":1,\"name\":\"Premium Vijaya Karadant\",\"short_description\":\"\",\"description\":\"super\",\"base_price\":\"720\",\"sale_price\":950,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\"}', '::1', '2026-04-10 08:42:18'),
(5, 'product', 1000, 'update', 2, '{\"category_id\":1,\"name\":\"Premium Vijaya Karadant\",\"short_description\":\"\",\"description\":\"super\",\"base_price\":\"720\",\"sale_price\":950,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\"}', '::1', '2026-04-10 08:42:22'),
(6, 'product', 1017, 'create', 2, '{\"category_id\":1,\"name\":\"laddu\",\"slug\":\"laddu\",\"short_description\":\"fhfdhfdh\",\"description\":\"fhdfhdhfdhh\",\"base_price\":2000,\"sale_price\":300,\"tax_rate\":12,\"sku\":\"#2\",\"image_path\":\"assets\\/images\\/products\\/sw_69d8d4cd945f74.99532167.png\",\"status\":\"published\",\"stock_quantity\":5}', '::1', '2026-04-10 10:45:33'),
(7, 'product', 1017, 'delete', 2, NULL, '::1', '2026-04-11 08:57:48'),
(8, 'product', 4, 'delete', 2, NULL, '::1', '2026-04-11 12:35:05'),
(9, 'product', 1020, 'create', 2, '{\"category_id\":13,\"name\":\"demo\",\"slug\":\"demo\",\"short_description\":\"sdgsdgsdgsdgsd\",\"description\":\"gsdgsdgsdgsdgdsgsg\",\"base_price\":300,\"sale_price\":500,\"tax_rate\":16,\"sku\":\"#5\",\"image_path\":\"assets\\/images\\/products\\/sw_69da40a6797384.30012792.png\",\"status\":\"published\",\"stock_quantity\":5}', '::1', '2026-04-11 12:37:58'),
(10, 'product', 2011, 'delete', 2, NULL, '::1', '2026-04-13 03:53:44'),
(11, 'product', 2010, 'delete', 2, NULL, '::1', '2026-04-18 09:23:35'),
(12, 'product', 2009, 'delete', 2, NULL, '::1', '2026-04-18 09:25:50'),
(13, 'product', 2013, 'delete', 2, NULL, '::1', '2026-04-20 07:46:45'),
(14, 'product', 1040, 'update', 2, '{\"status\":\"draft\"}', '::1', '2026-04-20 17:18:52'),
(15, 'product', 1040, 'toggle_status', 2, '{\"new_status\":\"draft\"}', '::1', '2026-04-20 17:18:52'),
(16, 'product', 1040, 'update', 2, '{\"status\":\"published\"}', '::1', '2026-04-20 17:18:54'),
(17, 'product', 1040, 'toggle_status', 2, '{\"new_status\":\"published\"}', '::1', '2026-04-20 17:18:54'),
(18, 'product', 1040, 'update', 2, '{\"status\":\"draft\"}', '::1', '2026-04-21 12:23:38'),
(19, 'product', 1040, 'toggle_status', 2, '{\"new_status\":\"draft\"}', '::1', '2026-04-21 12:23:38'),
(20, 'product', 1040, 'update', 2, '{\"status\":\"published\"}', '::1', '2026-04-21 12:23:39'),
(21, 'product', 1040, 'toggle_status', 2, '{\"new_status\":\"published\"}', '::1', '2026-04-21 12:23:39'),
(22, 'product', 2014, 'create', 2, '{\"category_id\":5,\"subcategory_id\":null,\"name\":\"demo\",\"slug\":\"demo\",\"short_description\":\"d23dd23ddfewfwefe\",\"description\":\"vdbdbzsdbd\",\"base_price\":500,\"sale_price\":300,\"tax_rate\":0,\"sku\":\"#2\",\"image_path\":\"assets\\/images\\/products\\/sw_69e79550404821.98400110.png\",\"status\":\"published\",\"stock_quantity\":55,\"featured\":0}', '::1', '2026-04-21 15:18:40'),
(23, 'order', 90008, 'create', NULL, '{\"user_id\":9007,\"order_number\":\"SW-ORD-41CD9F-20260422\",\"total_amount\":2140,\"status\":\"pending\",\"payment_status\":\"paid\",\"shipping_address_id\":null,\"items\":{\"dink-karadant-500g\":{\"id\":1005,\"name\":\"Dink Karadant\",\"slug\":\"dink-karadant\",\"image\":\"assets\\/images\\/homepage\\/New folder\\/karant\\/bestseeler karadant (5).png\",\"price\":\"650.00\",\"weight\":\"500g\",\"quantity\":3},\"rice-kodubale-500g\":{\"id\":2006,\"name\":\"Rice Kodubale\",\"slug\":\"rice-kodubale\",\"image\":\"assets\\/images\\/banners\\/namkeen-page\\/our signature  (7).png\",\"price\":\"290.00\",\"weight\":\"500g\",\"quantity\":1}}}', '::1', '2026-04-22 08:00:52'),
(24, 'order', 90009, 'create', NULL, '{\"user_id\":9007,\"order_number\":\"SW-ORD-F98BAE-20260422\",\"total_amount\":2939,\"status\":\"pending\",\"payment_status\":\"paid\",\"shipping_address_id\":null,\"items\":{\"dink-karadant-500g\":{\"id\":1005,\"name\":\"Dink Karadant\",\"slug\":\"dink-karadant\",\"image\":\"assets\\/images\\/homepage\\/New folder\\/karant\\/bestseeler karadant (5).png\",\"price\":\"650.00\",\"weight\":\"500g\",\"quantity\":3,\"original_price\":650},\"rice-kodubale-500g\":{\"id\":2006,\"name\":\"Rice Kodubale\",\"slug\":\"rice-kodubale\",\"image\":\"assets\\/images\\/banners\\/namkeen-page\\/our signature  (7).png\",\"price\":\"290.00\",\"weight\":\"500g\",\"quantity\":1,\"original_price\":320},\"regal-anjeer-karadant-500g\":{\"id\":1004,\"name\":\"Regal Anjeer Karadant\",\"slug\":\"regal-anjeer-karadant\",\"image\":\"assets\\/images\\/homepage\\/New folder\\/karant\\/bestseeler karadant (7).png\",\"price\":\"799.00\",\"weight\":\"500g\",\"quantity\":1,\"original_price\":880}}}', '::1', '2026-04-22 08:29:19'),
(25, 'order', 90010, 'create', NULL, '{\"user_id\":9007,\"order_number\":\"SW-ORD-F93386-20260422\",\"total_amount\":700,\"status\":\"pending\",\"payment_status\":\"paid\",\"shipping_address_id\":null,\"items\":{\"dink-karadant-500g\":{\"id\":1005,\"name\":\"Dink Karadant\",\"slug\":\"dink-karadant\",\"image\":\"assets\\/images\\/homepage\\/New folder\\/karant\\/bestseeler karadant (5).png\",\"price\":\"650.00\",\"original_price\":\"650.00\",\"weight\":\"500g\",\"quantity\":1}}}', '::1', '2026-04-22 08:54:55'),
(26, 'product', 1, 'delete', 2, NULL, '::1', '2026-04-27 10:21:59'),
(27, 'product', 2, 'delete', 2, NULL, '::1', '2026-04-27 10:22:02'),
(28, 'product', 3, 'delete', 2, NULL, '::1', '2026-04-27 10:22:05'),
(29, 'product', 1010, 'update', 2, '{\"category_id\":2,\"subcategory_id\":4,\"name\":\"Ragi Laddu\",\"short_description\":\"Wholesome ragi laddus with a roasted nutty taste.\",\"description\":\"\",\"base_price\":\"450\",\"sale_price\":399,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"10\",\"featured\":1}', '::1', '2026-04-27 11:20:56'),
(30, 'product', 1010, 'update', 2, '{\"status\":\"draft\"}', '::1', '2026-04-27 11:32:13'),
(31, 'product', 1010, 'toggle_status', 2, '{\"new_status\":\"draft\"}', '::1', '2026-04-27 11:32:13'),
(32, 'product', 2001, 'update', 2, '{\"status\":\"out_of_stock\"}', '::1', '2026-04-28 11:10:01'),
(33, 'product', 2001, 'toggle_status', 2, '{\"new_status\":\"out_of_stock\"}', '::1', '2026-04-28 11:10:01'),
(34, 'product', 2001, 'update', 2, '{\"status\":\"published\"}', '::1', '2026-04-28 11:10:05'),
(35, 'product', 2001, 'toggle_status', 2, '{\"new_status\":\"published\"}', '::1', '2026-04-28 11:10:05'),
(36, 'product', 2001, 'update', 2, '{\"status\":\"out_of_stock\"}', '::1', '2026-04-28 11:10:06'),
(37, 'product', 2001, 'toggle_status', 2, '{\"new_status\":\"out_of_stock\"}', '::1', '2026-04-28 11:10:06'),
(38, 'product', 2001, 'update', 2, '{\"status\":\"published\"}', '::1', '2026-04-28 11:10:10'),
(39, 'product', 2001, 'toggle_status', 2, '{\"new_status\":\"published\"}', '::1', '2026-04-28 11:10:10'),
(40, 'product', 2001, 'update', 2, '{\"category_id\":4,\"subcategory_id\":5,\"name\":\"Spicy Mix Namkeen\",\"short_description\":\"A bold namkeen mix with signature house spices.\",\"description\":\"\",\"base_price\":\"320\",\"sale_price\":280,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"5\",\"featured\":1}', '::1', '2026-04-28 11:10:18'),
(41, 'product', 2014, 'update', 2, '{\"category_id\":5,\"subcategory_id\":null,\"name\":\"demo\",\"short_description\":\"d23dd23ddfewfwefe\",\"description\":\"vdbdbzsdbd\",\"base_price\":\"500\",\"sale_price\":300,\"tax_rate\":\"0\",\"sku\":\"#2\",\"status\":\"published\",\"stock_quantity\":\"5\",\"featured\":0}', '::1', '2026-04-28 11:10:41'),
(42, 'product', 2014, 'update', 2, '{\"category_id\":5,\"subcategory_id\":null,\"name\":\"demo\",\"short_description\":\"d23dd23ddfewfwefe\",\"description\":\"vdbdbzsdbd\",\"base_price\":\"500\",\"sale_price\":300,\"tax_rate\":\"0\",\"sku\":\"#2\",\"status\":\"published\",\"stock_quantity\":\"5\",\"featured\":0}', '::1', '2026-04-28 11:10:42'),
(43, 'order', 90034, 'cancelled_by_customer', 9008, '{\"from_status\":\"pending\",\"to_status\":\"cancelled\"}', '::1', '2026-04-29 09:12:51'),
(44, 'order', 90060, 'create', NULL, '{\"user_id\":9008,\"order_number\":\"SW-ORD-8E2B61-20260504\",\"total_amount\":849,\"status\":\"pending\",\"payment_status\":\"paid\",\"shipping_address_id\":21,\"billing_address_id\":21,\"items\":{\"regal-anjeer-karadant-1kg\":{\"type\":\"product\",\"name\":\"Regal Anjeer Karadant\",\"slug\":\"regal-anjeer-karadant\",\"image\":\"assets\\/images\\/homepage\\/New folder\\/karant\\/bestseeler karadant (7).png\",\"price\":\"799.00\",\"weight\":\"1kg\",\"quantity\":1,\"id\":1004,\"original_price\":880}}}', '::1', '2026-05-04 11:15:36'),
(45, 'order', 90061, 'create', NULL, '{\"user_id\":9008,\"order_number\":\"SW-ORD-13680C-20260504\",\"total_amount\":340,\"status\":\"pending\",\"payment_status\":\"paid\",\"shipping_address_id\":22,\"billing_address_id\":22,\"items\":{\"butter-muruku-500g\":{\"type\":\"product\",\"id\":2005,\"variant_id\":0,\"name\":\"Butter Muruku\",\"slug\":\"butter-muruku\",\"image\":\"assets\\/images\\/banners\\/namkeen-page\\/our signature  (6).png\",\"price\":290,\"original_price\":\"320.00\",\"weight\":\"500g\",\"quantity\":1}}}', '::1', '2026-05-04 11:16:49'),
(46, 'product', 2001, 'update', 2, '{\"category_id\":4,\"subcategory_id\":5,\"name\":\"Spicy Mix Namkeen\",\"short_description\":\"A bold namkeen mix with signature house spices.\",\"description\":\"\",\"base_price\":\"320\",\"sale_price\":280,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"200\",\"featured\":1}', '::1', '2026-05-06 07:42:05'),
(47, 'product', 2001, 'update', 2, '{\"category_id\":4,\"subcategory_id\":5,\"name\":\"Spicy Mix Namkeen\",\"short_description\":\"A bold namkeen mix with signature house spices.\",\"description\":\"\",\"base_price\":\"320\",\"sale_price\":280,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"200\",\"featured\":1}', '::1', '2026-05-06 07:44:15'),
(48, 'product', 2001, 'update', 2, '{\"category_id\":4,\"subcategory_id\":5,\"name\":\"Spicy Mix Namkeen\",\"short_description\":\"A bold namkeen mix with signature house spices.\",\"description\":\"\",\"base_price\":\"320\",\"sale_price\":280,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"200\",\"featured\":1}', '::1', '2026-05-06 07:44:57'),
(49, 'product', 1014, 'update', 2, '{\"category_id\":2,\"subcategory_id\":null,\"name\":\"Premium Otts Laddu\",\"short_description\":\"Soft and flavorful laddus with a traditional finish.\",\"description\":\"\",\"base_price\":\"500\",\"sale_price\":450,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"0\",\"featured\":0}', '::1', '2026-05-06 07:50:33'),
(50, 'product', 2001, 'update', 2, '{\"category_id\":4,\"subcategory_id\":5,\"name\":\"Spicy Mix Namkeen\",\"short_description\":\"A bold namkeen mix with signature house spices.\",\"description\":\"\",\"base_price\":\"320\",\"sale_price\":280,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"200\",\"featured\":1}', '::1', '2026-05-06 07:53:09'),
(51, 'product', 2001, 'update', 2, '{\"category_id\":4,\"subcategory_id\":5,\"name\":\"Spicy Mix Namkeen\",\"short_description\":\"A bold namkeen mix with signature house spices.\",\"description\":\"\",\"base_price\":\"320\",\"sale_price\":280,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"200\",\"featured\":1}', '::1', '2026-05-06 07:53:50'),
(52, 'product', 2001, 'update', 2, '{\"category_id\":4,\"subcategory_id\":5,\"name\":\"Spicy Mix Namkeen\",\"short_description\":\"A bold namkeen mix with signature house spices.\",\"description\":\"\",\"base_price\":\"320\",\"sale_price\":280,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"200\",\"featured\":1}', '::1', '2026-05-06 07:55:11'),
(53, 'product', 1014, 'update', 2, '{\"category_id\":2,\"subcategory_id\":null,\"name\":\"Premium Otts Laddu\",\"short_description\":\"Soft and flavorful laddus with a traditional finish.\",\"description\":\"\",\"base_price\":\"500\",\"sale_price\":450,\"tax_rate\":\"0\",\"sku\":null,\"status\":\"published\",\"stock_quantity\":\"0\",\"featured\":0}', '::1', '2026-05-06 07:56:57'),
(54, 'product', 2017, 'create', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Garlic Ribbon\",\"slug\":\"garlic-ribbon\",\"short_description\":\"\",\"description\":\"\",\"base_price\":450,\"sale_price\":320,\"tax_rate\":0,\"sku\":\"#5\",\"image_path\":\"assets\\/images\\/products\\/sw_69faf5506704d3.45873060.png\",\"status\":\"published\",\"stock_quantity\":10,\"featured\":1}', '::1', '2026-05-06 08:01:20'),
(55, 'product', 2018, 'create', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Nippattu\",\"slug\":\"nippattu\",\"short_description\":\"\",\"description\":\"\",\"base_price\":420,\"sale_price\":290,\"tax_rate\":0,\"sku\":\"#6\",\"image_path\":\"assets\\/images\\/products\\/sw_69faf5ca546564.22639439.png\",\"status\":\"published\",\"stock_quantity\":20,\"featured\":1}', '::1', '2026-05-06 08:03:22'),
(56, 'product', 2018, 'update', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Onion Kodubale\",\"short_description\":\"Crisp nippattu with roasted spice notes.\",\"description\":\"\",\"base_price\":\"420\",\"sale_price\":290,\"tax_rate\":\"0\",\"sku\":\"#6\",\"status\":\"published\",\"stock_quantity\":\"20\",\"featured\":1}', '::1', '2026-05-06 08:48:39'),
(57, 'product', 2019, 'create', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Ribbon Pakoda\",\"slug\":\"ribbon-pakoda\",\"short_description\":\"\",\"description\":\"\",\"base_price\":450,\"sale_price\":320,\"tax_rate\":0,\"sku\":\"#4\",\"image_path\":\"assets\\/images\\/products\\/sw_69fb0127618d01.15771343.png\",\"status\":\"published\",\"stock_quantity\":10,\"featured\":1}', '::1', '2026-05-06 08:51:51'),
(58, 'product', 2021, 'create', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Garlic Ribbon\",\"slug\":\"garlic-ribbon-1\",\"short_description\":\"\",\"description\":\"\",\"base_price\":450,\"sale_price\":320,\"tax_rate\":0,\"sku\":\"#3\",\"image_path\":\"assets\\/images\\/products\\/sw_69fb01ca2dab70.06238032.png\",\"status\":\"published\",\"stock_quantity\":10,\"featured\":1}', '::1', '2026-05-06 08:54:34'),
(59, 'product', 2026, 'create', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Nippattu\",\"slug\":\"nippattu-1\",\"short_description\":\"\",\"description\":\"\",\"base_price\":410,\"sale_price\":280,\"tax_rate\":0,\"sku\":\"#7\",\"image_path\":\"assets\\/images\\/products\\/sw_69fb02a5225ee9.55018342.png\",\"status\":\"published\",\"stock_quantity\":10,\"featured\":1}', '::1', '2026-05-06 08:58:13'),
(60, 'product', 2027, 'create', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Bengaluru Mix\",\"slug\":\"bengaluru-mix\",\"short_description\":\"\",\"description\":\"\",\"base_price\":380,\"sale_price\":250,\"tax_rate\":0,\"sku\":\"#8\",\"image_path\":\"assets\\/images\\/products\\/sw_69fb0302a68dd7.63996990.png\",\"status\":\"published\",\"stock_quantity\":10,\"featured\":1}', '::1', '2026-05-06 08:59:46'),
(61, 'product', 2029, 'create', 2, '{\"category_id\":4,\"subcategory_id\":null,\"name\":\"Masala Peanuts\",\"slug\":\"masala-peanuts-1\",\"short_description\":\"\",\"description\":\"\",\"base_price\":380,\"sale_price\":250,\"tax_rate\":0,\"sku\":\"#9\",\"image_path\":\"assets\\/images\\/products\\/sw_69fb04250fcf16.43916756.png\",\"status\":\"published\",\"stock_quantity\":11,\"featured\":1}', '::1', '2026-05-06 09:04:37'),
(62, 'product', 2031, 'create', 2, '{\"category_id\":1,\"subcategory_id\":null,\"name\":\"remium Karadant Pack\",\"slug\":\"remium-karadant-pack\",\"short_description\":\"\",\"description\":\"\",\"base_price\":950,\"sale_price\":820,\"tax_rate\":0,\"sku\":\"#10\",\"image_path\":\"assets\\/images\\/products\\/sw_69fc4066de52d5.18136141.png\",\"status\":\"published\",\"stock_quantity\":20,\"featured\":1}', '::1', '2026-05-07 07:33:58'),
(63, 'product', 2031, 'update', 2, '{\"category_id\":1,\"subcategory_id\":null,\"name\":\"remium Karadant Pack\",\"short_description\":\"\",\"description\":\"Our signature Karadant made with premium nuts and jaggery\",\"base_price\":\"950\",\"sale_price\":820,\"tax_rate\":\"0\",\"sku\":\"#10\",\"status\":\"published\",\"stock_quantity\":\"20\",\"featured\":1}', '::1', '2026-05-07 07:34:32'),
(64, 'product', 2031, 'update', 2, '{\"category_id\":1,\"subcategory_id\":null,\"name\":\"Remium Karadant Pack\",\"short_description\":\"\",\"description\":\"Our signature Karadant made with premium nuts and jaggery\",\"base_price\":\"950\",\"sale_price\":820,\"tax_rate\":\"0\",\"sku\":\"#10\",\"status\":\"published\",\"stock_quantity\":\"20\",\"featured\":1}', '::1', '2026-05-07 07:34:57'),
(65, 'product', 2031, 'update', 2, '{\"category_id\":1,\"subcategory_id\":null,\"name\":\"Remium Karadant Pack\",\"short_description\":\"\",\"description\":\"Our signature Karadant made with premium nuts and jaggery\",\"base_price\":\"950\",\"sale_price\":820,\"tax_rate\":\"0\",\"sku\":\"#10\",\"status\":\"published\",\"stock_quantity\":\"20\",\"featured\":1}', '::1', '2026-05-07 07:36:29'),
(66, 'product', 2031, 'update', NULL, '{\"name\":\"Test Product\"}', '0.0.0.0', '2026-05-07 10:13:30'),
(67, 'product', 2031, 'update', 2, '{\"category_id\":1,\"subcategory_id\":null,\"name\":\"Test Product\",\"short_description\":\"\",\"description\":\"Our signature Karadant made with premium nuts and jaggery\",\"base_price\":\"950\",\"sale_price\":820,\"tax_rate\":\"0\",\"sku\":\"#10\",\"status\":\"published\",\"stock_quantity\":\"20\",\"featured\":1}', '::1', '2026-05-07 10:25:08'),
(68, 'product', 2031, 'set_primary_image', 2, '{\"image_id\":121}', '::1', '2026-05-07 10:25:23'),
(69, 'product', 2031, 'set_primary_image', 2, '{\"image_id\":120}', '::1', '2026-05-07 10:25:30'),
(70, 'product', 2031, 'delete_image', 2, '{\"image_id\":121}', '::1', '2026-05-07 10:25:39'),
(71, 'product', 2031, 'update', 2, '{\"category_id\":1,\"subcategory_id\":null,\"name\":\" Karadant\",\"short_description\":\"\",\"description\":\"Our signature Karadant made with premium nuts and jaggery\",\"base_price\":\"950\",\"sale_price\":820,\"tax_rate\":\"0\",\"sku\":\"#10\",\"status\":\"published\",\"stock_quantity\":\"20\",\"featured\":1}', '::1', '2026-05-07 10:25:59'),
(72, 'product', 2031, 'delete', 2, NULL, '::1', '2026-05-09 10:47:29'),
(73, 'product', 2014, 'update', 2, '{\"category_id\":5,\"subcategory_id\":null,\"name\":\"demo\",\"short_description\":\"d23dd23ddfewfwefe\",\"description\":\"vdbdbzsdbd\",\"base_price\":\"500\",\"sale_price\":300,\"tax_rate\":\"0\",\"sku\":\"#2\",\"image_path\":\"assets\\/images\\/products\\/sw_6a007898d77f45.11993884.png\",\"status\":\"published\",\"stock_quantity\":\"5\",\"featured\":0}', '::1', '2026-05-10 12:22:48'),
(74, 'product', 1003, 'update', 2, '{\"category_id\":1,\"subcategory_id\":3,\"name\":\"Supreme Vijaya Karadant\",\"short_description\":\"Richer blend of nuts and jaggery for a premium bite.\",\"description\":\"\",\"base_price\":\"420\",\"sale_price\":380,\"tax_rate\":\"0\",\"sku\":null,\"image_path\":\"assets\\/images\\/products\\/sw_6a0078dfcbb266.26209842.png\",\"status\":\"published\",\"stock_quantity\":\"80\",\"featured\":1}', '::1', '2026-05-10 12:23:59'),
(75, 'product', 1010, 'update', 2, '{\"status\":\"published\"}', '127.0.0.1', '2026-05-14 10:07:07'),
(76, 'product', 1010, 'toggle_status', 2, '{\"new_status\":\"published\"}', '127.0.0.1', '2026-05-14 10:07:07'),
(77, 'product', 1010, 'update', 2, '{\"status\":\"published\"}', '127.0.0.1', '2026-05-14 10:07:07'),
(78, 'product', 1010, 'toggle_status', 2, '{\"new_status\":\"published\"}', '127.0.0.1', '2026-05-14 10:07:07');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs_v2`
--

CREATE TABLE `audit_logs_v2` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `module` varchar(50) NOT NULL,
  `action` varchar(100) NOT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `payload` longtext DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `session_id`, `created_at`) VALUES
(1, 9007, NULL, '2026-04-21 10:42:05'),
(2, 1, NULL, '2026-04-22 07:25:26'),
(3, 9009, NULL, '2026-04-22 11:00:10'),
(4, 9008, NULL, '2026-04-27 11:19:02'),
(5, 9010, NULL, '2026-04-30 12:45:27'),
(6, 9011, NULL, '2026-04-30 12:45:37'),
(7, 9012, NULL, '2026-04-30 12:45:55'),
(8, 9014, NULL, '2026-05-11 06:46:48'),
(9, 9015, NULL, '2026-05-11 07:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `item_type` enum('product','combo') DEFAULT 'product',
  `product_id` int(11) DEFAULT NULL,
  `combo_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `weight` varchar(50) DEFAULT '500g',
  `price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `item_type`, `product_id`, `combo_id`, `quantity`, `weight`, `price`) VALUES
(1, 1, 'product', 1005, NULL, 3, '500g', 650.00),
(2, 1, 'product', 2006, NULL, 1, '500g', 290.00),
(3, 1, 'product', 1004, NULL, 4, '500g', 799.00),
(4, 3, 'product', 2006, NULL, 1, '250g', 290.00),
(11, 2, 'product', 1, NULL, 2, '500g', 100.00),
(12, 2, 'combo', NULL, 1, 1, 'Bundle', 250.00),
(13, 5, 'combo', NULL, 9, 1, 'Bundle', 500.00),
(14, 5, 'product', 2006, NULL, 1, '500g', 290.00),
(15, 5, 'product', 1005, NULL, 1, '500g', 650.00),
(16, 5, 'combo', NULL, 13, 1, 'Bundle', 1299.00),
(17, 1, 'product', 1040, NULL, 1, '250g', 880.00),
(20, 1, 'product', 1003, NULL, 1, '500g', 420.00),
(21, 1, 'product', 2003, NULL, 1, '500g', 250.00),
(33, 9, 'combo', NULL, 14, 1, 'Bundle', 1999.00);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `sku` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `hero_image` varchar(255) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `regular_price` decimal(10,2) DEFAULT NULL,
  `discount_price` decimal(10,2) DEFAULT NULL,
  `tax_rate` varchar(50) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `highlights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`highlights`)),
  `ingredients` text DEFAULT NULL,
  `benefits` text DEFAULT NULL,
  `storage_instructions` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `sku`, `description`, `short_description`, `parent_id`, `image_path`, `hero_image`, `status`, `created_at`, `updated_at`, `regular_price`, `discount_price`, `tax_rate`, `weight`, `highlights`, `ingredients`, `benefits`, `storage_instructions`) VALUES
(1, 'Karadant', 'karadant', NULL, NULL, NULL, NULL, 'assets/images/homepage/New folder/karant/bestseeler karadant (1).png', 'assets/images/banners/Karadant-banner.png', 'active', '2026-04-18 10:49:09', '2026-04-19 16:59:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 'Laddu', 'laddu', NULL, NULL, NULL, NULL, 'assets/images/homepage/New folder/bestseller-laddu 1.png', 'assets/images/homepage/New folder/bestseller-laddu 1.png', 'active', '2026-04-18 10:49:09', '2026-04-19 16:59:20', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Namkeen', 'namkeen', NULL, NULL, NULL, NULL, 'assets/images/banners/namkeen-page/nakeen.png', 'assets/images/banners/namkeen-page/namkeen-banner.png', 'active', '2026-04-18 10:49:09', '2026-04-19 17:08:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Gifting', 'gifting', NULL, NULL, NULL, NULL, 'assets/images/Karadant/giftpack 1 (1).png', 'assets/images/banners/gifing/gifting.png', 'active', '2026-04-18 10:49:09', '2026-04-19 17:08:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 'T-ShoeStyle', 't-shoestyle', NULL, 'Traditional authentic Karadant made with pure jaggery and premium dry fruits.', 'gdcskcwegcegcvgrgerhrh', 5, 'assets/images/categories/sw_69e5c3669da4c4.81670470.png', 'assets/images/categories/sw_69e5c3669deb83.11151714.png', 'active', '2026-04-20 06:00:33', '2026-04-20 06:10:46', 632.00, NULL, '5% (GST)', '250g', '[\"100% Pure Premium Cashews\",\"Free delivery over \\u20b9 999\",\"Handcrafted by Expert Halwais\"]', 'hthhrththrthrthrththrth', 'thrthhththt', 'hthshrtjhgjkyjytjyt'),
(19, 'Combos', 'combos', NULL, NULL, NULL, NULL, NULL, NULL, 'active', '2026-05-06 06:28:21', '2026-05-06 06:28:21', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `combos`
--

CREATE TABLE `combos` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT 'combo',
  `price` decimal(10,2) DEFAULT NULL COMMENT 'Fixed price. If NULL, derived dynamically',
  `image` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combos`
--

INSERT INTO `combos` (`id`, `name`, `slug`, `description`, `category`, `price`, `image`, `is_active`, `created_at`) VALUES
(1, 'Mega Sweet Combo', 'mega-sweet-combo', 'A delightful mix of our premium Karadant and Laddu.', 'karadant', 1200.00, 'assets/images/combos/karadant-combo.png', 1, '2026-04-30 10:40:44'),
(2, 'Festive Namkeen Mix', 'festive-namkeen-mix', 'Spicy and crunchy namkeen assortment.', 'namkeen', 450.00, 'assets/images/combos/namkeen-combo.png', 1, '2026-04-30 10:40:44'),
(3, 'Premium Laddu Box', 'premium-laddu-box', 'The best selection of assorted laddus.', 'laddu', 800.00, 'assets/images/combos/laddu-combo.png', 1, '2026-04-30 10:40:44'),
(4, 'Family Festival Pack', 'family-festival-pack', 'Something for everyone in the family.', 'karadant', 2500.00, 'assets/images/combos/family-combo.png', 1, '2026-04-30 10:40:44'),
(5, 'Classic Karadant Pair', 'classic-karadant-pair', 'Two of our best selling Karadants.', 'karadant', 1100.00, 'assets/images/combos/classic-karadant.png', 1, '2026-04-30 10:40:44'),
(6, 'Healthy Bites', 'healthy-bites', 'Nutritious Ragi Laddu and Dink Karadant.', 'karadant', 900.00, 'assets/images/combos/healthy-bites.png', 1, '2026-04-30 10:40:44'),
(7, 'Ultimate Gift Box', 'ultimate-gift-box', 'The perfect gift for any occasion.', 'gifting', 3000.00, 'assets/images/combos/gifting-combo.png', 1, '2026-04-30 10:40:44'),
(8, 'Mini Snack Pack', 'mini-snack-pack', 'A small pack of joy.', 'namkeen', 300.00, 'assets/images/combos/mini-snack.png', 1, '2026-04-30 10:40:44'),
(9, 'demo', 'demo', 'dagasagaASFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF', 'karadant', 500.00, 'assets/images/combos/sw_69f3408e6fa7a3.23596280.png', 1, '2026-04-30 11:44:14'),
(10, 'laddu', 'laddu', 'bfnfgmfgznsfbksdbklshdblsdzkbskldbsldzfbdbsdbsdb', 'laddu', 800.00, 'assets/images/combos/sw_69f34e9c3f2a59.46823182.png', 1, '2026-04-30 12:44:12'),
(11, 'Premium Traditional Combo 01', 'premium-traditional-combo-01', 'A curated selection of our finest traditional sweets.', 'laddu', 999.00, 'assets/images/combos/sw_6a0435c3150748.24359219.png', 1, '2026-05-02 10:46:54'),
(12, 'Festive Family Pack 02', 'festive-family-pack-02', 'Perfect for sharing with the whole family during celebrations.', 'gifting', 1499.00, 'assets/images/combos/sw_6a043880709273.24809387.png', 1, '2026-05-02 10:46:54'),
(13, 'Gourmet Sweet Assortment 03', 'gourmet-sweet-assortment-03', 'Indulge in a variety of gourmet sweet flavors.', 'karadant', 1299.00, 'assets/images/combos/sw_6a0441935ff286.76033428.png', 1, '2026-05-02 10:46:54'),
(14, 'Luxury Celebration Box 04', 'luxury-celebration-box-04', 'Our most luxurious sweets in one elegant package.', 'gifting', 1999.00, 'assets/images/combos/sw_6a043cec1244b2.17589271.png', 1, '2026-05-02 10:46:54'),
(15, 'Artisanal Karadant Selection 05', 'artisanal-karadant-selection-05', 'Traditional Karadant made with artisanal care.', 'karadant', 899.00, 'assets/images/combos/sw_6a044f8d699066.23944662.png', 1, '2026-05-02 10:46:54'),
(16, 'Classic Sweet Duo 06', 'classic-sweet-duo-06', 'Two of our most loved sweet treats in one pack.', 'karadant', 599.00, 'assets/images/combos/sw_6a044269451d68.48297841.png', 1, '2026-05-02 10:46:54'),
(17, 'Health-Conscious Sweet Mix 07', 'health-conscious-sweet-mix-07', 'Sweets made with healthy ingredients and minimal sugar.', 'laddu', 1099.00, 'assets/images/combos/sw_6a044f7000dba7.68701997.png', 1, '2026-05-02 10:46:54'),
(18, 'Royal Sweet Platter 08', 'royal-sweet-platter-08', 'A platter fit for royalty, featuring assorted delicacies.', 'gifting', 2499.00, 'assets/images/combos/sw_6a044f43a19f82.77140929.png', 1, '2026-05-02 10:46:54'),
(19, 'Grand Festival Combo 09', 'grand-festival-combo-09', 'Celebrate in grand style with this massive combo pack.', 'gifting', 2999.00, 'assets/images/combos/sw_6a044f5f60ff46.08403489.png', 1, '2026-05-02 10:46:54'),
(20, 'Signature Sweet Box 10', 'signature-sweet-box-10', 'Our signature sweets, hand-picked for perfection.', 'karadant', 1199.00, 'assets/images/combos/sw_6a044379e63c33.03061085.png', 1, '2026-05-02 10:46:54'),
(21, 'Traditional Delights 11', 'traditional-delights-11', 'Delightful traditional sweets for every occasion.', 'karadant', 799.00, 'assets/images/combos/sw_6a04443bdcb602.77812311.png', 1, '2026-05-02 10:46:54'),
(22, 'Sweet Heritage Pack 12', 'sweet-heritage-pack-12', 'Experience the rich heritage of our traditional recipes.', 'karadant', 1399.00, 'assets/images/combos/sw_6a044e2b2ea5a9.72406374.png', 1, '2026-05-02 10:46:54'),
(23, 'Premium Laddu Mix 13', 'premium-laddu-mix-13', 'An assortment of our premium laddus.', 'laddu', 849.00, 'assets/images/combos/sw_6a043d4779a525.55305471.png', 1, '2026-05-02 10:46:54'),
(24, 'Crunchy Namkeen Combo 14', 'crunchy-namkeen-combo-14', 'A mix of our best selling namkeen and snacks.', 'namkeen', 649.00, 'assets/images/combos/sw_6a044c869a76c4.82484652.png', 1, '2026-05-02 10:46:54'),
(25, 'Sweet & Spicy Pair 15', 'sweet-and-spicy-pair-15', 'The perfect balance of sweet treats and spicy snacks.', 'gifting', 899.00, 'assets/images/combos/sw_6a044b7d1dfeb8.94421208.png', 1, '2026-05-02 10:46:54'),
(26, 'Corporate Gifting Pack 16', 'corporate-gifting-pack-16', 'Elegant packaging and premium taste, ideal for gifting.', 'gifting', 1599.00, 'assets/images/combos/sw_6a044f31b36c45.17590021.png', 1, '2026-05-02 10:46:54'),
(27, 'Homecoming Special 17', 'homecoming-special-17', 'A warm welcome with our most nostalgic sweets.', 'karadant', 1249.00, 'assets/images/combos/sw_6a043f9176edb3.45289424.png', 1, '2026-05-02 10:46:54'),
(28, 'Evening Snack Mix 18', 'evening-snack-mix-18', 'Perfect accompaniments for your evening tea.', 'namkeen', 549.00, 'assets/images/combos/sw_6a044e648ee603.49780960.png', 1, '2026-05-02 10:46:54'),
(29, 'Bestseller Combo 19', 'bestseller-combo-19', 'A collection of our top 5 best selling items.', 'karadant', 1799.00, 'assets/images/combos/sw_6a044d0fdf21e5.08973332.png', 1, '2026-05-02 10:46:54'),
(30, 'Chef Special Selection 20', 'chef-special-selection-20', 'Hand-crafted selection by our master chefs.', 'karadant', 2199.00, 'assets/images/combos/sw_6a043e34adbff1.18129825.png', 1, '2026-05-02 10:46:54'),
(31, 'karkant', 'karkant', 'WEGEGWEGWEGWEGWEWE', 'laddu', 500.00, 'assets/images/combos/sw_69f5fde39d97c2.91285080.png', 1, '2026-05-02 13:36:35'),
(32, 'demo updated', 'demo-1', 'jjtgfjetjerdjher', 'karadant', 2000.00, 'assets/images/combos/sw_6a0164e2c43407.85131041.png', 1, '2026-05-11 05:10:58');

-- --------------------------------------------------------

--
-- Table structure for table `combo_items`
--

CREATE TABLE `combo_items` (
  `id` int(11) NOT NULL,
  `combo_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `combo_items`
--

INSERT INTO `combo_items` (`id`, `combo_id`, `product_id`, `quantity`) VALUES
(18, 9, 1003, 1),
(19, 9, 1003, 1),
(20, 10, 1003, 1),
(21, 10, 1001, 1),
(39, 1, 1001, 2),
(40, 1, 1009, 1),
(41, 2, 2, 1),
(42, 2, 1010, 2),
(43, 3, 3, 2),
(44, 3, 1009, 2),
(45, 4, 1001, 3),
(46, 4, 3, 2),
(47, 4, 2, 1),
(48, 5, 1001, 1),
(49, 5, 1002, 1),
(50, 6, 1010, 2),
(51, 6, 1005, 1),
(52, 7, 1003, 2),
(53, 7, 1004, 2),
(54, 8, 1009, 1),
(55, 8, 2, 1),
(66, 31, 1014, 1),
(73, 32, 1041, 1),
(79, 12, 1009, 1),
(80, 14, 1003, 1),
(81, 14, 1004, 1),
(82, 23, 2026, 1),
(83, 23, 2017, 1),
(84, 27, 2019, 1),
(85, 27, 1040, 1),
(86, 13, 1001, 1),
(87, 13, 1002, 1),
(88, 11, 2027, 1),
(89, 11, 1015, 1),
(90, 19, 1042, 1),
(91, 19, 2027, 1),
(92, 30, 2005, 1),
(93, 30, 1014, 1),
(94, 18, 2001, 1),
(95, 18, 1005, 1),
(96, 20, 2005, 1),
(97, 20, 1003, 1),
(98, 21, 2004, 1),
(99, 21, 1003, 1),
(100, 22, 2006, 1),
(101, 22, 1001, 1),
(102, 22, 1005, 1);

-- --------------------------------------------------------

--
-- Table structure for table `company_info`
--

CREATE TABLE `company_info` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gst_number` varchar(50) DEFAULT NULL,
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_info`
--

INSERT INTO `company_info` (`id`, `name`, `address`, `phone`, `email`, `gst_number`, `logo_path`, `created_at`) VALUES
(1, 'Luxury Sweets Co.', '123 Mithai Lane, Sweet City, 560001', '+91 98765 43210', 'billing@luxurysweets.com', 'GSTIN1234567890', NULL, '2026-04-18 11:35:08');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `status` varchar(50) DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('percentage','fixed') NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `min_cart_total` decimal(10,2) DEFAULT 0.00,
  `usage_limit` int(11) DEFAULT 1,
  `limit_per_user` int(11) DEFAULT 1,
  `applicable_categories` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`applicable_categories`)),
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `description`, `type`, `value`, `min_cart_total`, `usage_limit`, `limit_per_user`, `applicable_categories`, `expires_at`, `is_active`, `created_by`, `created_at`) VALUES
(1, 'DIWALI20', 'Diwali Dhamaka Sale - 20% off on festive hampers', 'percentage', 20.00, 1000.00, 500, 1, '[\"Karadant Special\", \"Festive Hampers\", \"Premium Sweets\", \"Dry Fruits\"]', NULL, 1, 2, '2026-04-20 12:07:35'),
(2, 'WELCOME10', 'Welcome Bonus for New Users', 'percentage', 10.00, 500.00, 1200, 1, '[\"All Categories\"]', NULL, 1, 1, '2026-04-27 12:06:59'),
(3, 'FREESHIP', 'Free Diwali Shipping', 'fixed', 0.00, 800.00, 1000, 1, '[\"All Categories\"]', NULL, 1, 1, '2026-04-27 12:06:59'),
(4, 'GANESHA15', 'Ganesha Special Offer', 'percentage', 15.00, 1500.00, 500, 1, '[\"Premium Mithai\"]', NULL, 1, 1, '2026-04-27 12:06:59'),
(5, 'CORP500', 'Corporate Gifting Flat Discount', 'fixed', 500.00, 5000.00, 500, 5, '[\"Gift Packs\"]', NULL, 1, 1, '2026-04-27 12:06:59');

-- --------------------------------------------------------

--
-- Table structure for table `coupon_usages`
--

CREATE TABLE `coupon_usages` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupon_usages`
--

INSERT INTO `coupon_usages` (`id`, `coupon_id`, `user_id`, `order_id`, `discount_amount`, `created_at`) VALUES
(1, 1, 116, 84, 250.00, '2026-04-20 12:07:35'),
(2, 2, 10, 36, 1.00, '2026-04-27 12:07:17'),
(3, 2, 1, 63, 1.00, '2026-04-27 12:07:17'),
(8, 2, 2, 60, 1.00, '2026-04-27 12:07:17'),
(11, 2, 2, 31, 1.00, '2026-04-27 12:07:17'),
(12, 3, 1, 54, 0.00, '2026-04-27 12:07:17'),
(17, 3, 2, 42, 0.00, '2026-04-27 12:07:17'),
(18, 3, 2, 84, 0.00, '2026-04-27 12:07:17'),
(23, 3, 1, 34, 0.00, '2026-04-27 12:07:17'),
(33, 3, 2, 52, 0.00, '2026-04-27 12:07:17'),
(34, 3, 10, 57, 0.00, '2026-04-27 12:07:17'),
(36, 3, 2, 73, 0.00, '2026-04-27 12:07:17'),
(43, 3, 10, 63, 0.00, '2026-04-27 12:07:17'),
(45, 3, 2, 49, 0.00, '2026-04-27 12:07:17'),
(49, 4, 1, 56, 1.50, '2026-04-27 12:07:17'),
(52, 4, 1, 44, 1.50, '2026-04-27 12:07:17'),
(54, 4, 2, 58, 1.50, '2026-04-27 12:07:17'),
(57, 4, 2, 34, 1.50, '2026-04-27 12:07:17'),
(59, 5, 2, 37, 50.00, '2026-04-27 12:07:17'),
(61, 5, 1, 88, 50.00, '2026-04-27 12:07:17'),
(65, 5, 10, 51, 50.00, '2026-04-27 12:07:17'),
(68, 5, 10, 41, 50.00, '2026-04-27 12:07:17'),
(71, 5, 2, 47, 50.00, '2026-04-27 12:07:17'),
(88, 1, 2, 63, 2.00, '2026-04-27 12:07:17');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `status` enum('active','suspended','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `user_id`, `name`, `phone`, `dob`, `status`, `created_at`) VALUES
(1, 10, 'Rajiv Sharma', '+91 98765 43210', NULL, 'active', '2026-04-20 10:21:47'),
(2, 11, 'Neha Patel', '+91 91234 56789', NULL, 'active', '2026-04-20 10:21:47'),
(3, 12, 'Amit Verma', '+91 88997 76655', NULL, 'active', '2026-04-20 10:21:47'),
(4, 13, 'Priya Desai', '+91 90001 22334', NULL, 'active', '2026-04-20 10:21:47'),
(5, 14, 'Sanjay Kumar', '+91 99887 77665', NULL, 'active', '2026-04-20 10:21:47'),
(6, 15, 'Rakesh Singh', '+91 77665 54433', NULL, 'active', '2026-04-20 10:21:47'),
(7, 16, 'Customer 101', '+91 0000000101', NULL, 'active', '2026-04-20 10:21:47'),
(8, 17, 'Customer 102', '+91 0000000102', NULL, 'active', '2026-04-20 10:21:47'),
(9, 18, 'Customer 103', '+91 0000000103', NULL, 'active', '2026-04-20 10:21:47'),
(10, 19, 'Customer 104', '+91 0000000104', NULL, 'active', '2026-04-20 10:21:47'),
(11, 20, 'Customer 105', '+91 0000000105', NULL, 'active', '2026-04-20 10:21:47'),
(12, 21, 'Customer 106', '+91 0000000106', NULL, 'active', '2026-04-20 10:21:47'),
(13, 22, 'Customer 107', '+91 0000000107', NULL, 'active', '2026-04-20 10:21:47'),
(14, 23, 'Customer 108', '+91 0000000108', NULL, 'active', '2026-04-20 10:21:47'),
(15, 24, 'Customer 109', '+91 0000000109', NULL, 'active', '2026-04-20 10:21:47'),
(16, 25, 'Customer 110', '+91 0000000110', NULL, 'active', '2026-04-20 10:21:47'),
(17, 26, 'Customer 111', '+91 0000000111', NULL, 'active', '2026-04-20 10:21:47'),
(18, 27, 'Customer 112', '+91 0000000112', NULL, 'active', '2026-04-20 10:21:47'),
(19, 28, 'Customer 113', '+91 0000000113', NULL, 'active', '2026-04-20 10:21:47'),
(20, 29, 'Customer 114', '+91 0000000114', NULL, 'active', '2026-04-20 10:21:47'),
(21, 30, 'Customer 115', '+91 0000000115', NULL, 'active', '2026-04-20 10:21:47'),
(22, 31, 'Customer 116', '+91 0000000116', NULL, 'active', '2026-04-20 10:21:47'),
(23, 32, 'Customer 117', '+91 0000000117', NULL, 'active', '2026-04-20 10:21:47'),
(24, 33, 'Customer 118', '+91 0000000118', NULL, 'active', '2026-04-20 10:21:47'),
(25, 34, 'Customer 119', '+91 0000000119', NULL, 'active', '2026-04-20 10:21:47'),
(26, 35, 'Customer 120', '+91 0000000120', NULL, 'active', '2026-04-20 10:21:47'),
(27, 36, 'Customer 121', '+91 0000000121', NULL, 'active', '2026-04-20 10:21:47'),
(28, 37, 'Customer 122', '+91 0000000122', NULL, 'active', '2026-04-20 10:21:47'),
(29, 38, 'Customer 123', '+91 0000000123', NULL, 'active', '2026-04-20 10:21:47'),
(30, 39, 'Customer 124', '+91 0000000124', NULL, 'active', '2026-04-20 10:21:47'),
(31, 40, 'Customer 125', '+91 0000000125', NULL, 'active', '2026-04-20 10:21:47'),
(32, 41, 'Customer 126', '+91 0000000126', NULL, 'active', '2026-04-20 10:21:47'),
(33, 42, 'Customer 127', '+91 0000000127', NULL, 'active', '2026-04-20 10:21:47'),
(34, 43, 'Customer 128', '+91 0000000128', NULL, 'active', '2026-04-20 10:21:47'),
(35, 44, 'Customer 129', '+91 0000000129', NULL, 'active', '2026-04-20 10:21:47'),
(36, 45, 'Customer 130', '+91 0000000130', NULL, 'active', '2026-04-20 10:21:47'),
(37, 46, 'Customer 131', '+91 0000000131', NULL, 'active', '2026-04-20 10:21:47'),
(38, 47, 'Customer 132', '+91 0000000132', NULL, 'active', '2026-04-20 10:21:47'),
(39, 48, 'Customer 133', '+91 0000000133', NULL, 'active', '2026-04-20 10:21:47'),
(40, 49, 'Customer 134', '+91 0000000134', NULL, 'active', '2026-04-20 10:21:47'),
(41, 50, 'Customer 135', '+91 0000000135', NULL, 'active', '2026-04-20 10:21:47'),
(42, 51, 'Customer 136', '+91 0000000136', NULL, 'active', '2026-04-20 10:21:47'),
(43, 52, 'Customer 137', '+91 0000000137', NULL, 'active', '2026-04-20 10:21:47'),
(44, 53, 'Customer 138', '+91 0000000138', NULL, 'active', '2026-04-20 10:21:47'),
(45, 54, 'Customer 139', '+91 0000000139', NULL, 'active', '2026-04-20 10:21:47'),
(46, 55, 'Customer 140', '+91 0000000140', NULL, 'active', '2026-04-20 10:21:47'),
(47, 56, 'Customer 141', '+91 0000000141', NULL, 'active', '2026-04-20 10:21:47'),
(48, 57, 'Customer 142', '+91 0000000142', NULL, 'active', '2026-04-20 10:21:47'),
(49, 58, 'Customer 143', '+91 0000000143', NULL, 'active', '2026-04-20 10:21:47'),
(50, 59, 'Customer 144', '+91 0000000144', NULL, 'active', '2026-04-20 10:21:47'),
(51, 60, 'Customer 145', '+91 0000000145', NULL, 'active', '2026-04-20 10:21:47'),
(52, 61, 'Customer 146', '+91 0000000146', NULL, 'active', '2026-04-20 10:21:47'),
(53, 62, 'Customer 147', '+91 0000000147', NULL, 'active', '2026-04-20 10:21:47'),
(54, 63, 'Customer 148', '+91 0000000148', NULL, 'active', '2026-04-20 10:21:47'),
(55, 64, 'Customer 149', '+91 0000000149', NULL, 'active', '2026-04-20 10:21:47'),
(56, 65, 'Customer 150', '+91 0000000150', NULL, 'active', '2026-04-20 10:21:47'),
(57, 66, 'Customer 151', '+91 0000000151', NULL, 'active', '2026-04-20 10:21:47'),
(58, 67, 'Customer 152', '+91 0000000152', NULL, 'active', '2026-04-20 10:21:47'),
(59, 68, 'Customer 153', '+91 0000000153', NULL, 'active', '2026-04-20 10:21:47'),
(60, 69, 'Customer 154', '+91 0000000154', NULL, 'active', '2026-04-20 10:21:47'),
(61, 70, 'Customer 155', '+91 0000000155', NULL, 'active', '2026-04-20 10:21:47'),
(62, 71, 'Customer 156', '+91 0000000156', NULL, 'active', '2026-04-20 10:21:47'),
(63, 72, 'Customer 157', '+91 0000000157', NULL, 'active', '2026-04-20 10:21:47'),
(64, 73, 'Customer 158', '+91 0000000158', NULL, 'active', '2026-04-20 10:21:47'),
(65, 74, 'Customer 159', '+91 0000000159', NULL, 'active', '2026-04-20 10:21:47'),
(66, 75, 'Customer 160', '+91 0000000160', NULL, 'active', '2026-04-20 10:21:47'),
(67, 76, 'Customer 161', '+91 0000000161', NULL, 'active', '2026-04-20 10:21:47'),
(68, 77, 'Customer 162', '+91 0000000162', NULL, 'active', '2026-04-20 10:21:47'),
(69, 78, 'Customer 163', '+91 0000000163', NULL, 'active', '2026-04-20 10:21:47'),
(70, 79, 'Customer 164', '+91 0000000164', NULL, 'active', '2026-04-20 10:21:47'),
(71, 80, 'Customer 165', '+91 0000000165', NULL, 'active', '2026-04-20 10:21:47'),
(72, 81, 'Customer 166', '+91 0000000166', NULL, 'active', '2026-04-20 10:21:47'),
(73, 82, 'Customer 167', '+91 0000000167', NULL, 'active', '2026-04-20 10:21:47'),
(74, 83, 'Customer 168', '+91 0000000168', NULL, 'active', '2026-04-20 10:21:47'),
(75, 84, 'Customer 169', '+91 0000000169', NULL, 'active', '2026-04-20 10:21:47'),
(76, 85, 'Customer 170', '+91 0000000170', NULL, 'active', '2026-04-20 10:21:47'),
(77, 86, 'Customer 171', '+91 0000000171', NULL, 'active', '2026-04-20 10:21:47'),
(78, 87, 'Customer 172', '+91 0000000172', NULL, 'active', '2026-04-20 10:21:47'),
(79, 88, 'Customer 173', '+91 0000000173', NULL, 'active', '2026-04-20 10:21:47'),
(80, 89, 'Customer 174', '+91 0000000174', NULL, 'active', '2026-04-20 10:21:47'),
(81, 90, 'Customer 175', '+91 0000000175', NULL, 'active', '2026-04-20 10:21:47'),
(82, 91, 'Customer 176', '+91 0000000176', NULL, 'active', '2026-04-20 10:21:47'),
(83, 92, 'Customer 177', '+91 0000000177', NULL, 'active', '2026-04-20 10:21:47'),
(84, 93, 'Customer 178', '+91 0000000178', NULL, 'active', '2026-04-20 10:21:47'),
(85, 94, 'Customer 179', '+91 0000000179', NULL, 'active', '2026-04-20 10:21:47'),
(86, 95, 'Customer 180', '+91 0000000180', NULL, 'active', '2026-04-20 10:21:47'),
(87, 96, 'Customer 181', '+91 0000000181', NULL, 'active', '2026-04-20 10:21:47'),
(88, 97, 'Customer 182', '+91 0000000182', NULL, 'active', '2026-04-20 10:21:47'),
(89, 98, 'Customer 183', '+91 0000000183', NULL, 'active', '2026-04-20 10:21:47'),
(90, 99, 'Customer 184', '+91 0000000184', NULL, 'active', '2026-04-20 10:21:47'),
(91, 100, 'Customer 185', '+91 0000000185', NULL, 'active', '2026-04-20 10:21:47'),
(92, 101, 'Customer 186', '+91 0000000186', NULL, 'active', '2026-04-20 10:21:47'),
(93, 102, 'Customer 187', '+91 0000000187', NULL, 'active', '2026-04-20 10:21:47'),
(94, 103, 'Customer 188', '+91 0000000188', NULL, 'active', '2026-04-20 10:21:47'),
(95, 104, 'Customer 189', '+91 0000000189', NULL, 'active', '2026-04-20 10:21:47'),
(96, 105, 'Customer 190', '+91 0000000190', NULL, 'active', '2026-04-20 10:21:47'),
(97, 106, 'Customer 191', '+91 0000000191', NULL, 'active', '2026-04-20 10:21:47'),
(98, 107, 'Customer 192', '+91 0000000192', NULL, 'active', '2026-04-20 10:21:47'),
(99, 108, 'Customer 193', '+91 0000000193', NULL, 'active', '2026-04-20 10:21:47'),
(100, 109, 'Customer 194', '+91 0000000194', NULL, 'active', '2026-04-20 10:21:47'),
(101, 110, 'Customer 195', '+91 0000000195', NULL, 'active', '2026-04-20 10:21:47'),
(102, 111, 'Customer 196', '+91 0000000196', NULL, 'active', '2026-04-20 10:21:47'),
(103, 112, 'Customer 197', '+91 0000000197', NULL, 'active', '2026-04-20 10:21:47'),
(104, 113, 'Customer 198', '+91 0000000198', NULL, 'active', '2026-04-20 10:21:47'),
(105, 114, 'Customer 199', '+91 0000000199', NULL, 'active', '2026-04-20 10:21:47'),
(106, 115, 'Customer 200', '+91 0000000200', NULL, 'active', '2026-04-20 10:21:47'),
(128, 116, 'Rajiv Sharma', '+91 98765 43210', '1985-03-12', 'active', '2025-10-22 11:19:38'),
(129, 117, 'Priya Patel', '+91 91234 56789', '1992-07-24', 'active', '2026-01-20 11:19:38'),
(130, 118, 'Amit Verma', '+91 99887 76655', '1988-11-05', 'active', '2026-03-21 11:19:38'),
(137, 125, 'Sunita Gupta', '+91 93344 55667', '1978-05-20', 'active', '2025-04-20 11:30:27'),
(138, 126, 'Vikram Singh', '+91 95566 77889', '1982-12-15', 'suspended', '2025-12-21 11:30:27'),
(139, 127, 'Ananya Rao', '+91 97788 99001', '1995-01-30', 'active', '2026-02-19 11:30:27'),
(140, 128, 'Karan Mehra', '+91 99900 11223', '1990-08-10', 'active', '2026-04-05 11:30:27'),
(141, 9006, 'test', '', NULL, 'active', '2026-04-21 10:38:36'),
(142, 9007, 'esakiraj', '9047478886', NULL, 'active', '2026-04-21 10:42:05'),
(143, 9008, 'esakiraj', '9047478886', NULL, 'active', '2026-04-21 10:53:51'),
(144, 1, 'Kevin Rajput', '+91 8897252325', NULL, 'active', '2026-04-22 08:27:53'),
(145, 9001, 'Rajiv Sharma', '9876543210', NULL, 'active', '2026-04-22 08:27:53'),
(146, 9002, 'Priya Patel', '9876543211', NULL, 'active', '2026-04-22 08:27:53'),
(147, 9003, 'Amit Kumar', '9876543212', NULL, 'active', '2026-04-22 08:27:53'),
(148, 9004, 'Sneha Reddy', '9876543213', NULL, 'active', '2026-04-22 08:27:53'),
(149, 9005, 'Vikram Singh', '9876543214', NULL, 'active', '2026-04-22 08:27:53'),
(151, 9009, 'Test User', '1234567890', NULL, 'active', '2026-04-22 11:00:10'),
(152, 9010, 'john', '8221633689', NULL, 'active', '2026-04-30 12:45:27'),
(153, 9011, 'john', '8221633689', NULL, 'active', '2026-04-30 12:45:37'),
(154, 9012, 'isaac', '8221633689', NULL, 'active', '2026-04-30 12:45:55'),
(157, 9013, 'raj', '9047478888', NULL, 'active', '2026-05-04 10:29:14'),
(160, 9014, 'nalej', '9047478887', NULL, 'active', '2026-05-11 06:46:48'),
(161, 9015, 'nalej', '885241255', NULL, 'active', '2026-05-11 07:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `customer_activity`
--

CREATE TABLE `customer_activity` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_activity`
--

INSERT INTO `customer_activity` (`id`, `user_id`, `action_type`, `description`, `ip_address`, `created_at`) VALUES
(1, 1, 'login', 'Logged in from Web Browser', NULL, '2026-04-20 12:43:10'),
(2, 1, 'profile_update', 'Updated delivery address', NULL, '2026-04-20 12:43:10'),
(3, 1, 'order_placed', 'Placed order #ORD-4091', NULL, '2026-04-20 12:43:10'),
(4, 9001, 'login', 'Logged into the system via web', NULL, '2026-04-20 04:35:50'),
(5, 9002, 'purchase', 'Placed Order #90004', NULL, '2026-04-16 04:35:50'),
(6, 9003, 'support', 'Contacted support for delivery tracking', NULL, '2026-04-19 04:35:50'),
(7, 9005, 'account_locked', 'Account marked inactive due to prolonged absence', NULL, '2026-04-11 04:35:50');

-- --------------------------------------------------------

--
-- Table structure for table `customer_addresses`
--

CREATE TABLE `customer_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('billing','shipping') NOT NULL,
  `address_line` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_addresses`
--

INSERT INTO `customer_addresses` (`id`, `user_id`, `type`, `address_line`, `city`, `state`, `pincode`, `phone`) VALUES
(1, 116, 'billing', 'Flat 402, Sunshine Apartments, Koramangala', 'Bengaluru', 'Karnataka', '560034', '+91 98765 43210'),
(2, 116, 'shipping', 'Flat 402, Sunshine Apartments, Koramangala', 'Bengaluru', 'Karnataka', '560034', '+91 98765 43210'),
(3, 117, 'billing', 'House No 15, Park Street', 'Mumbai', 'Maharashtra', '400001', '+91 91234 56789'),
(4, 116, 'billing', 'Flat 402, Sunshine Apartments, Koramangala', 'Bengaluru', 'Karnataka', '560034', '+91 98765 43210'),
(5, 117, 'billing', 'House No 15, Park Street', 'Mumbai', 'Maharashtra', '400001', '+91 91234 56789'),
(6, 125, 'billing', 'Plot 45, Civil Lines', 'Delhi', 'Delhi', '110001', '+91 93344 55667'),
(7, 127, 'shipping', 'Apartment 201, Marine Drive', 'Chennai', 'Tamil Nadu', '600001', '+91 97788 99001');

-- --------------------------------------------------------

--
-- Table structure for table `customer_metrics`
--

CREATE TABLE `customer_metrics` (
  `customer_id` int(11) NOT NULL,
  `total_orders` int(11) DEFAULT 0,
  `total_spend` decimal(10,2) DEFAULT 0.00,
  `avg_order_value` decimal(10,2) DEFAULT 0.00,
  `last_order_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_notes`
--

CREATE TABLE `customer_notes` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `note` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_notes`
--

INSERT INTO `customer_notes` (`id`, `user_id`, `note`, `created_at`) VALUES
(1, 116, 'Prefers morning delivery.', '2026-04-20 11:21:57'),
(2, 117, 'First time buyer, interested in gifting options.', '2026-04-18 11:21:57'),
(3, 125, 'Requested eco-friendly packaging only.', '2026-04-20 11:30:27'),
(4, 126, 'Account suspended due to repeated chargeback attempts.', '2026-04-15 11:30:27');

-- --------------------------------------------------------

--
-- Table structure for table `customer_profiles`
--

CREATE TABLE `customer_profiles` (
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` enum('male','female','other','unspecified') DEFAULT 'unspecified',
  `dob` date DEFAULT NULL,
  `avatar_url` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `marketing_opt_in` tinyint(1) NOT NULL DEFAULT 0,
  `alternate_phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_profiles`
--

INSERT INTO `customer_profiles` (`customer_id`, `full_name`, `gender`, `dob`, `avatar_url`, `updated_at`, `marketing_opt_in`, `alternate_phone`) VALUES
(10, 'Rajiv Sharma', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(11, 'Neha Patel', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(12, 'Amit Verma', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(13, 'Priya Desai', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(14, 'Sanjay Kumar', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(15, 'Rakesh Singh', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(16, 'Customer 101', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(17, 'Customer 102', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(18, 'Customer 103', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(19, 'Customer 104', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(20, 'Customer 105', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(21, 'Customer 106', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(22, 'Customer 107', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(23, 'Customer 108', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(24, 'Customer 109', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(25, 'Customer 110', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(26, 'Customer 111', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(27, 'Customer 112', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(28, 'Customer 113', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(29, 'Customer 114', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(30, 'Customer 115', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(31, 'Customer 116', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(32, 'Customer 117', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(33, 'Customer 118', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(34, 'Customer 119', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(35, 'Customer 120', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(36, 'Customer 121', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(37, 'Customer 122', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(38, 'Customer 123', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(39, 'Customer 124', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(40, 'Customer 125', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(41, 'Customer 126', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(42, 'Customer 127', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(43, 'Customer 128', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(44, 'Customer 129', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(45, 'Customer 130', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(46, 'Customer 131', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(47, 'Customer 132', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(48, 'Customer 133', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(49, 'Customer 134', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(50, 'Customer 135', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(51, 'Customer 136', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(52, 'Customer 137', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(53, 'Customer 138', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(54, 'Customer 139', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(55, 'Customer 140', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(56, 'Customer 141', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(57, 'Customer 142', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(58, 'Customer 143', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(59, 'Customer 144', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(60, 'Customer 145', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(61, 'Customer 146', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(62, 'Customer 147', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(63, 'Customer 148', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(64, 'Customer 149', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(65, 'Customer 150', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(66, 'Customer 151', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(67, 'Customer 152', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(68, 'Customer 153', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(69, 'Customer 154', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(70, 'Customer 155', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(71, 'Customer 156', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(72, 'Customer 157', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(73, 'Customer 158', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(74, 'Customer 159', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(75, 'Customer 160', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(76, 'Customer 161', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(77, 'Customer 162', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(78, 'Customer 163', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(79, 'Customer 164', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(80, 'Customer 165', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(81, 'Customer 166', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(82, 'Customer 167', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(83, 'Customer 168', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(84, 'Customer 169', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(85, 'Customer 170', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(86, 'Customer 171', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(87, 'Customer 172', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(88, 'Customer 173', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(89, 'Customer 174', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(90, 'Customer 175', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(91, 'Customer 176', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(92, 'Customer 177', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(93, 'Customer 178', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(94, 'Customer 179', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(95, 'Customer 180', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(96, 'Customer 181', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(97, 'Customer 182', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(98, 'Customer 183', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(99, 'Customer 184', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(100, 'Customer 185', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(101, 'Customer 186', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(102, 'Customer 187', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(103, 'Customer 188', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(104, 'Customer 189', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(105, 'Customer 190', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(106, 'Customer 191', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(107, 'Customer 192', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(108, 'Customer 193', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(109, 'Customer 194', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(110, 'Customer 195', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(111, 'Customer 196', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(112, 'Customer 197', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(113, 'Customer 198', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(114, 'Customer 199', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(115, 'Customer 200', 'unspecified', NULL, NULL, '2026-04-20 12:43:10', 0, NULL),
(116, '', 'unspecified', '1985-03-12', NULL, '2026-04-20 12:43:10', 0, NULL),
(117, '', 'unspecified', '1992-07-24', NULL, '2026-04-20 12:43:10', 0, NULL),
(118, '', 'unspecified', '1988-11-05', NULL, '2026-04-20 12:43:10', 0, NULL),
(125, 'Sunita Gupta', 'unspecified', '1978-05-20', NULL, '2026-04-20 12:43:10', 0, NULL),
(126, 'Vikram Singh', 'unspecified', '1982-12-15', NULL, '2026-04-20 12:43:10', 0, NULL),
(127, 'Ananya Rao', 'unspecified', '1995-01-30', NULL, '2026-04-20 12:43:10', 0, NULL),
(128, 'Karan Mehra', 'unspecified', '1990-08-10', NULL, '2026-04-20 12:43:10', 0, NULL),
(9001, 'Rajiv Sharma', 'male', '1985-06-15', NULL, '2026-04-21 04:35:50', 0, NULL),
(9002, 'Priya Patel', 'female', '1990-08-22', NULL, '2026-04-21 04:35:50', 0, NULL),
(9003, 'Amit Kumar', 'male', '1992-11-05', NULL, '2026-04-21 04:35:50', 0, NULL),
(9004, 'Sneha Reddy', 'female', '1995-02-14', NULL, '2026-04-21 04:35:50', 0, NULL),
(9005, 'Vikram Singh', 'male', '1988-09-30', NULL, '2026-04-21 04:35:50', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customer_tags`
--

CREATE TABLE `customer_tags` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_tags`
--

INSERT INTO `customer_tags` (`id`, `user_id`, `tag`) VALUES
(1, 116, 'VIP'),
(2, 116, 'Frequent Buyer'),
(3, 117, 'New Customer'),
(4, 125, 'High Value'),
(5, 125, 'Loyal Customer'),
(6, 126, 'Payment Issues'),
(7, 127, 'Social Media Influencer');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_tracking`
--

CREATE TABLE `delivery_tracking` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `status` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_tracking`
--

INSERT INTO `delivery_tracking` (`id`, `order_id`, `status`, `description`, `location`, `created_at`) VALUES
(1, 90010, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-27 06:12:27'),
(2, 90009, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-27 00:12:27'),
(3, 90009, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-27 05:12:27'),
(4, 90008, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 17:12:27'),
(5, 90008, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 22:12:27'),
(6, 90008, 'DELIVERED', 'Order has been delivered to the customer.', 'Bengaluru, Karnataka', '2026-04-27 03:12:27'),
(7, 90001, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-27 00:12:27'),
(8, 1005, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 18:12:27'),
(9, 1005, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 23:12:27'),
(10, 1004, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 11:12:27'),
(11, 1004, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 16:12:27'),
(12, 1004, 'DELIVERED', 'Order has been delivered to the customer.', 'Lucknow, Uttar Pradesh', '2026-04-26 21:12:27'),
(13, 1003, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 18:12:27'),
(14, 1002, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 12:12:27'),
(15, 1002, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 17:12:27'),
(16, 1001, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 05:12:27'),
(17, 1001, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 10:12:27'),
(18, 1001, 'DELIVERED', 'Order has been delivered to the customer.', 'Chennai, Tamil Nadu', '2026-04-26 15:12:27'),
(19, 91, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 12:12:27'),
(20, 90, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 06:12:27'),
(21, 90, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 11:12:27'),
(22, 89, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-25 23:12:27'),
(23, 89, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 04:12:27'),
(24, 89, 'DELIVERED', 'Order has been delivered to the customer.', 'Ahmedabad, Gujarat', '2026-04-26 09:12:27'),
(25, 88, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 06:12:27'),
(26, 86, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-26 00:12:27'),
(27, 86, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-26 05:12:27'),
(28, 85, 'PLACED', 'Order has been placed successfully.', 'Warehouse', '2026-04-25 17:12:27'),
(29, 85, 'SHIPPED', 'Shipment is out for transit.', 'Sorting Center', '2026-04-25 22:12:27'),
(30, 85, 'DELIVERED', 'Order has been delivered to the customer.', 'New Delhi, Delhi', '2026-04-26 03:12:27');

-- --------------------------------------------------------

--
-- Table structure for table `failed_orders`
--

CREATE TABLE `failed_orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `error_message` text NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `product_id` int(11) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `reserved_stock` int(11) NOT NULL DEFAULT 0,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`product_id`, `stock`, `reserved_stock`, `last_update`) VALUES
(2014, 5, 0, '2026-04-28 11:10:41'),
(2017, 11, 0, '2026-05-07 11:05:43'),
(2018, 20, 0, '2026-05-06 08:03:22'),
(2019, 10, 0, '2026-05-06 08:51:51'),
(2021, 10, 0, '2026-05-06 08:54:34'),
(2026, 10, 0, '2026-05-06 08:58:13'),
(2027, 10, 0, '2026-05-06 08:59:46'),
(2029, 11, 0, '2026-05-06 09:04:37'),
(2031, 22, 0, '2026-05-07 11:05:27');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_logs`
--

CREATE TABLE `inventory_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `previous_stock` int(11) NOT NULL,
  `new_stock` int(11) NOT NULL,
  `change_amount` int(11) NOT NULL,
  `action_type` enum('restock','sale','reservation_hold','reservation_release','manual_override','return') NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `order_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `status` enum('draft','sent','paid','cancelled') DEFAULT 'sent',
  `due_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `invoice_number`, `order_id`, `invoice_date`, `status`, `due_date`, `created_at`) VALUES
(1, 'INV-2026-0001', 30, '2026-04-18', 'sent', NULL, '2026-04-18 11:35:35'),
(3, 'INV-2026-0002', 62, '2026-04-22', 'sent', NULL, '2026-04-22 07:58:26'),
(4, 'INV-2026-0004', 90010, '2026-04-27', 'sent', NULL, '2026-04-27 10:54:22'),
(5, 'INV-2026-0005', 90040, '2026-04-29', 'sent', NULL, '2026-04-29 10:37:42'),
(6, 'INV-2026-0006', 90042, '2026-04-30', 'sent', NULL, '2026-04-30 05:07:28'),
(7, 'INV-2026-0007', 90053, '2026-05-02', 'sent', NULL, '2026-05-02 07:49:02'),
(8, 'INV-2026-0008', 90056, '2026-05-04', 'sent', NULL, '2026-05-04 07:38:07'),
(9, 'INV-2026-0009', 90073, '2026-05-06', 'sent', NULL, '2026-05-06 07:33:44'),
(10, 'INV-2026-0010', 90071, '2026-05-06', 'sent', NULL, '2026-05-06 07:35:33'),
(11, 'INV-2026-0011', 90076, '2026-05-13', 'sent', NULL, '2026-05-13 06:33:19');

-- --------------------------------------------------------

--
-- Table structure for table `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `source` varchar(50) NOT NULL DEFAULT 'footer',
  `subscribed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `newsletter_subscribers`
--

INSERT INTO `newsletter_subscribers` (`id`, `email`, `source`, `subscribed_at`, `is_active`) VALUES
(1, 'esakiraj006@gmail.com', 'checkout', '2026-05-04 16:45:18', 1),
(5, 'demo@gmail.com', 'checkout', '2026-05-05 11:50:29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `news_updates`
--

CREATE TABLE `news_updates` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `publish_date` date NOT NULL,
  `description` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL,
  `idempotency_key` varchar(100) DEFAULT NULL,
  `order_reference` varchar(50) DEFAULT NULL,
  `customer_name` varchar(150) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_id` varchar(100) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `shipping_charges` decimal(10,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 5.00,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','paid','processing','shipped','delivered','cancelled','failed') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','refunded') DEFAULT 'unpaid',
  `payment_method` varchar(50) NOT NULL DEFAULT 'online',
  `shipping_address_id` int(11) DEFAULT NULL,
  `billing_address_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `tracking_id` varchar(100) DEFAULT NULL,
  `delivery_partner` varchar(100) DEFAULT NULL,
  `estimated_delivery_date` date DEFAULT NULL,
  `admin_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `idempotency_key`, `order_reference`, `customer_name`, `total_amount`, `payment_id`, `subtotal`, `discount_amount`, `shipping_charges`, `tax_rate`, `tax_amount`, `status`, `payment_status`, `payment_method`, `shipping_address_id`, `billing_address_id`, `notes`, `tracking_id`, `delivery_partner`, `estimated_delivery_date`, `admin_notes`, `created_at`, `updated_at`) VALUES
(28, 10, 'VKR001', NULL, 'VKR001', 'Rajiv Sharma', 18000.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 04:30:00', '2026-04-27 10:37:09'),
(29, 10, 'VKR002', NULL, 'VKR002', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 04:30:00', '2026-04-27 10:37:09'),
(30, 10, 'VKR003', NULL, 'VKR003', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 04:30:00', '2026-04-27 10:37:09'),
(31, 10, 'VKR004', NULL, 'VKR004', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 04:30:00', '2026-04-27 10:37:09'),
(32, 10, 'VKR005', NULL, 'VKR005', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-10 04:30:00', '2026-04-27 10:37:09'),
(33, 10, 'VKR006', NULL, 'VKR006', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-09 04:30:00', '2026-04-27 10:37:09'),
(34, 10, 'VKR007', NULL, 'VKR007', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-08 04:30:00', '2026-04-27 10:37:09'),
(35, 10, 'VKR008', NULL, 'VKR008', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-07 04:30:00', '2026-04-27 10:37:09'),
(36, 10, 'VKR009', NULL, 'VKR009', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-06 04:30:00', '2026-04-27 10:37:09'),
(37, 10, 'VKR010', NULL, 'VKR010', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-05 04:30:00', '2026-04-27 10:37:09'),
(38, 10, 'VKR011', NULL, 'VKR011', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 04:30:00', '2026-04-27 10:37:09'),
(39, 10, 'VKR012', NULL, 'VKR012', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 04:30:00', '2026-04-27 10:37:09'),
(40, 10, 'VKR013', NULL, 'VKR013', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-02 04:30:00', '2026-04-27 10:37:09'),
(41, 10, 'VKR014', NULL, 'VKR014', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-01 04:30:00', '2026-04-27 10:37:09'),
(42, 10, 'VKR015', NULL, 'VKR015', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-31 04:30:00', '2026-04-27 10:37:09'),
(43, 10, 'VKR016', NULL, 'VKR016', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-30 04:30:00', '2026-04-27 10:37:09'),
(44, 10, 'VKR017', NULL, 'VKR017', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-29 04:30:00', '2026-04-27 10:37:09'),
(45, 10, 'VKR018', NULL, 'VKR018', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 04:30:00', '2026-04-27 10:37:09'),
(46, 10, 'VKR019', NULL, 'VKR019', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-27 04:30:00', '2026-04-27 10:37:09'),
(47, 10, 'VKR020', NULL, 'VKR020', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 04:30:00', '2026-04-27 10:37:09'),
(48, 10, 'VKR021', NULL, 'VKR021', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-25 04:30:00', '2026-04-27 10:37:09'),
(49, 10, 'VKR022', NULL, 'VKR022', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-24 04:30:00', '2026-04-27 10:37:09'),
(50, 10, 'VKR023', NULL, 'VKR023', 'Rajiv Sharma', 210.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-23 04:30:00', '2026-04-27 10:37:09'),
(51, 10, 'VKR024', NULL, 'VKR024', 'Rajiv Sharma', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-22 04:30:00', '2026-04-27 10:37:09'),
(52, 11, 'VKN001', NULL, 'VKN001', 'Neha Patel', 5130.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-21 04:30:00', '2026-04-27 10:37:09'),
(53, 11, 'VKN002', NULL, 'VKN002', 'Neha Patel', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-20 04:30:00', '2026-04-27 10:37:09'),
(54, 11, 'VKN003', NULL, 'VKN003', 'Neha Patel', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-19 04:30:00', '2026-04-27 10:37:09'),
(55, 11, 'VKN004', NULL, 'VKN004', 'Neha Patel', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-18 04:30:00', '2026-04-27 10:37:09'),
(56, 11, 'VKN005', NULL, 'VKN005', 'Neha Patel', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-17 04:30:00', '2026-04-27 10:37:09'),
(57, 11, 'VKN006', NULL, 'VKN006', 'Neha Patel', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-16 04:30:00', '2026-04-27 10:37:09'),
(58, 11, 'VKN007', NULL, 'VKN007', 'Neha Patel', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-15 04:30:00', '2026-04-27 10:37:09'),
(59, 11, 'VKN008', NULL, 'VKN008', 'Neha Patel', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-14 04:30:00', '2026-04-27 10:37:09'),
(60, 12, 'VKA001', NULL, 'VKA001', 'Amit Verma', 450.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 04:30:00', '2026-04-27 10:37:09'),
(61, 13, 'VKP001', NULL, 'VKP001', 'Priya Desai', 9670.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-11 04:30:00', '2026-04-27 10:37:09'),
(62, 13, 'VKP002', NULL, 'VKP002', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-10 04:30:00', '2026-04-27 10:37:09'),
(63, 13, 'VKP003', NULL, 'VKP003', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-09 04:30:00', '2026-04-27 10:37:09'),
(64, 13, 'VKP004', NULL, 'VKP004', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-08 04:30:00', '2026-04-27 10:37:09'),
(65, 13, 'VKP005', NULL, 'VKP005', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-07 04:30:00', '2026-04-27 10:37:09'),
(66, 13, 'VKP006', NULL, 'VKP006', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-06 04:30:00', '2026-04-27 10:37:09'),
(67, 13, 'VKP007', NULL, 'VKP007', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-05 04:30:00', '2026-04-27 10:37:09'),
(68, 13, 'VKP008', NULL, 'VKP008', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-04 04:30:00', '2026-04-27 10:37:09'),
(69, 13, 'VKP009', NULL, 'VKP009', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-03 04:30:00', '2026-04-27 10:37:09'),
(70, 13, 'VKP010', NULL, 'VKP010', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-02 04:30:00', '2026-04-27 10:37:09'),
(71, 13, 'VKP011', NULL, 'VKP011', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-01 04:30:00', '2026-04-27 10:37:09'),
(72, 13, 'VKP012', NULL, 'VKP012', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-31 04:30:00', '2026-04-27 10:37:09'),
(73, 13, 'VKP013', NULL, 'VKP013', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-30 04:30:00', '2026-04-27 10:37:09'),
(74, 13, 'VKP014', NULL, 'VKP014', 'Priya Desai', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-29 04:30:00', '2026-04-27 10:37:09'),
(75, 14, 'VKSJ001', NULL, 'VKSJ001', 'Sanjay Kumar', 1230.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-28 04:30:00', '2026-04-27 10:37:09'),
(76, 14, 'VKSJ002', NULL, 'VKSJ002', 'Sanjay Kumar', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-27 04:30:00', '2026-04-27 10:37:09'),
(77, 14, 'VKSJ003', NULL, 'VKSJ003', 'Sanjay Kumar', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 04:30:00', '2026-04-27 10:37:09'),
(78, 15, 'VKRS001', NULL, 'VKRS001', 'Rakesh Singh', 880.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-25 04:30:00', '2026-04-27 10:37:09'),
(79, 15, 'VKRS002', NULL, 'VKRS002', 'Rakesh Singh', 10.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-24 04:30:00', '2026-04-27 10:37:09'),
(84, 116, 'ORD-2024-001', NULL, 'ORD-2024-001', '', 1250.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-10 11:21:57', '2026-04-27 10:37:09'),
(85, 116, 'ORD-2024-002', NULL, 'ORD-2024-002', '', 3400.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-26 11:21:57', '2026-04-27 10:37:09'),
(86, 117, 'ORD-2024-003', NULL, 'ORD-2024-003', '', 850.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 11:21:57', '2026-04-27 10:37:09'),
(88, 125, 'ORD-2024-010', NULL, 'ORD-2024-010', 'Sunita Gupta', 5600.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-03-06 11:30:27', '2026-04-27 10:37:09'),
(89, 125, 'ORD-2024-015', NULL, 'ORD-2024-015', 'Sunita Gupta', 2100.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-15 11:30:27', '2026-04-27 10:37:09'),
(90, 127, 'ORD-2024-020', NULL, 'ORD-2024-020', 'Ananya Rao', 450.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'refunded', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-18 11:30:27', '2026-04-27 10:37:09'),
(91, 128, 'ORD-2024-025', NULL, 'ORD-2024-025', 'Karan Mehra', 1800.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'paid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-19 11:30:27', '2026-04-27 10:37:09'),
(1001, 1, 'ORD-2026-001', NULL, 'ORD-2026-001', 'Kevin Rajput', 850.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'processing', 'unpaid', 'online', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-20 07:22:46', '2026-04-27 10:37:09'),
(1002, 1, 'ORD-2026-002', NULL, 'ORD-2026-002', 'Kevin Rajput', 1250.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'shipped', 'unpaid', 'online', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-17 07:22:46', '2026-04-27 10:37:09'),
(1003, 1, 'ORD-2026-003', NULL, 'ORD-2026-003', 'Kevin Rajput', 450.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'delivered', 'unpaid', 'online', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-12 07:22:46', '2026-04-27 10:37:09'),
(1004, 1, 'ORD-2026-004', NULL, 'ORD-2026-004', 'Kevin Rajput', 2100.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'delivered', 'unpaid', 'online', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-07 07:22:46', '2026-04-27 10:37:09'),
(1005, 1, 'ORD-2026-005', NULL, 'ORD-2026-005', 'Kevin Rajput', 950.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'cancelled', 'unpaid', 'online', 1, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-02 07:22:46', '2026-04-27 10:37:09'),
(90001, 9001, '', NULL, '', 'Mira Chawla', 520.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-01-11 04:35:50', '2026-04-27 10:41:04'),
(90008, 9007, 'SW-ORD-41CD9F-20260422', NULL, 'SW-ORD-41CD9F-20260422', 'esakiraj', 2140.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'delivered', 'paid', 'online', NULL, NULL, NULL, '', '', '0000-00-00', '', '2026-04-22 08:00:52', '2026-04-27 10:37:09'),
(90009, 9007, 'SW-ORD-F98BAE-20260422', NULL, 'SW-ORD-F98BAE-20260422', 'esakiraj', 2939.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-22 08:29:19', '2026-04-27 10:37:09'),
(90010, 9007, 'SW-ORD-F93386-20260422', NULL, 'SW-ORD-F93386-20260422', 'esakiraj', 700.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-22 08:54:55', '2026-04-27 10:37:09'),
(90034, 9008, 'SW-3AC805E2-20260427', NULL, NULL, NULL, 2550.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'cancelled', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 14:30:08', '2026-04-29 09:12:50'),
(90035, 9008, 'SW-3DEBDBCA-20260427', NULL, NULL, NULL, 3430.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'shipped', 'paid', 'online', NULL, NULL, NULL, '', '', '0000-00-00', '', '2026-04-27 14:32:07', '2026-04-27 14:34:20'),
(90036, 9008, 'SW-1AA42CFB-20260428', NULL, NULL, NULL, 3850.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'cancelled', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-28 08:58:36', '2026-04-29 08:36:01'),
(90037, 9008, 'SW-E171DDC9-20260428', NULL, 'pay_SirApqraLAKZs6', NULL, 3850.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-28 09:04:24', '2026-04-28 09:04:24'),
(90038, 9008, 'SW-C0438CAA-20260428', NULL, 'pay_Sit6a78UhoF3gG', NULL, 340.00, NULL, 290.00, 0.00, 50.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 9, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-28 10:57:46', '2026-04-28 10:57:46'),
(90039, 9008, 'SW-A336DF85-20260428', NULL, 'pay_SixPhtSg1Qoinw', NULL, 5429.00, NULL, 5529.00, 100.00, 0.00, 5.00, 0.00, 'delivered', 'paid', 'razorpay', 10, NULL, NULL, '', '', '0000-00-00', '', '2026-04-28 15:10:50', '2026-04-29 08:29:51'),
(90040, 9008, 'SW-46D1927A-20260429', NULL, 'pay_SjHCkLWi1Xxj7k', NULL, 700.00, NULL, 650.00, 0.00, 50.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 11, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-29 10:32:13', '2026-04-29 10:32:13'),
(90041, 9008, 'SW-46FE132A-20260430', NULL, 'pay_SjZoXZQdgEcK8y', NULL, 700.00, NULL, 650.00, 0.00, 50.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 12, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30 04:44:34', '2026-04-30 04:44:34'),
(90042, 9008, 'SW-B612E64B-20260430', NULL, 'pay_SjaCIz2eVxqUKB', NULL, 365.00, NULL, 315.00, 0.00, 50.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 13, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30 05:07:01', '2026-04-30 05:07:01'),
(90043, 9012, 'SW-3135DD65-20260430', NULL, NULL, NULL, 2100.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30 12:53:57', '2026-04-30 12:53:57'),
(90044, 9012, 'SW-00F6096C-20260430', NULL, NULL, NULL, 2100.00, 'pay_SjiELS77gUuboi', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30 12:58:30', '2026-04-30 12:58:30'),
(90045, 9012, 'SW-448422CC-20260430', NULL, NULL, NULL, 450.00, 'pay_SjiFd7fzNHJr8m', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-30 12:59:46', '2026-04-30 12:59:46'),
(90047, 9010, 'SW-D5EBB993-20260502', NULL, NULL, NULL, 1440.00, 'pay_SkOmebS5aWJdik', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 06:36:07', '2026-05-02 06:36:07'),
(90048, 9010, 'SW-3F6718CA-20260502', NULL, NULL, NULL, 290.00, 'pay_SkOuUpFivzp5je', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 06:43:57', '2026-05-02 06:43:57'),
(90049, 9010, 'SW-45E066E3-20260502', NULL, NULL, ' ', 290.00, 'pay_SkPGITSFB13rMU', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 07:04:09', '2026-05-02 07:04:09'),
(90050, 9010, 'SW-50A72E22-20260502', NULL, NULL, ' ', 290.00, 'pay_SkPHS0LQ92SdD9', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 07:05:17', '2026-05-02 07:05:17'),
(90051, 9010, 'SW-04145791-20260502', NULL, NULL, 'Guest Customer', 799.00, 'pay_SkPcu7Rj14QJuN', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 07:25:33', '2026-05-02 07:25:33'),
(90052, 9010, 'SW-D24FA4F7-20260502', NULL, NULL, 'Guest Customer', 650.00, 'pay_SkPkxRo4GTP4V2', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-02 07:33:10', '2026-05-02 07:33:10'),
(90053, 9010, 'SW-7D809D0A-20260502', NULL, NULL, 'john raja', 315.00, 'pay_SkPtbC0elfZ6F8', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 14, 14, NULL, NULL, NULL, NULL, NULL, '2026-05-02 07:41:20', '2026-05-02 07:41:20'),
(90054, 9010, 'SW-1755F935-20260502', NULL, NULL, 'john raja', 2739.00, 'pay_SkVfd2LtubeAf3', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 15, 15, NULL, NULL, NULL, NULL, NULL, '2026-05-02 13:20:18', '2026-05-02 13:20:18'),
(90055, 9010, 'SW-29236124-20260502', NULL, NULL, 'john raja', 999.00, 'pay_SkVpKK5QDTCTGK', 0.00, 0.00, 0.00, 5.00, 0.00, 'delivered', 'paid', 'razorpay', 16, 16, NULL, '', '', '0000-00-00', '', '2026-05-02 13:29:28', '2026-05-05 16:27:25'),
(90056, 9007, 'SW-007A6EC0-20260504', NULL, NULL, 'esakiraj', 4637.00, 'pay_Sl9YiloUJlj7kv', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 17, 17, NULL, NULL, NULL, NULL, NULL, '2026-05-04 04:21:45', '2026-05-04 04:21:45'),
(90057, 9008, 'SW-8B283FEB-20260504', NULL, NULL, 'esakiraj raja', 3680.00, 'pay_SlDet2BXHsjEul', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 18, 18, NULL, NULL, NULL, NULL, NULL, '2026-05-04 08:22:19', '2026-05-04 08:22:19'),
(90058, 9013, 'SW-58034B92-20260504', NULL, NULL, 'raj', 650.00, 'pay_SlFwH2C250Xm32', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 19, 19, NULL, NULL, NULL, NULL, NULL, '2026-05-04 10:36:01', '2026-05-04 10:36:01'),
(90059, 9013, 'SW-C5E4156E-20260504', NULL, NULL, 'raj', 870.00, 'pay_SlG4wMFjYhrnS1', 0.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 20, 20, NULL, NULL, NULL, NULL, NULL, '2026-05-04 10:44:12', '2026-05-04 10:44:12'),
(90060, 9008, 'SW-ORD-8E2B61-20260504', NULL, NULL, NULL, 849.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', 21, 21, NULL, NULL, NULL, NULL, NULL, '2026-05-04 11:15:36', '2026-05-04 11:15:36'),
(90061, 9008, 'SW-ORD-13680C-20260504', NULL, NULL, NULL, 340.00, NULL, 0.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', 22, 22, NULL, NULL, NULL, NULL, NULL, '2026-05-04 11:16:49', '2026-05-04 11:16:49'),
(90062, 9008, 'SW-06EA52EC-20260504', NULL, NULL, 'esakiraj raja', 799.00, 'pay_SlGpp3sVAyRYw5', 0.00, 0.00, 0.00, 5.00, 0.00, 'delivered', 'paid', 'razorpay', 23, 23, NULL, '', '', '0000-00-00', '', '2026-05-04 11:28:50', '2026-05-05 16:27:33'),
(90063, 9008, 'SW-79FD5BC5-20260504', NULL, NULL, 'esakiraj raja', 270.00, 'pay_SlH8n59PH6s3we', 220.00, 0.00, 50.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 24, 24, NULL, NULL, NULL, NULL, NULL, '2026-05-04 11:46:31', '2026-05-04 11:46:31'),
(90064, 9008, 'SW-2EBD3E3D-20260504', NULL, NULL, 'esakiraj', 849.00, 'pay_SlHh3V9MRJvFQu', 799.00, 0.00, 50.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 25, 25, NULL, NULL, NULL, NULL, NULL, '2026-05-04 12:19:02', '2026-05-04 12:19:02'),
(90065, 9007, 'SW-9B7EE23C-20260505', NULL, NULL, 'esakiraj raja', 1950.00, NULL, 1950.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 06:33:33', '2026-05-05 06:33:33'),
(90066, 9007, 'SW-BD5587B1-20260505', NULL, NULL, 'esakiraj raja', 1950.00, NULL, 1950.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 06:33:48', '2026-05-05 06:33:48'),
(90067, 9007, 'SW-0B5D8239-20260505', NULL, NULL, 'esakiraj raja', 1950.00, NULL, 1950.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 06:33:51', '2026-05-05 06:33:51'),
(90068, 9007, 'SW-82421FC3-20260505', 'order_SlahPLn72NZ0OE', NULL, 'esakiraj raja', 1950.00, NULL, 1950.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 06:54:14', '2026-05-05 06:54:14'),
(90069, 9007, 'SW-DEC33CE6-20260505', 'order_SlaiepiG6hV5vt', NULL, 'esakiraj raja', 1950.00, NULL, 1950.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 06:55:25', '2026-05-05 06:55:25'),
(90070, 9007, 'SW-370E6AEA-20260505', 'order_SledkdIcxw3oP9', NULL, 'esakiraj raja', 7160.00, NULL, 7160.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 10:45:30', '2026-05-05 10:45:30'),
(90071, 9007, 'SW-B9EEB49B-20260505', 'order_SletgZx1uZmPKv', NULL, 'esakiraj raja', 7160.00, NULL, 7160.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 11:00:35', '2026-05-05 11:00:35'),
(90072, 9007, 'SW-CC7C1B69-20260505', 'order_SleuKMew8KXz42', NULL, 'esakiraj raja', 7160.00, NULL, 7160.00, 0.00, 0.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 11:01:12', '2026-05-05 11:01:12'),
(90073, 2, 'SW-80AF5492-20260505', 'order_Slk1Xe0n6xWtuE', NULL, 'esakiraj raja', 50.00, NULL, 0.00, 0.00, 50.00, 5.00, 0.00, 'pending', 'unpaid', 'online', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-05 16:01:30', '2026-05-05 16:01:30'),
(90074, 9008, 'SW-1FF1200B-20260510', 'order_SnjO6qTxnhvImC', NULL, 'esakiraj raja', 3000.00, 'pay_SnjOJ9mABc2bXi', 3000.00, 0.00, 0.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-05-10 16:42:22', '2026-05-10 16:42:55'),
(90075, 9014, 'SW-F0F393BE-20260511', 'order_Sny2KHllgZ9i4W', NULL, 'nalej raja', 300.00, 'pay_Sny2jWJRbDO4nY', 250.00, 0.00, 50.00, 5.00, 0.00, 'paid', 'paid', 'razorpay', 28, 28, NULL, NULL, NULL, NULL, NULL, '2026-05-11 07:02:10', '2026-05-11 07:02:48'),
(90076, 9015, 'SW-68F5D9C8-20260511', 'order_SnyzfofYdMQ08c', NULL, 'nalej raja', 700.00, 'pay_SnyzpWnzZT7MN1', 650.00, 0.00, 50.00, 5.00, 0.00, 'shipped', 'paid', 'razorpay', 30, 30, NULL, '', '', '0000-00-00', '', '2026-05-11 07:58:21', '2026-05-11 08:17:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_type` enum('product','combo') DEFAULT 'product',
  `product_id` int(11) DEFAULT NULL,
  `combo_id` int(11) DEFAULT NULL,
  `variant_id` int(11) DEFAULT 0,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `price_at_time` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_type`, `product_id`, `combo_id`, `variant_id`, `quantity`, `price`, `price_at_time`) VALUES
(10, 84, 'product', 1001, NULL, 0, 2, 500.00, 500.00),
(2001, 1001, 'product', 1, NULL, 0, 1, 850.00, 850.00),
(2002, 1002, 'product', 2, NULL, 0, 2, 625.00, 625.00),
(2003, 1003, 'product', 3, NULL, 0, 1, 450.00, 450.00),
(2004, 1004, 'product', 1, NULL, 0, 2, 850.00, 850.00),
(2005, 1004, 'product', 2, NULL, 0, 1, 400.00, 400.00),
(2006, 1005, 'product', 3, NULL, 0, 2, 475.00, 475.00),
(2007, 90008, 'product', 1005, NULL, 0, 3, 650.00, 650.00),
(2008, 90008, 'product', 2006, NULL, 0, 1, 290.00, 290.00),
(2009, 90009, 'product', 1005, NULL, 0, 3, 650.00, 650.00),
(2010, 90009, 'product', 2006, NULL, 0, 1, 290.00, 290.00),
(2011, 90009, 'product', 1004, NULL, 0, 1, 799.00, 799.00),
(2012, 90010, 'product', 1005, NULL, 0, 1, 650.00, 650.00),
(2013, 90034, 'product', 1002, NULL, 0, 1, 600.00, 0.00),
(2014, 90034, 'product', 1005, NULL, 0, 2, 650.00, 0.00),
(2015, 90034, 'product', 1005, NULL, 0, 1, 650.00, 0.00),
(2016, 90035, 'product', 1002, NULL, 0, 1, 600.00, 0.00),
(2017, 90035, 'product', 1005, NULL, 0, 2, 650.00, 0.00),
(2018, 90035, 'product', 1005, NULL, 0, 1, 650.00, 0.00),
(2019, 90035, 'product', 1004, NULL, 0, 1, 880.00, 0.00),
(2020, 90036, 'product', 1002, NULL, 0, 1, 600.00, 0.00),
(2021, 90036, 'product', 1005, NULL, 0, 2, 650.00, 0.00),
(2022, 90036, 'product', 1005, NULL, 0, 1, 650.00, 0.00),
(2023, 90036, 'product', 1004, NULL, 0, 1, 880.00, 0.00),
(2024, 90036, 'product', 1003, NULL, 0, 1, 420.00, 0.00),
(2025, 90037, 'product', 1002, NULL, 0, 1, 600.00, 0.00),
(2026, 90037, 'product', 1005, NULL, 0, 2, 650.00, 0.00),
(2027, 90037, 'product', 1005, NULL, 0, 1, 650.00, 0.00),
(2028, 90037, 'product', 1004, NULL, 0, 1, 880.00, 0.00),
(2029, 90037, 'product', 1003, NULL, 0, 1, 420.00, 0.00),
(2030, 90038, 'product', 2006, NULL, 0, 1, 320.00, 320.00),
(2031, 90039, 'product', 1002, NULL, 0, 2, 600.00, 600.00),
(2032, 90039, 'product', 1005, NULL, 0, 3, 650.00, 650.00),
(2033, 90039, 'product', 1005, NULL, 0, 1, 650.00, 650.00),
(2034, 90039, 'product', 1004, NULL, 0, 1, 880.00, 880.00),
(2035, 90039, 'product', 1003, NULL, 0, 2, 420.00, 420.00),
(2036, 90039, 'product', 2006, NULL, 0, 1, 320.00, 320.00),
(2037, 90040, 'product', 1005, NULL, 0, 1, 650.00, 650.00),
(2038, 90041, 'product', 1001, NULL, 0, 1, 720.00, 720.00),
(2039, 90042, 'product', 2004, NULL, 0, 1, 350.00, 350.00),
(2040, 52, 'combo', NULL, 1, 0, 1, 1200.00, 1200.00),
(2041, 52, 'product', 1001, NULL, 0, 1, 720.00, 720.00),
(2042, 90043, 'combo', NULL, 9, 0, 1, 500.00, 500.00),
(2043, 90043, 'combo', NULL, 10, 0, 2, 800.00, 800.00),
(2044, 90044, 'combo', NULL, 9, 0, 1, 500.00, 500.00),
(2045, 90044, 'combo', NULL, 10, 0, 2, 800.00, 800.00),
(2046, 90045, 'combo', NULL, 2, 0, 1, 450.00, 450.00),
(2049, 90047, 'combo', NULL, 9, 0, 1, 500.00, 500.00),
(2050, 90047, 'product', 2006, NULL, 23, 1, 290.00, 290.00),
(2051, 90047, 'product', 1005, NULL, 8, 1, 650.00, 650.00),
(2052, 90048, 'product', 2005, NULL, 22, 1, 290.00, 290.00),
(2053, 90049, 'product', 2005, NULL, 22, 1, 290.00, 290.00),
(2054, 90050, 'product', 2005, NULL, 22, 1, 290.00, 290.00),
(2055, 90051, 'product', 1004, NULL, 7, 1, 799.00, 799.00),
(2056, 90052, 'product', 1005, NULL, 8, 1, 650.00, 650.00),
(2057, 90053, 'product', 2004, NULL, 21, 1, 315.00, 315.00),
(2058, 90054, 'combo', NULL, 9, 0, 1, 500.00, 500.00),
(2059, 90054, 'product', 2006, NULL, 23, 1, 290.00, 290.00),
(2060, 90054, 'product', 1005, NULL, 8, 1, 650.00, 650.00),
(2061, 90054, 'combo', NULL, 13, 0, 1, 1299.00, 1299.00),
(2062, 90055, 'combo', NULL, 11, 0, 1, 999.00, 999.00),
(2063, 90056, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2064, 90056, 'product', 2006, NULL, 23, 1, 290.00, 290.00),
(2065, 90056, 'product', 1004, NULL, 7, 3, 799.00, 799.00),
(2066, 90057, 'product', 1002, NULL, 5, 2, 540.00, 540.00),
(2067, 90057, 'product', 1005, NULL, 8, 4, 650.00, 650.00),
(2068, 90058, 'product', 1005, NULL, 8, 1, 650.00, 650.00),
(2069, 90059, 'product', 2005, NULL, 22, 3, 290.00, 290.00),
(2070, 90060, 'product', 1004, NULL, 0, 1, 799.00, 799.00),
(2071, 90061, 'product', 2005, NULL, 0, 1, 290.00, 290.00),
(2072, 90062, 'product', 1004, NULL, 7, 1, 799.00, 799.00),
(2073, 90063, 'product', 2003, NULL, 20, 1, 220.00, 220.00),
(2074, 90064, 'product', 1004, NULL, 7, 1, 799.00, 799.00),
(2075, 90065, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2076, 90066, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2077, 90067, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2078, 90068, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2079, 90069, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2080, 90070, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2081, 90070, 'product', 2006, NULL, 23, 1, 320.00, 320.00),
(2082, 90070, 'product', 1004, NULL, 7, 4, 880.00, 880.00),
(2083, 90070, 'product', 1040, NULL, 14, 1, 950.00, 950.00),
(2084, 90070, 'product', 1003, NULL, 6, 1, 420.00, 420.00),
(2085, 90071, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2086, 90071, 'product', 2006, NULL, 23, 1, 320.00, 320.00),
(2087, 90071, 'product', 1004, NULL, 7, 4, 880.00, 880.00),
(2088, 90071, 'product', 1040, NULL, 14, 1, 950.00, 950.00),
(2089, 90071, 'product', 1003, NULL, 6, 1, 420.00, 420.00),
(2090, 90072, 'product', 1005, NULL, 8, 3, 650.00, 650.00),
(2091, 90072, 'product', 2006, NULL, 23, 1, 320.00, 320.00),
(2092, 90072, 'product', 1004, NULL, 7, 4, 880.00, 880.00),
(2093, 90072, 'product', 1040, NULL, 14, 1, 950.00, 950.00),
(2094, 90072, 'product', 1003, NULL, 6, 1, 420.00, 420.00),
(2095, 90074, 'combo', NULL, 7, 0, 1, 3000.00, 3000.00),
(2096, 90075, 'product', 2029, NULL, 26, 1, 250.00, 250.00),
(2097, 90076, 'product', 1005, NULL, 8, 1, 650.00, 650.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `otp` char(6) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token_hash`, `otp`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 'demo@gmail.com', '34e71823354fd72d768f164ccf3da0eb321b25a47292758e351def9e8afefc4d', NULL, '2026-04-27 16:46:04', '2026-04-27 16:16:28', '2026-04-27 10:46:04'),
(2, 'esakiraj006@gmail.com', 'f8f855a7f3fa46de84046e87e3df0ccf9361b705561cadeb0fc9359a0f310d8d', NULL, '2026-04-27 16:55:22', '2026-04-27 16:25:40', '2026-04-27 10:55:22'),
(18, 'esakiraj006@gmail.com', '6eeb14cfb0866f43bb67da239ad611d07a275cf7d26be37dd34fa020097784a1', '501529', '2026-04-27 17:00:22', '2026-04-27 16:45:38', '2026-04-27 11:15:22'),
(20, 'esakiraj006@gmail.com', '02d837ed6f16e1b6e171137157c1365b9b0e3e2d433c1bbd0e341ac6eb397ae8', '170945', '2026-04-27 17:01:19', '2026-04-27 16:46:41', '2026-04-27 11:16:19'),
(21, 'esakiraj006@gmail.com', '846401fb8fe8c0b312600fb9aa23d2b15c1f00b4c1f0febf5f8ad313eef06061', '474687', '2026-04-27 17:02:10', '2026-04-27 16:47:32', '2026-04-27 11:17:10'),
(22, 'esakiraj006@gmail.com', 'ae02dd87d475c923e23e4e8580fa06d2dae01902d0043988392c2d23344d6486', '701396', '2026-04-27 19:47:17', '2026-04-27 19:32:41', '2026-04-27 14:02:17'),
(23, 'john@gmail.com', '8beecc12b5c60a6d5c393906497bde603cae9cde37c31a9f65ad1960e878a0e5', '323694', '2026-05-02 11:50:39', '2026-05-02 11:36:00', '2026-05-02 06:05:39'),
(24, 'esakiraj006@gmail.com', '6c0ab362a30611215e18d7b6073c620fb4da5347e2203e6859804f3cd61d35ef', '205756', '2026-05-04 13:44:35', '2026-05-04 13:30:11', '2026-05-04 07:59:35'),
(25, 'esakiraj006@gmail.com', '7b5841164cf32aae73cc6b27957f1b8660367a8176dd6fcb420d2afee3503727', '551844', '2026-05-04 18:01:43', '2026-05-04 17:46:59', '2026-05-04 12:16:43'),
(26, 'esakiraj006@gmail.com', 'deaa0255d303b5b8a6f1d34c4a265562b8f375e40a001ac79ce87aedaf53c152', '900493', '2026-05-11 13:41:25', NULL, '2026-05-11 07:56:25');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `gateway` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'INR',
  `status` enum('initiated','success','failed','refunded') NOT NULL,
  `raw_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `gateway`, `transaction_id`, `amount`, `currency`, `status`, `raw_response`, `created_at`) VALUES
(1, 90044, 'razorpay', 'pay_SjiELS77gUuboi', 2000.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SjiELS77gUuboi\",\"razorpay_order_id\":\"order_SjiEC2jXr9nBaC\",\"razorpay_signature\":\"79afe7b5d2a3a51c192af2c8829ca9e93ec7004b188d4212b6b0c8fc58f48d8f\"}', '2026-04-30 12:58:30'),
(2, 90045, 'razorpay', 'pay_SjiFd7fzNHJr8m', 500.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SjiFd7fzNHJr8m\",\"razorpay_order_id\":\"order_SjiFS99rFM1OVj\",\"razorpay_signature\":\"47d456d2e0d948317dc75d864ebcde1153d3a7966f75554288a0dfbcac0ec07f\"}', '2026-04-30 12:59:46'),
(3, 90046, 'razorpay', 'pay_SkOQwjKScDhQiH', 840.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkOQwjKScDhQiH\",\"razorpay_order_id\":\"order_SkOQohMB1BxzAi\",\"razorpay_signature\":\"eaaafe2f61b3061d0095de9ec322111dbbe3fb402425e4eb764bd4d2a8718689\"}', '2026-05-02 06:15:32'),
(4, 90047, 'razorpay', 'pay_SkOmebS5aWJdik', 1440.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkOmebS5aWJdik\",\"razorpay_order_id\":\"order_SkOmaCQ7RhYVVk\",\"razorpay_signature\":\"431adad4bb119932709e1e82f87a266c9b713c429c2993980c0bca88256bfa8c\"}', '2026-05-02 06:36:07'),
(5, 90048, 'razorpay', 'pay_SkOuUpFivzp5je', 340.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkOuUpFivzp5je\",\"razorpay_order_id\":\"order_SkOuNsvebgLU5X\",\"razorpay_signature\":\"e179b482f3444d3dba88e2ed17dffd6aaeedbb67f1aebcabaeb9b91b21fc5d8b\"}', '2026-05-02 06:43:57'),
(6, 90049, 'razorpay', 'pay_SkPGITSFB13rMU', 340.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkPGITSFB13rMU\",\"razorpay_order_id\":\"order_SkPGCvtfBnDzJG\",\"razorpay_signature\":\"d83b60cb2d3c5b6a7e9c3ec0146eadf2966ed02dffa61097181826efc3c813b3\"}', '2026-05-02 07:04:09'),
(7, 90050, 'razorpay', 'pay_SkPHS0LQ92SdD9', 340.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkPHS0LQ92SdD9\",\"razorpay_order_id\":\"order_SkPHMjOvvFLWXc\",\"razorpay_signature\":\"fac54b9925558f54f1651c2465d593a357f32d8ede8210f8097919b57e342eef\"}', '2026-05-02 07:05:17'),
(8, 90051, 'razorpay', 'pay_SkPcu7Rj14QJuN', 849.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkPcu7Rj14QJuN\",\"razorpay_order_id\":\"order_SkPOo39AV5atDy\",\"razorpay_signature\":\"03b24416a533b19175e040f4e94adff2b0571e4fd70494b22fd234ac55a86664\"}', '2026-05-02 07:25:33'),
(9, 90052, 'razorpay', 'pay_SkPkxRo4GTP4V2', 700.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkPkxRo4GTP4V2\",\"razorpay_order_id\":\"order_SkPkrEHPjMt00n\",\"razorpay_signature\":\"b9f6865cc65b9357377317030ed349e3e50db349a3d233fa6da612931c32bbdf\"}', '2026-05-02 07:33:10'),
(10, 90053, 'razorpay', 'pay_SkPtbC0elfZ6F8', 365.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkPtbC0elfZ6F8\",\"razorpay_order_id\":\"order_SkPtUdBaSn14Zl\",\"razorpay_signature\":\"8b3a27724ad28201a4d39818239772e3c6561fe8ea1aff99da5255a51af48e68\",\"checkout_data\":{\"email\":\"john@gmail.com\",\"country\":\"India\",\"first_name\":\"john\",\"last_name\":\"raja\",\"address\":\"wqdwqfwqfwq\",\"city\":\"sivakasi\",\"state\":\"TAMIL NADU\",\"pin_code\":\"658623\",\"phone\":\"8221633689\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-02 07:41:20'),
(11, 90054, 'razorpay', 'pay_SkVfd2LtubeAf3', 2639.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkVfd2LtubeAf3\",\"razorpay_order_id\":\"order_SkVfMgGbblHZw8\",\"razorpay_signature\":\"5ae890ea69dcf163dc3b6549f39ca993d2f7e706c198deb536c3093f372ef538\",\"checkout_data\":{\"email\":\"john@gmail.com\",\"country\":\"India\",\"first_name\":\"john\",\"last_name\":\"raja\",\"address\":\"weegewgegweg\",\"city\":\"sivakasi\",\"state\":\"TAMIL NADU\",\"pin_code\":\"658622\",\"phone\":\"8221633689\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-02 13:20:18'),
(12, 90055, 'razorpay', 'pay_SkVpKK5QDTCTGK', 1049.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SkVpKK5QDTCTGK\",\"razorpay_order_id\":\"order_SkVpG1tcR0KM4X\",\"razorpay_signature\":\"9626809801664ad16c03bfca79d2471fcd858870ad20486dcf7aa54b76d03004\",\"checkout_data\":{\"email\":\"john@gmail.com\",\"country\":\"India\",\"first_name\":\"john\",\"last_name\":\"raja\",\"address\":\"wqdwqfwqfwq\",\"city\":\"sivakasi\",\"state\":\"TAMIL NADU\",\"pin_code\":\"658623\",\"phone\":\"8221633689\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-02 13:29:28'),
(13, 90056, 'razorpay', 'pay_Sl9YiloUJlj7kv', 4537.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_Sl9YiloUJlj7kv\",\"razorpay_order_id\":\"order_Sl9YY39VloM5R0\",\"razorpay_signature\":\"df4570bfc29ae748e729b239424a238b5db056ca5bdf4bd522a90457cb7d4bf7\",\"checkout_data\":{\"email\":\"demo@gmail.com\",\"country\":\"India\",\"first_name\":\"esakiraj\",\"last_name\":\"\",\"address\":\"eededwdw\",\"city\":\"madurai\",\"state\":\"TAMIL NADU\",\"pin_code\":\" 625001\",\"phone\":\"9047478886\",\"save_info\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-04 04:21:45'),
(14, 90057, 'razorpay', 'pay_SlDet2BXHsjEul', 3580.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SlDet2BXHsjEul\",\"razorpay_order_id\":\"order_SlDeN4yBQ54fSX\",\"razorpay_signature\":\"2c1aa3effed734d890e5d09fc0e455e5e12de410c055d99c6cd80316c120bb60\",\"checkout_data\":{\"email\":\"esakiraj006@gmail.com\",\"country\":\"India\",\"first_name\":\"esakiraj\",\"last_name\":\"raja\",\"address\":\"scscscs\",\"city\":\"sivakasi\",\"state\":\"TAMIL NADU\",\"pin_code\":\"625001\",\"phone\":\"9047478886\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"netbanking\"}}', '2026-05-04 08:22:19'),
(15, 90058, 'razorpay', 'pay_SlFwH2C250Xm32', 700.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SlFwH2C250Xm32\",\"razorpay_order_id\":\"order_SlFw34dOSFSbaX\",\"razorpay_signature\":\"5cda7fe18d96e0761273cfd29dc6ebd60e1f5c21ef9d179202f34c266e405aa4\",\"checkout_data\":{\"email\":\"raj@gmail.com\",\"email_subscribe\":\"1\",\"country\":\"India\",\"first_name\":\"raj\",\"last_name\":\"\",\"address\":\"eededwdw\",\"city\":\"madurai\",\"state\":\"TAMIL NADU\",\"pin_code\":\" 625001\",\"phone\":\"9047478888\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"\"}}', '2026-05-04 10:36:01'),
(16, 90059, 'razorpay', 'pay_SlG4wMFjYhrnS1', 920.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SlG4wMFjYhrnS1\",\"razorpay_order_id\":\"order_SlG4qtAL2cxxe6\",\"razorpay_signature\":\"89256390e0a50b6190564d68bea52a5e72f5f3d366e82c70f11e0dff160f6f62\",\"checkout_data\":{\"email\":\"raj@gmail.com\",\"email_subscribe\":\"1\",\"country\":\"India\",\"first_name\":\"raj\",\"last_name\":\"\",\"address\":\"eededwdw\",\"city\":\"madurai\",\"state\":\"TAMIL NADU\",\"pin_code\":\"625001\",\"phone\":\"9047478888\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-04 10:44:12'),
(17, 90062, 'razorpay', 'pay_SlGpp3sVAyRYw5', 849.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SlGpp3sVAyRYw5\",\"razorpay_order_id\":\"order_SlGpgppc7f44jy\",\"razorpay_signature\":\"0248cd4789d7d6d750bc7c84c93a65d6a27f640ed3c4e51ad91e6685786b15a2\",\"checkout_data\":{\"email\":\"\",\"email_subscribe\":\"1\",\"country\":\"India\",\"first_name\":\"esakiraj\",\"last_name\":\"raja\",\"address\":\"hhhtjrtsjjrtjtjj\",\"city\":\"sivakasi\",\"state\":\"TAMIL NADU\",\"pin_code\":\" 625001\",\"phone\":\"9685741122\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-04 11:28:50'),
(18, 90063, 'razorpay', 'pay_SlH8n59PH6s3we', 270.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SlH8n59PH6s3we\",\"razorpay_order_id\":\"order_SlH8d8xhgOjLBm\",\"razorpay_signature\":\"1753b4ab4ff5492a6c143b95bc3cdc071e695a3232a90003e78a487bae4cda85\",\"checkout_data\":{\"email\":\"esakiraj006@gmail.com\",\"first_name\":\"esakiraj\",\"last_name\":\"raja\",\"phone\":\"9047478886\",\"address\":\"wqdwqfwqfwq\",\"city\":\"madurai\",\"state\":\"TAMIL NADU\",\"pin_code\":\"625001\",\"country\":\"India\"}}', '2026-05-04 11:46:31'),
(19, 90064, 'razorpay', 'pay_SlHh3V9MRJvFQu', 849.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SlHh3V9MRJvFQu\",\"razorpay_order_id\":\"order_SlHgrk5YV84Rrm\",\"razorpay_signature\":\"6e4128fd361008ec176bac966575b7b8df7a231c51f35227a3d60a5e4a00b677\",\"checkout_data\":{\"email\":\"esakiraj006@gmail.com\",\"email_subscribe\":\"1\",\"country\":\"India\",\"first_name\":\"esakiraj\",\"last_name\":\"\",\"address\":\"wqdwqfwqfwq\",\"city\":\"madurai\",\"state\":\"TAMIL NADU\",\"pin_code\":\" 625001\",\"phone\":\"9047478886\",\"save_info\":\"1\",\"marketing_opt_in\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-04 12:19:02'),
(20, 90074, 'razorpay', 'pay_SnjOJ9mABc2bXi', 3000.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SnjOJ9mABc2bXi\",\"razorpay_order_id\":\"order_SnjO6qTxnhvImC\",\"razorpay_signature\":\"8a695179779529a7dda16082732b64d12f86a0f1b89444a6e0061ee33f139977\",\"checkout_data\":{\"email\":\"esakiraj006@gmail.com\",\"email_subscribe\":\"1\",\"country\":\"India\",\"first_name\":\"esakiraj\",\"last_name\":\"raja\",\"address\":\"wqdwqfwqfwq\",\"city\":\"madurai\",\"state\":\"TAMIL NADU\",\"pin_code\":\"254125\",\"phone\":\"9047478886\",\"save_info\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-10 16:42:55'),
(21, 90075, 'razorpay', 'pay_Sny2jWJRbDO4nY', 300.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_Sny2jWJRbDO4nY\",\"razorpay_order_id\":\"order_Sny2KHllgZ9i4W\",\"razorpay_signature\":\"671878e8be6dabefbce1c00054430db188adbc28ba616ae5738efd841a3494d3\",\"checkout_data\":{\"email\":\"nalej602nalej60273@imashr.com\",\"email_subscribe\":\"1\",\"country\":\"India\",\"first_name\":\"nalej\",\"last_name\":\"raja\",\"address\":\"wqdwqfwqfwq\",\"city\":\"Hyderabad\",\"state\":\" Andhra Pradesh\",\"pin_code\":\"858567\",\"phone\":\"9085858541\",\"save_info\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-11 07:02:48'),
(22, 90076, 'razorpay', 'pay_SnyzpWnzZT7MN1', 700.00, 'INR', 'success', '{\"razorpay_payment_id\":\"pay_SnyzpWnzZT7MN1\",\"razorpay_order_id\":\"order_SnyzfofYdMQ08c\",\"razorpay_signature\":\"c1d1a62b35ca7defe585c0fcd0dd6fb8df4a31f40a04ef44e7bfde6d8ba7b2cb\",\"checkout_data\":{\"email\":\"nalej60273@imashr.com\",\"email_subscribe\":\"1\",\"country\":\"India\",\"first_name\":\"nalej\",\"last_name\":\"raja\",\"address\":\"wqdwqfwqfwq\",\"city\":\"Hyderabad\",\"state\":\" Andhra Pradesh\",\"pin_code\":\"589625\",\"phone\":\"8525654215\",\"save_info\":\"1\",\"payment_method\":\"upi\"}}', '2026-05-11 07:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `key_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `key_name`, `description`, `created_at`, `deleted_at`) VALUES
(1, 'View Products', 'products:view', 'Ability to view products', '2026-05-13 07:47:01', NULL),
(2, 'Create Products', 'products:create', 'Ability to create products', '2026-05-13 07:47:01', NULL),
(3, 'Edit Products', 'products:edit', 'Ability to edit products', '2026-05-13 07:47:01', NULL),
(4, 'Delete Products', 'products:delete', 'Ability to delete products', '2026-05-13 07:47:01', NULL),
(5, 'Export Products', 'products:export', 'Ability to export products', '2026-05-13 07:47:01', NULL),
(6, 'View Orders', 'orders:view', 'Ability to view orders', '2026-05-13 07:47:01', NULL),
(7, 'Create Orders', 'orders:create', 'Ability to create orders', '2026-05-13 07:47:01', NULL),
(8, 'Edit Orders', 'orders:edit', 'Ability to edit orders', '2026-05-13 07:47:01', NULL),
(9, 'Delete Orders', 'orders:delete', 'Ability to delete orders', '2026-05-13 07:47:01', NULL),
(10, 'Export Orders', 'orders:export', 'Ability to export orders', '2026-05-13 07:47:01', NULL),
(11, 'View Customers', 'customers:view', 'Ability to view customers', '2026-05-13 07:47:01', NULL),
(12, 'Create Customers', 'customers:create', 'Ability to create customers', '2026-05-13 07:47:01', NULL),
(13, 'Edit Customers', 'customers:edit', 'Ability to edit customers', '2026-05-13 07:47:01', NULL),
(14, 'Delete Customers', 'customers:delete', 'Ability to delete customers', '2026-05-13 07:47:01', NULL),
(15, 'Export Customers', 'customers:export', 'Ability to export customers', '2026-05-13 07:47:01', NULL),
(16, 'View Inventory', 'inventory:view', 'Ability to view inventory', '2026-05-13 07:47:01', NULL),
(17, 'Create Inventory', 'inventory:create', 'Ability to create inventory', '2026-05-13 07:47:01', NULL),
(18, 'Edit Inventory', 'inventory:edit', 'Ability to edit inventory', '2026-05-13 07:47:01', NULL),
(19, 'Delete Inventory', 'inventory:delete', 'Ability to delete inventory', '2026-05-13 07:47:01', NULL),
(20, 'Export Inventory', 'inventory:export', 'Ability to export inventory', '2026-05-13 07:47:01', NULL),
(21, 'View Reports', 'reports:view', 'Ability to view reports', '2026-05-13 07:47:01', NULL),
(22, 'Create Reports', 'reports:create', 'Ability to create reports', '2026-05-13 07:47:01', NULL),
(23, 'Edit Reports', 'reports:edit', 'Ability to edit reports', '2026-05-13 07:47:01', NULL),
(24, 'Delete Reports', 'reports:delete', 'Ability to delete reports', '2026-05-13 07:47:01', NULL),
(25, 'Export Reports', 'reports:export', 'Ability to export reports', '2026-05-13 07:47:01', NULL),
(26, 'View Dashboard', 'dashboard:view', 'Ability to view dashboard', '2026-05-13 07:47:01', NULL),
(27, 'Manage Settings', 'settings:manage', 'Ability to manage settings', '2026-05-13 07:47:01', NULL),
(28, 'Manage Permissions', 'permissions:manage', 'Ability to manage permissions', '2026-05-13 07:47:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pincodes`
--

CREATE TABLE `pincodes` (
  `pincode` varchar(6) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `is_serviceable` tinyint(1) DEFAULT 1,
  `cod_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pincodes`
--

INSERT INTO `pincodes` (`pincode`, `city`, `state`, `latitude`, `longitude`, `is_serviceable`, `cod_available`, `created_at`) VALUES
('110001', 'New Delhi', 'Delhi', 28.61390000, 77.20900000, 1, 0, '2026-04-28 12:39:36'),
('560001', 'Bangalore', 'Karnataka', 12.97160000, 77.59460000, 1, 1, '2026-04-28 12:39:36'),
('600001', 'Chennai', 'Tamil Nadu', 13.08270000, 80.27070000, 1, 1, '2026-04-28 12:39:36'),
('600028', 'Chennai', 'Tamil Nadu', 13.02420000, 80.26420000, 1, 1, '2026-04-28 12:39:36'),
('625001', 'Madurai', 'Tamil Nadu', 9.92520000, 78.11980000, 1, 1, '2026-04-28 12:39:36'),
('641001', 'Coimbatore', 'Tamil Nadu', 11.01680000, 76.95580000, 1, 1, '2026-04-28 12:39:36'),
('999999', 'Remote Island', 'Nowhere', 0.00000000, 0.00000000, 0, 0, '2026-04-28 12:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `subcategory_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `short_description` text DEFAULT NULL,
  `description` longtext DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `tax_rate` decimal(5,2) DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `reserved_quantity` int(11) DEFAULT 0,
  `sold_quantity` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10,
  `sku` varchar(50) DEFAULT NULL,
  `status` enum('draft','published','out_of_stock') DEFAULT 'published',
  `last_modified_by` int(11) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `subcategory_id`, `name`, `slug`, `short_description`, `description`, `base_price`, `sale_price`, `cost_price`, `tax_rate`, `stock_quantity`, `reserved_quantity`, `sold_quantity`, `low_stock_threshold`, `sku`, `status`, `last_modified_by`, `featured`, `created_at`, `deleted_at`) VALUES
(1, NULL, NULL, 'Premium Vijaya Karadant', 'premium-karadant', NULL, NULL, 850.00, NULL, 0.00, 0.00, 50, 0, 0, 10, 'KARD-001', 'published', NULL, 0, '2026-04-22 07:22:46', '2026-04-27 10:21:59'),
(2, NULL, NULL, 'Festive Sweet Box', 'festive-box', NULL, NULL, 625.00, NULL, 0.00, 0.00, 50, 0, 0, 10, 'BOX-001', 'published', NULL, 0, '2026-04-22 07:22:46', '2026-04-27 10:22:02'),
(3, 2, NULL, 'Dry Fruit Laddu', 'dry-fruit-laddu', NULL, NULL, 450.00, NULL, 0.00, 0.00, 50, 0, 0, 10, 'LAD-001', 'published', NULL, 0, '2026-04-22 07:22:46', '2026-04-27 10:22:05'),
(1001, 1, 3, 'Premium Vijaya Karadant', 'premium-vijaya-karadant', 'Our signature Karadant made with premium nuts and jaggery.', NULL, 720.00, NULL, 432.00, 0.00, 100, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1002, 1, 3, 'Classic Vijaya Karadant', 'classic-vijaya-karadant', 'Traditional Vijaya Karadant with authentic taste and texture.', NULL, 600.00, NULL, 360.00, 0.00, 50, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1003, 1, 3, 'Supreme Vijaya Karadant', 'supreme-vijaya-karadant', 'Richer blend of nuts and jaggery for a premium bite.', '', 420.00, 380.00, 252.00, 0.00, 80, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1004, 1, 3, 'Regal Anjeer Karadant', 'regal-anjeer-karadant', 'Anjeer-infused Karadant with a naturally rich sweetness.', NULL, 880.00, NULL, 528.00, 0.00, 89, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1005, 1, 3, 'Dink Karadant', 'dink-karadant', 'Nutritious Karadant with edible gum for extra energy.', NULL, 650.00, NULL, 390.00, 0.00, 70, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1009, 2, 4, 'Dink Laddu', 'dink-laddu', 'Traditional dink laddu for daily nourishment.', NULL, 480.00, NULL, 288.00, 0.00, 120, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1010, 2, 4, 'Ragi Laddu', 'ragi-laddu', 'Wholesome ragi laddus with a roasted nutty taste.', '', 450.00, 399.00, 270.00, 0.00, 130, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1011, 2, 4, 'Besan Laddu', 'besan-laddu', 'Classic besan laddu made with pure ghee and gram flour.', NULL, 420.00, NULL, 252.00, 0.00, 110, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1012, 2, 4, 'Premium Ladagi Laddu', 'premium-ladagi-laddu', 'Premium laddu assortment with rich dry fruits.', NULL, 550.00, NULL, 330.00, 0.00, 100, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1013, 2, 4, 'Til Laddu', 'til-laddu', 'Sesame laddus with a warm jaggery sweetness.', NULL, 400.00, NULL, 240.00, 0.00, 80, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1014, 2, NULL, 'Premium Otts Laddu', 'otts-laddu', 'Soft and flavorful laddus with a traditional finish.', '', 500.00, 450.00, 0.00, 0.00, 0, 0, 0, 10, NULL, 'published', NULL, 0, '2026-04-30 04:57:15', NULL),
(1015, 2, NULL, 'Peanut Laddu', 'peanut-laddu', NULL, NULL, 440.00, 390.00, 0.00, 0.00, 0, 0, 0, 10, NULL, 'published', NULL, 0, '2026-04-30 04:57:15', NULL),
(1017, 2, NULL, 'Gandahagiri Laddu', 'gandhagiri-laddu', NULL, NULL, 950.00, 890.00, 0.00, 0.00, 0, 0, 0, 10, NULL, 'published', NULL, 0, '2026-04-30 04:57:15', NULL),
(1040, 5, 2, 'Premium Gift Box', 'premium-gift-box', 'A luxurious assortment of our finest Karadant varieties.', NULL, 950.00, NULL, 570.00, 0.00, 50, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1041, 5, 2, 'Festive Special Box', 'festive-special-box', 'Celebrate with our curated festive collection.', NULL, 1200.00, NULL, 720.00, 0.00, 40, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1042, 5, 2, 'Tilkut Gift Box', 'tilkut-gift-box', 'Traditional Tilkut sweets in a premium festive box.', NULL, 950.00, NULL, 570.00, 0.00, 50, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(1043, 5, 2, 'Anjeer Gift Box', 'anjeer-gift-box', 'Exotic Anjeer sweets beautifully packed for special occasions.', NULL, 950.00, NULL, 570.00, 0.00, 50, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(2001, 4, 5, 'Spicy Mix Namkeen', 'spicy-mix-namkeen', 'A bold namkeen mix with signature house spices.', '', 320.00, 280.00, 192.00, 0.00, 200, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(2002, 4, 5, 'Golden Sev', 'golden-sev', 'Crispy golden sev, light and perfectly seasoned.', NULL, 280.00, NULL, 168.00, 0.00, 210, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(2003, 4, 5, 'Masala Peanuts', 'masala-peanuts', 'Crunchy masala-coated peanuts with balanced heat.', NULL, 250.00, NULL, 150.00, 0.00, 220, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(2004, 4, 5, 'Premium Mixture', 'premium-mixture', 'Premium crunchy mixture perfect for tea-time snacking.', NULL, 350.00, NULL, 210.00, 0.00, 180, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(2005, 4, 5, 'Butter Muruku', 'butter-muruku', 'Traditional butter muruku with a crisp bite.', NULL, 320.00, NULL, 192.00, 0.00, 159, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(2006, 4, 5, 'Rice Kodubale', 'rice-kodubale', 'Rice flour kodubale with classic spice blend.', NULL, 320.00, NULL, 192.00, 0.00, 150, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:49:09', NULL),
(2013, 2, 4, 'Moong Dal Laddu', 'moong-dal-laddu', 'Traditional Moong Dal Laddu crafted with pure ghee.', NULL, 280.00, NULL, 168.00, 0.00, 150, 0, 0, 10, NULL, 'published', NULL, 1, '2026-04-18 10:50:00', '2026-04-20 07:46:45'),
(2014, 5, NULL, 'demo', 'demo', 'd23dd23ddfewfwefe', 'vdbdbzsdbd', 500.00, 300.00, 300.00, 0.00, 0, 0, 0, 10, '#2', 'published', NULL, 0, '2026-04-21 15:18:40', NULL),
(2017, 4, NULL, 'Garlic Ribbon', 'garlic-ribbon', '', '', 450.00, 320.00, 0.00, 0.00, 0, 0, 0, 10, '#5', 'published', NULL, 1, '2026-05-06 08:01:20', NULL),
(2018, 4, NULL, 'Onion Kodubale', 'nippattu', 'Crisp nippattu with roasted spice notes.', '', 420.00, 290.00, 0.00, 0.00, 0, 0, 0, 10, '#6', 'published', NULL, 1, '2026-05-06 08:03:22', NULL),
(2019, 4, NULL, 'Ribbon Pakoda', 'ribbon-pakoda', '', '', 450.00, 320.00, 0.00, 0.00, 0, 0, 0, 10, '#4', 'published', NULL, 1, '2026-05-06 08:51:51', NULL),
(2021, 4, NULL, 'Garlic Ribbon', 'garlic-ribbon-1', '', '', 450.00, 320.00, 0.00, 0.00, 0, 0, 0, 10, '#3', 'published', NULL, 1, '2026-05-06 08:54:34', NULL),
(2026, 4, NULL, 'Nippattu', 'nippattu-1', '', '', 410.00, 280.00, 0.00, 0.00, 0, 0, 0, 10, '#7', 'published', NULL, 1, '2026-05-06 08:58:13', NULL),
(2027, 4, NULL, 'Bengaluru Mix', 'bengaluru-mix', '', '', 380.00, 250.00, 0.00, 0.00, 0, 0, 0, 10, '#8', 'published', NULL, 1, '2026-05-06 08:59:46', NULL),
(2029, 4, NULL, 'Masala Peanuts', 'masala-peanuts-1', '', '', 380.00, 250.00, 0.00, 0.00, 0, 0, 0, 10, '#9', 'published', NULL, 1, '2026-05-06 09:04:37', NULL),
(2031, 1, NULL, ' Karadant', 'remium-karadant-pack', '', 'Our signature Karadant made with premium nuts and jaggery', 950.00, 820.00, 0.00, 0.00, 0, 0, 0, 10, '#10', 'published', NULL, 1, '2026-05-07 07:33:58', '2026-05-09 10:47:29');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_main`) VALUES
(81, 1001, 'assets/images/homepage/New folder/karant/bestseeler karadant (1).png', 1),
(82, 1002, 'assets/images/homepage/New folder/karant/bestseeler karadant (2).png', 1),
(83, 1003, 'assets/images/homepage/The Karadant Range (1).png', 0),
(84, 1004, 'assets/images/homepage/New folder/karant/bestseeler karadant (7).png', 1),
(85, 1005, 'assets/images/homepage/New folder/karant/bestseeler karadant (5).png', 1),
(86, 1009, 'assets/images/homepage/New folder/bestseller-laddu 1.png', 1),
(87, 1010, 'assets/images/homepage/New folder/bestseller-laddu 2.png', 1),
(88, 1011, 'assets/images/homepage/New folder/bestseller-laddu 3.png', 1),
(89, 1012, 'assets/images/homepage/New folder/bestseller-laddu 4.png', 1),
(90, 1013, 'assets/images/homepage/New folder/bestseller-laddu6.png', 1),
(91, 1040, 'assets/images/banners/gifing/Featured Gifting Specials (1).png', 1),
(92, 1041, 'assets/images/banners/gifing/Featured Gifting Specials (2).png', 1),
(93, 1042, 'assets/images/banners/gifing/Featured Gifting Specials (3).png', 1),
(94, 1043, 'assets/images/banners/gifing/Featured Gifting Specials (4).png', 1),
(95, 2001, 'assets/images/homepage/Best Sellers (1).png', 1),
(96, 2002, 'assets/images/homepage/Best Sellers (2).png', 1),
(97, 2003, 'assets/images/homepage/Best Sellers (5).png', 1),
(98, 2004, 'assets/images/homepage/Best Sellers (7).png', 1),
(99, 2005, 'assets/images/banners/namkeen-page/our signature  (6).png', 1),
(100, 2006, 'assets/images/banners/namkeen-page/our signature  (7).png', 1),
(101, 2014, 'assets/images/products/sw_69e79550404821.98400110.png', 0),
(102, 1014, 'assets/images/homepage/New folder/bestseller-laddu 5.png', 1),
(103, 1015, 'assets/images/homepage/New folder/bestseller-laddu  7.png', 1),
(104, 1017, 'assets/images/homepage/New folder/bestseller-laddu 8.png', 1),
(105, 2001, 'assets/images/products/sw_69faf0cd204339.94164159.png', 0),
(106, 2001, 'assets/images/products/sw_69faf14fafee05.44224767.png', 0),
(107, 2001, 'assets/images/products/sw_69faf179c2eaa9.29590047.png', 0),
(108, 1014, 'assets/images/products/sw_69faf2c908ac70.49420140.png', 0),
(109, 2001, 'assets/images/products/sw_69faf365c6e222.51251315.png', 0),
(110, 2001, 'assets/images/products/sw_69faf38e6fb618.56725398.png', 0),
(111, 2001, 'assets/images/products/sw_69faf3df817f15.24812106.png', 0),
(112, 1014, 'assets/images/products/sw_69faf449ab0134.55560760.png', 0),
(113, 2017, 'assets/images/products/sw_69faf5506704d3.45873060.png', 1),
(114, 2018, 'assets/images/products/sw_69faf5ca546564.22639439.png', 1),
(115, 2019, 'assets/images/products/sw_69fb0127618d01.15771343.png', 1),
(116, 2021, 'assets/images/products/sw_69fb01ca2dab70.06238032.png', 1),
(117, 2026, 'assets/images/products/sw_69fb02a5225ee9.55018342.png', 1),
(118, 2027, 'assets/images/products/sw_69fb0302a68dd7.63996990.png', 1),
(119, 2029, 'assets/images/products/sw_69fb04250fcf16.43916756.png', 1),
(120, 2031, 'assets/images/products/sw_69fc4066de52d5.18136141.png', 1),
(122, 2014, 'assets/images/products/sw_6a007898d77f45.11993884.png', 1),
(123, 1003, 'assets/images/products/sw_6a0078dfcbb266.26209842.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `weight` varchar(50) NOT NULL,
  `label` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `reserved_stock` int(11) DEFAULT 0,
  `sold_count` int(11) DEFAULT 0,
  `low_stock_threshold` int(11) DEFAULT 10
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `weight`, `label`, `price`, `stock`, `reserved_stock`, `sold_count`, `low_stock_threshold`) VALUES
(1, 1, '500g', '500g Standard Pack', 850.00, 99, 0, 0, 10),
(2, 2, '500g', '500g Standard Pack', 625.00, 99, 0, 0, 10),
(3, 3, '500g', '500g Standard Pack', 450.00, 99, 0, 0, 10),
(4, 1001, '500g', '500g Standard Pack', 720.00, 95, 0, 0, 10),
(5, 1002, '500g', '500g Standard Pack', 600.00, 92, 0, 0, 10),
(6, 1003, '500g', '500g Standard Pack', 420.00, 85, 0, 0, 10),
(7, 1004, '500g', '500g Standard Pack', 880.00, 78, 0, 0, 10),
(8, 1005, '500g', '500g Standard Pack', 650.00, 47, 0, 0, 10),
(9, 1009, '500g', '500g Standard Pack', 480.00, 100, 0, 0, 10),
(10, 1010, '500g', '500g Standard Pack', 450.00, 98, 0, 0, 10),
(11, 1011, '500g', '500g Standard Pack', 420.00, 100, 0, 0, 10),
(12, 1012, '500g', '500g Standard Pack', 550.00, 100, 0, 0, 10),
(13, 1013, '500g', '500g Standard Pack', 400.00, 100, 0, 0, 10),
(14, 1040, '500g', '500g Standard Pack', 950.00, 97, 0, 0, 10),
(15, 1041, '500g', '500g Standard Pack', 1200.00, 100, 0, 0, 10),
(16, 1042, '500g', '500g Standard Pack', 950.00, 100, 0, 0, 10),
(17, 1043, '500g', '500g Standard Pack', 950.00, 100, 0, 0, 10),
(18, 2001, '500g', '500g Standard Pack', 320.00, 100, 0, 0, 10),
(19, 2002, '500g', '500g Standard Pack', 280.00, 100, 0, 0, 10),
(20, 2003, '500g', '500g Standard Pack', 250.00, 99, 0, 0, 10),
(21, 2004, '500g', '500g Standard Pack', 350.00, 98, 0, 0, 10),
(22, 2005, '500g', '500g Standard Pack', 320.00, 94, 0, 0, 10),
(23, 2006, '500g', '500g Standard Pack', 320.00, 91, 0, 0, 10),
(24, 2013, '500g', '500g Standard Pack', 280.00, 100, 0, 0, 10),
(25, 2014, '500g', '500g Standard Pack', 500.00, 100, 0, 0, 10),
(26, 2029, '1kg', '1kg Pack', 800.00, 17, 0, 0, 10);

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `section_id` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `discount_badge` varchar(50) DEFAULT NULL,
  `image_path` varchar(255) NOT NULL,
  `btn1_text` varchar(50) DEFAULT NULL,
  `btn1_link` varchar(255) DEFAULT NULL,
  `btn2_text` varchar(50) DEFAULT NULL,
  `btn2_link` varchar(255) DEFAULT NULL,
  `timer_end` datetime DEFAULT NULL,
  `stat1_val` varchar(50) DEFAULT NULL,
  `stat1_label` varchar(100) DEFAULT NULL,
  `stat2_val` varchar(50) DEFAULT NULL,
  `stat2_label` varchar(100) DEFAULT NULL,
  `stat3_val` varchar(50) DEFAULT NULL,
  `stat3_label` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promotions`
--

INSERT INTO `promotions` (`id`, `section_id`, `title`, `subtitle`, `description`, `discount_badge`, `image_path`, `btn1_text`, `btn1_link`, `btn2_text`, `btn2_link`, `timer_end`, `stat1_val`, `stat1_label`, `stat2_val`, `stat2_label`, `stat3_val`, `stat3_label`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'curated-combos', 'Curated Combos for Every Celebration', NULL, 'Thoughtfully crafted selections designed for gifting and festive moments. Discover the perfect harmony of traditional flavors and modern luxury.', NULL, 'assets/images/homepage/Celebration.png', 'View Offers', '#', 'View Catalogue', '#', NULL, '50+', 'Varieties', 'SINCE 1907', 'Handcrafted with Care', '4.9/5', 'rating', 1, '2026-03-06 11:20:08', '2026-03-06 11:20:08'),
(2, 'festival-offers', 'Vibrant Festival Offers', 'Celebrate with Sweet Savings', 'Experience the joy of gifting with our exclusive festival discounts. Handcrafted sweets, premium packaging, and timeless traditions delivered to your doorstep.', 'UP TO 30% OFF', 'assets/images/homepage/FestivalOffer.png', 'Explore Offers', 'category-products.php?slug=gifting', NULL, NULL, '2026-05-04 15:47:58', NULL, NULL, NULL, NULL, NULL, NULL, 1, '2026-04-27 13:47:58', '2026-04-27 13:47:58');

-- --------------------------------------------------------

--
-- Table structure for table `refunds`
--

CREATE TABLE `refunds` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `refunds`
--

INSERT INTO `refunds` (`id`, `order_id`, `amount`, `reason`, `status`, `created_at`) VALUES
(1, 1005, 950.00, 'Customer cancelled before shipping', 'completed', '2026-04-03 07:22:21');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) DEFAULT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'super_admin', 'Full access to everything', '2026-05-13 07:47:01', NULL),
(2, 'Manager', 'manager', 'Can manage products, orders and customers', '2026-05-13 07:47:01', NULL),
(3, 'Editor', 'editor', 'Can only manage products and news', '2026-05-13 07:47:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 26),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(2, 26),
(3, 1),
(3, 3),
(3, 6),
(3, 7),
(3, 8),
(3, 9),
(3, 10),
(3, 26);

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `courier_name` varchar(100) DEFAULT NULL,
  `tracking_id` varchar(100) DEFAULT NULL,
  `dispatch_date` datetime DEFAULT NULL,
  `estimated_delivery` date DEFAULT NULL,
  `shipping_method` varchar(50) DEFAULT 'Standard',
  `delivery_charge` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','in_transit','delivered') NOT NULL DEFAULT 'pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `order_id`, `destination`, `courier_name`, `tracking_id`, `dispatch_date`, `estimated_delivery`, `shipping_method`, `delivery_charge`, `created_at`, `status`, `updated_at`) VALUES
(1, 90010, 'Mumbai, Maharashtra', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'pending', '2026-04-27 07:12:27'),
(2, 90009, 'Ahmedabad, Gujarat', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'in_transit', '2026-04-27 05:12:27'),
(3, 90008, 'Bengaluru, Karnataka', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'in_transit', '2026-04-29 08:28:53'),
(4, 90001, 'Pune, Maharashtra', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'pending', '2026-04-27 01:12:27'),
(5, 1005, 'New Delhi, Delhi', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'in_transit', '2026-04-26 23:12:27'),
(6, 1004, 'Lucknow, Uttar Pradesh', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'delivered', '2026-04-26 21:12:27'),
(7, 1003, 'Kochi, Kerala', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'pending', '2026-04-26 19:12:27'),
(8, 1002, 'Hyderabad, Telangana', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'in_transit', '2026-04-26 17:12:27'),
(9, 1001, 'Chennai, Tamil Nadu', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'delivered', '2026-04-26 15:12:27'),
(10, 91, 'Kolkata, West Bengal', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'pending', '2026-04-26 13:12:27'),
(11, 90, 'Mumbai, Maharashtra', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'pending', '2026-04-27 14:06:48'),
(12, 89, 'Ahmedabad, Gujarat', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'delivered', '2026-04-26 09:12:27'),
(13, 88, 'Bengaluru, Karnataka', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'pending', '2026-04-26 07:12:27'),
(14, 86, 'Pune, Maharashtra', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'in_transit', '2026-04-26 05:12:27'),
(15, 85, 'New Delhi, Delhi', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-04-27 10:42:27', 'delivered', '2026-04-26 03:12:27'),
(16, 90060, '', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-05-04 11:15:36', 'pending', '2026-05-04 11:15:36'),
(17, 90061, '', NULL, NULL, NULL, NULL, 'Standard', 0.00, '2026-05-04 11:16:49', 'pending', '2026-05-07 10:34:00');

-- --------------------------------------------------------

--
-- Table structure for table `site_settings`
--

CREATE TABLE `site_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `group_name` varchar(50) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `site_settings`
--

INSERT INTO `site_settings` (`setting_key`, `setting_value`, `group_name`, `updated_at`) VALUES
('address', '145, Market Road, Near Gandhi Chowk, Gokak, Belagavi, Karnataka 591307', 'store', '2026-04-10 09:27:04'),
('email', 'hello@vijayakaradant.com', 'store', '2026-04-10 09:27:04'),
('gst', '29AAACV2288J1Z2', 'store', '2026-04-10 09:27:04'),
('notify_abandoned_cart', '1', 'notifications', '2026-04-10 09:27:04'),
('notify_customer_signup', '0', 'notifications', '2026-04-10 09:27:04'),
('notify_delivered', '1', 'notifications', '2026-04-10 09:27:04'),
('notify_feedback_review', '1', 'notifications', '2026-04-10 09:27:04'),
('notify_frequency', 'instant', 'notifications', '2026-04-10 09:27:04'),
('notify_new_order', '1', 'notifications', '2026-04-10 09:27:04'),
('notify_order_cancelled', '1', 'notifications', '2026-04-10 09:27:04'),
('notify_order_status', '1', 'notifications', '2026-04-10 09:27:04'),
('notify_out_for_delivery', '0', 'notifications', '2026-04-10 09:27:04'),
('notify_payment_received', '0', 'notifications', '2026-04-10 09:27:04'),
('notify_shipped', '1', 'notifications', '2026-04-10 09:27:04'),
('payMethodNet', '1', 'general', '2026-04-22 11:27:27'),
('phone', '+91 98860 24567', 'store', '2026-04-10 09:27:04'),
('shipping_enable_national', '1', 'shipping', '2026-05-05 04:45:27'),
('store_logo', 'assets/images/settings/sw_69fa1cf8c342d2.52755647.png', 'store', '2026-05-05 16:38:16'),
('store_name', 'Vijaya Karadant', 'store', '2026-04-10 09:27:04'),
('tagline', 'Authentic Karnataka sweets, crafted with heritage.', 'store', '2026-04-10 09:27:04'),
('ui_favicon_path', 'assets/images/settings/favicon_premium.png', 'appearance', '2026-05-04 07:24:54'),
('ui_logo_path', 'assets/images/settings/logo_premium.png', 'appearance', '2026-05-04 07:24:54'),
('ui_primary_font', 'Poppins', 'appearance', '2026-05-04 07:17:38');

-- --------------------------------------------------------

--
-- Table structure for table `stock_activity`
--

CREATE TABLE `stock_activity` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `action_type` enum('added','reduced','updated','reserved','released','finalized') NOT NULL,
  `quantity_change` int(11) NOT NULL,
  `previous_stock` int(11) DEFAULT NULL,
  `new_stock` int(11) DEFAULT NULL,
  `performed_by` varchar(100) DEFAULT 'System',
  `performed_by_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_activity`
--

INSERT INTO `stock_activity` (`id`, `product_id`, `action_type`, `quantity_change`, `previous_stock`, `new_stock`, `performed_by`, `performed_by_id`, `notes`, `reference_type`, `reference_id`, `created_at`) VALUES
(1, 1, 'added', 50, 0, 50, 'Admin', NULL, 'Restocking for test', NULL, NULL, '2026-05-07 10:54:29'),
(2, 2031, 'added', 2, 20, 22, 'Admin', NULL, 'Manual stock update to 22', NULL, NULL, '2026-05-07 11:05:27'),
(3, 2017, 'added', 1, 10, 11, 'Admin', NULL, 'Manual stock update to 11', NULL, NULL, '2026-05-07 11:05:43'),
(4, 2029, 'added', 1, 20, 21, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:41:07'),
(5, 2029, 'added', 1, 21, 22, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:41:07'),
(6, 2029, 'added', 1, 22, 23, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:41:08'),
(7, 2029, 'reduced', -1, 23, 22, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:45:22'),
(8, 2029, 'reduced', -1, 22, 21, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:45:22'),
(9, 2029, 'reduced', -1, 21, 20, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:45:22'),
(10, 2029, 'reduced', -1, 20, 19, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:45:23'),
(11, 2029, 'reduced', -1, 19, 18, 'Admin', NULL, 'Quick update from inventory list [Variant: 1kg Pack]', NULL, NULL, '2026-05-11 04:45:23');

-- --------------------------------------------------------

--
-- Table structure for table `stock_notifications`
--

CREATE TABLE `stock_notifications` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_type` enum('product','combo') NOT NULL DEFAULT 'product',
  `variant_id` int(11) DEFAULT NULL,
  `email` varchar(191) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('pending','notified','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_notifications`
--

INSERT INTO `stock_notifications` (`id`, `product_id`, `product_type`, `variant_id`, `email`, `phone`, `status`, `created_at`, `notified_at`) VALUES
(1, 1, 'product', NULL, 'test@example.com', '1234567890', 'notified', '2026-05-07 10:54:29', '2026-05-07 10:54:29');

-- --------------------------------------------------------

--
-- Table structure for table `subcategories`
--

CREATE TABLE `subcategories` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subcategories`
--

INSERT INTO `subcategories` (`id`, `category_id`, `name`, `slug`, `description`, `status`, `created_at`) VALUES
(1, 5, 'T-ShoeStyle', 't-shoestyle', 'Traditional authentic Karadant made with pure jaggery and premium dry fruits.', 'active', '2026-04-20 06:00:33'),
(2, 5, 'General', 'gifting-general', NULL, 'active', '2026-04-20 06:22:35'),
(3, 1, 'General', 'karadant-general', NULL, 'active', '2026-04-20 06:22:35'),
(4, 2, 'General', 'laddu-general', NULL, 'active', '2026-04-20 06:22:35'),
(5, 4, 'General', 'namkeen-general', NULL, 'active', '2026-04-20 06:22:35'),
(6, 18, 'General', 't-shoestyle-general', NULL, 'active', '2026-04-20 06:22:35');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `status` enum('Active','Inactive','Blocked') DEFAULT 'Active',
  `avatar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `marketing_opt_in` tinyint(1) NOT NULL DEFAULT 0,
  `language` varchar(50) DEFAULT 'English (US)',
  `timezone` varchar(100) DEFAULT '(UTC+05:30) Asia/Kolkata'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `phone`, `password`, `role`, `status`, `avatar`, `created_at`, `updated_at`, `deleted_at`, `marketing_opt_in`, `language`, `timezone`) VALUES
(1, 'Kevin Rajput', 'kevinrajput@gmail.com', '+91 8897252325', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'customer', 'Active', NULL, '2026-04-22 07:22:46', '2026-04-22 07:22:46', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(2, 'Principal Admin Test', 'admin@sweets.com', '+91 99999 88888', '$2y$10$P37KCMXMxlhEKMAw4unUp.Wl2pLuoHu/M03zEx1Jy.raWdC1GcKPC', 'admin', 'Active', NULL, '2026-04-06 17:57:29', '2026-05-12 08:16:31', NULL, 0, 'Kannada', '(UTC+05:30) Asia/Kolkata'),
(10, 'Rajiv Sharma', 'rajiv.s@example.com', '+91 98765 43210', 'pass123', 'customer', 'Active', NULL, '2023-01-12 04:30:00', '2026-04-12 17:18:46', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(11, 'Neha Patel', 'neha.p92@example.com', '+91 91234 56789', 'pass123', 'customer', 'Active', NULL, '2023-03-04 06:00:00', '2026-04-12 17:18:46', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(12, 'Amit Verma', 'amit.v@domain.in', '+91 88997 76655', 'pass123', 'customer', 'Inactive', NULL, '2022-11-22 03:45:00', '2026-04-12 17:18:46', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(13, 'Priya Desai', 'priya.d@example.com', '+91 90001 22334', 'pass123', 'customer', 'Active', NULL, '2023-08-18 09:15:00', '2026-04-12 17:18:46', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(14, 'Sanjay Kumar', 'sanjay.k@example.com', '+91 99887 77665', 'pass123', 'customer', 'Active', NULL, '2023-10-05 10:50:00', '2026-04-12 17:18:46', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(15, 'Rakesh Singh', 'rakesh.s@domain.co.in', '+91 77665 54433', 'pass123', 'customer', 'Inactive', NULL, '2022-02-11 04:40:00', '2026-04-12 17:18:46', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(16, 'Customer 101', 'cust101@example.com', '+91 0000000101', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(17, 'Customer 102', 'cust102@example.com', '+91 0000000102', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(18, 'Customer 103', 'cust103@example.com', '+91 0000000103', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(19, 'Customer 104', 'cust104@example.com', '+91 0000000104', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(20, 'Customer 105', 'cust105@example.com', '+91 0000000105', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(21, 'Customer 106', 'cust106@example.com', '+91 0000000106', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(22, 'Customer 107', 'cust107@example.com', '+91 0000000107', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(23, 'Customer 108', 'cust108@example.com', '+91 0000000108', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(24, 'Customer 109', 'cust109@example.com', '+91 0000000109', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(25, 'Customer 110', 'cust110@example.com', '+91 0000000110', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(26, 'Customer 111', 'cust111@example.com', '+91 0000000111', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(27, 'Customer 112', 'cust112@example.com', '+91 0000000112', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(28, 'Customer 113', 'cust113@example.com', '+91 0000000113', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(29, 'Customer 114', 'cust114@example.com', '+91 0000000114', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(30, 'Customer 115', 'cust115@example.com', '+91 0000000115', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(31, 'Customer 116', 'cust116@example.com', '+91 0000000116', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(32, 'Customer 117', 'cust117@example.com', '+91 0000000117', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(33, 'Customer 118', 'cust118@example.com', '+91 0000000118', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(34, 'Customer 119', 'cust119@example.com', '+91 0000000119', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(35, 'Customer 120', 'cust120@example.com', '+91 0000000120', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(36, 'Customer 121', 'cust121@example.com', '+91 0000000121', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(37, 'Customer 122', 'cust122@example.com', '+91 0000000122', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(38, 'Customer 123', 'cust123@example.com', '+91 0000000123', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(39, 'Customer 124', 'cust124@example.com', '+91 0000000124', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(40, 'Customer 125', 'cust125@example.com', '+91 0000000125', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(41, 'Customer 126', 'cust126@example.com', '+91 0000000126', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(42, 'Customer 127', 'cust127@example.com', '+91 0000000127', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(43, 'Customer 128', 'cust128@example.com', '+91 0000000128', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(44, 'Customer 129', 'cust129@example.com', '+91 0000000129', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(45, 'Customer 130', 'cust130@example.com', '+91 0000000130', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(46, 'Customer 131', 'cust131@example.com', '+91 0000000131', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(47, 'Customer 132', 'cust132@example.com', '+91 0000000132', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(48, 'Customer 133', 'cust133@example.com', '+91 0000000133', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(49, 'Customer 134', 'cust134@example.com', '+91 0000000134', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(50, 'Customer 135', 'cust135@example.com', '+91 0000000135', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(51, 'Customer 136', 'cust136@example.com', '+91 0000000136', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(52, 'Customer 137', 'cust137@example.com', '+91 0000000137', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(53, 'Customer 138', 'cust138@example.com', '+91 0000000138', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(54, 'Customer 139', 'cust139@example.com', '+91 0000000139', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(55, 'Customer 140', 'cust140@example.com', '+91 0000000140', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(56, 'Customer 141', 'cust141@example.com', '+91 0000000141', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(57, 'Customer 142', 'cust142@example.com', '+91 0000000142', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(58, 'Customer 143', 'cust143@example.com', '+91 0000000143', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(59, 'Customer 144', 'cust144@example.com', '+91 0000000144', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(60, 'Customer 145', 'cust145@example.com', '+91 0000000145', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(61, 'Customer 146', 'cust146@example.com', '+91 0000000146', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(62, 'Customer 147', 'cust147@example.com', '+91 0000000147', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(63, 'Customer 148', 'cust148@example.com', '+91 0000000148', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(64, 'Customer 149', 'cust149@example.com', '+91 0000000149', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(65, 'Customer 150', 'cust150@example.com', '+91 0000000150', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(66, 'Customer 151', 'cust151@example.com', '+91 0000000151', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(67, 'Customer 152', 'cust152@example.com', '+91 0000000152', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(68, 'Customer 153', 'cust153@example.com', '+91 0000000153', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(69, 'Customer 154', 'cust154@example.com', '+91 0000000154', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(70, 'Customer 155', 'cust155@example.com', '+91 0000000155', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(71, 'Customer 156', 'cust156@example.com', '+91 0000000156', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(72, 'Customer 157', 'cust157@example.com', '+91 0000000157', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(73, 'Customer 158', 'cust158@example.com', '+91 0000000158', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(74, 'Customer 159', 'cust159@example.com', '+91 0000000159', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(75, 'Customer 160', 'cust160@example.com', '+91 0000000160', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(76, 'Customer 161', 'cust161@example.com', '+91 0000000161', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(77, 'Customer 162', 'cust162@example.com', '+91 0000000162', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(78, 'Customer 163', 'cust163@example.com', '+91 0000000163', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(79, 'Customer 164', 'cust164@example.com', '+91 0000000164', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(80, 'Customer 165', 'cust165@example.com', '+91 0000000165', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(81, 'Customer 166', 'cust166@example.com', '+91 0000000166', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(82, 'Customer 167', 'cust167@example.com', '+91 0000000167', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(83, 'Customer 168', 'cust168@example.com', '+91 0000000168', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(84, 'Customer 169', 'cust169@example.com', '+91 0000000169', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(85, 'Customer 170', 'cust170@example.com', '+91 0000000170', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(86, 'Customer 171', 'cust171@example.com', '+91 0000000171', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(87, 'Customer 172', 'cust172@example.com', '+91 0000000172', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(88, 'Customer 173', 'cust173@example.com', '+91 0000000173', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(89, 'Customer 174', 'cust174@example.com', '+91 0000000174', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(90, 'Customer 175', 'cust175@example.com', '+91 0000000175', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(91, 'Customer 176', 'cust176@example.com', '+91 0000000176', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(92, 'Customer 177', 'cust177@example.com', '+91 0000000177', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(93, 'Customer 178', 'cust178@example.com', '+91 0000000178', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(94, 'Customer 179', 'cust179@example.com', '+91 0000000179', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(95, 'Customer 180', 'cust180@example.com', '+91 0000000180', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(96, 'Customer 181', 'cust181@example.com', '+91 0000000181', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(97, 'Customer 182', 'cust182@example.com', '+91 0000000182', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(98, 'Customer 183', 'cust183@example.com', '+91 0000000183', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(99, 'Customer 184', 'cust184@example.com', '+91 0000000184', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(100, 'Customer 185', 'cust185@example.com', '+91 0000000185', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(101, 'Customer 186', 'cust186@example.com', '+91 0000000186', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(102, 'Customer 187', 'cust187@example.com', '+91 0000000187', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(103, 'Customer 188', 'cust188@example.com', '+91 0000000188', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(104, 'Customer 189', 'cust189@example.com', '+91 0000000189', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(105, 'Customer 190', 'cust190@example.com', '+91 0000000190', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(106, 'Customer 191', 'cust191@example.com', '+91 0000000191', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(107, 'Customer 192', 'cust192@example.com', '+91 0000000192', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(108, 'Customer 193', 'cust193@example.com', '+91 0000000193', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(109, 'Customer 194', 'cust194@example.com', '+91 0000000194', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(110, 'Customer 195', 'cust195@example.com', '+91 0000000195', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(111, 'Customer 196', 'cust196@example.com', '+91 0000000196', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(112, 'Customer 197', 'cust197@example.com', '+91 0000000197', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(113, 'Customer 198', 'cust198@example.com', '+91 0000000198', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(114, 'Customer 199', 'cust199@example.com', '+91 0000000199', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(115, 'Customer 200', 'cust200@example.com', '+91 0000000200', 'pass', 'customer', 'Active', NULL, '2026-04-12 17:19:13', '2026-04-12 17:19:13', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(116, '', 'rajiv.sharma@example.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', 'customer', 'Active', NULL, '2025-10-22 11:19:38', '2026-04-20 11:19:38', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(117, '', 'priya.patel@example.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', 'customer', 'Active', NULL, '2026-01-20 11:19:38', '2026-04-20 11:19:38', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(118, '', 'amit.verma@example.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', 'customer', 'Active', NULL, '2026-03-21 11:19:38', '2026-04-20 11:19:38', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(125, 'Sunita Gupta', 'sunita.gupta@example.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', 'customer', 'Active', NULL, '2025-04-20 11:30:27', '2026-04-20 11:30:27', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(126, 'Vikram Singh', 'vikram.singh@example.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', 'customer', 'Active', NULL, '2025-12-21 11:30:27', '2026-04-20 11:30:27', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(127, 'Ananya Rao', 'ananya.rao@example.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', 'customer', 'Active', NULL, '2026-02-19 11:30:27', '2026-04-20 11:30:27', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(128, 'Karan Mehra', 'karan.mehra@example.com', NULL, '$2y$10$abcdefghijklmnopqrstuv', 'customer', 'Active', NULL, '2026-04-05 11:30:27', '2026-04-20 11:30:27', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9001, 'Rajiv Sharma', 'rajiv.demo@example.com', '9876543210', '$2y$10$dummy', 'customer', 'Active', NULL, '2025-12-22 04:35:50', '2026-04-21 04:35:50', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9002, 'Priya Patel', 'priya.demo@example.com', '9876543211', '$2y$10$dummy', 'customer', 'Active', NULL, '2026-03-07 04:35:50', '2026-04-21 04:35:50', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9003, 'Amit Kumar', 'amit.demo@example.com', '9876543212', '$2y$10$dummy', 'customer', 'Active', NULL, '2026-04-11 04:35:50', '2026-04-21 04:35:50', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9004, 'Sneha Reddy', 'sneha.demo@example.com', '9876543213', '$2y$10$dummy', 'customer', 'Active', NULL, '2026-04-19 04:35:50', '2026-04-21 04:35:50', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9005, 'Vikram Singh', 'vikram.demo@example.com', '9876543214', '$2y$10$dummy', 'customer', 'Inactive', NULL, '2025-10-03 04:35:50', '2026-04-21 04:35:50', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9006, 'test', 'test@test.com', '', '$2y$10$SIbQZkxzJ8Ih7oDJyk0jEOc1CuKxU57eDRSVBAsrqyK8PO6L87wsS', 'customer', 'Active', NULL, '2026-04-21 10:38:36', '2026-04-21 10:38:36', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9007, 'esakiraj', 'demo@gmail.com', '9047478886', '$2y$10$ux9/tDsTwPrBtuJ9phkpZOoPanx/yjwXoNNYS526tOrd5NY.YytTe', 'customer', 'Active', NULL, '2026-04-21 10:42:05', '2026-04-27 10:46:28', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9008, 'esakiraj', 'esakiraj006@gmail.com', '9047478886', '$2y$10$XX646u1E6pytfojYOIEp5es73ds9sNRb9ClYCmUmvc1l03E5D1J5S', 'customer', 'Active', NULL, '2026-04-21 10:53:51', '2026-05-04 12:16:59', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9009, 'Test User', 'test@example.com', '1234567890', '$2y$10$7z/GNk7AQbpMkq91uTKEbeQNdVL2iSAfsAAeEx3E9TTAKcNAURife', 'customer', 'Active', NULL, '2026-04-22 11:00:10', '2026-04-22 11:00:10', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9010, 'john', 'john@gmail.com', '8221633689', '$2y$10$srySC6VnO9DP9gjKe.CSoeVCFxav9Ys4fnjG1ZdiCZ0Jbn8RotDy2', 'customer', 'Active', NULL, '2026-04-30 12:45:27', '2026-05-02 06:06:00', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9011, 'john', 'johns@gmail.com', '8221633689', '$2y$10$re2tmwIV23eoiuMnC.d4oOL6cxO9y02D3.QygWpva93xZ6mLqsNSq', 'customer', 'Active', NULL, '2026-04-30 12:45:37', '2026-04-30 12:45:37', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9012, 'isaac', 'isaac@gmail.com', '8221633689', '$2y$10$wUIchWjN8DiNS5SYaXGap.8u6mu3NUd.9Ym.BiK/lHnq.uH2YJNgG', 'customer', 'Active', NULL, '2026-04-30 12:45:55', '2026-04-30 12:45:55', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9013, 'raj', 'raj@gmail.com', '9047478888', '$2y$10$QsNZ8M2OVYTzA4lDceO.ROLhXOXUg66ERpGg3P1SnlCA.XRGyr6Sy', 'customer', 'Active', NULL, '2026-05-04 10:29:14', '2026-05-04 10:29:14', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9014, 'nalej', 'nalej602nalej60273@imashr.com', '9047478887', '$2y$10$ERfj1t8cr1VUYdleMajtzeux1AVlhaau4paR5a8tRGs1t344jGWd2', 'customer', 'Active', NULL, '2026-05-11 06:46:47', '2026-05-11 06:46:47', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata'),
(9015, 'nalej', 'nalej60273@imashr.com', '885241255', '$2y$10$pNm/wqZlcXjETszYa89omeNx.YBW5AlgsdltnIEF8wUVkqWV/pBQu', 'customer', 'Active', NULL, '2026-05-11 07:57:23', '2026-05-11 07:57:23', NULL, 0, 'English (US)', '(UTC+05:30) Asia/Kolkata');

-- --------------------------------------------------------

--
-- Table structure for table `user_payment_methods`
--

CREATE TABLE `user_payment_methods` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('card','upi','wallet') NOT NULL,
  `provider` varchar(50) NOT NULL,
  `last4` varchar(4) DEFAULT NULL,
  `expiry` varchar(7) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_payment_methods`
--

INSERT INTO `user_payment_methods` (`id`, `user_id`, `type`, `provider`, `last4`, `expiry`, `token`, `is_default`, `created_at`) VALUES
(1, 1, 'card', 'Visa', '4242', '12/2026', NULL, 1, '2026-04-22 12:05:27'),
(2, 1, 'card', 'MasterCard', '5555', '08/2025', NULL, 0, '2026-04-22 12:05:27');

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `type` enum('allow','deny') DEFAULT 'allow'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES
(2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `pincode` varchar(6) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`id`, `name`, `pincode`, `latitude`, `longitude`, `is_active`, `created_at`) VALUES
(1, 'Chennai Main Hub', '600001', 13.08270000, 80.27070000, 1, '2026-04-28 12:39:36');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_inventory`
--

CREATE TABLE `warehouse_inventory` (
  `id` int(11) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT 0,
  `stock` int(11) NOT NULL DEFAULT 0,
  `reserved_stock` int(11) NOT NULL DEFAULT 0,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_login_activity`
--
ALTER TABLE `admin_login_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ala_admin` (`admin_id`),
  ADD KEY `idx_ala_status` (`status`),
  ADD KEY `idx_ala_created` (`created_at`);

--
-- Indexes for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `analytics_events`
--
ALTER TABLE `analytics_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_funnel` (`event_type`,`created_at`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `performed_by` (`performed_by`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`);

--
-- Indexes for table `audit_logs_v2`
--
ALTER TABLE `audit_logs_v2`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `fk_cart_items_combo` (`combo_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `parent_id` (`parent_id`),
  ADD KEY `idx_parent_id` (`parent_id`);

--
-- Indexes for table `combos`
--
ALTER TABLE `combos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `combo_items`
--
ALTER TABLE `combo_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `combo_items_ibfk_1` (`combo_id`),
  ADD KEY `combo_items_ibfk_2` (`product_id`);

--
-- Indexes for table `company_info`
--
ALTER TABLE `company_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `fk_coupon_creator` (`created_by`);

--
-- Indexes for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usage_coupon` (`coupon_id`),
  ADD KEY `idx_usage_user` (`user_id`),
  ADD KEY `idx_usage_order` (`order_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idx_customer_user` (`user_id`);

--
-- Indexes for table `customer_activity`
--
ALTER TABLE `customer_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_activity_user` (`user_id`),
  ADD KEY `idx_activity_time` (`created_at`);

--
-- Indexes for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_addr_user` (`user_id`);

--
-- Indexes for table `customer_metrics`
--
ALTER TABLE `customer_metrics`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `customer_notes`
--
ALTER TABLE `customer_notes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_note_user` (`user_id`);

--
-- Indexes for table `customer_profiles`
--
ALTER TABLE `customer_profiles`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `customer_tags`
--
ALTER TABLE `customer_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tag_user` (`user_id`);

--
-- Indexes for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `failed_orders`
--
ALTER TABLE `failed_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `created_at` (`created_at`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- Indexes for table `news_updates`
--
ALTER TABLE `news_updates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `shipping_address_id` (`shipping_address_id`),
  ADD KEY `billing_address_id` (`billing_address_id`),
  ADD KEY `idx_payment_method` (`payment_method`),
  ADD KEY `idx_idempotency` (`idempotency_key`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_items_ibfk_3` (`combo_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_password_resets_token_hash` (`token_hash`),
  ADD KEY `idx_password_resets_email` (`email`),
  ADD KEY `idx_password_resets_expires` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_payment_order` (`order_id`),
  ADD KEY `idx_payment_txn` (`transaction_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key_name` (`key_name`);

--
-- Indexes for table `pincodes`
--
ALTER TABLE `pincodes`
  ADD PRIMARY KEY (`pincode`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `slug_2` (`slug`),
  ADD KEY `status` (`status`),
  ADD KEY `fk_product_subcategory` (`subcategory_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_id` (`section_id`);

--
-- Indexes for table `refunds`
--
ALTER TABLE `refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_refund_order` (`order_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `fk_rp_perm` (`permission_id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`setting_key`),
  ADD KEY `group_name` (`group_name`);

--
-- Indexes for table `stock_activity`
--
ALTER TABLE `stock_activity`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_created` (`product_id`,`created_at`);

--
-- Indexes for table `stock_notifications`
--
ALTER TABLE `stock_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `email` (`email`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`);

--
-- Indexes for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_id`,`permission_id`),
  ADD KEY `fk_up_perm` (`permission_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `fk_ur_role` (`role_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `warehouse_inventory`
--
ALTER TABLE `warehouse_inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_inventory` (`warehouse_id`,`product_id`,`variant_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `admin_login_activity`
--
ALTER TABLE `admin_login_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `admin_notifications`
--
ALTER TABLE `admin_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `analytics_events`
--
ALTER TABLE `analytics_events`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=79;

--
-- AUTO_INCREMENT for table `audit_logs_v2`
--
ALTER TABLE `audit_logs_v2`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `combos`
--
ALTER TABLE `combos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `combo_items`
--
ALTER TABLE `combo_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT for table `company_info`
--
ALTER TABLE `company_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=162;

--
-- AUTO_INCREMENT for table `customer_activity`
--
ALTER TABLE `customer_activity`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer_addresses`
--
ALTER TABLE `customer_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `customer_notes`
--
ALTER TABLE `customer_notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_tags`
--
ALTER TABLE `customer_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `failed_orders`
--
ALTER TABLE `failed_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_logs`
--
ALTER TABLE `inventory_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `news_updates`
--
ALTER TABLE `news_updates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90077;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2098;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2032;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `refunds`
--
ALTER TABLE `refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `stock_activity`
--
ALTER TABLE `stock_activity`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `stock_notifications`
--
ALTER TABLE `stock_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subcategories`
--
ALTER TABLE `subcategories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9016;

--
-- AUTO_INCREMENT for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouse_inventory`
--
ALTER TABLE `warehouse_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cart_items_combo` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `combo_items`
--
ALTER TABLE `combo_items`
  ADD CONSTRAINT `combo_items_ibfk_1` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `combo_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `fk_coupon_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_usages`
--
ALTER TABLE `coupon_usages`
  ADD CONSTRAINT `fk_usage_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `fk_customer_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_metrics`
--
ALTER TABLE `customer_metrics`
  ADD CONSTRAINT `customer_metrics_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_tracking`
--
ALTER TABLE `delivery_tracking`
  ADD CONSTRAINT `delivery_tracking_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`billing_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`combo_id`) REFERENCES `combos` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_product_subcategory` FOREIGN KEY (`subcategory_id`) REFERENCES `subcategories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_activity`
--
ALTER TABLE `stock_activity`
  ADD CONSTRAINT `stock_activity_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subcategories`
--
ALTER TABLE `subcategories`
  ADD CONSTRAINT `subcategories_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_payment_methods`
--
ALTER TABLE `user_payment_methods`
  ADD CONSTRAINT `user_payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `fk_up_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_up_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `fk_ur_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `warehouse_inventory`
--
ALTER TABLE `warehouse_inventory`
  ADD CONSTRAINT `warehouse_inventory_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
