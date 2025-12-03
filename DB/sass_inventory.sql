-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2025 at 02:37 AM
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
-- Database: `sass_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL COMMENT 'Optional description',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Mobile', 'Mobile Phones, Smart Phones, handheld phones', '2025-12-02 06:16:04', '2025-12-02 12:09:41'),
(2, 'Electronics', 'Devices, gadgets, and accessories', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(3, 'Stationery', 'Office and school supplies', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(4, 'Furniture', 'Home and office furniture', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(5, 'Clothing', 'Apparel for men, women, and kids', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(6, 'Footwear', 'Shoes, sandals, and boots', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(7, 'Toys', 'Toys and games for children', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(8, 'Groceries', 'Daily food and household items', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(9, 'Beauty & Personal Care', 'Cosmetics, skincare, and hygiene', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(10, 'Books', 'Educational and recreational books', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(11, 'Sports', 'Sporting goods and equipment', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(12, 'Automotive', 'Car accessories and tools', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(13, 'Hardware', 'Tools and construction materials', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(14, 'Garden & Outdoors', 'Gardening and outdoor items', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(15, 'Pet Supplies', 'Food and accessories for pets', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(16, 'Music & Instruments', 'Musical instruments and accessories', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(17, 'Jewelry & Watches', 'Accessories and watches', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(19, 'Home Appliances', 'Appliances for home use', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(20, 'Art & Craft', 'Art supplies and DIY kits', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(21, 'Office Electronics', 'Printers, scanners, and office gadgets', '2025-12-02 11:47:57', '2025-12-02 11:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

CREATE TABLE `permission` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Defines permissions like add, edit, delete, view, etc.';

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`id`, `permission_name`) VALUES
(17, 'Add Category'),
(29, 'Add Inventory Adjustment'),
(9, 'Add Product'),
(21, 'Add Purchase'),
(5, 'Add Role'),
(25, 'Add Sale'),
(13, 'Add Supplier'),
(1, 'Add User'),
(19, 'Delete Category'),
(31, 'Delete Inventory Adjustment'),
(11, 'Delete Product'),
(23, 'Delete Purchase'),
(7, 'Delete Role'),
(27, 'Delete Sale'),
(15, 'Delete Supplier'),
(3, 'Delete User'),
(18, 'Edit Category'),
(30, 'Edit Inventory Adjustment'),
(10, 'Edit Product'),
(22, 'Edit Purchase'),
(6, 'Edit Role'),
(26, 'Edit Sale'),
(14, 'Edit Supplier'),
(2, 'Edit User'),
(34, 'Process Product Request'),
(20, 'View Categories'),
(32, 'View Inventory Adjustments'),
(33, 'View Product Requests'),
(12, 'View Products'),
(24, 'View Purchases'),
(8, 'View Roles'),
(28, 'View Sales'),
(16, 'View Suppliers'),
(4, 'View Users');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category_id` int(11) DEFAULT NULL COMMENT 'Nullable — ON DELETE SET NULL',
  `supplier_id` int(11) DEFAULT NULL COMMENT 'Nullable — ON DELETE SET NULL',
  `price` decimal(10,2) NOT NULL,
  `quantity_in_stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores all product info. Admin/staff can add/update.';

-- --------------------------------------------------------

--
-- Table structure for table `product_request`
--

CREATE TABLE `product_request` (
  `id` int(11) NOT NULL,
  `visitor_name` varchar(100) NOT NULL,
  `visitor_email` varchar(100) DEFAULT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `notes` text DEFAULT NULL COMMENT 'Optional additional details from visitor',
  `status` enum('pending','reviewed','rejected','approved') DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_by` int(11) DEFAULT NULL COMMENT 'FK to user.id when admin/staff processes the request',
  `processed_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When request was reviewed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores product requests submitted by visitors before adding to inventory.';

-- --------------------------------------------------------

--
-- Table structure for table `purchase`
--

CREATE TABLE `purchase` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL COMMENT 'Optional',
  `quantity` int(11) NOT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `purchase_date` date DEFAULT curdate(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Records all product purchases. Admin/staff can add/update.';

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Defines user roles such as admin, staff, etc.';

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `role_name`) VALUES
(1, 'Admin'),
(2, 'Employee'),
(3, 'Manager');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE `role_permission` (
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maps roles to permissions (many-to-many relationship).';

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(2, 9),
(2, 10),
(2, 12),
(2, 13),
(2, 14),
(2, 16),
(2, 21),
(2, 22),
(2, 24),
(2, 25),
(2, 26),
(2, 28),
(2, 33),
(2, 34),
(3, 4),
(3, 8),
(3, 12),
(3, 16),
(3, 20),
(3, 24),
(3, 28),
(3, 32),
(3, 33);

-- --------------------------------------------------------

--
-- Table structure for table `sale`
--

CREATE TABLE `sale` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `sale_date` date DEFAULT curdate(),
  `created_by` int(11) NOT NULL COMMENT 'FK to user.id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Records all product sales. Admin/staff can add/update.';

-- --------------------------------------------------------

--
-- Table structure for table `supplier`
--

CREATE TABLE `supplier` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Used to track product sources. Admin and staff can add/update.';

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` text DEFAULT NULL COMMENT 'Password must be hashed',
  `email` varchar(100) DEFAULT NULL,
  `role_id` int(11) NOT NULL COMMENT 'FK to role.id',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Only admin can add or edit users.';

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `email`, `role_id`, `created_at`, `updated_at`) VALUES
(1, 'sazzadul admin', '13a84f75e1d009808e94f1910115089c', 'admin@gmail.com', 1, '2025-12-01 06:01:46', '2025-12-01 06:01:46'),
(2, 'Sazzadul Employee', '4405078d2226307785c00984bb847b6d', 'employee@gmail.com', 2, '2025-12-01 06:01:46', '2025-12-01 06:01:46'),
(3, 'Test', '478c7ba90ee096df1026eab052313474', 'Test@gmail.com', 2, '2025-12-01 20:20:25', '2025-12-01 20:20:25'),
(5, 'Test2', 'c454552d52d55d3ef56408742887362b', 'Test2@gmail.com', 2, '2025-12-01 20:25:37', '2025-12-01 20:25:37'),
(7, 'Teest3', '17e310318a1e207509c0f0cd8042063b', 'Test3@gmail.com', 2, '2025-12-01 20:30:41', '2025-12-01 20:30:41'),
(9, 'sedafs', '8cca08722e4108babcb9218e5bb14a2d', 'asfswf@gmail.com', 2, '2025-12-01 20:32:52', '2025-12-01 20:32:52'),
(10, 'fasfsfsdfvasd', '9dedbcec383f739207577016d7c387b3', 'faswefssdfvsa@gmail.com', 2, '2025-12-01 20:34:41', '2025-12-01 20:34:41');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `permission`
--
ALTER TABLE `permission`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_index_0` (`category_id`),
  ADD KEY `product_index_1` (`supplier_id`);

--
-- Indexes for table `product_request`
--
ALTER TABLE `product_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_request_index_7` (`status`),
  ADD KEY `product_request_index_8` (`processed_by`);

--
-- Indexes for table `purchase`
--
ALTER TABLE `purchase`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_index_2` (`product_id`),
  ADD KEY `purchase_index_3` (`supplier_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD PRIMARY KEY (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`);

--
-- Indexes for table `sale`
--
ALTER TABLE `sale`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_index_4` (`product_id`),
  ADD KEY `sale_index_5` (`created_by`),
  ADD KEY `sale_index_6` (`sale_date`);

--
-- Indexes for table `supplier`
--
ALTER TABLE `supplier`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_request`
--
ALTER TABLE `product_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sale`
--
ALTER TABLE `sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `product_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_request`
--
ALTER TABLE `product_request`
  ADD CONSTRAINT `product_request_ibfk_1` FOREIGN KEY (`processed_by`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `product_request_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `user` (`id`);

--
-- Constraints for table `purchase`
--
ALTER TABLE `purchase`
  ADD CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `purchase_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`);

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `role_permission_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`),
  ADD CONSTRAINT `role_permission_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`id`);

--
-- Constraints for table `sale`
--
ALTER TABLE `sale`
  ADD CONSTRAINT `sale_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `sale_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
