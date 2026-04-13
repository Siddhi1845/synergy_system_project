-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 08, 2025 at 01:44 PM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `synergy_system_project`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `performed_by` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `action`, `performed_by`, `created_at`) VALUES
(1, 'Added new employee: Samruddhi Janwalkar', 'admin', '2025-09-24 15:44:04');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `complaint_text` text NOT NULL,
  `status` enum('pending','in-progress','resolved','closed') DEFAULT 'pending',
  `remarks` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `address` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`customer_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `mobile_no`, `address`, `created_at`) VALUES
(2, 'Sai', 'Malvankar', 'sai@gmail.com', '$2y$10$Y3sj1tZuKFA/5l6JgDVBKeOFzo9Ye0X2EqXbYKCc3AZiP4tTB4Z2C', '9087654321', '9087654321', 'Malvan', '2025-09-19 07:52:36'),
(5, 'Tarvesh', 'Vichare', 'tarvesh@123', '$2y$10$PAVG79yGwJ6fxVdLwzkLwekKurZlyt32JouJHVak2qQSSHLdBiQHm', '9322325854', '9322325854', 'Ratnagiri', '2025-09-24 15:48:43'),
(6, 'Siddhi', 'Juvatkar', 'juvatkarsiddhi00@gmail.com', '$2y$10$EdLT4cAmvqg0DSHliUGG7O1LxItcaUos.zmmnbJTYn/n1jeAgPG5a', '7385453654', '7385453654', 'Malvan', '2025-09-24 16:22:12');

-- --------------------------------------------------------

--
-- Table structure for table `employee_details`
--

CREATE TABLE `employee_details` (
  `employee_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `date_of_joining` date NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `designation` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `mobile_no` varchar(15) NOT NULL,
  `whatsapp_no` varchar(15) DEFAULT NULL,
  `email_id` varchar(100) NOT NULL,
  `residential_address` varchar(255) DEFAULT NULL,
  `permanent_address` varchar(255) DEFAULT NULL,
  `aadhar_card_no` varchar(20) NOT NULL,
  `pan_card_no` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `employee_details`
--

INSERT INTO `employee_details` (`employee_id`, `user_id`, `date_of_joining`, `address`, `designation`, `first_name`, `middle_name`, `last_name`, `mobile_no`, `whatsapp_no`, `email_id`, `residential_address`, `permanent_address`, `aadhar_card_no`, `pan_card_no`) VALUES
(1, 2, '2021-10-16', 'Malvan, Sindhudurg', 'Manager', 'Harsh', 'Prasad', 'Bandekar', '8446142027', NULL, 'bandekarharsh124@gmail.com', 'Malvan', 'Malvan', '', NULL),
(2, 3, '2025-09-10', NULL, 'HR', 'Anuja ', 'Ravindra', 'Dharane', '9322383559', NULL, 'anujadh2505@gmail.com', 'Devgad', 'Devgad', '', NULL),
(3, 4, '2024-11-27', NULL, 'Secretary', 'Siddhi', 'Dipak ', 'Juvatkar', '7385453654', NULL, 'juvatkarsiddhi00@gmail.com', NULL, NULL, '', NULL),
(4, 5, '2025-09-25', NULL, 'HR', 'Samruddhi', 'Shivaji', 'Janwalkar', '9322983109', NULL, 'samruddhi@gmail.com', 'Chiplun', 'Ratnagiri', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_type` enum('customer','employee','admin') NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_type`, `user_id`, `message`, `is_read`, `created_at`) VALUES
(1, 'customer', 3, 'Your order #12 status has been updated to Delivered.', 0, '2025-09-21 18:01:39'),
(2, 'customer', 2, 'Your order #10 status has been updated to Delivered.', 1, '2025-09-21 18:02:12'),
(3, 'customer', 4, 'Your order #13 status has been updated to Approved.', 1, '2025-09-21 18:04:57'),
(4, 'customer', 4, 'Your order #13 status has been updated to Delivered.', 1, '2025-09-21 18:05:19'),
(5, 'customer', 4, 'Your order #14 status has been updated to Approved.', 1, '2025-09-24 08:31:47'),
(6, 'customer', 4, 'Your order #15 status has been updated to Approved.', 0, '2025-09-24 12:41:06'),
(7, 'customer', 5, 'Your order #16 status has been updated to Approved.', 0, '2025-09-24 16:17:17'),
(8, 'customer', 6, 'Your order #18 status has been updated to Approved.', 1, '2025-09-24 16:22:53'),
(9, 'customer', 6, 'Your order #20 status has been updated to Approved.', 1, '2025-09-24 16:59:07'),
(10, 'customer', 6, 'Your order #20 status has been updated to Delivered.', 1, '2025-09-24 16:59:38'),
(11, 'customer', 6, 'Your order #22 status has been updated to Cancelled.', 1, '2025-09-24 17:11:46'),
(12, 'customer', 6, 'Your order #21 status has been updated to Cancelled.', 1, '2025-09-24 17:15:57'),
(13, 'customer', 6, 'Your order #27 status has been updated to Delivered.', 1, '2025-09-24 17:18:01'),
(14, 'customer', 6, 'Your order #28 status has been updated to Delivered.', 1, '2025-09-24 17:39:46'),
(15, 'customer', 6, 'Your order #1 status has been updated to Approved.', 1, '2025-09-25 08:28:39'),
(16, 'customer', 6, 'Your order #1 status has been updated to Delivered.', 1, '2025-09-25 08:28:55'),
(17, 'customer', 6, 'Your order #2 status has been updated to Approved.', 1, '2025-09-29 09:03:30');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) DEFAULT '1',
  `price` decimal(10,2) NOT NULL,
  `order_date` date NOT NULL,
  `service_start` date DEFAULT NULL,
  `service_expiry` date DEFAULT NULL,
  `status` enum('pending','approved','delivered','cancelled') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `customer_id`, `product_id`, `quantity`, `price`, `order_date`, `service_start`, `service_expiry`, `status`) VALUES
(1, 6, 4, 2, '0.00', '2025-09-25', '2025-09-25', '2026-09-25', 'delivered'),
(2, 6, 5, 1, '0.00', '2025-09-29', '2025-09-29', '2026-09-29', 'approved');

--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `total_qty` int(11) NOT NULL,
  `sold_qty` int(11) DEFAULT '0',
  `remaining_qty` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `type` varchar(100) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `model_no` varchar(100) DEFAULT NULL,
  `warranty` int(11) DEFAULT NULL,
  `servicing_warranty` int(11) DEFAULT NULL,
  `frequency_service` int(11) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `category`, `total_qty`, `sold_qty`, `remaining_qty`, `created_at`, `type`, `company`, `model_no`, `warranty`, `servicing_warranty`, `frequency_service`, `price`) VALUES
(4, 'Battery', 'Battery', 50, -23, 73, '2025-09-19 07:57:51', 'Hi-80Ah', 'Hi-Power', '121', 12, 6, 6, '0.00'),
(5, 'Hide and Seek', 'Biscuit', 1, 0, 1, '2025-09-24 15:47:14', 'Food', 'Britannia', '1', 1, 0, 0, '0.00'),
(6, 'adfdhg', 'fgf', 10, -7, 17, '2025-09-24 16:57:12', 'dfg', 'xdh', '132456', 12, 12, 12, '500.00');

-- --------------------------------------------------------

--
-- Table structure for table `reminder_logs`
--

CREATE TABLE `reminder_logs` (
  `id` int(11) NOT NULL,
  `reminder_id` int(11) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `message` text,
  `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `service_reminders`
--

CREATE TABLE `service_reminders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `next_service_date` date NOT NULL,
  `recurrence_months` int(11) NOT NULL DEFAULT '3',
  `status` enum('Pending','Completed') NOT NULL DEFAULT 'Pending',
  `last_completed` datetime DEFAULT NULL,
  `notes` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `service_reminders`
--

INSERT INTO `service_reminders` (`id`, `customer_id`, `product_id`, `product_name`, `next_service_date`, `recurrence_months`, `status`, `last_completed`, `notes`, `created_at`) VALUES
(1, 2, 4, 'hjuyrdg', '2026-11-26', 1, 'Completed', '2025-09-28 15:49:29', 'sdfg', '2025-09-25 12:54:40'),
(2, 6, 4, 'zgf', '2025-10-26', 1, 'Completed', '2025-09-28 15:58:11', 'ngfdfzf', '2025-09-25 12:55:05'),
(3, 2, 4, NULL, '2026-12-26', 1, 'Pending', NULL, 'sdfg', '2025-09-28 10:19:29'),
(4, 6, 4, NULL, '2025-11-26', 1, 'Completed', '2025-09-29 14:34:41', 'ngfdfzf', '2025-09-28 10:19:51'),
(5, 6, 4, NULL, '2025-11-26', 1, 'Pending', NULL, 'ngfdfzf', '2025-09-28 10:28:11'),
(6, 6, 4, NULL, '2025-12-26', 1, 'Pending', NULL, 'ngfdfzf', '2025-09-29 09:04:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`, `last_login`) VALUES
(1, 'admin', 'admin123', 'admin', NULL),
(2, 'Harsh@01', '123456', 'employee', NULL),
(3, 'Anu', 'Anuja', 'employee', NULL),
(4, 'Siddhi@18', 'Siddhi@18', 'employee', NULL),
(5, 'Samruddhi@19', '$2y$10$RRDwFDq41kdRgX0lCCRhmurZBq2QQ3RonoaW.X6InFxWQUmHHdOYO', 'employee', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `fk_customer` (`customer_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `employee_details`
--
ALTER TABLE `employee_details`
  ADD PRIMARY KEY (`employee_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `orders_ibfk_1` (`customer_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `reminder_logs`
--
ALTER TABLE `reminder_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `service_reminders`
--
ALTER TABLE `service_reminders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employee_details`
--
ALTER TABLE `employee_details`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reminder_logs`
--
ALTER TABLE `reminder_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `service_reminders`
--
ALTER TABLE `service_reminders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `fk_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_details`
--
ALTER TABLE `employee_details`
  ADD CONSTRAINT `employee_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
