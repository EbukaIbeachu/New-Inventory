-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 16, 2026 at 08:50 AM
-- Server version: 5.7.17
-- PHP Version: 8.5.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `automation_tasks`
--

CREATE TABLE `automation_tasks` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `task_type` enum('email_alert','report_generation','backup') NOT NULL,
  `schedule_cron` varchar(50) DEFAULT '0 0 * * *',
  `is_active` tinyint(1) DEFAULT '1',
  `last_run` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `automation_tasks`
--

INSERT INTO `automation_tasks` (`id`, `name`, `task_type`, `schedule_cron`, `is_active`, `last_run`, `created_at`) VALUES
(1, 'Low stock', 'email_alert', '30 2 * * *', 1, '2026-01-13 12:24:32', '2026-01-13 08:56:10'),
(2, 'Low stock', 'email_alert', '30 2 * * *', 0, NULL, '2026-01-13 08:56:12'),
(3, 'Low stock', 'email_alert', '30 2 * * *', 0, NULL, '2026-01-13 08:56:13'),
(4, 'Low stock', 'email_alert', '30 2 * * *', 0, NULL, '2026-01-13 08:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT '0',
  `unit_price` decimal(10,2) DEFAULT '0.00',
  `location` varchar(100) DEFAULT NULL,
  `barcode_data` varchar(100) DEFAULT NULL,
  `description` text,
  `image_path` varchar(255) DEFAULT NULL,
  `low_stock_threshold` int(11) DEFAULT '10',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `sku`, `category`, `quantity`, `unit_price`, `location`, `barcode_data`, `description`, `image_path`, `low_stock_threshold`, `created_at`, `updated_at`) VALUES
(1, 'Marble', 'BDM0001', 'Beads', 9810, 4000.00, 'Warehouse', 'BDM0001', '', 'uploads/inventory/6968a93205947.jpg', 10, '2026-01-13 08:58:45', '2026-01-15 09:47:32'),
(2, 'Wooden Bead', 'WB0002', 'Beads', 950, 6000.00, 'Warehouse', 'WB0002', '', 'uploads/inventory/6967ea5e5c50f.jpg', 10, '2026-01-14 19:11:26', '2026-01-15 08:26:52');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `type` enum('inbound','outbound') NOT NULL,
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `status` enum('paid','unpaid','overdue') DEFAULT 'paid',
  `due_date` date DEFAULT NULL,
  `receipt_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `receipt_number`, `type`, `total_amount`, `customer_name`, `customer_phone`, `status`, `due_date`, `receipt_date`, `created_by`) VALUES
(4, 'REC-6968A4CC9F273', 'outbound', 700000.00, 'Alhaji', NULL, 'unpaid', '2026-01-15', '2026-01-15 09:26:52', 7),
(5, 'REC-6968A53648BDF', 'outbound', 80000.00, 'Emeka', NULL, 'paid', '2026-01-15', '2026-01-15 09:28:38', 7),
(6, 'REC-6968A8C2508FE', 'outbound', 200000.00, 'Suji', '07030782345', 'unpaid', '2026-01-15', '2026-01-15 09:43:46', 7),
(7, 'REC-6968B7B4ADBCA', 'outbound', 80000.00, 'Monday', '08134567890', 'unpaid', '2026-01-15', '2026-01-15 10:47:32', 8);

-- --------------------------------------------------------

--
-- Table structure for table `receipt_items`
--

CREATE TABLE `receipt_items` (
  `id` int(11) NOT NULL,
  `receipt_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `receipt_items`
--

INSERT INTO `receipt_items` (`id`, `receipt_id`, `inventory_id`, `quantity`, `unit_price`) VALUES
(8, 5, 1, 20, 4000.00),
(7, 4, 2, 50, 6000.00),
(6, 4, 1, 100, 4000.00),
(10, 7, 1, 20, 4000.00),
(9, 6, 1, 50, 4000.00);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('company_name', 'My Inventory System'),
('currency_symbol', '$'),
('email_smtp_host', 'smtp.example.com'),
('email_smtp_user', 'user@example.com'),
('email_smtp_pass', 'password'),
('email_from_address', 'noreply@example.com');

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `activity_log`
--

-- (no initial rows)

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reset_token` varchar(100) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `status`, `reset_token`, `reset_expires`, `created_at`) VALUES
(8, 'Chika Admin', 'gwangzou@gmail.com', '$2y$12$OFRXhCZ1Uhoqz5bAJF2rHeZkZ/Cr/VudcC1c2fdVz/ELiivvoDscS', 'admin', 'approved', NULL, NULL, '2026-01-15 09:05:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `automation_tasks`
--
ALTER TABLE `automation_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `receipt_items`
--
ALTER TABLE `receipt_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `receipt_id` (`receipt_id`),
  ADD KEY `inventory_id` (`inventory_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `automation_tasks`
--
ALTER TABLE `automation_tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `receipt_items`
--
ALTER TABLE `receipt_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
