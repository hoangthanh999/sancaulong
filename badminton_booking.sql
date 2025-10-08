-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 08, 2025 at 12:01 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `badminton_booking`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `court_id` int NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','approved','rejected','cancelled','paid') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deposit_status` enum('pending','paid') COLLATE utf8mb4_unicode_ci DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `court_id`, `booking_date`, `start_time`, `end_time`, `total_price`, `status`, `created_at`, `deposit_status`) VALUES
(1, 2, 1, '2025-10-08', '07:00:00', '08:00:00', '100000.00', 'paid', '2025-10-08 06:59:07', 'pending'),
(2, 2, 2, '2025-10-08', '07:00:00', '08:00:00', '150000.00', 'paid', '2025-10-08 07:22:23', 'pending'),
(3, 3, 1, '2025-10-09', '21:00:00', '22:00:00', '100000.00', 'paid', '2025-10-08 07:43:56', 'paid'),
(4, 3, 1, '2025-10-09', '04:00:00', '05:00:00', '100000.00', 'paid', '2025-10-08 08:57:48', 'pending'),
(5, 3, 1, '2025-10-08', '16:00:00', '17:00:00', '100000.00', 'pending', '2025-10-08 08:58:09', 'pending'),
(6, 4, 1, '2025-10-09', '17:45:00', '19:45:00', '200000.00', 'paid', '2025-10-08 10:37:01', 'pending'),
(7, 4, 2, '2025-10-09', '06:00:00', '19:15:00', '1987500.00', 'paid', '2025-10-08 11:07:13', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `courts`
--

CREATE TABLE `courts` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price_per_hour` decimal(10,2) NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courts`
--

INSERT INTO `courts` (`id`, `name`, `description`, `price_per_hour`, `status`, `created_at`) VALUES
(1, 'Sân 1', 'Sân tiêu chuẩn, có đèn chiếu sáng', '100000.00', 'active', '2025-10-08 02:18:32'),
(2, 'Sân 2', 'Sân VIP, có điều hòa', '150000.00', 'active', '2025-10-08 02:18:32'),
(4, 'Sân 3', 'sân này mới', '200000.00', 'active', '2025-10-08 07:13:14');

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `user_id`, `message`, `created_at`) VALUES
(1, 2, 'hungg', '2025-10-08 14:42:12'),
(2, 3, 'yếu', '2025-10-08 14:43:25');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','bank_transfer','momo','vnpay') COLLATE utf8mb4_unicode_ci DEFAULT 'cash',
  `payment_status` enum('pending','completed','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `transaction_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `key` varchar(100) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`key`, `value`) VALUES
('account_name', 'Ho Van hung'),
('account_no', '0962750432'),
('bank_code', 'vietcombank'),
('deposit_percent', '15'),
('qr_template', 'compact'),
('site_name', 'BS Badminton');

-- --------------------------------------------------------

--
-- Table structure for table `timeslots`
--

CREATE TABLE `timeslots` (
  `id` int NOT NULL,
  `label` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `timeslots`
--

INSERT INTO `timeslots` (`id`, `label`, `start_time`, `end_time`, `status`, `created_at`) VALUES
(1, '04:00 - 05:00', '04:00:00', '05:00:00', 'active', '2025-10-08 14:34:29'),
(2, '05:00 - 06:00', '05:00:00', '06:00:00', 'active', '2025-10-08 14:34:29'),
(3, '06:00 - 07:00', '06:00:00', '07:00:00', 'active', '2025-10-08 14:34:29'),
(4, '07:00 - 08:00', '07:00:00', '08:00:00', 'active', '2025-10-08 14:34:29'),
(5, '08:00 - 09:00', '08:00:00', '09:00:00', 'active', '2025-10-08 14:34:29'),
(6, '09:00 - 10:00', '09:00:00', '10:00:00', 'active', '2025-10-08 14:34:29'),
(7, '10:00 - 11:00', '10:00:00', '11:00:00', 'active', '2025-10-08 14:34:29'),
(8, '11:00 - 12:00', '11:00:00', '12:00:00', 'active', '2025-10-08 14:34:29'),
(9, '12:00 - 13:00', '12:00:00', '13:00:00', 'active', '2025-10-08 14:34:29'),
(10, '13:00 - 14:00', '13:00:00', '14:00:00', 'active', '2025-10-08 14:34:29'),
(11, '14:00 - 15:00', '14:00:00', '15:00:00', 'active', '2025-10-08 14:34:29'),
(12, '15:00 - 16:00', '15:00:00', '16:00:00', 'active', '2025-10-08 14:34:29'),
(13, '16:00 - 17:00', '16:00:00', '17:00:00', 'active', '2025-10-08 14:34:29'),
(14, '17:00 - 18:00', '17:00:00', '18:00:00', 'active', '2025-10-08 14:34:29'),
(15, '18:00 - 19:00', '18:00:00', '19:00:00', 'active', '2025-10-08 14:34:29'),
(16, '19:00 - 20:00', '19:00:00', '20:00:00', 'active', '2025-10-08 14:34:29'),
(17, '20:00 - 21:00', '20:00:00', '21:00:00', 'active', '2025-10-08 14:34:29'),
(18, '21:00 - 22:00', '21:00:00', '22:00:00', 'active', '2025-10-08 14:34:29'),
(19, '22:00 - 23:00', '22:00:00', '23:00:00', 'active', '2025-10-08 14:34:29'),
(20, '23:00 - 00:00', '23:00:00', '00:00:00', 'active', '2025-10-08 14:34:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','inactive','blocked') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `full_name`, `phone`, `role`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@badminton.com', 'Administrator', NULL, 'admin', 'active', '2025-10-08 02:18:32'),
(2, 'user_1b5b24', '$2y$10$z7N9oYacYJTowCuyJE/xG.fF6sfAIDf.lA8rxKrQUASXzibB4C8Se', 'HOVAN@gmail.com', 'HO VAN', '0566225777', 'admin', 'active', '2025-10-08 06:34:27'),
(3, 'user_294063', '$2y$10$3fdviJDCUezWm0WnLV0tsuBwuYL6w6ZNuwabqhI5a6W5/1CAQI7gS', 'ha@gmail.com', 'haha', '0566225770', 'user', 'active', '2025-10-08 07:43:08'),
(4, 'user_fc3c6b', '$2y$10$fUQaxXBHYvuPUUSHE3W.8.5XT.t16AMxnAVs9Y1q3oumYHrcOwHHC', 'hung2@gmail.com', 'O VAN HUNG', '0962750432', 'admin', 'active', '2025-10-08 10:35:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `court_id` (`court_id`);

--
-- Indexes for table `courts`
--
ALTER TABLE `courts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `timeslots`
--
ALTER TABLE `timeslots`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `courts`
--
ALTER TABLE `courts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timeslots`
--
ALTER TABLE `timeslots`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`court_id`) REFERENCES `courts` (`id`);

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
