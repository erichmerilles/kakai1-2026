-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 20, 2026 at 03:48 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kakaione`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`log_id`, `user_id`, `action`, `module`, `description`, `created_at`) VALUES
(1, 1, 'Generate', 'Payroll', 'Generated payroll run #9 for period 2026-02-19 to 2026-02-20 (1 employee(s) processed)', '2026-02-20 13:32:05'),
(2, 1, 'Generate', 'Payroll', 'Generated payroll run #10 for period 2026-02-19 to 2026-02-20 (1 employee(s) processed)', '2026-02-20 13:36:03'),
(3, 1, 'Generate', 'Payroll', 'Generated payroll run #11 for period 2026-02-15 to 2026-02-20 (1 employee(s) processed)', '2026-02-20 13:44:36'),
(4, 1, 'Generate', 'Payroll', 'Generated payroll run #12 for period 2026-02-15 to 2026-02-20 (1 employee(s) processed)', '2026-02-20 13:50:32'),
(5, 1, 'Generate', 'Payroll', 'Generated payroll run #13 for period 2026-02-15 to 2026-02-20 (1 employee(s) processed)', '2026-02-20 14:03:02'),
(6, 1, 'Generate', 'Payroll', 'Generated payroll run #19 for period 2026-02-15 to 2026-02-20 (2 employee(s) processed)', '2026-02-20 14:08:22'),
(7, 1, 'Manual Entry', 'Attendance', 'Manually logged attendance for Employee ID: 8 on Feb 18, 2026', '2026-02-20 14:09:35'),
(8, 1, 'Generate', 'Payroll', 'Generated payroll run #20 for period 2026-02-14 to 2026-02-20 (1 employee(s) processed)', '2026-02-20 14:09:45'),
(9, 1, 'Generate', 'Payroll', 'Generated payroll run #21 for period 2026-02-15 to 2026-02-20 (1 employee(s) processed)', '2026-02-20 14:26:18'),
(10, 1, 'Generate', 'Payroll', 'Generated payroll run #25 for period 2026-02-15 to 2026-02-20 (3 employee(s) processed)', '2026-02-20 14:32:05');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `status` enum('Pending','Present','Late','Absent','Approved','Rejected') DEFAULT 'Pending',
  `is_paid` tinyint(1) DEFAULT 0,
  `payroll_id` int(11) DEFAULT NULL,
  `total_hours` decimal(5,2) DEFAULT 0.00,
  `pending_overtime` decimal(5,2) DEFAULT 0.00,
  `approved_overtime` decimal(5,2) DEFAULT 0.00,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `time_in`, `time_out`, `status`, `is_paid`, `payroll_id`, `total_hours`, `pending_overtime`, `approved_overtime`, `remarks`, `created_at`) VALUES
(8, 8, '2026-02-19 07:00:00', '2026-02-19 17:00:00', 'Present', 1, 7, 10.00, 0.00, 0.00, NULL, '2026-02-19 15:45:15'),
(9, 5, '2026-02-19 08:50:00', '2026-02-19 15:00:00', '', 0, NULL, 6.17, 0.00, 0.00, NULL, '2026-02-19 15:46:58'),
(10, 5, '2026-02-18 11:29:00', '2026-02-18 14:30:00', 'Late', 0, NULL, 3.02, 0.00, 0.00, NULL, '2026-02-19 16:29:33'),
(11, 8, '2026-02-19 07:00:00', '2026-02-19 17:00:00', 'Present', 1, 7, 10.00, 0.00, 0.00, NULL, '2026-02-19 17:08:44'),
(12, 5, '2026-02-20 07:00:00', '2026-02-20 18:30:00', 'Present', 0, NULL, 10.00, 0.00, 1.50, NULL, '2026-02-19 23:39:31'),
(13, 8, '2026-02-20 09:57:38', '2026-02-20 17:00:00', 'Late', 0, NULL, 7.03, 0.00, 0.00, NULL, '2026-02-20 01:57:38'),
(14, 38, '2026-02-20 07:00:00', '2026-02-20 18:00:00', 'Present', 0, NULL, 10.00, 0.00, 1.00, NULL, '2026-02-20 06:32:55'),
(15, 38, '2026-02-20 07:00:00', '2026-02-20 17:00:00', 'Present', 0, NULL, 10.00, 0.00, 0.00, NULL, '2026-02-20 06:34:20'),
(16, 38, '2026-02-12 07:00:00', '2026-02-12 17:00:00', 'Present', 0, NULL, 10.00, 0.00, 0.00, NULL, '2026-02-20 08:24:18'),
(17, 8, '2026-02-18 07:00:00', '2026-02-18 17:00:00', 'Present', 0, NULL, 10.00, 0.00, 0.00, NULL, '2026-02-20 14:09:35');

-- --------------------------------------------------------

--
-- Table structure for table `cash_advance`
--

CREATE TABLE `cash_advance` (
  `ca_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected','Paid') DEFAULT 'Pending',
  `is_paid` tinyint(1) DEFAULT 0,
  `payroll_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`) VALUES
(2, 'Biscuits', ''),
(3, 'Yema', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL,
  `full_name` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `position` varchar(100) DEFAULT NULL,
  `daily_rate` decimal(10,2) DEFAULT 500.00,
  `role` enum('Admin','Employee') DEFAULT 'Employee',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `date_hired` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_code`, `first_name`, `last_name`, `email`, `contact_number`, `password`, `position`, `daily_rate`, `role`, `status`, `date_hired`, `created_at`) VALUES
(1, 'EMP-0001', 'kakai', 'one', '', NULL, NULL, 'System Admin', 500.00, 'Admin', 'Active', '2025-11-01', '2025-10-31 20:04:32'),
(2, 'EMP-0002', 'Erich', 'Merilles', 'erich@gmail.com', '09513996139', NULL, 'IT', 63.63, 'Employee', 'Active', '2025-11-01', '2025-10-31 20:04:32'),
(5, 'EMP-0003', 'Echo', 'Alvior', 'echo@gmail.com', '09132654987', NULL, 'Staff', 63.63, 'Employee', 'Active', '2025-11-04', '2025-11-04 04:31:09'),
(8, 'EMP-0004', 'Aleciz', 'Ortiz', 'aleciz@gmail.com', '09123456789', NULL, 'Staff', 63.63, 'Employee', 'Active', '2026-02-10', '2026-02-10 07:42:25'),
(9, 'EMP-0005', 'Joel', 'Merilles', 'qwe@gmail.com', '09654987159', NULL, 'Staff', 63.63, 'Employee', 'Active', '2026-02-19', '2026-02-19 11:20:36'),
(10, 'EMP-0006', 'Mark', 'Mabanag', 'Mark@gmail.com', '09456456456', NULL, 'Staff', 63.63, 'Employee', 'Active', '2026-02-19', '2026-02-19 14:18:36'),
(38, 'EMP-0038', 'James', 'Salanio', 'james@gmail.com', '09123456789', '$2y$10$s4D0wPRK8gblvDchJ950/O9mi1FkudTzzEy85y1Ap.pAJ4LJiInhu', 'Staff', 63.63, 'Employee', 'Active', '2026-02-20', '2026-02-20 06:31:51'),
(39, 'EMP-0039', 'Rafael', 'Guevara', 'paeng@gmail.com', '09123123123', '$2y$10$4sbmtSGJlvuq9PMFBaWYveyyKfCfbW6kRPxmJ/Z44wgnWhhCzi6XW', 'Staff', 63.63, 'Employee', 'Active', '2026-02-20', '2026-02-20 06:39:51');

-- --------------------------------------------------------

--
-- Table structure for table `employee_permissions`
--

CREATE TABLE `employee_permissions` (
  `permission_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `inv_view` tinyint(1) DEFAULT 0,
  `inv_add` tinyint(1) DEFAULT 0,
  `inv_edit` tinyint(1) DEFAULT 0,
  `inv_delete` tinyint(1) DEFAULT 0,
  `inv_stock_in` tinyint(1) DEFAULT 0,
  `inv_stock_out` tinyint(1) DEFAULT 0,
  `order_view` tinyint(1) DEFAULT 0,
  `order_create` tinyint(1) DEFAULT 0,
  `order_status` tinyint(1) DEFAULT 0,
  `emp_view` tinyint(1) DEFAULT 0,
  `emp_add` tinyint(1) DEFAULT 0,
  `emp_edit` tinyint(1) DEFAULT 0,
  `emp_delete` tinyint(1) DEFAULT 0,
  `payroll_view` tinyint(1) DEFAULT 0,
  `payroll_generate` tinyint(1) DEFAULT 0,
  `payroll_analytics` tinyint(1) DEFAULT 0,
  `payroll_distribute` tinyint(1) DEFAULT 0,
  `payslip_print` tinyint(1) NOT NULL DEFAULT 0,
  `att_view` tinyint(1) DEFAULT 0,
  `att_edit` tinyint(1) NOT NULL DEFAULT 0,
  `att_approve` tinyint(1) DEFAULT 0,
  `att_manual` tinyint(1) DEFAULT 0,
  `ca_manage` tinyint(1) DEFAULT 0,
  `ca_view` tinyint(1) DEFAULT 0,
  `inv_cat_manage` tinyint(1) DEFAULT 0,
  `inv_sup_manage` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_permissions`
--

INSERT INTO `employee_permissions` (`permission_id`, `employee_id`, `inv_view`, `inv_add`, `inv_edit`, `inv_delete`, `inv_stock_in`, `inv_stock_out`, `order_view`, `order_create`, `order_status`, `emp_view`, `emp_add`, `emp_edit`, `emp_delete`, `payroll_view`, `payroll_generate`, `payroll_analytics`, `payroll_distribute`, `payslip_print`, `att_view`, `att_edit`, `att_approve`, `att_manual`, `ca_manage`, `ca_view`, `inv_cat_manage`, `inv_sup_manage`) VALUES
(1, 5, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0),
(3, 2, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0),
(4, 8, 1, 1, 1, 0, 1, 1, 0, 0, 0, 1, 1, 1, 0, 1, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 1, 1),
(10, 9, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1),
(21, 38, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0),
(22, 39, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `reorder_level` int(11) DEFAULT 10,
  `supplier_id` int(11) DEFAULT NULL,
  `status` enum('Available','Low Stock','Out of Stock') DEFAULT 'Available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`item_id`, `item_name`, `category_id`, `quantity`, `unit_price`, `reorder_level`, `supplier_id`, `status`, `created_at`, `image_path`) VALUES
(1, 'Rebisco', 2, 10, 12.00, 10, 1, 'Low Stock', '2026-02-19 07:50:24', ''),
(2, 'Hansel', 2, 100, 12.00, 10, 1, 'Available', '2026-02-19 07:52:18', ''),
(3, 'Yema', 3, 0, 10.00, 10, 1, 'Out of Stock', '2026-02-19 09:17:54', '');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `movement_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('IN','OUT') NOT NULL,
  `quantity` int(11) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `leave_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `leave_type` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_by` int(11) DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('Unread','Read') DEFAULT 'Unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT current_timestamp(),
  `status` enum('Pending','Processing','Ready for Pick-Up','Delivered') DEFAULT 'Pending',
  `payment_status` enum('Unpaid','Paid') DEFAULT 'Unpaid',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('GCash','PayMaya','Bank','Cash') DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_entries`
--

CREATE TABLE `payroll_entries` (
  `entry_id` int(11) NOT NULL,
  `payroll_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `gross_pay` decimal(10,2) DEFAULT 0.00,
  `overtime_pay` decimal(10,2) DEFAULT 0.00,
  `cash_advance` decimal(10,2) DEFAULT 0.00,
  `net_pay` decimal(10,2) DEFAULT 0.00,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_entries`
--

INSERT INTO `payroll_entries` (`entry_id`, `payroll_id`, `employee_id`, `gross_pay`, `overtime_pay`, `cash_advance`, `net_pay`, `details`) VALUES
(13, 7, 8, 1400.00, 0.00, 0.00, 1400.00, '[{\"date\":\"2026-02-19\",\"reg_hours\":10,\"reg_pay\":700,\"ot_pay\":0,\"total\":700},{\"date\":\"2026-02-19\",\"reg_hours\":10,\"reg_pay\":700,\"ot_pay\":0,\"total\":700}]');

-- --------------------------------------------------------

--
-- Table structure for table `payroll_runs`
--

CREATE TABLE `payroll_runs` (
  `payroll_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_gross` decimal(10,2) DEFAULT 0.00,
  `total_deductions` decimal(10,2) DEFAULT 0.00,
  `total_net` decimal(10,2) DEFAULT 0.00,
  `is_published` tinyint(1) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_runs`
--

INSERT INTO `payroll_runs` (`payroll_id`, `start_date`, `end_date`, `total_gross`, `total_deductions`, `total_net`, `is_published`, `created_by`, `created_at`) VALUES
(7, '2026-02-14', '2026-02-20', 1400.00, 0.00, 1400.00, 1, 1, '2026-02-20 09:10:04');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `product_name` varchar(150) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `reorder_level` int(11) DEFAULT 5,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `sale_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `sale_date` datetime DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('Cash','GCash','PayMaya','Bank') DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `sale_item_id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(150) DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_person`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'abc', 'aasd', '09123456789', 'qwerty@gmail.com', '123 HAHAHAHAHAHA', '2026-02-19 07:49:41');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `setting_id` int(11) NOT NULL,
  `setting_key` varchar(100) DEFAULT NULL,
  `setting_value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`setting_id`, `setting_key`, `setting_value`) VALUES
(1, 'weekday_rate', '63.63'),
(2, 'sunday_rate', '80'),
(3, 'weekday_full', '700'),
(4, 'sunday_full', '800'),
(5, 'weekday_logout', '17:00'),
(6, 'sunday_logout', '16:00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Employee','Owner') DEFAULT 'Employee',
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `employee_id`, `username`, `password`, `role`, `status`, `last_login`, `created_at`) VALUES
(1, 1, 'admin', '$2y$10$W67MJUbjCmMYWh7tFdfyH.f9rJUZXlUK1F8ZtLnWwF/uIpY/UbUcu', 'Admin', 'Active', '2026-02-19 17:39:46', '2025-10-31 20:04:32'),
(2, 2, 'erich.merilles@gmail.com', '$2y$10$P4j0O7zjE/OGjYb2Qw7AueF0utA4Pv/Ex1fG5l4eQ2tkzRQ7b3qCu', 'Employee', 'Active', NULL, '2025-10-31 20:04:32'),
(4, 5, 'qwe@gmail.com', '$2y$10$BpHRVx9ZjH5rSa0XAwTV8eCJ3Xu2txgeQsHSJiV7tLxIi.I7hfYzq', 'Employee', 'Active', '2026-02-20 14:20:33', '2025-11-04 04:31:09'),
(7, 8, 'aleciz@gmail.com', '$2y$10$xUh2RGs9bFg76MPlStxQreKZwM4zqSMAapaJUGDi6rGRGOZIatfq6', 'Employee', 'Active', '2026-02-20 18:09:19', '2026-02-10 07:42:25'),
(8, 9, 'merilles.erich@gmail.com', '$2y$10$dCasV3cWC.eZ4nF3L1cKSu0PNdalEUnF4jX5wOc2HTewVRruyTx12', 'Employee', 'Active', '2026-02-20 12:27:39', '2026-02-19 11:20:36'),
(9, 10, 'Mark@gmail.com', '$2y$10$22nLuaeTQqJDagASMGYGWOnCDMAOj2xSaxKXt/ISeO499eY2q2joG', 'Employee', 'Active', NULL, '2026-02-19 14:18:36'),
(11, 38, 'james@gmail.com', '$2y$10$s4D0wPRK8gblvDchJ950/O9mi1FkudTzzEy85y1Ap.pAJ4LJiInhu', 'Employee', 'Active', '2026-02-20 16:12:30', '2026-02-20 06:31:51'),
(12, 39, 'paeng@gmail.com', '$2y$10$4sbmtSGJlvuq9PMFBaWYveyyKfCfbW6kRPxmJ/Z44wgnWhhCzi6XW', 'Employee', 'Active', NULL, '2026-02-20 06:39:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `cash_advance`
--
ALTER TABLE `cash_advance`
  ADD PRIMARY KEY (`ca_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`);

--
-- Indexes for table `employee_permissions`
--
ALTER TABLE `employee_permissions`
  ADD PRIMARY KEY (`permission_id`),
  ADD UNIQUE KEY `unique_emp` (`employee_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  ADD PRIMARY KEY (`entry_id`),
  ADD KEY `payroll_id` (`payroll_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD PRIMARY KEY (`payroll_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`sale_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`sale_item_id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`setting_id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `cash_advance`
--
ALTER TABLE `cash_advance`
  MODIFY `ca_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `employee_permissions`
--
ALTER TABLE `employee_permissions`
  MODIFY `permission_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  MODIFY `entry_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  MODIFY `payroll_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `sale_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `sale_item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `setting_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `cash_advance`
--
ALTER TABLE `cash_advance`
  ADD CONSTRAINT `cash_advance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `employee_permissions`
--
ALTER TABLE `employee_permissions`
  ADD CONSTRAINT `employee_permissions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `inventory_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventory_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`customer_id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;

--
-- Constraints for table `payroll_entries`
--
ALTER TABLE `payroll_entries`
  ADD CONSTRAINT `payroll_entries_ibfk_1` FOREIGN KEY (`payroll_id`) REFERENCES `payroll_runs` (`payroll_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payroll_entries_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON DELETE SET NULL;

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `sale_items_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`sale_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sale_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
