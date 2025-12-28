-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2025 at 06:31 PM
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

CREATE DEFINER=`root`@`localhost` PROCEDURE `update_role_with_permissions` (IN `p_role_id` INT, IN `p_role_name` VARCHAR(255), IN `p_permissions` VARCHAR(255))   BEGIN
    DECLARE
        perm_id INT ; DECLARE pos INT DEFAULT 1 ; DECLARE next_pos INT ; DECLARE val VARCHAR(10) ;
    START TRANSACTION
        ;
        -- Update role name
    UPDATE
        role
    SET
        role_name = p_role_name
    WHERE
        id = p_role_id ;
        -- Delete existing permissions
    DELETE
FROM
    role_permission
WHERE
    role_id = p_role_id ;
    -- Insert new permissions
    WHILE pos > 0
DO
SET
    next_pos = LOCATE(',', p_permissions, pos) ; IF next_pos > 0 THEN
SET
    val = SUBSTRING(
        p_permissions,
        pos,
        next_pos - pos
    ) ;
SET
    pos = next_pos + 1 ; ELSE
SET
    val = SUBSTRING(p_permissions, pos) ;
SET
    pos = 0 ;
END IF ; IF val != '' THEN
INSERT INTO role_permission(role_id, permission_id)
VALUES(
    p_role_id,
    CAST(val AS UNSIGNED)
) ;
END IF ;
END WHILE ;
COMMIT
    ;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL COMMENT 'Optional description',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `name`, `parent_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Mobile', NULL, 'Mobile Phones, Smart Phones, handheld phones', '2025-12-02 06:16:04', '2025-12-19 01:10:09'),
(2, 'Electronics', NULL, 'Devices, gadgets, and accessories', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(3, 'Stationery', NULL, 'Office and school supplies', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(4, 'Furniture', NULL, 'Home and office furniture', '2025-12-02 11:47:57', '2025-12-27 10:05:54'),
(5, 'Clothing', NULL, 'Apparel for men, women, and kids', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(6, 'Footwear', NULL, 'Shoes, sandals, and boots', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(7, 'Toys', NULL, 'Toys and games for children', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(8, 'Groceries', NULL, 'Daily food and household items', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(10, 'Books', NULL, 'Educational and recreational books', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(11, 'Sports', NULL, 'Sporting goods and equipment', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(12, 'Automotive', NULL, 'Car accessories and tools', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(13, 'Hardware', NULL, 'Tools and construction materials', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(14, 'Garden & Outdoors', NULL, 'Gardening and outdoor items', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(15, 'Pet Supplies', NULL, 'Food and accessories for pets', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(16, 'Music & Instruments', NULL, 'Musical instruments and accessories', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(17, 'Jewelry & Watches', NULL, 'Accessories and watches', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(19, 'Home Appliances', NULL, 'Appliances for home use', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(20, 'Art & Craft', NULL, 'Art supplies and DIY kits', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(21, 'Office Electronics', NULL, 'Printers, scanners, and office gadgets', '2025-12-02 11:47:57', '2025-12-02 11:47:57'),
(23, 'Chocklet', NULL, 'Chocklet Products', '2025-12-19 00:58:09', '2025-12-19 00:58:09'),
(24, 'Bars', 23, 'afwefwsfwes', '2025-12-19 00:58:09', '2025-12-27 10:09:00'),
(26, 'Smart Phone', 1, 'daqwFDFEWSA', '2025-12-19 01:10:09', '2025-12-19 01:32:52'),
(28, 'Chair', 4, 'afeswfawefae', '2025-12-27 10:00:01', '2025-12-27 10:05:54'),
(29, 'Table', 4, 'ewafawefefewf', '2025-12-27 10:00:01', '2025-12-27 10:05:54'),
(33, 'FWEFAEWF', NULL, 'FAEFAWEF', '2025-12-27 10:08:52', '2025-12-27 10:08:52'),
(34, 'afsfes', 33, 'faewsfaewf ewf gaswerg', '2025-12-27 10:08:52', '2025-12-27 10:08:52'),
(35, 'gawegserg', 33, 'era gerg hrgs', '2025-12-27 10:08:52', '2025-12-27 10:08:52');

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
(12, 'add_category'),
(20, 'add_product'),
(29, 'add_purchase'),
(8, 'add_role'),
(36, 'add_sale'),
(16, 'add_supplier'),
(4, 'add_user'),
(45, 'delete_category'),
(47, 'delete_product'),
(46, 'delete_supplier'),
(43, 'delete_user'),
(13, 'edit_category'),
(21, 'edit_product'),
(7, 'edit_role'),
(17, 'edit_supplier'),
(5, 'edit_user'),
(44, 'update_permissions'),
(51, 'view_all_my_receipts'),
(24, 'view_all_purchases'),
(26, 'view_all_purchase_receipts'),
(37, 'view_all_receipts'),
(31, 'view_all_sales'),
(33, 'view_all_sales_receipts'),
(2, 'view_authentication_menu'),
(11, 'view_categories'),
(10, 'view_categories_menu'),
(1, 'view_dashboard'),
(40, 'view_low_stock'),
(25, 'view_my_purchases'),
(27, 'view_my_purchase_receipts'),
(32, 'view_my_sales'),
(34, 'view_my_sales_receipts'),
(9, 'view_permissions'),
(19, 'view_products'),
(18, 'view_products_menu'),
(23, 'view_purchases_menu'),
(28, 'view_purchase_receipt'),
(42, 'view_purchase_report'),
(48, 'view_receipt'),
(38, 'view_reports_menu'),
(6, 'view_roles'),
(30, 'view_sales_menu'),
(35, 'view_sales_receipt'),
(41, 'view_sales_report'),
(22, 'view_stock_overview'),
(39, 'view_stock_report'),
(15, 'view_suppliers'),
(14, 'view_suppliers_menu'),
(3, 'view_users');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `category_id` int(11) DEFAULT NULL COMMENT 'Nullable — ON DELETE SET NULL',
  `subcategory_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL COMMENT 'Nullable — ON DELETE SET NULL',
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `vat` decimal(5,2) NOT NULL DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL,
  `quantity_in_stock` int(11) DEFAULT 0,
  `low_stock_limit` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Auto-update on record change'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Stores all product info. Admin/staff can add/update.';

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`id`, `name`, `sku`, `status`, `category_id`, `subcategory_id`, `supplier_id`, `cost_price`, `selling_price`, `vat`, `price`, `quantity_in_stock`, `low_stock_limit`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 15', '', 'active', 1, NULL, 1, 0.00, 0.00, 0.00, 999.99, 100, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 12:03:34'),
(2, 'Samsung Galaxy S23', '', 'active', 1, NULL, 3, 0.00, 0.00, 0.00, 899.99, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(3, 'MacBook Air M2', '', 'active', 2, NULL, 4, 0.00, 0.00, 0.00, 1299.99, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 08:32:24'),
(4, 'Wireless Mouse', '', 'active', 2, NULL, 5, 0.00, 0.00, 0.00, 25.50, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(5, 'Office Chair', '', 'active', 4, NULL, 6, 0.00, 0.00, 0.00, 149.99, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 16:13:17'),
(6, 'Notebook Pack (5pcs)', '', 'active', 3, NULL, 7, 0.00, 0.00, 0.00, 12.99, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(7, 'Running Shoes', '', 'active', 6, NULL, 8, 0.00, 0.00, 0.00, 79.99, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 08:32:24'),
(8, 'LEGO Star Wars Set', '', 'active', 7, NULL, 1, 0.00, 0.00, 0.00, 59.99, 1000, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 12:03:34'),
(9, 'Men\'s Leather Jacket', '', 'active', 5, NULL, 3, 0.00, 0.00, 0.00, 199.99, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 14:29:00'),
(10, 'Bluetooth Headphones', '', 'active', 2, NULL, 4, 0.00, 0.00, 0.00, 89.99, 0, NULL, NULL, NULL, '2025-12-05 04:03:58', '2025-12-05 08:21:40'),
(11, 'Refurbished iPhone 13', '', 'active', 1, NULL, 1, 0.00, 0.00, 0.00, 599.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 14:01:09'),
(12, 'Used Samsung Galaxy S21', '', 'active', 1, NULL, 3, 0.00, 0.00, 0.00, 499.99, 120, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-08 05:05:43'),
(13, 'Refurbished MacBook Pro 2019', '', 'active', 2, NULL, 4, 0.00, 0.00, 0.00, 999.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(14, 'Second-hand HP Printer', '', 'active', 2, NULL, 5, 0.00, 0.00, 0.00, 79.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(15, 'Pre-owned Office Desk', '', 'active', 4, NULL, 6, 0.00, 0.00, 0.00, 89.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(16, 'Used Notebook (Single)', '', 'active', 3, NULL, 7, 0.00, 0.00, 0.00, 2.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(17, 'Refurbished Running Shoes', '', 'active', 6, NULL, 8, 0.00, 0.00, 0.00, 49.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(18, 'Second-hand LEGO City Set', '', 'active', 7, NULL, 1, 0.00, 0.00, 0.00, 39.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(19, 'Used Men\'s Jeans', '', 'active', 5, NULL, 3, 0.00, 0.00, 0.00, 29.99, 1000, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-08 05:05:43'),
(20, 'Pre-owned Bluetooth Speaker', '', 'active', 2, NULL, 4, 0.00, 0.00, 0.00, 34.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(21, 'Refurbished iPad Air', '', 'active', 2, NULL, 5, 0.00, 0.00, 0.00, 399.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(22, 'Used Wireless Keyboard', '', 'active', 2, NULL, 6, 0.00, 0.00, 0.00, 15.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(23, 'Pre-owned Office Lamp', '', 'active', 4, NULL, 7, 0.00, 0.00, 0.00, 19.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(24, 'Second-hand Kids Puzzle Set', '', 'active', 7, NULL, 8, 0.00, 0.00, 0.00, 9.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(25, 'Used Men\'s Hoodie', '', 'active', 5, NULL, 1, 0.00, 0.00, 0.00, 24.99, 0, NULL, NULL, NULL, '2025-12-05 04:04:06', '2025-12-05 08:21:40'),
(27, 'iPhone 15', 'IP15-001', 'active', 1, NULL, 1, 700.00, 999.99, 12.00, 999.99, 0, 10, 'The latest iPhone 15 with advanced features.', 'iphone15.png', '2025-12-05 04:03:58', '2025-12-05 12:03:34');

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
(5, 5, 6, 1000, 200000.00, '2025-12-05', '2025-12-05 12:20:04', '2025-12-05 12:20:04', 2, 1),
(6, 12, 3, 120, 50000.00, '2025-12-08', '2025-12-08 05:05:43', '2025-12-08 05:05:43', 6, 1),
(7, 19, 3, 1000, 30000.00, '2025-12-08', '2025-12-08 05:05:43', '2025-12-08 05:05:43', 6, 1);

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
-- Stand-in structure for view `purchase_receipts_view`
-- (See below for the actual view)
--
CREATE TABLE `purchase_receipts_view` (
`id` int(11)
,`receipt_number` varchar(50)
,`type` enum('purchase','sale')
,`total_amount` decimal(12,2)
,`created_at` timestamp
,`created_by` int(11)
,`created_by_name` varchar(100)
,`num_products` bigint(21)
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
(5, '20251205105545f3d8fc6cc31f5ed509', 'sale', 500000.00, 1, '2025-12-05 16:13:17', '2025-12-05 16:13:17'),
(6, '202512081d5f4380e18fe8fe1d38a33c', 'purchase', 80000.00, 1, '2025-12-08 05:05:43', '2025-12-08 05:05:43');

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
(3, 'Manager'),
(8, 'Observer');

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
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 51),
(2, 7),
(2, 10),
(2, 11),
(2, 12),
(2, 19),
(2, 20),
(2, 22),
(2, 29),
(2, 36),
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
(3, 33),
(3, 36),
(8, 18),
(8, 19);

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
-- Stand-in structure for view `sales_receipts_view`
-- (See below for the actual view)
--
CREATE TABLE `sales_receipts_view` (
`id` int(11)
,`receipt_number` varchar(50)
,`type` enum('purchase','sale')
,`total_amount` decimal(12,2)
,`created_at` timestamp
,`created_by` int(11)
,`created_by_name` varchar(100)
,`num_products` bigint(21)
);

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
  `address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Used to track product sources. Admin and staff can add/update.';

--
-- Dumping data for table `supplier`
--

INSERT INTO `supplier` (`id`, `name`, `phone`, `email`, `address`, `contact_person`, `type`, `created_at`, `updated_at`) VALUES
(1, 'Henry Acevedo', '+1 (693) 537-4872', 'vijo@mailinator.com', 'Architecto nulla eve', 'Illo ab blanditiis q', 'wholesaler', '2025-12-03 08:05:12', '2025-12-19 06:59:09'),
(3, 'Hayden Sims', '+1 (709) 873-7028', 'duwuhu@mailinator.com', 'f weaf wefw', 'Md Shafikul Islam ii', 'manufacturer', '2025-12-03 08:09:49', '2025-12-19 03:58:42'),
(4, 'Octavia Griffin', '+1 (323) 768-7496', 'hamypaxe@mailinator.com', 'f weaf wefw', 'Md Shafikul Islam ii', 'distributor', '2025-12-03 08:09:57', '2025-12-19 06:58:38'),
(5, 'Maia Keller', '+1 (767) 523-8209', 'judoqozag@mailinator.com', 'Et totam error place', 'Hic minus iusto reru', 'wholesaler', '2025-12-03 08:10:08', '2025-12-19 06:59:14'),
(6, 'Idona Hunter', '+1 (349) 757-5266', 'vysyrinyti@mailinator.com', NULL, NULL, NULL, '2025-12-03 08:10:15', '2025-12-03 08:10:15'),
(7, 'Geraldine Greer', '+1 (486) 521-2676', 'qaqiwi@mailinator.com', NULL, NULL, NULL, '2025-12-03 08:10:48', '2025-12-03 08:10:48'),
(8, 'Aurora Hardin', '+1 (944) 354-6738', 'vaja@mailinator.com', NULL, NULL, NULL, '2025-12-03 08:50:45', '2025-12-03 08:50:45');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_category`
--

CREATE TABLE `supplier_category` (
  `supplier_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_category`
--

INSERT INTO `supplier_category` (`supplier_id`, `category_id`) VALUES
(1, 1),
(1, 15),
(1, 19),
(1, 21),
(3, 5),
(3, 10),
(3, 12),
(4, 12),
(4, 20),
(5, 2),
(5, 4),
(5, 7),
(5, 10),
(5, 11),
(5, 17);

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
(3, 'Test', '25f9e794323b453885f5181f1b624d0b', 'Test@gmail.com', 2, '2025-12-01 20:20:25', '2025-12-07 02:10:42'),
(7, 'Teest3', '17e310318a1e207509c0f0cd8042063b', 'Test3@gmail.com', 2, '2025-12-01 20:30:41', '2025-12-01 20:30:41'),
(9, 'sedafs', '8cca08722e4108babcb9218e5bb14a2d', 'asfswf@gmail.com', 8, '2025-12-01 20:32:52', '2025-12-07 23:18:33'),
(12, 'Test221', '356308d897f6d2f67e9f83730a9ec258', 'vaja@mailinator.com', 2, '2025-12-04 01:27:28', '2025-12-04 01:27:28'),
(16, 'SazzadulFFYUJ', 'e807f1fcf82d132f9bb018ca6738a19f', '123@gmail.com', 2, '2025-12-08 04:22:17', '2025-12-08 04:22:17'),
(17, 'Pritom Test User', 'ea3c1823aa5bf4a74356098da02de9b4', 'Psazzadul@gmail.com', 2, '2025-12-09 14:59:31', '2025-12-09 14:59:31');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_product_movement`
-- (See below for the actual view)
--
CREATE TABLE `view_product_movement` (
`movement_id` int(11)
,`product_id` int(11)
,`product_name` varchar(100)
,`movement_type` varchar(8)
,`qty_in` int(11)
,`qty_out` int(11)
,`movement_date` date
,`receipt_id` int(11)
,`user_name` varchar(100)
,`receipt_number` varchar(50)
,`supplier_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_purchase_report`
-- (See below for the actual view)
--
CREATE TABLE `view_purchase_report` (
`id` int(11)
,`product_name` varchar(100)
,`supplier_name` varchar(100)
,`qty_purchased` int(11)
,`unit_price` decimal(11,2)
,`total_cost` decimal(10,2)
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
`sale_id` int(11)
,`product_name` varchar(100)
,`qty_sold` int(11)
,`unit_price` decimal(11,2)
,`total_sale` decimal(10,2)
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
-- Structure for view `purchase_receipts_view`
--
DROP TABLE IF EXISTS `purchase_receipts_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `purchase_receipts_view`  AS SELECT `r`.`id` AS `id`, `r`.`receipt_number` AS `receipt_number`, `r`.`type` AS `type`, `r`.`total_amount` AS `total_amount`, `r`.`created_at` AS `created_at`, `r`.`created_by` AS `created_by`, `u`.`username` AS `created_by_name`, (select count(0) from `purchase` `p` where `p`.`receipt_id` = `r`.`id`) AS `num_products` FROM (`receipt` `r` join `user` `u` on(`r`.`created_by` = `u`.`id`)) WHERE `r`.`type` = 'purchase' ;

-- --------------------------------------------------------

--
-- Structure for view `role_permission_matrix`
--
DROP TABLE IF EXISTS `role_permission_matrix`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `role_permission_matrix`  AS SELECT `r`.`id` AS `role_id`, `r`.`role_name` AS `role_name`, `p`.`id` AS `permission_id`, `p`.`permission_name` AS `permission_name`, CASE WHEN `rp`.`role_id` is not null THEN 1 ELSE 0 END AS `assigned` FROM ((`permission` `p` join `role` `r`) left join `role_permission` `rp` on(`rp`.`role_id` = `r`.`id` and `rp`.`permission_id` = `p`.`id`)) ORDER BY `p`.`id` ASC, `r`.`id` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `sales_receipts_view`
--
DROP TABLE IF EXISTS `sales_receipts_view`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sales_receipts_view`  AS SELECT `r`.`id` AS `id`, `r`.`receipt_number` AS `receipt_number`, `r`.`type` AS `type`, `r`.`total_amount` AS `total_amount`, `r`.`created_at` AS `created_at`, `r`.`created_by` AS `created_by`, `u`.`username` AS `created_by_name`, (select count(0) from `sale` `s` where `s`.`receipt_id` = `r`.`id`) AS `num_products` FROM (`receipt` `r` join `user` `u` on(`r`.`created_by` = `u`.`id`)) WHERE `r`.`type` = 'sale' ORDER BY `r`.`id` DESC ;

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
-- Structure for view `view_product_movement`
--
DROP TABLE IF EXISTS `view_product_movement`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_product_movement`  AS SELECT `combined_movements`.`movement_id` AS `movement_id`, `combined_movements`.`product_id` AS `product_id`, `combined_movements`.`product_name` AS `product_name`, `combined_movements`.`movement_type` AS `movement_type`, `combined_movements`.`qty_in` AS `qty_in`, `combined_movements`.`qty_out` AS `qty_out`, `combined_movements`.`movement_date` AS `movement_date`, `combined_movements`.`receipt_id` AS `receipt_id`, `combined_movements`.`user_name` AS `user_name`, `combined_movements`.`receipt_number` AS `receipt_number`, `combined_movements`.`supplier_name` AS `supplier_name` FROM (select `sa`.`id` AS `movement_id`,`p`.`id` AS `product_id`,`p`.`name` AS `product_name`,'sale' AS `movement_type`,0 AS `qty_in`,`sa`.`quantity` AS `qty_out`,`sa`.`sale_date` AS `movement_date`,`sa`.`receipt_id` AS `receipt_id`,`u`.`username` AS `user_name`,`r`.`receipt_number` AS `receipt_number`,'-' AS `supplier_name` from (((`sale` `sa` left join `product` `p` on(`sa`.`product_id` = `p`.`id`)) left join `user` `u` on(`sa`.`sold_by` = `u`.`id`)) left join `receipt` `r` on(`sa`.`receipt_id` = `r`.`id`)) union all select `pu`.`id` AS `movement_id`,`p`.`id` AS `product_id`,`p`.`name` AS `product_name`,'purchase' AS `movement_type`,`pu`.`quantity` AS `qty_in`,0 AS `qty_out`,`pu`.`purchase_date` AS `movement_date`,`pu`.`receipt_id` AS `receipt_id`,`u`.`username` AS `user_name`,`r`.`receipt_number` AS `receipt_number`,`s`.`name` AS `supplier_name` from ((((`purchase` `pu` left join `product` `p` on(`pu`.`product_id` = `p`.`id`)) left join `user` `u` on(`pu`.`purchased_by` = `u`.`id`)) left join `receipt` `r` on(`pu`.`receipt_id` = `r`.`id`)) left join `supplier` `s` on(`p`.`supplier_id` = `s`.`id`))) AS `combined_movements` ;

-- --------------------------------------------------------

--
-- Structure for view `view_purchase_report`
--
DROP TABLE IF EXISTS `view_purchase_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_purchase_report`  AS SELECT `pu`.`id` AS `id`, `p`.`name` AS `product_name`, `s`.`name` AS `supplier_name`, `pu`.`quantity` AS `qty_purchased`, round(`pu`.`purchase_price` / `pu`.`quantity`,2) AS `unit_price`, `pu`.`purchase_price` AS `total_cost`, `pu`.`purchase_date` AS `purchase_date`, `r`.`receipt_number` AS `receipt_number`, `u`.`username` AS `purchased_by` FROM ((((`purchase` `pu` left join `product` `p` on(`pu`.`product_id` = `p`.`id`)) left join `supplier` `s` on(`pu`.`supplier_id` = `s`.`id`)) left join `receipt` `r` on(`pu`.`receipt_id` = `r`.`id`)) left join `user` `u` on(`pu`.`purchased_by` = `u`.`id`)) ORDER BY `pu`.`purchase_date` ASC ;

-- --------------------------------------------------------

--
-- Structure for view `view_sales_report`
--
DROP TABLE IF EXISTS `view_sales_report`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_sales_report`  AS SELECT `s`.`id` AS `sale_id`, `p`.`name` AS `product_name`, `s`.`quantity` AS `qty_sold`, round(`s`.`sale_price` / `s`.`quantity`,2) AS `unit_price`, `s`.`sale_price` AS `total_sale`, `s`.`sale_date` AS `sale_date`, `r`.`receipt_number` AS `receipt_number`, `u`.`username` AS `sold_by` FROM (((`sale` `s` left join `product` `p` on(`s`.`product_id` = `p`.`id`)) left join `receipt` `r` on(`s`.`receipt_id` = `r`.`id`)) left join `user` `u` on(`s`.`sold_by` = `u`.`id`)) ORDER BY `s`.`sale_date` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `fk_category_parent` (`parent_id`);

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
-- Indexes for table `supplier_category`
--
ALTER TABLE `supplier_category`
  ADD PRIMARY KEY (`supplier_id`,`category_id`),
  ADD KEY `fk_category` (`category_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `permission`
--
ALTER TABLE `permission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_request`
--
ALTER TABLE `product_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase`
--
ALTER TABLE `purchase`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `receipt`
--
ALTER TABLE `receipt`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sale`
--
ALTER TABLE `sale`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supplier`
--
ALTER TABLE `supplier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `fk_category_parent` FOREIGN KEY (`parent_id`) REFERENCES `category` (`id`);

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
-- Constraints for table `supplier_category`
--
ALTER TABLE `supplier_category`
  ADD CONSTRAINT `fk_category` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
