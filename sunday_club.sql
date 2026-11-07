-- phpMyAdmin SQL Dump
-- version 6.0.0-dev+20261024.6eba7ba9ad
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 07, 2026 at 08:27 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sunday_club`
--

-- --------------------------------------------------------

--
-- Table structure for table `sc_orders`
--
CREATE TABLE `sc_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_number` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tracking_access_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_name` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address_1` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address_2` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `shipping_city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_state` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_postal_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_country` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'US',
  `shipping_method` enum('standard','express') COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_status` enum('unfulfilled','processing','packed','shipped','out_for_delivery','delivered','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'processing',
  `carrier_name` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_method` enum('cash_on_delivery') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cash_on_delivery',
  `payment_status` enum('pending','paid','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PKR',
  `subtotal_cents` int UNSIGNED NOT NULL,
  `shipping_cents` int UNSIGNED NOT NULL,
  `tax_cents` int UNSIGNED NOT NULL DEFAULT '0',
  `total_cents` int UNSIGNED NOT NULL,
  `customer_note` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `out_for_delivery_at` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sc_orders`
--

INSERT INTO `sc_orders` (`id`, `order_number`, `tracking_access_hash`, `customer_name`, `customer_email`, `customer_phone`, `shipping_address_1`, `shipping_address_2`, `shipping_city`, `shipping_state`, `shipping_postal_code`, `shipping_country`, `shipping_method`, `shipping_status`, `carrier_name`, `tracking_number`, `payment_method`, `payment_status`, `currency`, `subtotal_cents`, `shipping_cents`, `tax_cents`, `total_cents`, `customer_note`, `created_at`, `updated_at`, `out_for_delivery_at`, `delivered_at`) VALUES
(4, 'SC-20261107-6F0A86', 'd43508911f404623502d4ed3322dfcf99f212c2592050330c3fc283d8db7e0d8', 'sardar khan', 'sardarkhan@gmail.om', '03702812998', 'satellite town block no 4', '', 'Quetta', '', '', 'PK', 'standard', 'delivered', NULL, NULL, 'cash_on_delivery', 'paid', 'PKR', 17200, 0, 0, 17200, 'when reached call me.', '2026-11-07 06:16:49', '2026-11-07 07:59:38', NULL, '2026-11-07 11:34:56'),
(7, 'SC-20261107-EA0F44', '10b73133f52316548ddb8e33e2fb17fd2b8961b50d3dffbb9cf81f03271e1f6b', 'sardar khan', 'sardarkhan4699@gmail.com', '03702812095', 'satellite town block no 4', '', 'Quetta', '', '', 'PK', 'standard', 'delivered', NULL, NULL, 'cash_on_delivery', 'paid', 'PKR', 17200, 0, 0, 17200, 'When reached call me.', '2026-11-07 07:56:24', '2026-11-07 07:59:44', '2026-11-07 12:57:45', '2026-11-07 12:59:06');

-- --------------------------------------------------------

--
-- Table structure for table `sc_order_items`
--
CREATE TABLE `sc_order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` int UNSIGNED DEFAULT NULL,
  `sku` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` smallint UNSIGNED NOT NULL,
  `unit_price_cents` int UNSIGNED NOT NULL,
  `line_total_cents` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sc_order_items`
--

INSERT INTO `sc_order_items` (`id`, `order_id`, `product_id`, `sku`, `product_name`, `quantity`, `unit_price_cents`, `line_total_cents`) VALUES
(8, 7, 1, 'SC-BOOT-001', 'The Sunday Boot', 1, 12400, 12400),
(9, 7, 2, 'SC-BALL-001', 'Match Day Ball', 1, 4800, 4800);

-- --------------------------------------------------------

--
-- Table structure for table `sc_products`
--
CREATE TABLE `sc_products` (
  `id` int UNSIGNED NOT NULL,
  `sku` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_type` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `badge` varchar(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `rating` decimal(2,1) NOT NULL DEFAULT '4.5',
  `price_cents` int UNSIGNED NOT NULL,
  `stock_quantity` int UNSIGNED NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `removed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sc_products`
--

INSERT INTO `sc_products` (`id`, `sku`, `name`, `slug`, `category`, `product_type`, `description`, `image_path`, `badge`, `rating`, `price_cents`, `stock_quantity`, `active`, `created_at`, `updated_at`, `removed_at`) VALUES
(1, 'SC-BOOT-001', 'The Sunday Boot', 'the-sunday-boot', 'Boots', 'Firm ground', 'A little extra magic, every match.', 'assets/products/boot-sunday.jpg', 'BESTSELLER', 4.9, 12400, 16, 1, '2026-11-06 11:35:25', '2026-11-07 07:56:24', NULL),
(2, 'SC-BALL-001', 'Match Day Ball', 'match-day-ball', 'Balls', 'Match ball · size 5', 'Made for those top-corner moments.', 'assets/products/ball-match.jpg', 'PLAYER FAVE', 4.8, 4800, 22, 1, '2026-11-06 11:35:25', '2026-11-07 07:56:24', NULL),
(3, 'SC-BOOT-002', 'Everyday Boot', 'everyday-boot', 'Boots', 'Artificial grass', 'Comfort that goes the full 90.', 'assets/products/boot-everyday.jpg', 'NEW IN', 4.7, 9800, 22, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(4, 'SC-SOCK-001', 'Grip Knit Socks', 'grip-knit-socks', 'Apparel', 'One size · 2 pack', 'Stay put. Play on.', 'assets/products/socks.jpg', '', 4.8, 2200, 40, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(5, 'SC-BALL-002', 'Sunday Training Ball', 'sunday-training-ball', 'Balls', 'Training ball · size 5', 'Your first touch’s new best friend.', 'assets/products/ball-training.jpg', '', 4.6, 3200, 32, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(6, 'SC-BOOT-003', 'Match Day Boot', 'match-day-boot', 'Boots', 'Firm ground', 'Light on your feet, big on confidence.', 'assets/products/boot-match-day.png', 'JUST LANDED', 4.9, 14500, 12, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(7, 'SC-CONE-001', 'Training Cones', 'training-cones', 'Training', 'Set of 20', 'Little cones. Big improvement.', 'assets/products/cones.jpg', '', 4.7, 1800, 55, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(8, 'SC-TOP-001', 'The Warm-Up Top', 'the-warm-up-top', 'Apparel', 'Recycled performance jersey', 'Easy layers for early kick-offs.', 'assets/products/warmup-top.jpg', '', 4.8, 5400, 24, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(9, 'SC-GLOVE-001', 'Goalkeeper Gloves', 'goalkeeper-gloves', 'Accessories', 'Adult · all weather', 'Big saves start with a good grip.', 'assets/products/goalkeeper-gloves.jpg', 'PLAYER FAVE', 4.9, 4600, 16, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(10, 'SC-BAG-001', 'The Boot Bag', 'the-boot-bag', 'Accessories', 'Water-resistant', 'Keep the mud where it belongs.', 'assets/products/boot-bag.jpg', '', 4.6, 2800, 29, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(11, 'SC-BIB-001', 'Training Bibs', 'training-bibs', 'Training', 'Set of 5 · assorted', 'Pick teams, then get out there.', 'assets/products/training-bibs.jpg', '', 4.5, 2500, 34, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(12, 'SC-BALL-003', 'Junior Match Ball', 'junior-match-ball', 'Balls', 'Size 4 · ages 8–12', 'For the next generation of game changers.', 'assets/products/ball-junior.jpg', '', 4.8, 3400, 27, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(13, 'SC-BOOT-004', 'Soft Ground Boot', 'soft-ground-boot', 'Boots', 'Soft ground', 'For wet pitches and big ambitions.', 'assets/products/boot-soft-ground.jpg', '', 4.7, 13200, 14, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(14, 'SC-ARM-001', 'The Captain’s Armband', 'the-captain-s-armband', 'Accessories', 'Adjustable · one size', 'Wear it like you mean it.', 'assets/products/captains-armband.svg', '', 4.6, 1200, 44, 1, '2026-11-06 11:35:25', '2026-11-07 06:30:34', NULL),
(15, 'SC-LADDER-001', 'Agility Ladder', 'agility-ladder', 'Training', '4m · carry bag included', 'Quick feet make good things happen.', 'assets/products/agility-ladder.jpg', '', 4.7, 3600, 20, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(16, 'SC-HOODIE-001', 'The Sunday Hoodie', 'the-sunday-hoodie', 'Apparel', 'Organic cotton blend', 'For the walk to the pitch and back.', 'assets/products/hoodie.jpg', '', 4.9, 6800, 18, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(17, 'SC-SHIN-001', 'Shin Guards', 'shin-guards', 'Accessories', 'Lightweight · adult', 'A little peace of mind, built in.', 'assets/products/shin-guards.jpg', '', 4.6, 2400, 32, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(18, 'SC-WHISTLE-001', 'Referee Whistle Set', 'referee-whistle-set', 'Accessories', '2 whistles · lanyards', 'For keeping the beautiful game beautiful.', 'assets/products/whistle.jpg', '', 4.5, 1600, 25, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(19, 'SC-JACKET-001', 'The Touchline Jacket', 'the-touchline-jacket', 'Apparel', 'Lightweight · water resistant', 'For the ones cheering from the side.', 'assets/products/jacket.jpg', 'NEW IN', 4.8, 8900, 13, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(20, 'SC-PUMP-001', 'Pump & Needle Kit', 'pump-needle-kit', 'Accessories', 'Dual-action mini pump', 'Because a flat ball never scored.', 'assets/products/pump-kit.png', '', 4.7, 1900, 31, 1, '2026-11-06 11:35:25', '2026-11-06 12:13:24', NULL),
(21, 'SC-SLIDE-001', 'Recovery Slides', 'recovery-slides', 'Accessories', 'Comfort footbed', 'You earned the walk-off comfort.', 'assets/products/slides.jpg', '', 4.8, 3800, 22, 1, '2026-11-06 11:35:25', '2026-11-06 12:09:51', NULL),
(22, 'SC-DUFFEL-001', 'The Away Day Duffel', 'the-away-day-duffel', 'Accessories', '35L · recycled fabric', 'Room for everything but an excuse.', 'assets/products/duffel.jpg', '', 4.9, 76000, 15, 1, '2026-11-06 11:35:25', '2026-11-07 08:21:49', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `sc_orders`
--
ALTER TABLE `sc_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sc_orders_number` (`order_number`),
  ADD KEY `idx_sc_orders_email` (`customer_email`),
  ADD KEY `idx_sc_orders_shipping_status` (`shipping_status`),
  ADD KEY `idx_sc_orders_created_at` (`created_at`);

--
-- Indexes for table `sc_order_items`
--
ALTER TABLE `sc_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sc_order_items_order` (`order_id`),
  ADD KEY `fk_sc_order_items_product` (`product_id`);

--
-- Indexes for table `sc_products`
--
ALTER TABLE `sc_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sc_products_sku` (`sku`),
  ADD UNIQUE KEY `uq_sc_products_slug` (`slug`),
  ADD KEY `idx_sc_products_category_active` (`category`,`active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sc_orders`
--
ALTER TABLE `sc_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `sc_order_items`
--
ALTER TABLE `sc_order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `sc_products`
--
ALTER TABLE `sc_products`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sc_order_items`
--
ALTER TABLE `sc_order_items`
  ADD CONSTRAINT `fk_sc_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `sc_orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sc_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `sc_products` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
