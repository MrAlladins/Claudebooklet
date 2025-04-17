-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 17, 2025 at 07:14 AM
-- Server version: 10.11.10-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u357300497_thaibooklet`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `active`) VALUES
(1, 'Restaurant', 'Restauranger och matställen', 1),
(2, 'Massage', 'Massage och spa', 1),
(3, 'Beer Bar', 'Barer och pubar', 1),
(4, 'Shopping', 'Butiker och köpcentrum', 1),
(5, 'Activities', 'Aktiviteter och upplevelser', 1);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_info` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `contact_info`, `logo_url`, `active`) VALUES
(1, 'Karlssons Restaurant', 'mail, telefon', 'https://www.karlssonrestaurant.com/wp-content/uploads/2024/07/logo-karlson-03-1.png', 1);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` int(11) NOT NULL,
  `edition_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `value` varchar(255) NOT NULL,
  `terms` text DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  `max_uses` int(11) DEFAULT NULL,
  `current_uses` int(11) NOT NULL DEFAULT 0,
  `status` enum('active','inactive','expired') NOT NULL DEFAULT 'active',
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `edition_id`, `company_id`, `category_id`, `title`, `description`, `value`, `terms`, `valid_from`, `valid_until`, `max_uses`, `current_uses`, `status`, `image_path`) VALUES
(1, 1, 1, 1, 'Free coffe', 'When you eat, you get free coffe. ', 'Free coffe for adults', 'Only valid on per user', '2025-04-16', '2025-04-30', NULL, 0, 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupon_uses`
--

CREATE TABLE `coupon_uses` (
  `id` int(11) NOT NULL,
  `coupon_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `used_at` datetime NOT NULL DEFAULT current_timestamp(),
  `verification_code` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `editions`
--

CREATE TABLE `editions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `published_at` datetime DEFAULT NULL,
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `woocommerce_product_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `valid_until` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `editions`
--

INSERT INTO `editions` (`id`, `title`, `description`, `created_at`, `published_at`, `status`, `woocommerce_product_id`, `image_path`, `valid_until`) VALUES
(1, 'KaronBooklet', 'offers in karonbeach', '2025-04-16 10:03:33', '2025-04-15 13:23:04', 'published', NULL, 'uploads/editions/Booklet_600x600_PatongKata20241.png', '2025-12-31');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','manager','customer') NOT NULL DEFAULT 'customer'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `name`, `created_at`, `role`) VALUES
(3, 'admin@example.com', '112233', 'Administrator', '2025-04-16 09:40:36', 'admin'),
(4, 'test@example.com', '$2y$10$iusesomecrazystring22', 'Test User', '2025-04-16 09:41:39', 'admin'),
(9, 'testadmin@example.com', '$2y$10$DnDpj9Owg3sxHeEvB27MUuEpq5V5X1c9jtxDWWngw0WIHCqokxz56', 'Test Admin', '2025-04-16 10:02:29', 'admin'),
(10, 'mikahei1970@gmail.com', '$2y$10$aRnj6A4H.8a1iouJ/P3ahe5j0iduatcj3SYCY0kER3DV4nThdmhzm', 'Mika Heikkinen', '2025-04-16 11:20:05', 'admin'),
(11, 'jonas.d.stromberg@gmail.com', '$2y$10$4kbAegnBH9kOKTADrsxdDOZkJ02E84vS00CxpYpojXf30wuCRySQq', 'jag', '2025-04-16 12:21:37', 'admin'),
(12, 'mikaheik1970@gmail.com', '$2y$10$LPRCgNMKDJ9wU4Uf2vt1V.rZlwuCcaRLPy4BJLbnU3Y8F9AGopxva', 'Mika test', '2025-04-16 12:39:46', 'manager');

-- --------------------------------------------------------

--
-- Table structure for table `user_editions`
--

CREATE TABLE `user_editions` (
  `user_id` int(11) NOT NULL,
  `edition_id` int(11) NOT NULL,
  `purchased_at` datetime NOT NULL DEFAULT current_timestamp(),
  `woocommerce_order_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `edition_id` (`edition_id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `coupons_ibfk_3` (`category_id`);

--
-- Indexes for table `coupon_uses`
--
ALTER TABLE `coupon_uses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `coupon_id` (`coupon_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `editions`
--
ALTER TABLE `editions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_editions`
--
ALTER TABLE `user_editions`
  ADD PRIMARY KEY (`user_id`,`edition_id`),
  ADD KEY `edition_id` (`edition_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `coupon_uses`
--
ALTER TABLE `coupon_uses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `editions`
--
ALTER TABLE `editions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `coupons`
--
ALTER TABLE `coupons`
  ADD CONSTRAINT `coupons_ibfk_1` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupons_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `coupon_uses`
--
ALTER TABLE `coupon_uses`
  ADD CONSTRAINT `coupon_uses_ibfk_1` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `coupon_uses_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_editions`
--
ALTER TABLE `user_editions`
  ADD CONSTRAINT `user_editions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_editions_ibfk_2` FOREIGN KEY (`edition_id`) REFERENCES `editions` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
