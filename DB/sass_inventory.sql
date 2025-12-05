-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 07:44 PM
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

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `create_purchase_receipt` (IN `p_created_by` INT, IN `p_items_json` JSON, OUT `p_receipt_id` INT)   BEGIN
    DECLARE v_idx INT DEFAULT 0;
    DECLARE v_len INT DEFAULT 0;
    DECLARE v_prod_id INT;
    DECLARE v_supplier_id INT;
    DECLARE v_qty INT;
    DECLARE v_price DECIMAL(20,2);
    DECLARE v_purchase_date DATE;
    DECLARE v_total DECIMAL(20,2) DEFAULT 0.00;
    DECLARE v_receipt_number VARCHAR(64);

    -- Error handling: rollback if any exception occurs
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_receipt_id = 0;
    END;

    START TRANSACTION;

    SET v_len = JSON_LENGTH(p_items_json);

    IF v_len = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'No items provided';
    END IF;

    -- Calculate total
    SET v_idx = 0;
    WHILE v_idx < v_len DO
        SET v_prod_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].product_id'))) AS UNSIGNED);
        SET v_qty     = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].quantity'))) AS UNSIGNED);
        SET v_price   = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].purchase_price'))) AS DECIMAL(20,2));

        SET v_total = v_total + (v_price * v_qty);

        SET v_idx = v_idx + 1;
    END WHILE;

    -- Generate receipt number
    SET v_receipt_number = DATE_FORMAT(NOW(), '%Y%m%d');
    SET v_receipt_number = CONCAT(
        v_receipt_number,
        LPAD(CAST(p_created_by AS CHAR), 3, '0'),
        SUBSTRING(MD5(CONCAT(UNIX_TIMESTAMP(), RAND())), 1, 8)
    );

    -- Insert receipt
    INSERT INTO receipt (receipt_number, type, total_amount, created_by, created_at, updated_at)
    VALUES (v_receipt_number, 'Purchase', v_total, p_created_by, NOW(), NOW());

    SET p_receipt_id = LAST_INSERT_ID();

    -- Insert purchase items and update stock
    SET v_idx = 0;
    WHILE v_idx < v_len DO
        SET v_prod_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].product_id'))) AS UNSIGNED);
        SET v_supplier_id = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].supplier_id'))) AS UNSIGNED);
        SET v_qty     = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].quantity'))) AS UNSIGNED);
        SET v_price   = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].purchase_price'))) AS DECIMAL(20,2));
        SET v_purchase_date = CAST(JSON_UNQUOTE(JSON_EXTRACT(p_items_json, CONCAT('$[', v_idx, '].purchase_date'))) AS DATE);

        INSERT INTO purchase (
            receipt_id, product_id, supplier_id, quantity, purchase_price, purchase_date, created_at, updated_at
        )
        VALUES (
            p_receipt_id, v_prod_id, v_supplier_id, v_qty, v_price, COALESCE(v_purchase_date, CURDATE()), NOW(), NOW()
        );

        UPDATE product
        SET quantity_in_stock = quantity_in_stock + v_qty,
            updated_at = NOW()
        WHERE id = v_prod_id;

        SET v_idx = v_idx + 1;
    END WHILE;

    COMMIT;
END$$

DELIMITER ;

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

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `category_id`, `supplier_id`, `price`, `quantity_in_stock`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 15', 1, 1, 999.99, 100, '2025-12-05 04:03:58', '2025-12-05 12:03:34'),
(2, 'Samsung Galaxy S23', 1, 3, 899.99, 0, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(3, 'MacBook Air M2', 2, 4, 1299.99, 0, '2025-12-05 04:03:58', '2025-12-05 08:32:24'),
(4, 'Wireless Mouse', 2, 5, 25.50, 0, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(5, 'Office Chair', 4, 6, 149.99, 0, '2025-12-05 04:03:58', '2025-12-05 16:13:17'),
(6, 'Notebook Pack (5pcs)', 3, 7, 12.99, 0, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(7, 'Running Shoes', 6, 8, 79.99, 0, '2025-12-05 04:03:58', '2025-12-05 08:32:24'),
(8, 'LEGO Star Wars Set', 7, 1, 59.99, 1000, '2025-12-05 04:03:58', '2025-12-05 12:03:34'),
(9, 'Men\'s Leather Jacket', 5, 3, 199.99, 0, '2025-12-05 04:03:58', '2025-12-05 14:29:00'),
(10, 'Bluetooth Headphones', 2, 4, 89.99, 0, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(11, 'Refurbished iPhone 13', 1, 1, 599.99, 0, '2025-12-05 04:04:06', '2025-12-05 14:01:09'),
(12, 'Used Samsung Galaxy S21', 1, 3, 499.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(13, 'Refurbished MacBook Pro 2019', 2, 4, 999.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(14, 'Second-hand HP Printer', 2, 5, 79.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(15, 'Pre-owned Office Desk', 4, 6, 89.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(16, 'Used Notebook (Single)', 3, 7, 2.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(17, 'Refurbished Running Shoes', 6, 8, 49.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(18, 'Second-hand LEGO City Set', 7, 1, 39.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(19, 'Used Men\'s Jeans', 5, 3, 29.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(20, 'Pre-owned Bluetooth Speaker', 2, 4, 34.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(21, 'Refurbished iPad Air', 2, 5, 399.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(22, 'Used Wireless Keyboard', 2, 6, 15.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(23, 'Pre-owned Office Lamp', 4, 7, 19.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(24, 'Second-hand Kids Puzzle Set', 7, 8, 9.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(25, 'Used Men\'s Hoodie', 5, 1, 24.99, 0, '2025-12-05 04:04:06', '2025-12-05 08:21:40');

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
-- Stand-in structure for view `product_with_details`
-- (See below for the actual view)
--
CREATE TABLE `product_with_details` (
`id` int(11)
,`name` varchar(100)
,`category_id` int(11)
,`supplier_id` int(11)
,`price` decimal(10,2)
,`quantity_in_stock` int(11)
,`created_at` timestamp
,`updated_at` timestamp
,`category_name` varchar(100)
,`supplier_name` varchar(100)
);

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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change',
  `receipt_id` int(11) DEFAULT NULL,
  `purchased_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Records all product purchases. Admin/staff can add/update.';

--
-- Dumping data for table `purchase`
--

INSERT INTO `purchase` (`id`, `product_id`, `supplier_id`, `quantity`, `purchase_price`, `purchase_date`, `created_at`, `updated_at`, `receipt_id`, `purchased_by`) VALUES
(1, 1, 1, 100, 110000.00, '2025-12-05', '2025-12-05 12:03:34', '2025-12-05 12:03:34', 1, 1),
(2, 8, 1, 1000, 50000.00, '2025-12-05', '2025-12-05 12:03:34', '2025-12-05 12:03:34', 1, 1),
(3, 9, 3, 100, 20000.00, '2025-12-05', '2025-12-05 12:03:34', '2025-12-05 12:03:34', 1, 1),
(4, 11, 1, 40, 20000.00, '2025-12-05', '2025-12-05 12:03:34', '2025-12-05 12:03:34', 1, 1),
(5, 5, 6, 1000, 200000.00, '2025-12-05', '2025-12-05 12:20:04', '2025-12-05 12:20:04', 2, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `purchase_details`
-- (See below for the actual view)
--
CREATE TABLE `purchase_details` (
`id` int(11)
,`product_id` int(11)
,`product_name` varchar(100)
,`supplier_id` int(11)
,`supplier_name` varchar(100)
,`quantity` int(11)
,`purchase_price` decimal(10,2)
,`purchase_date` date
,`receipt_id` int(11)
,`purchased_by` int(11)
,`purchased_by_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `receipt`
--

CREATE TABLE `receipt` (
  `id` int(11) NOT NULL,
  `receipt_number` varchar(50) NOT NULL,
  `type` enum('purchase','sale') NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipt`
--

INSERT INTO `receipt` (`id`, `receipt_number`, `type`, `total_amount`, `created_by`, `created_at`, `updated_at`) VALUES
(1, '20251205152bc261d1cdd162025da23c', 'purchase', 200000.00, 1, '2025-12-05 12:03:34', '2025-12-05 12:21:47'),
(2, '2025120518a1ab8bec5b3e3daf6fd184', 'purchase', 200000.00, 1, '2025-12-05 12:20:04', '2025-12-05 12:20:04'),
(3, '2025120517be3a36c25ab9ca3974ce8a', 'sale', 40000.50, 1, '2025-12-05 14:01:09', '2025-12-05 14:01:09'),
(4, '20251205199287b7ae04f7eb907990f9', 'sale', 20000.00, 1, '2025-12-05 14:29:00', '2025-12-05 14:29:00'),
(5, '20251205105545f3d8fc6cc31f5ed509', 'sale', 500000.00, 1, '2025-12-05 16:13:17', '2025-12-05 16:13:17');

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
(2, 1),
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
(3, 1),
(3, 4),
(3, 8),
(3, 12),
(3, 16),
(3, 17),
(3, 18),
(3, 20),
(3, 24),
(3, 28),
(3, 32),
(3, 33);

-- --------------------------------------------------------

--
-- Stand-in structure for view `role_permission_matrix`
-- (See below for the actual view)
--
CREATE TABLE `role_permission_matrix` (
`role_id` int(11)
,`role_name` varchar(50)
,`permission_id` int(11)
,`permission_name` varchar(100)
,`assigned` int(1)
);

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change',
  `receipt_id` int(11) DEFAULT NULL,
  `sold_by` int(11) NOT NULL COMMENT 'FK to user.id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Records all product sales. Admin/staff can add/update.';

--
-- Dumping data for table `sale`
--

INSERT INTO `sale` (`id`, `product_id`, `quantity`, `sale_price`, `sale_date`, `created_at`, `updated_at`, `receipt_id`, `sold_by`) VALUES
(1, 9, 50, 10000.50, '0000-00-00', '2025-12-05 14:01:09', '2025-12-05 14:01:09', 3, 1),
(2, 11, 40, 30000.00, '0000-00-00', '2025-12-05 14:01:09', '2025-12-05 14:01:09', 3, 1),
(3, 9, 50, 20000.00, '2025-12-05', '2025-12-05 14:29:00', '2025-12-05 14:29:00', 4, 1),
(4, 5, 1000, 500000.00, '2025-12-05', '2025-12-05 16:13:17', '2025-12-05 16:13:17', 5, 1);

-- --------------------------------------------------------

--
-- Stand-in structure for view `sale_details`
-- (See below for the actual view)
--
CREATE TABLE `sale_details` (
`id` int(11)
,`product_id` int(11)
,`product_name` varchar(100)
,`quantity` int(11)
,`sale_price` decimal(10,2)
,`sale_date` date
,`receipt_id` int(11)
,`sold_by` int(11)
,`sold_by_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `stock_report`
-- (See below for the actual view)
--
CREATE TABLE `stock_report` (
`id` int(11)
,`product_name` varchar(100)
,`purchase_price` decimal(10,2)
,`current_stock` int(11)
,`supplier_name` varchar(100)
,`total_purchased_qty` decimal(32,0)
,`total_sold_qty` decimal(32,0)
);

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

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `name`, `phone`, `email`, `created_at`, `updated_at`) VALUES
(1, 'Talon Buchanan', '+1 (293) 637-4881', 'nytyr@mailinator.com', '2025-12-03 08:05:12', '2025-12-03 09:11:47'),
(3, 'Hayden Sims', '+1 (709) 873-7028', 'duwuhu@mailinator.com', '2025-12-03 08:09:49', '2025-12-03 08:09:49'),
(4, 'Octavia Griffin', '+1 (323) 768-7496', 'hamypaxe@mailinator.com', '2025-12-03 08:09:57', '2025-12-03 08:09:57'),
(5, 'Raphael Guerra', '+1 (876) 388-5466', 'ziluxas@mailinator.com', '2025-12-03 08:10:08', '2025-12-03 08:10:08'),
(6, 'Idona Hunter', '+1 (349) 757-5266', 'vysyrinyti@mailinator.com', '2025-12-03 08:10:15', '2025-12-03 08:10:15'),
(7, 'Geraldine Greer', '+1 (486) 521-2676', 'qaqiwi@mailinator.com', '2025-12-03 08:10:48', '2025-12-03 08:10:48'),
(8, 'Aurora Hardin', '+1 (944) 354-6738', 'vaja@mailinator.com', '2025-12-03 08:50:45', '2025-12-03 08:50:45');

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
(10, 'fasfsfsdfvasd', '9dedbcec383f739207577016d7c387b3', 'faswefssdfvsa@gmail.com', 2, '2025-12-01 20:34:41', '2025-12-01 20:34:41'),
(12, 'Test221', '356308d897f6d2f67e9f83730a9ec258', 'vaja@mailinator.com', 2, '2025-12-04 01:27:28', '2025-12-04 01:27:28');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_purchase_report`
-- (See below for the actual view)
--
CREATE TABLE `view_purchase_report` (
`purchase_id` int(11)
,`product_name` varchar(100)
,`supplier_name` varchar(100)
,`qty_purchased` int(11)
,`unit_price` decimal(10,2)
,`total_cost` decimal(20,2)
,`purchase_date` date
,`receipt_number` varchar(50)
,`purchased_by` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_sales_report`
-- (See below for the actual view)
--
CREATE TABLE `view_sales_report` (
`id` int(11)
,`product_name` varchar(100)
,`supplier_name` varchar(100)
,`qty_sold` int(11)
,`unit_price` decimal(11,2)
,`total_revenue` decimal(10,2)
,`sale_date` date
,`receipt_number` varchar(50)
,`sold_by` varchar(100)
);

-- --------------------------------------------------------

--
-- Structure for view `product_with_details`
--
DROP TABLE IF EXISTS `product_with_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `product_with_details`  AS SELECT `p`.`id` AS `id`, `p`.`name` AS `name`, `p`.`category_id` AS `category_id`, `p`.`supplier_id` AS `supplier_id`, `p`.`price` AS `price`, `p`.`quantity_in_stock` AS `quantity_in_stock`, `p`.`created_at` AS `created_at`, `p`.`updated_at` AS `updated_at`, `c`.`name` AS `category_name`, `s`.`name` AS `supplier_name` FROM ((`product` `p` left join `category` `c` on(`p`.`category_id` = `c`.`id`)) left join `supplier` `s` on(`p`.`supplier_id` = `s`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `purchase_details`
--
DROP TABLE IF EXISTS `purchase_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `purchase_details`  AS SELECT `p`.`id` AS `id`, `p`.`product_id` AS `product_id`, `pr`.`name` AS `product_name`, `p`.`supplier_id` AS `supplier_id`, `s`.`name` AS `supplier_name`, `p`.`quantity` AS `quantity`, `p`.`purchase_price` AS `purchase_price`, `p`.`purchase_date` AS `purchase_date`, `p`.`receipt_id` AS `receipt_id`, `p`.`purchased_by` AS `purchased_by`, `u`.`username` AS `purchased_by_name` FROM (((`purchase` `p` left join `product` `pr` on(`p`.`product_id` = `pr`.`id`)) left join `supplier` `s` on(`p`.`supplier_id` = `s`.`id`)) left join `user` `u` on(`p`.`purchased_by` = `u`.`id`)) ORDER BY `p`.`id` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `role_permission_matrix`
--
DROP TABLE IF EXISTS `role_permission_matrix`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `role_permission_matrix`  AS SELECT `r`.`id` AS `role_id`, `r`.`role_name` AS `role_name`, `p`.`id` AS `permission_id`, `p`.`permission_name` AS `permission_name`, CASE WHEN `rp`.`role_id` is not null THEN 1 ELSE 0 END AS `assigned` FROM ((`permission` `p` join `role` `r`) left join `role_permission` `rp` on(`rp`.`role_id` = `r`.`id` and `rp`.`permission_id` = `p`.`id`)) ORDER BY `p`.`id` ASC, `r`.`id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `sale_details`
--
DROP TABLE IF EXISTS `sale_details`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sale_details`  AS SELECT `s`.`id` AS `id`, `s`.`product_id` AS `product_id`, `p`.`name` AS `product_name`, `s`.`quantity` AS `quantity`, `s`.`sale_price` AS `sale_price`, `s`.`sale_date` AS `sale_date`, `s`.`receipt_id` AS `receipt_id`, `s`.`sold_by` AS `sold_by`, `u`.`username` AS `sold_by_name` FROM ((`sale` `s` left join `user` `u` on(`s`.`sold_by` = `u`.`id`)) left join `product` `p` on(`s`.`product_id` = `p`.`id`)) ;

-- --------------------------------------------------------

--
-- Structure for view `stock_report`
--
DROP TABLE IF EXISTS `stock_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `stock_report`  AS SELECT `pr`.`id` AS `id`, `pr`.`name` AS `product_name`, `pr`.`price` AS `purchase_price`, `pr`.`quantity_in_stock` AS `current_stock`, `s`.`name` AS `supplier_name`, ifnull(sum(`p`.`quantity`),0) AS `total_purchased_qty`, ifnull(sum(`sa`.`quantity`),0) AS `total_sold_qty` FROM (((`product` `pr` left join `purchase` `p` on(`pr`.`id` = `p`.`product_id`)) left join `sale` `sa` on(`pr`.`id` = `sa`.`product_id`)) left join `supplier` `s` on(`pr`.`supplier_id` = `s`.`id`)) GROUP BY `pr`.`id`, `pr`.`name`, `pr`.`price`, `pr`.`quantity_in_stock`, `s`.`name` ORDER BY `pr`.`name` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_purchase_report`
--
DROP TABLE IF EXISTS `view_purchase_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_purchase_report`  AS SELECT `pu`.`id` AS `purchase_id`, `p`.`name` AS `product_name`, `s`.`name` AS `supplier_name`, `pu`.`quantity` AS `qty_purchased`, `pu`.`purchase_price` AS `unit_price`, `pu`.`quantity`* `pu`.`purchase_price` AS `total_cost`, `pu`.`purchase_date` AS `purchase_date`, `r`.`receipt_number` AS `receipt_number`, `u`.`username` AS `purchased_by` FROM ((((`purchase` `pu` left join `product` `p` on(`pu`.`product_id` = `p`.`id`)) left join `supplier` `s` on(`pu`.`supplier_id` = `s`.`id`)) left join `receipt` `r` on(`pu`.`receipt_id` = `r`.`id`)) left join `user` `u` on(`pu`.`purchased_by` = `u`.`id`)) ORDER BY `pu`.`purchase_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_sales_report`
--
DROP TABLE IF EXISTS `view_sales_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_sales_report`  AS SELECT `sa`.`id` AS `id`, `p`.`name` AS `product_name`, `s`.`name` AS `supplier_name`, `sa`.`quantity` AS `qty_sold`, round(`sa`.`sale_price` / `sa`.`quantity`,2) AS `unit_price`, `sa`.`sale_price` AS `total_revenue`, `sa`.`sale_date` AS `sale_date`, `r`.`receipt_number` AS `receipt_number`, `u`.`username` AS `sold_by` FROM ((((`sale` `sa` left join `product` `p` on(`sa`.`product_id` = `p`.`id`)) left join `supplier` `s` on(`p`.`supplier_id` = `s`.`id`)) left join `receipt` `r` on(`sa`.`receipt_id` = `r`.`id`)) left join `user` `u` on(`sa`.`sold_by` = `u`.`id`)) ORDER BY `sa`.`sale_date` ASC ;

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
  ADD KEY `purchase_index_3` (`supplier_id`),
  ADD KEY `fk_purchase_receipt` (`receipt_id`),
  ADD KEY `fk_purchase_user` (`purchased_by`);

--
-- Indexes for table `receipt`
--
ALTER TABLE `receipt`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `fk_receipt_user` (`created_by`);

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
  ADD KEY `sale_index_5` (`sold_by`),
  ADD KEY `sale_index_6` (`sale_date`),
  ADD KEY `fk_sale_receipt` (`receipt_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `product_request`
--
ALTER TABLE `product_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `receipt`
--
ALTER TABLE `receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sale`
--
ALTER TABLE `sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

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
  ADD CONSTRAINT `fk_purchase_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `receipt` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_purchase_user` FOREIGN KEY (`purchased_by`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `purchase_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`);

--
-- Constraints for table `receipt`
--
ALTER TABLE `receipt`
  ADD CONSTRAINT `fk_receipt_user` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`);

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
  ADD CONSTRAINT `fk_sale_receipt` FOREIGN KEY (`receipt_id`) REFERENCES `receipt` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sale_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`),
  ADD CONSTRAINT `sale_ibfk_2` FOREIGN KEY (`sold_by`) REFERENCES `user` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
