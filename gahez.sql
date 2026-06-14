-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 14, 2026 at 09:59 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gahez`
--

-- --------------------------------------------------------

--
-- Table structure for table `addresses`
--

CREATE TABLE `addresses` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `addresses`
--

INSERT INTO `addresses` (`id`, `user_id`, `address`, `latitude`, `longitude`, `name`, `phone`, `city`, `state`, `is_default`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 3, 'Nasr City, Cairo, Egypt', '30.0561', '31.3300', 'Home', '+201000111110', 'Cairo', 'Cairo', 1, 1, '2026-06-11 11:00:25', '2026-06-13 13:05:51'),
(2, 3, 'Zamalek, Cairo, Egypt', '30.0626', '31.2197', 'Office', '+201000111100', 'Cairo', 'Cairo', 0, 1, '2026-06-11 11:00:25', '2026-06-13 13:06:11');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `longitude` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `latitude`, `longitude`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '{\"ar\": \"الفرع الرئيسي\", \"en\": \"Main Branch\"}', 'Nasr City, Cairo, Egypt', '30.0444', '31.2357', '+201012345678', 1, '2026-06-11 11:00:24', '2026-06-13 12:49:13');

-- --------------------------------------------------------

--
-- Table structure for table `brands`
--

CREATE TABLE `brands` (
  `id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `brands`
--

INSERT INTO `brands` (`id`, `name`, `image`, `created_at`, `updated_at`) VALUES
(1, '{\"ar\": \"جاهز\", \"en\": \"Gahez\"}', 'brands/Fl6z5LNapKZ58ALbLnyCNlsBiOIYAaTRzxWFI0So.png', '2026-06-11 11:00:24', '2026-06-13 13:36:42'),
(2, '{\"ar\": \"المزارع الطازجة\", \"en\": \"Fresh Farms\"}', NULL, '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(3, '{\"ar\": \"الأساسيات اليومية\", \"en\": \"Daily Essentials\"}', 'brands/eutBC3kexMI0jH3AfTXczDvvI2zw3St1EELjcl4N.png', '2026-06-11 11:00:24', '2026-06-13 13:38:31');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('gahez-akeed-cache-5c785c036466adea360111aa28563bfd556b5fba', 'i:2;', 1781357984),
('gahez-akeed-cache-5c785c036466adea360111aa28563bfd556b5fba:timer', 'i:1781357984;', 1781357984),
('gahez-akeed-cache-77de68daecd823babbb58edb1c8e14d7106e83bb', 'i:2;', 1781353613),
('gahez-akeed-cache-77de68daecd823babbb58edb1c8e14d7106e83bb:timer', 'i:1781353613;', 1781353613),
('gahez-akeed-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:20:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:16:\"manage customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:17:\"manage categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"manage products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:13:\"manage brands\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:15:\"manage branches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"manage variants\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:14:\"manage coupons\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"manage offers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"manage goals\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:14:\"manage sliders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:13:\"manage orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:14:\"manage refunds\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:14:\"manage ratings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:22:\"manage product-reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"manage tickets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:20:\"manage support-chats\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:13:\"manage admins\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:1:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super-admin\";s:1:\"c\";s:3:\"web\";}}}', 1781441358),
('gahez-akid-cache-77de68daecd823babbb58edb1c8e14d7106e83bb', 'i:2;', 1781362257),
('gahez-akid-cache-77de68daecd823babbb58edb1c8e14d7106e83bb:timer', 'i:1781362257;', 1781362257),
('gahez-akid-cache-spatie.permission.cache', 'a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:20:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:16:\"manage customers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:14:\"view dashboard\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:17:\"manage categories\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:15:\"manage products\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:13:\"manage brands\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:15:\"manage branches\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"manage variants\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:14:\"manage coupons\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:13:\"manage offers\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"manage goals\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:14:\"manage sliders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:13:\"manage orders\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:14:\"manage refunds\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:12:\"view reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:14:\"manage ratings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:22:\"manage product-reports\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:14:\"manage tickets\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:20:\"manage support-chats\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:15:\"manage settings\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:13:\"manage admins\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:1:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:11:\"super-admin\";s:1:\"c\";s:3:\"web\";}}}', 1781445496);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `product_unit_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `parent_id` bigint UNSIGNED DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `image`, `is_active`, `is_featured`, `parent_id`, `sort_order`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"ar\": \"بقالة\", \"en\": \"Groceries\"}', 'groceries', NULL, 1, 1, NULL, 1, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(2, '{\"ar\": \"فواكه وخضروات\", \"en\": \"Fruits & Vegetables\"}', 'fruits-vegetables', 'categories/5Zs58pHRJicTV5AZOgZxel6pAlMDdkFFQOtNv6wT.jpg', 1, 0, 1, 1, '2026-06-11 11:00:24', '2026-06-13 13:23:20', NULL),
(3, '{\"ar\": \"ألبان\", \"en\": \"Dairy\"}', 'dairy', NULL, 1, 0, 1, 2, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(4, '{\"ar\": \"مشروبات\", \"en\": \"Beverages\"}', 'beverages', NULL, 1, 0, 1, 3, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(5, '{\"ar\": \"مخبوزات\", \"en\": \"Bakery\"}', 'bakery', NULL, 1, 1, NULL, 2, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(6, '{\"ar\": \"مستلزمات منزلية\", \"en\": \"Household\"}', 'household', NULL, 1, 0, NULL, 3, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(7, '{\"ar\": \"منظفات\", \"en\": \"Detergents\"}', 'detergents', NULL, 1, 1, NULL, 1, '2026-06-11 11:45:01', '2026-06-11 11:45:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `coupons`
--

CREATE TABLE `coupons` (
  `id` bigint UNSIGNED NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('fixed','percentage','free_delivery') COLLATE utf8mb4_unicode_ci NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_cart_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `usage_limit_per_user` int DEFAULT NULL,
  `usage_limit` int UNSIGNED DEFAULT NULL,
  `first_order_only` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `coupons`
--

INSERT INTO `coupons` (`id`, `code`, `type`, `discount_value`, `min_cart_amount`, `usage_limit_per_user`, `usage_limit`, `first_order_only`, `start_date`, `end_date`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'WELCOME10', 'percentage', '10.00', '5.00', 1, 1000, 1, '2026-06-09 21:00:00', '2026-12-10 21:00:00', 0, '2026-06-11 11:00:24', '2026-06-13 09:52:13'),
(2, 'SAVE5', 'fixed', '5.00', '20.00', 3, 500, 0, '2026-06-10 11:00:24', '2026-12-11 11:00:24', 1, '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(3, 'FREESHIP', 'free_delivery', '0.00', '15.00', NULL, NULL, 0, '2026-06-09 21:00:00', '2026-12-10 21:00:00', 0, '2026-06-11 11:00:24', '2026-06-13 09:52:02');

-- --------------------------------------------------------

--
-- Table structure for table `data_transfer_batches`
--

CREATE TABLE `data_transfer_batches` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `entity` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `direction` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `file_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `processed_rows` int UNSIGNED NOT NULL DEFAULT '0',
  `success_count` int UNSIGNED NOT NULL DEFAULT '0',
  `failed_count` int UNSIGNED NOT NULL DEFAULT '0',
  `skipped_count` int UNSIGNED NOT NULL DEFAULT '0',
  `message` text COLLATE utf8mb4_unicode_ci,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `data_transfer_batches`
--

INSERT INTO `data_transfer_batches` (`id`, `user_id`, `entity`, `direction`, `status`, `file_path`, `total_rows`, `processed_rows`, `success_count`, `failed_count`, `skipped_count`, `message`, `started_at`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'categories', 'export', 'completed', 'exports/categories_export_2026-06-11_144404.xlsx', 6, 6, 6, 0, 0, 'Export completed successfully.', '2026-06-11 11:44:04', '2026-06-11 11:44:05', '2026-06-11 11:44:04', '2026-06-11 11:44:05'),
(2, 1, 'categories', 'import', 'completed', 'imports/categories-2.xlsx', 1629, 1629, 1, 0, 1628, 'Import finished with 1 successful row(s), 1628 skipped (already exist or duplicate in file), and 0 failed row(s).', '2026-06-11 11:45:00', '2026-06-11 11:45:01', '2026-06-11 11:44:58', '2026-06-11 11:45:01'),
(3, 1, 'categories', 'export', 'completed', 'exports/categories_export_2026-06-13_143549.xlsx', 7, 7, 7, 0, 0, 'Export completed successfully.', '2026-06-13 11:35:49', '2026-06-13 11:35:49', '2026-06-13 11:35:49', '2026-06-13 11:35:49'),
(4, 1, 'products', 'export', 'completed', 'exports/products_export_2026-06-13_143603.xlsx', 10, 10, 10, 0, 0, 'Export completed successfully.', '2026-06-13 11:36:03', '2026-06-13 11:36:03', '2026-06-13 11:36:03', '2026-06-13 11:36:03'),
(5, 1, 'categories', 'export', 'completed', 'exports/categories_export_2026-06-13_144413.xlsx', 7, 7, 7, 0, 0, 'Export completed successfully.', '2026-06-13 11:44:13', '2026-06-13 11:44:13', '2026-06-13 11:44:13', '2026-06-13 11:44:13'),
(6, 1, 'categories', 'import', 'completed', 'imports/categories-6.xlsx', 1629, 1629, 0, 0, 1629, 'Import finished with 0 successful row(s), 1629 skipped (already exist or duplicate in file), and 0 failed row(s).', '2026-06-13 11:44:30', '2026-06-13 11:44:32', '2026-06-13 11:44:28', '2026-06-13 11:44:32'),
(7, 1, 'products', 'import', 'completed', 'imports/products-7.xlsx', 3298, 3298, 1, 0, 3297, 'Import finished with 1 successful row(s), 3297 skipped (already exist or duplicate in file), and 0 failed row(s).', '2026-06-13 11:44:50', '2026-06-13 11:44:56', '2026-06-13 11:44:48', '2026-06-13 11:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `description` json DEFAULT NULL,
  `period_type` enum('daily','weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_order_total` decimal(10,2) NOT NULL,
  `reward_amount` decimal(10,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `name`, `description`, `period_type`, `min_order_total`, `reward_amount`, `start_date`, `end_date`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, '{\"ar\": \"الهدف اليومي\", \"en\": \"Daily Goal\"}', '{\"ar\": \"كاش باك 50 ج.م لو فاتورتك ب 2000 ج.م اليوم\", \"en\": \"50 EGP cashback when your orders reach 2000 EGP today\"}', 'daily', '2000.00', '50.00', NULL, NULL, 1, 1, '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(2, '{\"ar\": \"الهدف الأسبوعي\", \"en\": \"Weekly Goal\"}', '{\"ar\": \"كاش باك 500 ج.م لو فاتورتك ب 5000 ج.م\", \"en\": \"500 EGP cashback when your orders reach 2000 EGP this week\"}', 'weekly', '5000.00', '500.00', NULL, NULL, 1, 2, '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(3, '{\"ar\": \"الهدف الشهري\", \"en\": \"Monthly Goal\"}', '{\"ar\": \"كاش باك 1000 ج.م لو فاتورتك ب 10000 ج.م هذا الشهر\", \"en\": \"1000 EGP cashback when your orders reach 10000 EGP this month\"}', 'monthly', '10000.00', '1000.00', NULL, NULL, 1, 3, '2026-06-11 11:00:24', '2026-06-11 11:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `goal_achievements`
--

CREATE TABLE `goal_achievements` (
  `id` bigint UNSIGNED NOT NULL,
  `goal_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `period_start` date NOT NULL,
  `period_end` date NOT NULL,
  `order_total` decimal(10,2) NOT NULL,
  `reward_amount` decimal(10,2) NOT NULL,
  `awarded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goal_achievements`
--

INSERT INTO `goal_achievements` (`id`, `goal_id`, `user_id`, `period_start`, `period_end`, `order_total`, `reward_amount`, `awarded_at`, `created_at`, `updated_at`) VALUES
(1, 1, 3, '2026-06-04', '2026-06-04', '2000.00', '50.00', '2026-06-04 18:59:59', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 2, 3, '2026-05-25', '2026-05-31', '5150.00', '500.00', '2026-05-30 20:59:59', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 1, 3, '2026-06-13', '2026-06-13', '2425.00', '50.00', '2026-06-13 10:13:10', '2026-06-13 10:13:10', '2026-06-13 10:13:10'),
(4, 2, 3, '2026-06-08', '2026-06-14', '6647.50', '500.00', '2026-06-13 10:36:55', '2026-06-13 10:36:55', '2026-06-13 10:36:55'),
(5, 3, 3, '2026-06-01', '2026-06-30', '11542.50', '1000.00', '2026-06-13 14:30:39', '2026-06-13 14:30:39', '2026-06-13 14:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `import_row_logs`
--

CREATE TABLE `import_row_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `data_transfer_batch_id` bigint UNSIGNED NOT NULL,
  `row_number` int UNSIGNED NOT NULL,
  `row_data` json DEFAULT NULL,
  `errors` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(27, 'default', '{\"uuid\":\"e446f66f-77c7-4c1d-a4a8-fcb613c116f3\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"9368be96-ce10-4886-bb23-2ed4c9ffbf5d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781360689,\"delay\":null}', 0, NULL, 1781360689, 1781360689),
(28, 'default', '{\"uuid\":\"130aad71-8a2a-4dda-8a75-8237af08ca2f\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"9368be96-ce10-4886-bb23-2ed4c9ffbf5d\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781360689,\"delay\":null}', 0, NULL, 1781360689, 1781360689),
(29, 'default', '{\"uuid\":\"30d41d86-ca69-487e-ae56-4be0009edb41\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"58a80d31-ea9e-4c83-b402-c893094d31ee\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781360691,\"delay\":null}', 0, NULL, 1781360691, 1781360691),
(30, 'default', '{\"uuid\":\"624aaed1-136b-4edd-b27d-01044a4bc7ad\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"58a80d31-ea9e-4c83-b402-c893094d31ee\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781360691,\"delay\":null}', 0, NULL, 1781360691, 1781360691),
(31, 'default', '{\"uuid\":\"b55c3775-7074-46e8-8c05-83e99e76e00e\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"5dbb0421-f00d-49ff-ba7c-88ea158ec4db\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781360695,\"delay\":null}', 0, NULL, 1781360695, 1781360695),
(32, 'default', '{\"uuid\":\"d17f5e04-7781-4866-9fde-9e39f6923014\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"5dbb0421-f00d-49ff-ba7c-88ea158ec4db\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781360695,\"delay\":null}', 0, NULL, 1781360695, 1781360695),
(33, 'default', '{\"uuid\":\"9b08a294-6fff-4811-aa63-401e7c494afa\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"6385081f-50c9-48c2-b163-c4f76da99e38\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781360697,\"delay\":null}', 0, NULL, 1781360697, 1781360697),
(34, 'default', '{\"uuid\":\"c98b8678-ef0e-4a2a-a785-b95a5988b033\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:7;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"6385081f-50c9-48c2-b163-c4f76da99e38\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781360697,\"delay\":null}', 0, NULL, 1781360697, 1781360697),
(35, 'default', '{\"uuid\":\"b9e678c8-eb27-491f-8c0d-3475dd7a752b\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"f7abb640-c9d1-4a6a-bb31-5dbcaa3214c0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781361030,\"delay\":null}', 0, NULL, 1781361030, 1781361030),
(36, 'default', '{\"uuid\":\"77610028-427f-44bd-82b8-d74ca4f770a3\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"f7abb640-c9d1-4a6a-bb31-5dbcaa3214c0\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781361030,\"delay\":null}', 0, NULL, 1781361030, 1781361030),
(37, 'default', '{\"uuid\":\"9bdb610d-4689-417b-9b6e-fea08e625d48\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"42003c8f-a866-42e1-bb19-b522898480c2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781361033,\"delay\":null}', 0, NULL, 1781361033, 1781361033),
(38, 'default', '{\"uuid\":\"c3eb4cf0-5093-47f1-bd26-07dd84f54852\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"42003c8f-a866-42e1-bb19-b522898480c2\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781361033,\"delay\":null}', 0, NULL, 1781361033, 1781361033),
(39, 'default', '{\"uuid\":\"7f552f06-be72-412a-ae53-43b2015744e9\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"e972d4c4-d097-4f91-acb1-54e915a61a3b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781361036,\"delay\":null}', 0, NULL, 1781361036, 1781361036),
(40, 'default', '{\"uuid\":\"f6c0fb5f-3d6b-4a93-b8f1-f65ee94ba72e\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"e972d4c4-d097-4f91-acb1-54e915a61a3b\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781361036,\"delay\":null}', 0, NULL, 1781361036, 1781361036),
(41, 'default', '{\"uuid\":\"236d2952-e498-43a0-a757-ee8c56c2ecc4\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"30b984f1-377f-4149-a8c8-a67d78802b5e\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:8:\\\"database\\\";}}\",\"batchId\":null},\"createdAt\":1781361039,\"delay\":null}', 0, NULL, 1781361039, 1781361039),
(42, 'default', '{\"uuid\":\"340f5722-fa59-4171-8452-208b3a8e4aa3\",\"displayName\":\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"deleteWhenMissingModels\":false,\"data\":{\"commandName\":\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\",\"command\":\"O:48:\\\"Illuminate\\\\Notifications\\\\SendQueuedNotifications\\\":3:{s:11:\\\"notifiables\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:15:\\\"App\\\\Models\\\\User\\\";s:2:\\\"id\\\";a:1:{i:0;i:3;}s:9:\\\"relations\\\";a:0:{}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:12:\\\"notification\\\";O:48:\\\"App\\\\Notifications\\\\OrderStatusUpdatedNotification\\\":2:{s:5:\\\"order\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:16:\\\"App\\\\Models\\\\Order\\\";s:2:\\\"id\\\";i:9;s:9:\\\"relations\\\";a:8:{i:0;s:4:\\\"user\\\";i:1;s:6:\\\"coupon\\\";i:2;s:7:\\\"address\\\";i:3;s:5:\\\"items\\\";i:4;s:13:\\\"items.product\\\";i:5;s:13:\\\"items.variant\\\";i:6;s:4:\\\"logs\\\";i:7;s:9:\\\"logs.user\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}s:2:\\\"id\\\";s:36:\\\"30b984f1-377f-4149-a8c8-a67d78802b5e\\\";}s:8:\\\"channels\\\";a:1:{i:0;s:4:\\\"mail\\\";}}\",\"batchId\":null},\"createdAt\":1781361039,\"delay\":null}', 0, NULL, 1781361039, 1781361039);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_14_080600_create_brands_table', 1),
(5, '2026_05_14_080714_create_categories_table', 1),
(6, '2026_05_14_081105_create_variants_table', 1),
(7, '2026_05_14_081112_create_variant_options_table', 1),
(8, '2026_05_14_081340_create_branches_table', 1),
(9, '2026_05_14_081436_create_products_table', 1),
(10, '2026_05_14_081754_create_product_relations_table', 1),
(11, '2026_05_14_081906_create_category_products_table', 1),
(12, '2026_05_14_081950_create_product_images_table', 1),
(13, '2026_05_14_082113_create_product_variants_table', 1),
(14, '2026_05_14_082139_create_product_variant_values_table', 1),
(15, '2026_05_14_082256_create_verifications_table', 1),
(16, '2026_05_14_082619_create_wishlists_table', 1),
(17, '2026_05_14_082634_create_addresses_table', 1),
(18, '2026_05_14_082743_create_tickets_table', 1),
(19, '2026_05_14_082751_create_ticket_messages_table', 1),
(20, '2026_05_14_082847_create_cart_items_table', 1),
(21, '2026_05_14_082921_create_coupons_table', 1),
(22, '2026_05_14_082959_create_wallet_transactions_table', 1),
(23, '2026_05_14_083046_create_orders_table', 1),
(24, '2026_05_14_083402_create_order_refund_requests_table', 1),
(25, '2026_05_14_083522_create_order_logs_table', 1),
(26, '2026_05_14_083651_create_product_ratings_table', 1),
(27, '2026_05_14_083724_create_product_reports_table', 1),
(28, '2026_05_14_084141_create_offers_table', 1),
(29, '2026_05_14_094924_create_personal_access_tokens_table', 1),
(30, '2026_05_14_100022_create_sliders_table', 1),
(31, '2026_05_14_100343_create_permission_tables', 1),
(32, '2026_05_14_100533_create_notifications_table', 1),
(33, '2026_05_14_100627_create_settings_table', 1),
(34, '2026_05_15_120000_create_data_transfer_batches_table', 1),
(35, '2026_05_15_140000_add_variant_id_to_variant_options_table', 1),
(36, '2026_05_16_100000_create_order_items_table', 1),
(37, '2026_05_17_090705_update_role_column_in_users_table', 1),
(38, '2026_05_18_120000_add_stock_deducted_at_to_orders_table', 1),
(39, '2026_05_18_140000_add_performance_indexes_to_tables', 1),
(40, '2026_05_20_103600_add_snapshots_and_restrict_product_delete_on_order_items', 1),
(41, '2026_05_20_114500_add_brand_snapshots_and_keep_products_when_brand_deleted', 1),
(42, '2026_05_20_121500_add_order_snapshots_and_history_safe_foreign_keys', 1),
(43, '2026_05_20_140000_add_thumbnail_to_product_variant_values_table', 1),
(44, '2026_06_03_135336_add_ready_for_delivery_to_orders', 1),
(45, '2026_06_03_142029_add_shipping_price_per_km_setting', 1),
(46, '2026_06_03_144030_add_branch_id_to_orders_table', 1),
(47, '2026_06_04_074601_add_manage_customers_permission', 1),
(48, '2026_06_06_083310_create_order_ratings_table', 1),
(49, '2026_06_06_083311_add_cancellation_reason_to_orders_table', 1),
(50, '2026_06_06_134125_add_sort_order_to_products_table', 1),
(51, '2026_06_06_160000_extend_offers_system', 1),
(52, '2026_06_07_100000_add_sort_order_to_categories_table', 1),
(53, '2026_06_07_105039_create_point_transactions_table', 1),
(54, '2026_06_07_105311_add_cashback_percentage_settings', 1),
(55, '2026_06_07_105334_add_point_to_value_settings', 1),
(56, '2026_06_07_120000_add_show_countdown_to_offers_table', 1),
(57, '2026_06_07_130000_add_first_order_only_to_coupons_table', 1),
(58, '2026_06_07_140000_extend_coupons_system', 1),
(59, '2026_06_07_150000_add_optional_stock_tracking', 1),
(60, '2026_06_07_170000_add_cashback_support_columns', 1),
(61, '2026_06_07_180000_extend_bogo_offer_fields', 1),
(62, '2026_06_11_100000_add_checkout_shipping_to_orders_table', 1),
(63, '2026_06_11_110000_create_units_and_product_units_tables', 1),
(64, '2026_06_11_120000_remove_price_and_stock_from_products_table', 1),
(65, '2026_06_11_130000_add_product_variant_id_to_product_units_table', 1),
(66, '2026_06_12_100000_create_supports_table', 1),
(67, '2026_06_12_100001_create_support_messages_table', 1),
(68, '2026_06_15_100000_create_goals_tables', 1),
(69, '2026_06_10_160000_add_type_to_sliders_table', 2),
(70, '2026_06_10_170000_normalize_slider_offer_type', 3),
(71, '2026_06_10_180000_make_sliders_image_nullable', 4),
(72, '2026_06_11_120000_add_type_to_tickets_table', 5),
(73, '2026_06_10_120000_add_image_to_brands_table', 6),
(74, '2026_06_10_150000_add_standard_shipping_fee_setting', 7);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_permissions`
--

INSERT INTO `model_has_permissions` (`permission_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 1),
(3, 'App\\Models\\User', 1),
(4, 'App\\Models\\User', 1),
(5, 'App\\Models\\User', 1),
(6, 'App\\Models\\User', 1),
(7, 'App\\Models\\User', 1),
(8, 'App\\Models\\User', 1),
(9, 'App\\Models\\User', 1),
(10, 'App\\Models\\User', 1),
(11, 'App\\Models\\User', 1),
(12, 'App\\Models\\User', 1),
(13, 'App\\Models\\User', 1),
(14, 'App\\Models\\User', 1),
(15, 'App\\Models\\User', 1),
(16, 'App\\Models\\User', 1),
(17, 'App\\Models\\User', 1),
(18, 'App\\Models\\User', 1),
(19, 'App\\Models\\User', 1),
(20, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 2),
(4, 'App\\Models\\User', 2),
(5, 'App\\Models\\User', 2),
(6, 'App\\Models\\User', 2),
(7, 'App\\Models\\User', 2),
(8, 'App\\Models\\User', 2),
(9, 'App\\Models\\User', 2),
(10, 'App\\Models\\User', 2),
(11, 'App\\Models\\User', 2),
(12, 'App\\Models\\User', 2),
(13, 'App\\Models\\User', 2),
(14, 'App\\Models\\User', 2),
(15, 'App\\Models\\User', 2),
(16, 'App\\Models\\User', 2),
(17, 'App\\Models\\User', 2),
(18, 'App\\Models\\User', 2);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint UNSIGNED NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type`, `notifiable_type`, `notifiable_id`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
('098b8f35-44c3-4e38-88e1-8c863ef71392', 'App\\Notifications\\CouponPromotionNotification', 'App\\Models\\User', 3, '{\"type\":\"coupon_promotion\",\"title\":\"New coupon at Gahez Akeed\",\"message\":\"New coupon code: SAVE5\",\"coupon_id\":2,\"coupon_code\":\"SAVE5\",\"coupon_type\":\"fixed\"}', NULL, '2026-06-13 13:12:47', '2026-06-13 13:12:47'),
('0e6e301e-66f6-42bb-9d3f-cc1521c56c84', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New order\",\"message\":\"A new order #7 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/7\",\"order_id\":7}', '2026-06-13 14:31:50', '2026-06-13 14:24:16', '2026-06-13 14:31:50'),
('1398e62e-dc96-4fc0-888f-e60bd281ba83', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #4 status is now delivered.\",\"order_id\":4,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('158c7990-e39b-4eaa-9e24-3f510dcfa9ce', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New order\",\"message\":\"A new order #4 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/4\\/edit\",\"order_id\":4}', '2026-06-13 10:18:32', '2026-06-13 09:57:22', '2026-06-13 10:18:32'),
('15fbacfa-ffac-4982-8b99-790f63d9efb1', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New order\",\"message\":\"A new order #4 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/4\\/edit\",\"order_id\":4}', NULL, '2026-06-13 09:57:22', '2026-06-13 09:57:22'),
('1d4df5ec-7939-4be8-a63a-274827c87f7f', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 1, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/3\",\"support_id\":3,\"support_message_id\":9,\"sender_type\":\"user\"}', '2026-06-13 12:24:56', '2026-06-13 12:15:32', '2026-06-13 12:24:56'),
('26add8bf-ee4d-4003-be3f-5b8c8e92def7', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 2, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #1\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/1\",\"support_id\":1,\"support_message_id\":7,\"sender_type\":\"user\"}', NULL, '2026-06-13 12:14:55', '2026-06-13 12:14:55'),
('2943aa6b-21f8-4d80-8690-170f836c52f7', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New order\",\"message\":\"A new order #9 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/9\",\"order_id\":9}', NULL, '2026-06-13 14:30:09', '2026-06-13 14:30:09'),
('2eefc010-2d0a-4b50-bfea-b20f0632b5f4', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New order\",\"message\":\"A new order #7 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/7\",\"order_id\":7}', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:16'),
('3718d4c6-2f5c-4d11-a7ad-c3a2c8c05729', 'App\\Notifications\\SupportMessageFromAdminNotification', 'App\\Models\\User', 3, '{\"type\":\"support_message\",\"title\":\"New support chat message\",\"message\":\"Support agent replied to your chat\",\"support_id\":3,\"support_message_id\":11,\"sender_type\":\"admin\"}', NULL, '2026-06-13 12:21:54', '2026-06-13 12:21:54'),
('4da825ad-ebf0-4ca5-b1cc-59c75a94bac7', 'App\\Notifications\\CouponPromotionNotification', 'App\\Models\\User', 3, '{\"type\":\"coupon_promotion\",\"title\":\"New coupon at Gahez Akeed\",\"message\":\"New coupon code: SAVE5\",\"coupon_id\":2,\"coupon_code\":\"SAVE5\",\"coupon_type\":\"fixed\"}', NULL, '2026-06-13 13:12:17', '2026-06-13 13:12:17'),
('4e31d14c-f5b1-4556-accd-9d37f9186672', 'App\\Notifications\\TicketCreatedNotification', 'App\\Models\\User', 1, '{\"title\":\"New ticket\",\"message\":\"New support ticket: Test 4 Subject\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/tickets\\/3\",\"ticket_id\":3}', '2026-06-13 09:41:46', '2026-06-13 09:33:23', '2026-06-13 09:41:46'),
('50d71217-ffeb-4cbc-b0ef-f9781fe90fa6', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New order\",\"message\":\"A new order #9 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/9\",\"order_id\":9}', '2026-06-13 14:30:26', '2026-06-13 14:30:09', '2026-06-13 14:30:26'),
('53e0c01a-a1ce-461c-b810-919fdaf79554', 'App\\Notifications\\OrderDeliveredAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"Order delivered\",\"message\":\"Order #5 has been delivered successfully.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/5\",\"order_id\":5,\"status\":\"delivered\"}', '2026-06-13 10:18:21', '2026-06-13 10:13:10', '2026-06-13 10:18:21'),
('552228a2-02a8-47e5-8836-cc73f8ac2ba5', 'App\\Notifications\\ProductReportSubmittedAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New product report\",\"message\":\"A customer reported Potato Chips 200g.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/product-reports?status=pending\",\"product_report_id\":3,\"product_id\":5}', NULL, '2026-06-13 10:40:18', '2026-06-13 10:40:18'),
('57264c4a-babf-4e3f-b21f-1e337286cd73', 'App\\Notifications\\TicketCreatedNotification', 'App\\Models\\User', 2, '{\"title\":\"New ticket\",\"message\":\"New Recommendation ticket: Product enhancement\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/tickets\\/4\",\"ticket_id\":4}', NULL, '2026-06-13 14:49:57', '2026-06-13 14:49:57'),
('5eb8f544-96aa-42c6-aa44-7b8caf39a02b', 'App\\Notifications\\OrderDeliveredAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"Order delivered\",\"message\":\"Order #5 has been delivered successfully.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/5\",\"order_id\":5,\"status\":\"delivered\"}', NULL, '2026-06-13 10:13:10', '2026-06-13 10:13:10'),
('5f1e44f0-9b2d-45f6-bbc7-b9bf03791814', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 1, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/3\",\"support_id\":3,\"support_message_id\":12,\"sender_type\":\"user\"}', '2026-06-13 12:24:56', '2026-06-13 12:24:44', '2026-06-13 12:24:56'),
('5f425d6e-0e14-4b38-835c-ee3b9478f7b1', 'App\\Notifications\\TicketMessageAddedNotification', 'App\\Models\\User', 3, '{\"title\":\"New ticket message\",\"message\":\"New message on ticket #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/tickets\\/3\",\"ticket_id\":3,\"ticket_message_id\":5,\"sender_type\":\"admin\"}', NULL, '2026-06-13 09:33:50', '2026-06-13 09:33:50'),
('61922baa-5191-418f-a981-3e8acdb7ec9f', 'App\\Notifications\\OrderRefundRequestSubmittedAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New refund request\",\"message\":\"A customer requested a refund for order #4.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/order-refund-requests\\/3\",\"order_refund_request_id\":3,\"order_id\":4}', '2026-06-13 10:37:40', '2026-06-13 10:37:06', '2026-06-13 10:37:40'),
('627a6953-76cf-4841-a1e6-e591471fdd88', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #4 status is now delivered.\",\"order_id\":4,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('64e9a508-9748-4d11-aedc-75e9c80f242b', 'App\\Notifications\\TicketMessageAddedNotification', 'App\\Models\\User', 2, '{\"title\":\"New ticket message\",\"message\":\"New message on ticket #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/tickets\\/3\",\"ticket_id\":3,\"ticket_message_id\":6,\"sender_type\":\"user\"}', NULL, '2026-06-13 09:34:50', '2026-06-13 09:34:50'),
('7069ad4a-2a25-4012-a344-ae2459df1013', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 2, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/3\",\"support_id\":3,\"support_message_id\":8,\"sender_type\":\"user\"}', NULL, '2026-06-13 12:15:16', '2026-06-13 12:15:16'),
('8124a1c1-520b-4983-a120-ff26b47cd9bb', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #4 status is now delivered.\",\"order_id\":4,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('81ff5aac-cdb4-40ba-8ca2-1c1fafac7ec1', 'App\\Notifications\\SupportMessageFromAdminNotification', 'App\\Models\\User', 3, '{\"type\":\"support_message\",\"title\":\"New support chat message\",\"message\":\"Support agent replied to your chat\",\"support_id\":3,\"support_message_id\":10,\"sender_type\":\"admin\"}', NULL, '2026-06-13 12:15:57', '2026-06-13 12:15:57'),
('82c0d4e4-216a-44f0-a754-e7c0e8440afd', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #4 status is now delivered.\",\"order_id\":4,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('8ad8c051-67c1-4219-8df4-ca2b8231618f', 'App\\Notifications\\ProductReportSubmittedAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New product report\",\"message\":\"A customer reported Potato Chips 200g.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/product-reports?status=pending\",\"product_report_id\":3,\"product_id\":5}', '2026-06-13 10:40:35', '2026-06-13 10:40:18', '2026-06-13 10:40:35'),
('8c7a9850-9d2c-4264-b8ea-73746eed6038', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New order\",\"message\":\"A new order #8 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/8\",\"order_id\":8}', NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25'),
('937d1883-646c-4179-bc79-07c3131e758b', 'App\\Notifications\\OrderRefundRequestSubmittedAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New refund request\",\"message\":\"A customer requested a refund for order #4.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/order-refund-requests\\/3\",\"order_refund_request_id\":3,\"order_id\":4}', NULL, '2026-06-13 10:37:06', '2026-06-13 10:37:06'),
('9682a450-36c3-4d64-b42f-aa8089c3a818', 'App\\Notifications\\CouponPromotionNotification', 'App\\Models\\User', 3, '{\"type\":\"coupon_promotion\",\"title\":\"New coupon at Gahez Akeed\",\"message\":\"New coupon code: SAVE5\",\"coupon_id\":2,\"coupon_code\":\"SAVE5\",\"coupon_type\":\"fixed\"}', NULL, '2026-06-13 13:12:38', '2026-06-13 13:12:38'),
('9698b86e-5ab7-438e-a5a3-4aa9b5e9b3d1', 'App\\Notifications\\TicketMessageAddedNotification', 'App\\Models\\User', 1, '{\"title\":\"New ticket message\",\"message\":\"New message on ticket #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/tickets\\/3\",\"ticket_id\":3,\"ticket_message_id\":6,\"sender_type\":\"user\"}', '2026-06-13 09:41:36', '2026-06-13 09:34:50', '2026-06-13 09:41:36'),
('ab4dd7ab-8f87-492d-bdeb-456401081d6d', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New order\",\"message\":\"A new order #8 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/8\",\"order_id\":8}', '2026-06-13 14:31:50', '2026-06-13 14:28:25', '2026-06-13 14:31:50'),
('c3046e38-2e49-4854-9532-4e58e2e6405e', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 2, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/3\",\"support_id\":3,\"support_message_id\":9,\"sender_type\":\"user\"}', NULL, '2026-06-13 12:15:32', '2026-06-13 12:15:32'),
('c43274bd-336e-48c7-8f31-8a3377f8ff20', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New order\",\"message\":\"A new order #6 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/6\",\"order_id\":6}', '2026-06-13 14:31:50', '2026-06-13 14:13:18', '2026-06-13 14:31:50'),
('c49bf6e5-f8e6-4ded-9b4e-41d7501d5c3c', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 1, '{\"title\":\"New order\",\"message\":\"A new order #5 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/5\\/edit\",\"order_id\":5}', '2026-06-13 10:18:23', '2026-06-13 10:08:17', '2026-06-13 10:18:23'),
('c75fe424-4926-458f-8b9c-0cb19ade168a', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #5 status is now delivered.\",\"order_id\":5,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('c771acc9-96d0-4a62-9b4a-069277a8398d', 'App\\Notifications\\TicketCreatedNotification', 'App\\Models\\User', 1, '{\"title\":\"New ticket\",\"message\":\"New Recommendation ticket: Product enhancement\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/tickets\\/4\",\"ticket_id\":4}', '2026-06-13 14:50:09', '2026-06-13 14:49:57', '2026-06-13 14:50:09'),
('d00e2a3a-13de-4490-9395-536ea9f9e471', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New order\",\"message\":\"A new order #6 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/6\",\"order_id\":6}', NULL, '2026-06-13 14:13:18', '2026-06-13 14:13:18'),
('d4f8660e-48e1-4573-aa74-fa31ab7fa22a', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 1, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/3\",\"support_id\":3,\"support_message_id\":8,\"sender_type\":\"user\"}', '2026-06-13 12:24:56', '2026-06-13 12:15:16', '2026-06-13 12:24:56'),
('dcb83764-3797-40f5-abf0-1c2e3ce88fb0', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 2, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/3\",\"support_id\":3,\"support_message_id\":6,\"sender_type\":\"user\"}', NULL, '2026-06-13 12:14:34', '2026-06-13 12:14:34'),
('e865ab38-e97a-4a4a-8e67-3cbf474ea6ec', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #5 status is now delivered.\",\"order_id\":5,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('f3647bfd-4a98-4ef2-9c61-fc8c4bcbe248', 'App\\Notifications\\CouponPromotionNotification', 'App\\Models\\User', 3, '{\"type\":\"coupon_promotion\",\"title\":\"New coupon at Gahez Akeed\",\"message\":\"New coupon code: SAVE5\",\"coupon_id\":2,\"coupon_code\":\"SAVE5\",\"coupon_type\":\"fixed\"}', NULL, '2026-06-13 13:12:43', '2026-06-13 13:12:43'),
('f3862ecc-1d8e-4101-9fb9-acd51e0c0ee9', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #5 status is now delivered.\",\"order_id\":5,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('f3e4483c-3476-413c-adfb-c2cb81128386', 'App\\Notifications\\NewOrderForAdminNotification', 'App\\Models\\User', 2, '{\"title\":\"New order\",\"message\":\"A new order #5 has been placed.\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/orders\\/5\\/edit\",\"order_id\":5}', NULL, '2026-06-13 10:08:17', '2026-06-13 10:08:17'),
('f7dee738-4ccc-4072-88bd-8db92da24e23', 'App\\Notifications\\OrderStatusUpdatedNotification', 'App\\Models\\User', 3, '{\"type\":\"order_status_updated\",\"title\":\"Order status updated\",\"message\":\"Your order #5 status is now delivered.\",\"order_id\":5,\"status\":\"delivered\",\"cancellation_reason\":null}', NULL, '2026-06-13 11:44:03', '2026-06-13 11:44:03'),
('fb5f95ac-ded8-43ce-85fe-f582c3977ad2', 'App\\Notifications\\TicketCreatedNotification', 'App\\Models\\User', 2, '{\"title\":\"New ticket\",\"message\":\"New support ticket: Test 4 Subject\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/tickets\\/3\",\"ticket_id\":3}', NULL, '2026-06-13 09:33:23', '2026-06-13 09:33:23'),
('fbd55eb6-2888-47f6-b22e-c1f7bd559a51', 'App\\Notifications\\SupportMessageFromCustomerNotification', 'App\\Models\\User', 1, '{\"type\":\"support_message_from_customer\",\"title\":\"New support chat message\",\"message\":\"New message on support chat #3\",\"url\":\"http:\\/\\/gahez.test\\/admin\\/support-chats\\/3\",\"support_id\":3,\"support_message_id\":6,\"sender_type\":\"user\"}', '2026-06-13 12:14:37', '2026-06-13 12:14:34', '2026-06-13 12:14:37');

-- --------------------------------------------------------

--
-- Table structure for table `offers`
--

CREATE TABLE `offers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `type` enum('fixed','percentage','bogo','threshold_gift','free_delivery') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `bogo_buy_quantity` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `bogo_bonus_quantity` tinyint UNSIGNED NOT NULL DEFAULT '1',
  `bogo_bonus_discount_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bogo_bonus_discount_value` decimal(10,2) DEFAULT NULL,
  `min_cart_amount` decimal(10,2) DEFAULT NULL,
  `max_discounted_quantity` int UNSIGNED DEFAULT NULL,
  `ends_when_out_of_stock` tinyint(1) NOT NULL DEFAULT '0',
  `start_date` timestamp NULL DEFAULT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `show_countdown` tinyint(1) NOT NULL DEFAULT '0',
  `offerable_id` bigint UNSIGNED DEFAULT NULL,
  `offerable_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `offers`
--

INSERT INTO `offers` (`id`, `name`, `type`, `value`, `bogo_buy_quantity`, `bogo_bonus_quantity`, `bogo_bonus_discount_type`, `bogo_bonus_discount_value`, `min_cart_amount`, `max_discounted_quantity`, `ends_when_out_of_stock`, `start_date`, `end_date`, `is_active`, `show_countdown`, `offerable_id`, `offerable_type`, `created_at`, `updated_at`) VALUES
(1, '{\"ar\": \"خصم ١٥٪ على التفاح\", \"en\": \"15% off Apples\"}', 'percentage', '15.00', 1, 1, 'percentage', '100.00', NULL, 5, 0, '2026-06-09 21:00:00', '2026-09-10 21:00:00', 0, 0, 1, 'App\\Models\\Product', '2026-06-11 11:00:24', '2026-06-13 14:27:32'),
(2, '{\"ar\": \"اشتري ١ واحصل على ١ حليب\", \"en\": \"Buy 1 Get 1 Milk\"}', 'bogo', '0.00', 1, 1, 'percentage', '100.00', NULL, NULL, 0, '2026-06-09 21:00:00', '2026-08-10 21:00:00', 0, 0, 2, 'App\\Models\\Product', '2026-06-11 11:00:24', '2026-06-13 14:27:21');

-- --------------------------------------------------------

--
-- Table structure for table `offer_reward_products`
--

CREATE TABLE `offer_reward_products` (
  `id` bigint UNSIGNED NOT NULL,
  `offer_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sub_total` decimal(10,2) NOT NULL,
  `order_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `coupon_id` bigint UNSIGNED DEFAULT NULL,
  `coupon_discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_shipping` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total` decimal(10,2) NOT NULL,
  `wallet_used` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','processing','ready_for_delivery','shipped','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `gift_offer_id` bigint UNSIGNED DEFAULT NULL,
  `gift_product_id` bigint UNSIGNED DEFAULT NULL,
  `address_id` bigint UNSIGNED DEFAULT NULL,
  `shipping_address_snapshot` longtext COLLATE utf8mb4_unicode_ci,
  `shipping_day` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_fast_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `fast_shipping_fee` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_commission` decimal(10,2) NOT NULL DEFAULT '0.00',
  `refund_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `refunded_total` decimal(10,2) NOT NULL DEFAULT '0.00',
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `stock_deducted_at` timestamp NULL DEFAULT NULL,
  `cashback_awarded_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `branch_id` bigint UNSIGNED DEFAULT NULL,
  `cancellation_reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `sub_total`, `order_discount`, `coupon_id`, `coupon_discount`, `total_shipping`, `total`, `wallet_used`, `status`, `payment_status`, `notes`, `gift_offer_id`, `gift_product_id`, `address_id`, `shipping_address_snapshot`, `shipping_day`, `is_fast_shipping`, `fast_shipping_fee`, `total_commission`, `refund_status`, `refunded_total`, `payment_method`, `paid_at`, `stock_deducted_at`, `cashback_awarded_at`, `created_at`, `updated_at`, `deleted_at`, `branch_id`, `cancellation_reason`) VALUES
(1, 3, 'Customer1', 'customer1@gmail.com', '50001111', '2195.00', '0.00', 1, '219.50', '5.00', '1980.50', '0.00', 'delivered', 'paid', 'Demo delivered order', NULL, NULL, 1, '{\"name\":\"Home\",\"address\":\"Block 5, Street 12, Salmiya\",\"latitude\":\"29.3375\",\"longitude\":\"48.0758\",\"phone\":\"50001111\"}', 'monday', 0, '0.00', '0.00', 'rejected', '0.00', 'cash_on_delivery', '2026-06-08 11:00:25', NULL, NULL, '2026-06-11 11:00:25', '2026-06-13 13:10:03', NULL, 1, NULL),
(2, 3, 'Customer1', 'customer1@gmail.com', '50001111', '2675.00', '0.00', 2, '5.00', '5.00', '2675.00', '0.00', 'pending', 'pending', 'Demo seeded order', NULL, NULL, 1, '{\"name\":\"Home\",\"address\":\"Block 5, Street 12, Salmiya\",\"latitude\":\"29.3375\",\"longitude\":\"48.0758\",\"phone\":\"50001111\"}', 'monday', 0, '0.00', '0.00', 'none', '0.00', 'cash_on_delivery', NULL, NULL, NULL, '2026-06-11 11:00:25', '2026-06-11 11:00:25', NULL, 1, NULL),
(3, 3, 'Customer1', 'customer1@gmail.com', '50001111', '2740.00', '0.00', NULL, '0.00', '5.00', '2745.00', '0.00', 'refunded', 'refunded', 'Demo refunded order', NULL, NULL, 1, '{\"name\":\"Home\",\"address\":\"Block 5, Street 12, Salmiya\",\"latitude\":\"29.3375\",\"longitude\":\"48.0758\",\"phone\":\"50001111\"}', 'monday', 0, '0.00', '0.00', 'refunded', '2745.00', 'cash_on_delivery', '2026-06-04 11:00:25', NULL, NULL, '2026-06-11 11:00:25', '2026-06-11 11:00:25', NULL, 1, NULL),
(4, 3, 'Customer1', 'customer1@gmail.com', '50001111', '2027.50', '232.50', NULL, '0.00', '40.00', '2067.50', '0.00', 'delivered', 'paid', NULL, NULL, NULL, 1, '{\"name\":\"Home\",\"phone\":\"50001111\",\"address\":\"Block 5, Street 12, Salmiya\",\"city\":\"Salmiya\",\"state\":\"Hawalli\",\"latitude\":\"29.3375\",\"longitude\":\"48.0758\"}', 'wednesday', 0, '0.00', '0.00', 'none', '0.00', 'cash_on_delivery', '2026-06-13 10:36:55', '2026-06-13 10:36:55', NULL, '2026-06-13 09:57:22', '2026-06-13 10:36:55', NULL, 1, NULL),
(5, 3, 'Customer1', 'customer1@gmail.com', '50001111', '2425.00', '45.00', NULL, '0.00', '140.00', '2565.00', '0.00', 'delivered', 'paid', NULL, NULL, NULL, 1, '{\"name\":\"Home\",\"phone\":\"50001111\",\"address\":\"Block 5, Street 12, Salmiya\",\"city\":\"Salmiya\",\"state\":\"Hawalli\",\"latitude\":\"29.3375\",\"longitude\":\"48.0758\"}', 'saturday', 1, '100.00', '0.00', 'none', '0.00', 'cash_on_delivery', '2026-06-13 10:13:10', '2026-06-13 10:13:10', NULL, '2026-06-13 10:08:17', '2026-06-13 10:13:10', NULL, 1, NULL),
(7, 3, 'Customer1', 'customer1@gmail.com', '+201000111110', '2425.00', '45.00', NULL, '0.00', '45.85', '2470.85', '0.00', 'delivered', 'paid', NULL, NULL, NULL, 1, '{\"name\":\"Home\",\"phone\":\"+201000111110\",\"address\":\"Nasr City, Cairo, Egypt\",\"city\":\"Cairo\",\"state\":\"Cairo\",\"latitude\":\"30.0561\",\"longitude\":\"31.3300\"}', 'tuesday', 0, '0.00', '0.00', 'none', '0.00', 'cash_on_delivery', '2026-06-13 14:24:57', '2026-06-13 14:24:57', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:57', NULL, 1, NULL),
(8, 3, 'Customer1', 'customer1@gmail.com', '+201000111110', '2470.00', '0.00', NULL, '0.00', '145.85', '2615.85', '0.00', 'pending', 'pending', NULL, NULL, NULL, 1, '{\"name\":\"Home\",\"phone\":\"+201000111110\",\"address\":\"Nasr City, Cairo, Egypt\",\"city\":\"Cairo\",\"state\":\"Cairo\",\"latitude\":\"30.0561\",\"longitude\":\"31.3300\"}', 'saturday', 1, '100.00', '0.00', 'none', '0.00', 'cash_on_delivery', NULL, NULL, NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25', NULL, 1, NULL),
(9, 3, 'Customer1', 'customer1@gmail.com', '+201000111110', '2470.00', '0.00', NULL, '0.00', '160.00', '2630.00', '0.00', 'delivered', 'paid', NULL, NULL, NULL, 1, '{\"name\":\"Home\",\"phone\":\"+201000111110\",\"address\":\"Nasr City, Cairo, Egypt\",\"city\":\"Cairo\",\"state\":\"Cairo\",\"latitude\":\"30.0561\",\"longitude\":\"31.3300\"}', 'saturday', 1, '100.00', '0.00', 'none', '0.00', 'cash_on_delivery', '2026-06-13 14:30:39', '2026-06-13 14:30:39', '2026-06-13 14:30:39', '2026-06-13 14:30:09', '2026-06-13 14:30:39', NULL, 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `product_unit_id` bigint UNSIGNED DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_name_ar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_factor` int UNSIGNED DEFAULT NULL,
  `quantity` int UNSIGNED NOT NULL DEFAULT '1',
  `unit_price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `is_gift` tinyint(1) NOT NULL DEFAULT '0',
  `line_discount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `product_unit_id`, `product_name`, `product_name_ar`, `product_slug`, `product_sku`, `variant_name`, `variant_name_ar`, `variant_sku`, `unit_name`, `unit_name_ar`, `unit_factor`, `quantity`, `unit_price`, `is_gift`, `line_discount`, `note`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, 1, 'Red Apples 1kg', 'تفاح أحمر 1 كجم', 'red-apples-1kg', 'APPLE-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 8, '150.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 1, 2, NULL, 2, 'Fresh Milk 1L', 'لبن طازج 1 لتر', 'fresh-milk-1l', 'MILK-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 10, '60.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 1, 3, NULL, 3, 'White Bread 500g', 'خبز أبيض 500 جرام', 'white-bread-500g', 'BREAD-500G', NULL, NULL, NULL, 'piece', 'قطعة', 1, 10, '15.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(4, 1, 4, NULL, 4, 'Orange Juice 1L', 'عصير برتقال 1 لتر', 'orange-juice-1l', 'JUICE-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 2, '70.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(5, 1, 5, NULL, 5, 'Potato Chips 200g', 'شيبسي 200 جرام', 'potato-chips-200g', 'CHIPS-200G', NULL, NULL, NULL, 'box', 'صندوق', 1, 3, '35.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(6, 2, 6, NULL, 6, 'Mineral Water 1.5L', 'مياه معدنية 1.5 لتر', 'mineral-water-1-5l', 'WATER-1.5L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 30, '20.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(7, 2, 7, NULL, 7, 'Laundry Detergent 1L', 'منظف ملابس 1 لتر', 'laundry-detergent-1l', 'DETERGENT-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 10, '120.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(8, 2, 8, NULL, 8, 'Tomatoes 1kg', 'طماطم 1 كجم', 'tomatoes-1kg', 'TOMATO-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 10, '45.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(9, 2, 4, NULL, 4, 'Orange Juice 1L', 'عصير برتقال 1 لتر', 'orange-juice-1l', 'JUICE-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 5, '70.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(10, 2, 3, NULL, 3, 'White Bread 500g', 'خبز أبيض 500 جرام', 'white-bread-500g', 'BREAD-500G', NULL, NULL, NULL, 'piece', 'قطعة', 1, 5, '15.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(11, 3, 1, NULL, 1, 'Red Apples 1kg', 'تفاح أحمر 1 كجم', 'red-apples-1kg', 'APPLE-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 6, '150.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(12, 3, 2, NULL, 2, 'Fresh Milk 1L', 'لبن طازج 1 لتر', 'fresh-milk-1l', 'MILK-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 8, '60.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(13, 3, 6, NULL, 6, 'Mineral Water 1.5L', 'مياه معدنية 1.5 لتر', 'mineral-water-1-5l', 'WATER-1.5L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 20, '20.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(14, 3, 7, NULL, 7, 'Laundry Detergent 1L', 'منظف ملابس 1 لتر', 'laundry-detergent-1l', 'DETERGENT-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 5, '120.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(15, 3, 8, NULL, 8, 'Tomatoes 1kg', 'طماطم 1 كجم', 'tomatoes-1kg', 'TOMATO-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 8, '45.00', 0, '0.00', '', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(16, 4, 1, NULL, 1, 'Red Apples 1kg', 'تفاح أحمر 1 كجم', 'red-apples-1kg', 'APPLE-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 10, '138.75', 0, '0.00', NULL, '2026-06-13 09:57:22', '2026-06-13 09:57:22'),
(17, 4, 2, NULL, 2, 'Fresh Milk 1L', 'لبن طازج 1 لتر', 'fresh-milk-1l', 'MILK-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 4, '30.00', 0, '0.00', NULL, '2026-06-13 09:57:22', '2026-06-13 09:57:22'),
(18, 4, 3, NULL, 3, 'White Bread 500g', 'خبز أبيض 500 جرام', 'white-bread-500g', 'BREAD-500G', NULL, NULL, NULL, 'piece', 'قطعة', 1, 2, '15.00', 0, '0.00', NULL, '2026-06-13 09:57:22', '2026-06-13 09:57:22'),
(19, 4, 4, NULL, 4, 'Orange Juice 1L', 'عصير برتقال 1 لتر', 'orange-juice-1l', 'JUICE-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 2, '70.00', 0, '0.00', NULL, '2026-06-13 09:57:22', '2026-06-13 09:57:22'),
(20, 4, 5, NULL, 5, 'Potato Chips 200g', 'شيبسي 200 جرام', 'potato-chips-200g', 'CHIPS-200G', NULL, NULL, NULL, 'box', 'صندوق', 1, 10, '35.00', 0, '0.00', NULL, '2026-06-13 09:57:22', '2026-06-13 09:57:22'),
(21, 5, 1, NULL, 1, 'Red Apples 1kg', 'تفاح أحمر 1 كجم', 'red-apples-1kg', 'APPLE-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 2, '127.50', 0, '0.00', NULL, '2026-06-13 10:08:17', '2026-06-13 10:08:17'),
(22, 5, 3, NULL, 3, 'White Bread 500g', 'خبز أبيض 500 جرام', 'white-bread-500g', 'BREAD-500G', NULL, NULL, NULL, 'piece', 'قطعة', 1, 5, '15.00', 0, '0.00', NULL, '2026-06-13 10:08:17', '2026-06-13 10:08:17'),
(23, 5, 4, NULL, 4, 'Orange Juice 1L', 'عصير برتقال 1 لتر', 'orange-juice-1l', 'JUICE-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 7, '70.00', 0, '0.00', NULL, '2026-06-13 10:08:17', '2026-06-13 10:08:17'),
(24, 5, 7, NULL, 7, 'Laundry Detergent 1L', 'منظف ملابس 1 لتر', 'laundry-detergent-1l', 'DETERGENT-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 10, '120.00', 0, '0.00', NULL, '2026-06-13 10:08:17', '2026-06-13 10:08:17'),
(25, 5, 8, NULL, 8, 'Tomatoes 1kg', 'طماطم 1 كجم', 'tomatoes-1kg', 'TOMATO-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 10, '40.50', 0, '0.00', NULL, '2026-06-13 10:08:17', '2026-06-13 10:08:17'),
(31, 7, 1, NULL, 1, 'Red Apples 1kg', 'تفاح أحمر 1 كجم', 'red-apples-1kg', 'APPLE-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 2, '150.00', 0, '45.00', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:16'),
(32, 7, 3, NULL, 3, 'White Bread 500g', 'خبز أبيض 500 جرام', 'white-bread-500g', 'BREAD-500G', NULL, NULL, NULL, 'piece', 'قطعة', 1, 5, '15.00', 0, '0.00', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:16'),
(33, 7, 4, NULL, 4, 'Orange Juice 1L', 'عصير برتقال 1 لتر', 'orange-juice-1l', 'JUICE-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 7, '70.00', 0, '0.00', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:16'),
(34, 7, 7, NULL, 7, 'Laundry Detergent 1L', 'منظف ملابس 1 لتر', 'laundry-detergent-1l', 'DETERGENT-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 10, '120.00', 0, '0.00', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:16'),
(35, 7, 8, NULL, 8, 'Tomatoes 1kg', 'طماطم 1 كجم', 'tomatoes-1kg', 'TOMATO-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 10, '40.50', 0, '0.00', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:16'),
(36, 8, 1, NULL, 1, 'Red Apples 1kg', 'تفاح أحمر 1 كجم', 'red-apples-1kg', 'APPLE-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 2, '150.00', 0, '0.00', NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25'),
(37, 8, 3, NULL, 3, 'White Bread 500g', 'خبز أبيض 500 جرام', 'white-bread-500g', 'BREAD-500G', NULL, NULL, NULL, 'piece', 'قطعة', 1, 5, '15.00', 0, '0.00', NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25'),
(38, 8, 4, NULL, 4, 'Orange Juice 1L', 'عصير برتقال 1 لتر', 'orange-juice-1l', 'JUICE-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 7, '70.00', 0, '0.00', NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25'),
(39, 8, 7, NULL, 7, 'Laundry Detergent 1L', 'منظف ملابس 1 لتر', 'laundry-detergent-1l', 'DETERGENT-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 10, '120.00', 0, '0.00', NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25'),
(40, 8, 8, NULL, 8, 'Tomatoes 1kg', 'طماطم 1 كجم', 'tomatoes-1kg', 'TOMATO-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 10, '40.50', 0, '0.00', NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25'),
(41, 9, 1, NULL, 1, 'Red Apples 1kg', 'تفاح أحمر 1 كجم', 'red-apples-1kg', 'APPLE-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 2, '150.00', 0, '0.00', NULL, '2026-06-13 14:30:09', '2026-06-13 14:30:09'),
(42, 9, 3, NULL, 3, 'White Bread 500g', 'خبز أبيض 500 جرام', 'white-bread-500g', 'BREAD-500G', NULL, NULL, NULL, 'piece', 'قطعة', 1, 5, '15.00', 0, '0.00', NULL, '2026-06-13 14:30:09', '2026-06-13 14:30:09'),
(43, 9, 4, NULL, 4, 'Orange Juice 1L', 'عصير برتقال 1 لتر', 'orange-juice-1l', 'JUICE-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 7, '70.00', 0, '0.00', NULL, '2026-06-13 14:30:09', '2026-06-13 14:30:09'),
(44, 9, 7, NULL, 7, 'Laundry Detergent 1L', 'منظف ملابس 1 لتر', 'laundry-detergent-1l', 'DETERGENT-1L', NULL, NULL, NULL, 'bottle', 'زجاجة', 1, 10, '120.00', 0, '0.00', NULL, '2026-06-13 14:30:09', '2026-06-13 14:30:09'),
(45, 9, 8, NULL, 8, 'Tomatoes 1kg', 'طماطم 1 كجم', 'tomatoes-1kg', 'TOMATO-1KG', NULL, NULL, NULL, 'kg', 'كجم', 1, 10, '40.50', 0, '0.00', NULL, '2026-06-13 14:30:09', '2026-06-13 14:30:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_logs`
--

CREATE TABLE `order_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_logs`
--

INSERT INTO `order_logs` (`id`, `order_id`, `user_id`, `type`, `from_status`, `to_status`, `payload`, `created_at`, `updated_at`) VALUES
(1, 4, 3, 'order_placed', NULL, 'pending', NULL, '2026-06-13 09:57:22', '2026-06-13 09:57:22'),
(2, 5, 3, 'order_placed', NULL, 'pending', NULL, '2026-06-13 10:08:17', '2026-06-13 10:08:17'),
(3, 5, 1, 'status_change', 'pending', 'processing', NULL, '2026-06-13 10:13:00', '2026-06-13 10:13:00'),
(4, 5, 1, 'status_change', 'processing', 'ready_for_delivery', NULL, '2026-06-13 10:13:03', '2026-06-13 10:13:03'),
(5, 5, 1, 'status_change', 'ready_for_delivery', 'shipped', NULL, '2026-06-13 10:13:07', '2026-06-13 10:13:07'),
(6, 5, 1, 'status_change', 'shipped', 'delivered', NULL, '2026-06-13 10:13:10', '2026-06-13 10:13:10'),
(7, 5, 1, 'payment_change', 'pending', 'paid', '{\"trigger\": \"delivered\", \"payment_method\": \"cash_on_delivery\"}', '2026-06-13 10:13:10', '2026-06-13 10:13:10'),
(8, 4, 1, 'status_change', 'pending', 'processing', NULL, '2026-06-13 10:36:46', '2026-06-13 10:36:46'),
(9, 4, 1, 'status_change', 'processing', 'ready_for_delivery', NULL, '2026-06-13 10:36:49', '2026-06-13 10:36:49'),
(10, 4, 1, 'status_change', 'ready_for_delivery', 'shipped', NULL, '2026-06-13 10:36:52', '2026-06-13 10:36:52'),
(11, 4, 1, 'status_change', 'shipped', 'delivered', NULL, '2026-06-13 10:36:55', '2026-06-13 10:36:55'),
(12, 4, 1, 'payment_change', 'pending', 'paid', '{\"trigger\": \"delivered\", \"payment_method\": \"cash_on_delivery\"}', '2026-06-13 10:36:55', '2026-06-13 10:36:55'),
(14, 7, 3, 'order_placed', NULL, 'pending', NULL, '2026-06-13 14:24:16', '2026-06-13 14:24:16'),
(15, 7, 1, 'status_change', 'pending', 'processing', NULL, '2026-06-13 14:24:49', '2026-06-13 14:24:49'),
(16, 7, 1, 'status_change', 'processing', 'ready_for_delivery', NULL, '2026-06-13 14:24:51', '2026-06-13 14:24:51'),
(17, 7, 1, 'status_change', 'ready_for_delivery', 'shipped', NULL, '2026-06-13 14:24:55', '2026-06-13 14:24:55'),
(18, 7, 1, 'status_change', 'shipped', 'delivered', NULL, '2026-06-13 14:24:57', '2026-06-13 14:24:57'),
(19, 7, 1, 'payment_change', 'pending', 'paid', '{\"trigger\": \"delivered\", \"payment_method\": \"cash_on_delivery\"}', '2026-06-13 14:24:57', '2026-06-13 14:24:57'),
(20, 8, 3, 'order_placed', NULL, 'pending', NULL, '2026-06-13 14:28:25', '2026-06-13 14:28:25'),
(21, 9, 3, 'order_placed', NULL, 'pending', NULL, '2026-06-13 14:30:09', '2026-06-13 14:30:09'),
(22, 9, 1, 'status_change', 'pending', 'processing', NULL, '2026-06-13 14:30:30', '2026-06-13 14:30:30'),
(23, 9, 1, 'status_change', 'processing', 'ready_for_delivery', NULL, '2026-06-13 14:30:33', '2026-06-13 14:30:33'),
(24, 9, 1, 'status_change', 'ready_for_delivery', 'shipped', NULL, '2026-06-13 14:30:36', '2026-06-13 14:30:36'),
(25, 9, 1, 'status_change', 'shipped', 'delivered', NULL, '2026-06-13 14:30:39', '2026-06-13 14:30:39'),
(26, 9, 1, 'payment_change', 'pending', 'paid', '{\"trigger\": \"delivered\", \"payment_method\": \"cash_on_delivery\"}', '2026-06-13 14:30:39', '2026-06-13 14:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `order_ratings`
--

CREATE TABLE `order_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_ratings`
--

INSERT INTO `order_ratings` (`id`, `order_id`, `user_id`, `rating`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 5, 'Fast delivery and fresh products.', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 9, 3, 4, NULL, '2026-06-13 14:35:43', '2026-06-13 14:35:43');

-- --------------------------------------------------------

--
-- Table structure for table `order_refund_requests`
--

CREATE TABLE `order_refund_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` text COLLATE utf8mb4_unicode_ci,
  `processed_by` bigint UNSIGNED DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_refund_requests`
--

INSERT INTO `order_refund_requests` (`id`, `order_id`, `user_id`, `status`, `reason`, `details`, `processed_by`, `processed_at`, `created_at`, `updated_at`) VALUES
(1, 3, 3, 'approved', 'damaged_items', 'Products arrived with damaged packaging.', 2, '2026-06-05 11:00:25', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 1, 3, 'rejected', 'wrong_item', 'Received apples instead of milk.', 1, '2026-06-13 13:10:03', '2026-06-11 11:00:25', '2026-06-13 13:10:03'),
(3, 4, 3, 'pending', 'Damaged item', 'Box was opened and product scratched.', NULL, NULL, '2026-06-13 10:37:06', '2026-06-13 10:37:06');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'manage customers', 'web', '2026-06-11 11:00:22', '2026-06-11 11:00:22'),
(2, 'view dashboard', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(3, 'manage categories', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(4, 'manage products', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(5, 'manage brands', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(6, 'manage branches', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(7, 'manage variants', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(8, 'manage coupons', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(9, 'manage offers', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(10, 'manage goals', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(11, 'manage sliders', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(12, 'manage orders', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(13, 'manage refunds', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(14, 'view reports', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(15, 'manage ratings', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(16, 'manage product-reports', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(17, 'manage tickets', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(18, 'manage support-chats', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(19, 'manage settings', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(20, 'manage admins', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 3, 'auth-token', 'aabdaf4fc0d7208ca0af752eeac50db1bed300827030ec4fd6d37928836b68f7', '[\"*\"]', '2026-06-13 14:49:57', NULL, '2026-06-13 09:32:43', '2026-06-13 14:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `point_transactions`
--

CREATE TABLE `point_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('addition','subtraction') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int NOT NULL,
  `balance_after` int NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `point_transactions`
--

INSERT INTO `point_transactions` (`id`, `user_id`, `type`, `amount`, `balance_after`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 'addition', 80, 80, 'Cashback for delivered order', '2026-06-06 11:00:25', '2026-06-06 11:00:25'),
(2, 3, 'addition', 50, 130, 'Welcome bonus points', '2026-06-01 11:00:25', '2026-06-01 11:00:25'),
(3, 3, 'subtraction', 20, 110, 'Points used on order discount', '2026-06-08 11:00:25', '2026-06-08 11:00:25'),
(4, 3, 'addition', 40, 150, 'Cashback for second delivered order', '2026-06-10 11:00:25', '2026-06-10 11:00:25'),
(5, 3, 'addition', 12, 162, 'Cashback for order #9 (5% of 2,470.00 L.E.)', '2026-06-13 14:30:39', '2026-06-13 14:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint UNSIGNED NOT NULL,
  `type` enum('simple','variable') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'simple',
  `name` json NOT NULL,
  `description` json NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `sort_order` int NOT NULL DEFAULT '0',
  `discount` decimal(10,2) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_new` tinyint(1) NOT NULL DEFAULT '0',
  `is_approved` tinyint(1) NOT NULL DEFAULT '1',
  `is_bookable` tinyint(1) NOT NULL DEFAULT '1',
  `brand_id` bigint UNSIGNED NOT NULL,
  `brand_snapshot` json DEFAULT NULL,
  `category_snapshot` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `type`, `name`, `description`, `thumbnail`, `sku`, `slug`, `is_in_stock`, `sort_order`, `discount`, `discount_type`, `is_active`, `is_featured`, `is_new`, `is_approved`, `is_bookable`, `brand_id`, `brand_snapshot`, `category_snapshot`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'simple', '{\"ar\": \"تفاح أحمر 1 كجم\", \"en\": \"Red Apples 1kg\"}', '{\"ar\": \"تفاح أحمر طازج.\", \"en\": \"Fresh red apples.\"}', NULL, 'APPLE-1KG', 'red-apples-1kg', 1, 0, '0.00', NULL, 1, 1, 1, 1, 1, 2, NULL, NULL, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(2, 'simple', '{\"ar\": \"لبن طازج 1 لتر\", \"en\": \"Fresh Milk 1L\"}', '{\"ar\": \"لبن كامل الدسم.\", \"en\": \"Full cream fresh milk.\"}', NULL, 'MILK-1L', 'fresh-milk-1l', 1, 0, '0.00', NULL, 1, 1, 0, 1, 1, 1, NULL, NULL, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(3, 'simple', '{\"ar\": \"خبز أبيض 500 جرام\", \"en\": \"White Bread 500g\"}', '{\"ar\": \"خبز طري من المخبز.\", \"en\": \"Soft bakery bread.\"}', NULL, 'BREAD-500G', 'white-bread-500g', 1, 0, '0.00', NULL, 1, 0, 0, 1, 1, 1, NULL, NULL, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(4, 'simple', '{\"ar\": \"عصير برتقال 1 لتر\", \"en\": \"Orange Juice 1L\"}', '{\"ar\": \"عصير برتقال بدون سكر مضاف.\", \"en\": \"No added sugar orange juice.\"}', NULL, 'JUICE-1L', 'orange-juice-1l', 1, 0, '0.00', NULL, 1, 0, 1, 1, 1, 3, NULL, NULL, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(5, 'simple', '{\"ar\": \"شيبسي 200 جرام\", \"en\": \"Potato Chips 200g\"}', '{\"ar\": \"شيبسي مملح كلاسيكي.\", \"en\": \"Classic salted chips.\"}', NULL, 'CHIPS-200G', 'potato-chips-200g', 1, 0, '0.00', NULL, 1, 0, 0, 1, 1, 3, NULL, NULL, '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(6, 'simple', '{\"ar\": \"مياه معدنية 1.5 لتر\", \"en\": \"Mineral Water 1.5L\"}', '{\"ar\": \"مياه معدنية غازية.\", \"en\": \"Still mineral water.\"}', NULL, 'WATER-1.5L', 'mineral-water-1-5l', 1, 0, '0.00', 'percentage', 0, 0, 0, 1, 1, 1, '{\"id\": 1, \"name\": \"Gahez\", \"name_ar\": \"جاهز\"}', '[{\"id\": 4, \"name\": \"Beverages\", \"name_ar\": \"مشروبات\"}]', '2026-06-11 11:00:24', '2026-06-11 14:46:26', NULL),
(7, 'simple', '{\"ar\": \"منظف ملابس 1 لتر\", \"en\": \"Laundry Detergent 1L\"}', '{\"ar\": \"منظف ملابس مركز.\", \"en\": \"Concentrated laundry detergent.\"}', NULL, 'DETERGENT-1L', 'laundry-detergent-1l', 1, 0, '0.00', 'percentage', 1, 0, 0, 1, 1, 3, '{\"id\": 3, \"name\": \"Daily Essentials\", \"name_ar\": \"الأساسيات اليومية\"}', '[{\"id\": 6, \"name\": \"Household\", \"name_ar\": \"مستلزمات منزلية\"}, {\"id\": 7, \"name\": \"Detergents\", \"name_ar\": \"منظفات\"}]', '2026-06-11 11:00:24', '2026-06-11 11:53:17', NULL),
(8, 'simple', '{\"ar\": \"طماطم 1 كجم\", \"en\": \"Tomatoes 1kg\"}', '{\"ar\": \"طماطم حمراء طازجة.\", \"en\": \"Ripe red tomatoes.\"}', 'products/1NuTTeR6eBSeLHY9mHeUo5oWCnPnB74coyufBMfh.webp', 'TOMATO-1KG', 'tomatoes-1kg', 1, 0, '0.00', 'percentage', 1, 0, 0, 1, 1, 2, '{\"id\": 2, \"name\": \"Fresh Farms\", \"name_ar\": \"المزارع الطازجة\"}', '[{\"id\": 2, \"name\": \"Fruits & Vegetables\", \"name_ar\": \"فواكه وخضروات\"}]', '2026-06-11 11:00:24', '2026-06-11 11:51:30', NULL),
(9, 'variable', '{\"ar\": \"منظف متعدد الاستخدامات داك 3 لتر\", \"en\": \"Dac Detergent Multi-Purpose 3L\"}', '{\"ar\": \"منظف متعدد الاستخدامات داك 3 لتر\", \"en\": \"Dac Detergent Multi-Purpose 3L\"}', 'products/UJ1sxdgD8u1C1weTKAoJxLYxPFjUTmaJ9dCpFgIG.avif', 'PRD-0001', 'dac-detergent-multi-purpose-3l', 1, 0, '5.00', 'percentage', 1, 0, 1, 1, 1, 3, '{\"id\": 3, \"name\": \"Daily Essentials\", \"name_ar\": \"الأساسيات اليومية\"}', '[{\"id\": 7, \"name\": \"Detergents\", \"name_ar\": \"منظفات\"}]', '2026-06-13 08:27:03', '2026-06-13 08:27:03', NULL),
(10, 'simple', '{\"ar\": \"ليز فلفل حار\", \"en\": \"Lay\'s Hot Pepper\"}', '{\"ar\": \"ليز فلفل حار\", \"en\": \"Lay\'s Hot Pepper\"}', 'products/LURDt8kSmbRplzDJkEUUWeJTqcGlBgE3FRBFkYF8.jpg', 'PRD-0002', 'lays-hot-pepper', 1, 0, '0.00', 'percentage', 1, 0, 0, 1, 1, 1, '{\"id\": 1, \"name\": \"Gahez\", \"name_ar\": \"جاهز\"}', '[{\"id\": 1, \"name\": \"Groceries\", \"name_ar\": \"بقالة\"}]', '2026-06-13 08:37:20', '2026-06-13 08:37:20', NULL),
(11, 'simple', '{\"ar\": \"سيؤءيس\", \"en\": \"test\"}', '{\"ar\": \"سؤيؤ\", \"en\": \"testttt\"}', 'products/whaYDcxknNQPsGkBPdLiyqDAvsEQfrNXMuKHEolF.jpg', 'PRD-0003', 'test', 1, 0, '0.00', 'percentage', 0, 0, 0, 1, 1, 1, '{\"id\": 1, \"name\": \"Gahez\", \"name_ar\": \"جاهز\"}', '[{\"id\": 1, \"name\": \"Groceries\", \"name_ar\": \"بقالة\"}]', '2026-06-13 09:08:03', '2026-06-13 09:08:09', '2026-06-13 09:08:09'),
(12, 'variable', '{\"ar\": \"فثفسفسلي\", \"en\": \"testtt\"}', '{\"ar\": \"يسبسب\", \"en\": \"tetstttdtd\"}', 'products/kojZd9tc9SxBltdzeabArL5EZneh2w9DqEHbmazv.webp', 'PRD-0004', 'testtt', 1, 0, '0.00', 'percentage', 0, 0, 0, 1, 1, 2, '{\"id\": 2, \"name\": \"Fresh Farms\", \"name_ar\": \"المزارع الطازجة\"}', '[{\"id\": 4, \"name\": \"Beverages\", \"name_ar\": \"مشروبات\"}]', '2026-06-13 09:09:27', '2026-06-13 09:09:31', '2026-06-13 09:09:31'),
(13, 'simple', '{\"ar\": \"برتقال طازج ١ كجم\", \"en\": \"Fresh oranges 1kg\"}', '{\"ar\": \"برتقال طازج مع الكثير من العصير\", \"en\": \"Fresh oranges with lot of juices\"}', NULL, 'ORANGE-1KG', 'fresh-oranges-1kg', 1, 0, '0.00', NULL, 1, 1, 1, 1, 1, 1, '{\"id\": 1, \"name\": \"Gahez\", \"name_ar\": \"جاهز\"}', '[{\"id\": 2, \"name\": \"Fruits & Vegetables\", \"name_ar\": \"فواكه وخضروات\"}]', '2026-06-13 11:44:53', '2026-06-13 11:44:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `product_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, NULL),
(2, 3, NULL, NULL),
(3, 5, NULL, NULL),
(4, 4, NULL, NULL),
(5, 1, NULL, NULL),
(6, 4, NULL, NULL),
(7, 6, NULL, NULL),
(7, 7, NULL, NULL),
(8, 2, NULL, NULL),
(9, 7, NULL, NULL),
(10, 1, NULL, NULL),
(11, 1, NULL, NULL),
(12, 4, NULL, NULL),
(13, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` bigint UNSIGNED NOT NULL,
  `imageable_id` bigint UNSIGNED NOT NULL,
  `imageable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `imageable_id`, `imageable_type`, `path`, `created_at`, `updated_at`) VALUES
(1, 8, 'App\\Models\\Product', 'products/IBf6nija7n1AJte288HpImPqtWk2dFK4X6e0LOcB.webp', '2026-06-11 11:51:30', '2026-06-11 11:51:30'),
(2, 9, 'App\\Models\\Product', 'products/5fNIZ8xwHBTgiMFzX0ozt2ZGSFpG7kpUDKgKzX15.jpg', '2026-06-13 08:27:03', '2026-06-13 08:27:03'),
(3, 9, 'App\\Models\\Product', 'products/3foF2Q2Jm6ZjoH8jlD6VuTOszXdVNX9uuZkTe8IQ.avif', '2026-06-13 08:27:03', '2026-06-13 08:27:03'),
(4, 10, 'App\\Models\\Product', 'products/0woJjX0WPSxtjnrDgIrBac1HF6jx4nhkHfBmgrH0.jpg', '2026-06-13 08:37:20', '2026-06-13 08:37:20'),
(5, 11, 'App\\Models\\Product', 'products/ypW4wlq0Pp7bYDCJ7G5kPTStoAKo23HmtqORFbor.jpg', '2026-06-13 09:08:04', '2026-06-13 09:08:04'),
(6, 12, 'App\\Models\\Product', 'products/krvbcsAQkrCGGaMkHTzD0RfCP89oJOn2ctJhl8Px.png', '2026-06-13 09:09:27', '2026-06-13 09:09:27');

-- --------------------------------------------------------

--
-- Table structure for table `product_ratings`
--

CREATE TABLE `product_ratings` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint UNSIGNED NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_ratings`
--

INSERT INTO `product_ratings` (`id`, `product_id`, `user_id`, `rating`, `comment`, `is_visible`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 4, 'Great quality and fast delivery.', 1, '2026-06-11 11:00:25', '2026-06-13 13:18:01'),
(2, 2, 3, 5, 'Good value for money.', 1, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 3, 3, 4, 'Fresh product, will order again.', 1, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(4, 4, 3, 5, 'Average taste, packaging was fine.', 1, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(5, 5, 3, 4, 'Excellent! Highly recommended.', 1, '2026-06-11 11:00:25', '2026-06-11 11:00:25');

-- --------------------------------------------------------

--
-- Table structure for table `product_relations`
--

CREATE TABLE `product_relations` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `related_product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_relations`
--

INSERT INTO `product_relations` (`id`, `product_id`, `related_product_id`, `created_at`, `updated_at`) VALUES
(1, 8, 1, '2026-06-11 11:51:30', '2026-06-11 11:51:30'),
(2, 9, 7, '2026-06-13 08:27:03', '2026-06-13 08:27:03'),
(3, 10, 5, '2026-06-13 08:37:20', '2026-06-13 08:37:20'),
(4, 11, 1, '2026-06-13 09:08:03', '2026-06-13 09:08:03'),
(5, 11, 2, '2026-06-13 09:08:03', '2026-06-13 09:08:03'),
(6, 11, 3, '2026-06-13 09:08:04', '2026-06-13 09:08:04'),
(7, 11, 4, '2026-06-13 09:08:04', '2026-06-13 09:08:04'),
(8, 12, 5, '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(9, 12, 6, '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(10, 12, 7, '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(11, 12, 8, '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(12, 12, 9, '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(13, 12, 10, '2026-06-13 09:09:27', '2026-06-13 09:09:27');

-- --------------------------------------------------------

--
-- Table structure for table `product_reports`
--

CREATE TABLE `product_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `handled_by` bigint UNSIGNED DEFAULT NULL,
  `handled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_reports`
--

INSERT INTO `product_reports` (`id`, `product_id`, `user_id`, `reason`, `description`, `status`, `handled_by`, `handled_at`, `created_at`, `updated_at`) VALUES
(1, 5, 3, 'misleading_description', 'Product image does not match the actual item.', 'ignored', 1, '2026-06-13 13:18:08', '2026-06-11 11:00:25', '2026-06-13 13:18:08'),
(2, 6, 3, 'quality_issue', 'Item quality was below expectations.', 'reviewed', 2, '2026-06-09 11:00:25', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 5, 3, 'Misleading description', 'Photos do not match the actual product color.', 'pending', NULL, NULL, '2026-06-13 10:40:18', '2026-06-13 10:40:18');

-- --------------------------------------------------------

--
-- Table structure for table `product_units`
--

CREATE TABLE `product_units` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `unit_id` bigint UNSIGNED NOT NULL,
  `product_variant_id` bigint UNSIGNED DEFAULT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT NULL,
  `is_in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `factor` int UNSIGNED NOT NULL DEFAULT '1',
  `discount` decimal(10,2) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_units`
--

INSERT INTO `product_units` (`id`, `product_id`, `unit_id`, `product_variant_id`, `sku`, `price`, `stock`, `is_in_stock`, `factor`, `discount`, `discount_type`, `is_default`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 5, NULL, 'APPLE-1KG', '150.00', 104, 1, 1, NULL, NULL, 1, 0, 1, '2026-06-11 11:00:24', '2026-06-13 14:30:39'),
(2, 2, 2, NULL, 'MILK-1L', '60.00', 196, 1, 1, NULL, NULL, 1, 0, 1, '2026-06-11 11:00:24', '2026-06-13 10:36:55'),
(3, 3, 1, NULL, 'BREAD-500G', '15.00', 63, 1, 1, NULL, NULL, 1, 0, 1, '2026-06-11 11:00:24', '2026-06-13 14:30:39'),
(4, 4, 2, NULL, 'JUICE-1L', '70.00', 37, 1, 1, NULL, NULL, 1, 0, 1, '2026-06-11 11:00:24', '2026-06-13 14:30:39'),
(5, 5, 4, NULL, 'CHIPS-200G', '35.00', 140, 1, 1, NULL, NULL, 1, 0, 1, '2026-06-11 11:00:24', '2026-06-13 10:36:55'),
(6, 6, 2, NULL, 'WATER-1.5L', '20.00', 300, 1, 1, '0.00', 'percentage', 1, 0, 1, '2026-06-11 11:00:24', '2026-06-11 11:59:19'),
(7, 7, 2, NULL, 'DETERGENT-1L', '120.00', 15, 1, 1, '0.00', 'percentage', 1, 0, 1, '2026-06-11 11:00:24', '2026-06-13 14:30:39'),
(8, 8, 5, NULL, 'TOMATO-1KG', '45.00', 60, 1, 1, '10.00', 'percentage', 1, 0, 1, '2026-06-11 11:00:24', '2026-06-13 14:30:39'),
(9, 8, 1, NULL, 'TOMATO-1KG-piece', '12.00', NULL, 1, 1, '0.00', 'percentage', 0, 1, 1, '2026-06-11 11:51:30', '2026-06-11 11:51:30'),
(10, 9, 2, 1, 'PRD-0001-lilac-bottle', '150.00', NULL, 1, 3, '5.00', 'percentage', 1, 0, 1, '2026-06-13 08:27:03', '2026-06-13 08:27:03'),
(11, 9, 2, 2, 'PRD-0001-ocean-breeze-bottle', '150.00', NULL, 1, 3, '6.00', 'percentage', 0, 1, 1, '2026-06-13 08:27:03', '2026-06-13 08:27:03'),
(12, 10, 1, NULL, 'PRD-0002-piece', '10.00', NULL, 1, 1, '0.00', 'percentage', 1, 0, 1, '2026-06-13 08:37:20', '2026-06-13 08:37:20'),
(13, 11, 1, NULL, 'PRD-0003-piece', '120.00', NULL, 1, 1, '2.00', 'percentage', 1, 0, 1, '2026-06-13 09:08:04', '2026-06-13 09:08:04'),
(14, 12, 1, 3, 'PRD-0004-m-piece', '2.00', NULL, 1, 1, '0.00', 'percentage', 1, 0, 1, '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(15, 12, 1, 4, 'PRD-0004-l-piece', '1.00', NULL, 1, 1, '0.00', 'percentage', 0, 1, 1, '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(16, 13, 1, NULL, 'ORANGE-1KG-piece', '1.50', 150, 1, 1, NULL, NULL, 1, 0, 1, '2026-06-13 11:44:56', '2026-06-13 11:44:56');

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

CREATE TABLE `product_variants` (
  `id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `sku` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT NULL,
  `is_in_stock` tinyint(1) NOT NULL DEFAULT '1',
  `discount` decimal(10,2) DEFAULT NULL,
  `discount_type` enum('percentage','fixed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variants`
--

INSERT INTO `product_variants` (`id`, `product_id`, `name`, `sku`, `slug`, `price`, `stock`, `is_in_stock`, `discount`, `discount_type`, `thumbnail`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 9, '{\"ar\": \"ليلاك\", \"en\": \"Lilac\"}', 'PRD-0001-lilac', 'prd-0001-lilac', '0.00', NULL, 1, '0.00', 'percentage', NULL, 1, '2026-06-13 08:27:03', '2026-06-13 08:27:03', NULL),
(2, 9, '{\"ar\": \"نسيم البحر\", \"en\": \"Ocean Breeze\"}', 'PRD-0001-ocean-breeze', 'prd-0001-ocean-breeze', '0.00', NULL, 1, '0.00', 'percentage', NULL, 1, '2026-06-13 08:27:03', '2026-06-13 08:27:03', NULL),
(3, 12, '{\"ar\": \"وسط\", \"en\": \"Medium\"}', 'PRD-0004-m', 'prd-0004-m', '0.00', NULL, 1, '0.00', 'percentage', NULL, 1, '2026-06-13 09:09:27', '2026-06-13 09:09:27', NULL),
(4, 12, '{\"ar\": \"كبير\", \"en\": \"Large\"}', 'PRD-0004-l', 'prd-0004-l', '0.00', NULL, 1, '0.00', 'percentage', NULL, 1, '2026-06-13 09:09:27', '2026-06-13 09:09:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_variant_values`
--

CREATE TABLE `product_variant_values` (
  `id` bigint UNSIGNED NOT NULL,
  `value` json NOT NULL,
  `product_variant_id` bigint UNSIGNED NOT NULL,
  `variant_option_id` bigint UNSIGNED NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_variant_values`
--

INSERT INTO `product_variant_values` (`id`, `value`, `product_variant_id`, `variant_option_id`, `thumbnail`, `created_at`, `updated_at`) VALUES
(1, '{\"ar\": \"ليلاك\", \"en\": \"Lilac\"}', 1, 6, 'products/variants/FafVtz7yQVXjSDFb5giYFUoU5BUnwysCWKsiyMt5.jpg', '2026-06-13 08:27:03', '2026-06-13 08:27:03'),
(2, '{\"ar\": \"نسيم البحر\", \"en\": \"Ocean Breeze\"}', 2, 7, 'products/variants/XWxuTzwWR8GT15t6BQvsFjjcqKJeuXZjNQxFgMYU.avif', '2026-06-13 08:27:03', '2026-06-13 08:27:03'),
(3, '{\"ar\": \"وسط\", \"en\": \"Medium\"}', 3, 2, 'products/variants/mvt5pqB68SuGROCH1IRz8VHb5SW4bPiI3ZIIrCa1.webp', '2026-06-13 09:09:27', '2026-06-13 09:09:27'),
(4, '{\"ar\": \"كبير\", \"en\": \"Large\"}', 4, 3, NULL, '2026-06-13 09:09:27', '2026-06-13 09:09:27');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(2, 'admin', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(3, 'user', 'web', '2026-06-11 11:00:24', '2026-06-11 11:00:24');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('1Wo62fxZ44Q34AXMfSRcJiI00PvE4WiiRB7fUF5m', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiJNa083V0wzV0lQWk1kd3Y4U05kTkR3OUZYNUZTV2dxblNFZ0h1YzFiIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2dhaGV6LnRlc3RcL2FkbWluIiwicm91dGUiOiJ2MS5hZG1pbi5kYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1781420374),
('5kATFdkymL9VCicyX1bUXF0zEyE5571MrzE2Rq3q', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiI3SjhuOGcxYnJidHJ2UUVDQkpDbkl1U2lYWFZNWFFnS1JBTElYNjIwIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2dhaGV6LnRlc3RcL2FkbWluXC9jdXN0b21lcnNcLzMiLCJyb3V0ZSI6InYxLmFkbWluLmN1c3RvbWVycy5zaG93In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjEsImxvY2FsZSI6ImVuIn0=', 1781358004),
('TIO1N7LqKpmBGBaorJzJFV25XTAyeJHxAr9JJMNv', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36 Edg/149.0.0.0', 'eyJfdG9rZW4iOiJvVlFNdlJDTXBONVJDZktVNU1kWm1zQzRCZVJuQXE0MTZCVklMWHdRIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJ1cmwiOltdLCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvZ2FoZXoudGVzdFwvZG9jc1wvMS4wXC9vdmVydmlldyIsInJvdXRlIjoibGFyZWNpcGUuc2hvdyJ9LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=', 1781362734);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint UNSIGNED NOT NULL,
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `type` enum('string','number','boolean','image') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `created_at`, `updated_at`) VALUES
(1, 'shipping_price_per_km', '5', 'number', '2026-06-11 11:00:22', '2026-06-13 14:09:57'),
(2, 'cashback_percentage', '5', 'number', '2026-06-11 11:00:23', '2026-06-11 11:00:24'),
(3, 'point_to_value', '10', 'number', '2026-06-11 11:00:23', '2026-06-11 11:00:24'),
(4, 'app_name', 'Gahez Akid', 'string', '2026-06-11 11:00:24', '2026-06-13 13:58:24'),
(5, 'currency', 'EGP', 'string', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(6, 'cart_min_line_count', '5', 'number', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(7, 'cart_min_subtotal', '2000', 'number', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(8, 'fast_shipping_fee', '100', 'number', '2026-06-11 11:00:24', '2026-06-11 11:00:24'),
(9, 'app_logo', 'settings/np8q9l5AhcGhC1LG8JG8ARoOLWasg7C35BHZLofF.png', 'image', '2026-06-11 11:01:02', '2026-06-11 11:01:02'),
(10, 'standard_shipping_fee', '60', 'number', '2026-06-13 14:07:48', '2026-06-13 14:29:57');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` bigint UNSIGNED NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'home',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `image`, `type`, `created_at`, `updated_at`) VALUES
(1, 'sliders/bpkY2yV2bU0aFm9WfTIR4R5ccScZ8TOTL21vE3UB.webp', 'home', '2026-06-13 09:17:05', '2026-06-13 09:21:25');

-- --------------------------------------------------------

--
-- Table structure for table `supports`
--

CREATE TABLE `supports` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `assigned_admin_id` bigint UNSIGNED DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_message_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supports`
--

INSERT INTO `supports` (`id`, `user_id`, `assigned_admin_id`, `status`, `subject`, `last_message_at`, `closed_at`, `created_at`, `updated_at`) VALUES
(1, 3, 2, 'open', 'Late delivery question', '2026-06-13 12:14:55', NULL, '2026-06-11 11:00:25', '2026-06-13 12:14:55'),
(2, 3, 2, 'closed', 'Payment issue', '2026-06-08 11:00:25', '2026-06-08 11:00:25', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 3, 1, 'open', 'Order Problem', '2026-06-13 12:24:44', NULL, '2026-06-13 12:14:34', '2026-06-13 12:24:44');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `support_id` bigint UNSIGNED NOT NULL,
  `sender_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sender_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `support_messages`
--

INSERT INTO `support_messages` (`id`, `support_id`, `sender_type`, `sender_id`, `message`, `attachments`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'user', 3, 'Hi, my order is delayed. Can you check?', NULL, NULL, '2026-06-11 09:00:25', '2026-06-11 09:00:25'),
(2, 1, 'admin', 2, 'We are checking with the branch now.', NULL, '2026-06-11 10:00:25', '2026-06-11 10:00:25', '2026-06-11 10:00:25'),
(3, 1, 'user', 3, 'Thanks, waiting for an update.', NULL, NULL, '2026-06-11 10:50:25', '2026-06-11 10:50:25'),
(4, 2, 'user', 3, 'I was charged twice for one order.', NULL, NULL, '2026-06-07 11:00:25', '2026-06-07 11:00:25'),
(5, 2, 'admin', 2, 'Refund has been processed. Chat closed.', NULL, '2026-06-08 11:00:25', '2026-06-08 11:00:25', '2026-06-08 11:00:25'),
(6, 3, 'user', 3, 'My order is delayed', NULL, '2026-06-13 12:20:59', '2026-06-13 12:14:34', '2026-06-13 12:20:59'),
(7, 1, 'user', 3, 'please replay', NULL, NULL, '2026-06-13 12:14:55', '2026-06-13 12:14:55'),
(8, 3, 'user', 3, 'please replay', NULL, '2026-06-13 12:20:59', '2026-06-13 12:15:16', '2026-06-13 12:20:59'),
(9, 3, 'user', 3, 'please', NULL, '2026-06-13 12:20:59', '2026-06-13 12:15:32', '2026-06-13 12:20:59'),
(10, 3, 'admin', 1, 'ok', '[\"support/messages/HdUHCZgg7sPG0aBSCZYuQQKir3yfSQKlVp5NQuYJ.webp\"]', '2026-06-13 12:21:03', '2026-06-13 12:15:57', '2026-06-13 12:21:03'),
(11, 3, 'admin', 1, 'delivered?', NULL, '2026-06-13 12:23:52', '2026-06-13 12:21:54', '2026-06-13 12:23:52'),
(12, 3, 'user', 3, 'yes, thanks', NULL, '2026-06-13 12:24:47', '2026-06-13 12:24:44', '2026-06-13 12:24:47');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'complaint',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','resolved','closed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `type`, `subject`, `description`, `status`, `attachments`, `created_at`, `updated_at`) VALUES
(1, 3, 'complaint', 'Missing item in order', 'One milk bottle was missing from my last delivery.', 'pending', NULL, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 3, 'complaint', 'App login issue', 'Could not log in with phone number.', 'resolved', NULL, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 3, 'complaint', 'Test 4 Subject', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magni earum aliquam, quisquam blanditiis iusto nam fuga deserunt quo. Quam nobis excepturi ipsa repellat doloremque illum laboriosam reprehenderit iure quod soluta.', 'resolved', '[\"tickets/xhfZmNiZXFkmnFG4T1tJhqRr12NDNiMyP8h844kR.webp\"]', '2026-06-13 09:33:23', '2026-06-13 09:36:48'),
(4, 3, 'recommendation', 'Product enhancement', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magni earum aliquam, quisquam blanditiis iusto nam fuga deserunt quo. Quam nobis excepturi ipsa repellat doloremque illum laboriosam reprehenderit iure quod soluta.', 'pending', '[\"tickets/2Pl7jO55b5GSSFlzN1slaJ01VChYcsgEMXweGBJ6.webp\"]', '2026-06-13 14:49:57', '2026-06-13 14:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` bigint UNSIGNED NOT NULL,
  `ticket_id` bigint UNSIGNED NOT NULL,
  `sender_type` enum('user','vendor','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `sender_id` bigint UNSIGNED NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `attachments` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `sender_type`, `sender_id`, `message`, `attachments`, `created_at`, `updated_at`) VALUES
(1, 1, 'user', 3, 'Please send the missing milk or refund the item.', NULL, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 2, 'user', 3, 'Login fails after password reset.', NULL, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 2, 'admin', 2, 'Please try again after clearing app cache. Issue resolved.', NULL, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(4, 3, 'user', 3, 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magni earum aliquam, quisquam blanditiis iusto nam fuga deserunt quo. Quam nobis excepturi ipsa repellat doloremque illum laboriosam reprehenderit iure quod soluta.', '[\"tickets/xhfZmNiZXFkmnFG4T1tJhqRr12NDNiMyP8h844kR.webp\"]', '2026-06-13 09:33:23', '2026-06-13 09:33:23'),
(5, 3, 'admin', 1, 'ok working on it', NULL, '2026-06-13 09:33:50', '2026-06-13 09:33:50'),
(6, 3, 'user', 3, 'NOOOO000O', NULL, '2026-06-13 09:34:50', '2026-06-13 09:34:50'),
(7, 4, 'user', 3, 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Magni earum aliquam, quisquam blanditiis iusto nam fuga deserunt quo. Quam nobis excepturi ipsa repellat doloremque illum laboriosam reprehenderit iure quod soluta.', '[\"tickets/2Pl7jO55b5GSSFlzN1slaJ01VChYcsgEMXweGBJ6.webp\"]', '2026-06-13 14:49:57', '2026-06-13 14:49:57');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`id`, `name`, `code`, `is_active`, `created_at`, `updated_at`) VALUES
(1, '{\"ar\": \"قطعة\", \"en\": \"piece\"}', 'piece', 1, '2026-06-11 11:00:23', '2026-06-11 11:00:23'),
(2, '{\"ar\": \"زجاجة\", \"en\": \"bottle\"}', 'bottle', 1, '2026-06-11 11:00:23', '2026-06-11 11:00:23'),
(3, '{\"ar\": \"كرتونة\", \"en\": \"carton\"}', 'carton', 1, '2026-06-11 11:00:23', '2026-06-11 11:00:23'),
(4, '{\"ar\": \"صندوق\", \"en\": \"box\"}', 'box', 1, '2026-06-11 11:00:23', '2026-06-11 11:00:23'),
(5, '{\"ar\": \"كجم\", \"en\": \"kg\"}', 'kg', 1, '2026-06-11 11:00:23', '2026-06-11 11:00:23'),
(6, '{\"ar\": \"لتر\", \"en\": \"liter\"}', 'liter', 1, '2026-06-13 11:41:46', '2026-06-13 11:41:46');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `phone_verified_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','user') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wallet` decimal(10,2) NOT NULL DEFAULT '0.00',
  `points` int UNSIGNED NOT NULL DEFAULT '0',
  `referral_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `referred_by_id` bigint UNSIGNED DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `birthdate`, `email_verified_at`, `phone_verified_at`, `role`, `is_active`, `is_verified`, `password`, `image`, `wallet`, `points`, `referral_code`, `referred_by_id`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'super-admin@gmail.com', '+201022039241', '1984-01-09', '2026-06-13 12:49:05', NULL, 'admin', 1, 1, '$2y$12$sEhHjXHoB9cLb1av.DpdNOLTPbrfBxKjiYL9THwP/1NHtwc3nkyyi', 'avatars/pdJEGjSKDjcyfhhnb1GcYYiC4C9iTr92Vs5HiYjO.webp', '0.00', 0, NULL, NULL, NULL, '2026-06-11 11:00:25', '2026-06-13 13:07:12', NULL),
(2, 'Admin', 'admin@gmail.com', '+201022039240', '1990-01-20', '2026-06-13 12:49:06', NULL, 'admin', 1, 1, '$2y$12$u93ECsrZnrjXElyw54Ei1.0nlYtS5kW8s5e.AKerlcLKAFriUbomy', NULL, '0.00', 0, NULL, NULL, NULL, '2026-06-11 11:00:25', '2026-06-13 13:06:46', NULL),
(3, 'Customer1', 'customer1@gmail.com', '+201000111111', '1992-05-20', '2026-06-13 12:49:06', NULL, 'user', 1, 1, '$2y$12$dkD6wkEkywtbfQQNjGU/m.VxjVfuuajELRiNQt4fUW7n4bxGFLib6', NULL, '2245.00', 162, NULL, NULL, NULL, '2026-06-11 11:00:25', '2026-06-13 14:30:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `variants`
--

CREATE TABLE `variants` (
  `id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `is_required` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variants`
--

INSERT INTO `variants` (`id`, `name`, `is_required`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '{\"ar\": \"المقاس\", \"en\": \"Size\"}', 0, 1, '2026-06-11 11:00:24', '2026-06-11 12:15:26', NULL),
(2, '{\"ar\": \"اللون\", \"en\": \"Color\"}', 0, 1, '2026-06-11 11:00:24', '2026-06-13 09:10:05', '2026-06-13 09:10:05'),
(3, '{\"ar\": \"رائحة\", \"en\": \"Scent\"}', 0, 1, '2026-06-13 07:56:43', '2026-06-13 07:56:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `variant_options`
--

CREATE TABLE `variant_options` (
  `id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED NOT NULL,
  `name` json NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `variant_options`
--

INSERT INTO `variant_options` (`id`, `variant_id`, `name`, `code`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '{\"ar\": \"صغير\", \"en\": \"Small\"}', 'S', '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(2, 1, '{\"ar\": \"وسط\", \"en\": \"Medium\"}', 'M', '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(3, 1, '{\"ar\": \"كبير\", \"en\": \"Large\"}', 'L', '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(4, 2, '{\"ar\": \"أسود\", \"en\": \"Black\"}', 'BLK', '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(5, 2, '{\"ar\": \"أبيض\", \"en\": \"White\"}', 'WHT', '2026-06-11 11:00:24', '2026-06-11 11:00:24', NULL),
(6, 3, '{\"ar\": \"ليلاك\", \"en\": \"Lilac\"}', 'lilac', '2026-06-13 07:57:14', '2026-06-13 07:57:14', NULL),
(7, 3, '{\"ar\": \"نسيم البحر\", \"en\": \"Ocean Breeze\"}', 'ocean-breeze', '2026-06-13 07:58:42', '2026-06-13 07:58:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `verifications`
--

CREATE TABLE `verifications` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wallet_transactions`
--

CREATE TABLE `wallet_transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `type` enum('addition','subtraction') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `balance_after` decimal(10,2) NOT NULL,
  `notes` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wallet_transactions`
--

INSERT INTO `wallet_transactions` (`id`, `user_id`, `type`, `amount`, `balance_after`, `notes`, `created_at`, `updated_at`) VALUES
(1, 3, 'addition', '50.00', '75.00', 'Seeded daily goal reward', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 3, 'addition', '500.00', '575.00', 'Seeded weekly goal reward', '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 3, 'addition', '50.00', '625.00', 'Goal reward: Daily Goal (orders 2,425.00 L.E.)', '2026-06-13 10:13:10', '2026-06-13 10:13:10'),
(4, 3, 'addition', '500.00', '1125.00', 'Goal reward: Weekly Goal (orders 6,647.50 L.E.)', '2026-06-13 10:36:55', '2026-06-13 10:36:55'),
(5, 3, 'addition', '120.00', '1245.00', 'Cashback wallet credit for order #9 (12 pts × 10.00 L.E.)', '2026-06-13 14:30:39', '2026-06-13 14:30:39'),
(6, 3, 'addition', '1000.00', '2245.00', 'Goal reward: Monthly Goal (orders 11,542.50 L.E.)', '2026-06-13 14:30:39', '2026-06-13 14:30:39');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `product_id`, `created_at`, `updated_at`) VALUES
(1, 3, 1, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(2, 3, 2, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(3, 3, 3, '2026-06-11 11:00:25', '2026-06-11 11:00:25'),
(4, 3, 4, '2026-06-11 11:00:25', '2026-06-11 11:00:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `addresses_user_default_index` (`user_id`,`is_default`),
  ADD KEY `addresses_user_active_index` (`user_id`,`is_active`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `branches_is_active_index` (`is_active`);

--
-- Indexes for table `brands`
--
ALTER TABLE `brands`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_items_product_id_foreign` (`product_id`),
  ADD KEY `cart_items_variant_id_foreign` (`variant_id`),
  ADD KEY `cart_items_user_product_index` (`user_id`,`product_id`),
  ADD KEY `cart_items_user_product_variant_index` (`user_id`,`product_id`,`variant_id`),
  ADD KEY `cart_items_product_unit_id_foreign` (`product_unit_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `categories_parent_id_foreign` (`parent_id`),
  ADD KEY `categories_status_index` (`is_active`,`is_featured`),
  ADD KEY `categories_created_at_index` (`created_at`);

--
-- Indexes for table `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `coupons_code_unique` (`code`),
  ADD KEY `coupons_active_end_date_index` (`is_active`,`end_date`);

--
-- Indexes for table `data_transfer_batches`
--
ALTER TABLE `data_transfer_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data_transfer_batches_user_id_foreign` (`user_id`),
  ADD KEY `data_transfer_batches_entity_direction_status_index` (`entity`,`direction`,`status`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `goals_is_active_start_date_end_date_index` (`is_active`,`start_date`,`end_date`),
  ADD KEY `goals_period_type_index` (`period_type`);

--
-- Indexes for table `goal_achievements`
--
ALTER TABLE `goal_achievements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `goal_achievements_goal_id_user_id_period_start_unique` (`goal_id`,`user_id`,`period_start`),
  ADD KEY `goal_achievements_user_id_foreign` (`user_id`);

--
-- Indexes for table `import_row_logs`
--
ALTER TABLE `import_row_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `import_row_logs_data_transfer_batch_id_row_number_index` (`data_transfer_batch_id`,`row_number`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`);

--
-- Indexes for table `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `offers_offerable_id_offerable_type_index` (`offerable_id`,`offerable_type`),
  ADD KEY `offers_active_dates_index` (`is_active`,`start_date`,`end_date`);

--
-- Indexes for table `offer_reward_products`
--
ALTER TABLE `offer_reward_products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `offer_reward_products_offer_id_product_id_unique` (`offer_id`,`product_id`),
  ADD KEY `offer_reward_products_product_id_foreign` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_user_status_index` (`user_id`,`status`),
  ADD KEY `orders_status_payment_index` (`status`,`payment_status`),
  ADD KEY `orders_payment_created_index` (`payment_status`,`created_at`),
  ADD KEY `orders_created_at_index` (`created_at`),
  ADD KEY `orders_total_index` (`total`),
  ADD KEY `orders_payment_method_index` (`payment_method`),
  ADD KEY `orders_refund_status_index` (`refund_status`),
  ADD KEY `orders_coupon_id_foreign` (`coupon_id`),
  ADD KEY `orders_address_id_foreign` (`address_id`),
  ADD KEY `orders_branch_id_foreign` (`branch_id`),
  ADD KEY `orders_gift_offer_id_foreign` (`gift_offer_id`),
  ADD KEY `orders_gift_product_id_foreign` (`gift_product_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`),
  ADD KEY `order_items_variant_id_foreign` (`variant_id`),
  ADD KEY `order_items_product_id_foreign` (`product_id`),
  ADD KEY `order_items_product_unit_id_foreign` (`product_unit_id`);

--
-- Indexes for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_logs_user_id_foreign` (`user_id`),
  ADD KEY `order_logs_order_created_index` (`order_id`,`created_at`),
  ADD KEY `order_logs_type_index` (`type`);

--
-- Indexes for table `order_ratings`
--
ALTER TABLE `order_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_ratings_order_id_foreign` (`order_id`),
  ADD KEY `order_ratings_user_id_foreign` (`user_id`);

--
-- Indexes for table `order_refund_requests`
--
ALTER TABLE `order_refund_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_refund_requests_user_id_foreign` (`user_id`),
  ADD KEY `order_refund_requests_processed_by_foreign` (`processed_by`),
  ADD KEY `order_refund_requests_order_id_status_index` (`order_id`,`status`),
  ADD KEY `refund_requests_status_index` (`status`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `point_transactions_user_id_foreign` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `products_sku_unique` (`sku`),
  ADD UNIQUE KEY `products_slug_unique` (`slug`),
  ADD KEY `products_brand_id_foreign` (`brand_id`),
  ADD KEY `products_status_index` (`is_active`,`is_approved`,`is_featured`),
  ADD KEY `products_type_index` (`type`),
  ADD KEY `products_created_at_index` (`created_at`),
  ADD KEY `products_is_new_index` (`is_new`),
  ADD KEY `products_is_bookable_index` (`is_bookable`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD UNIQUE KEY `product_categories_product_id_category_id_unique` (`product_id`,`category_id`),
  ADD KEY `product_categories_category_id_index` (`category_id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_images_imageable_id_imageable_type_index` (`imageable_id`,`imageable_type`);

--
-- Indexes for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_ratings_product_id_user_id_unique` (`product_id`,`user_id`),
  ADD KEY `product_ratings_user_id_foreign` (`user_id`),
  ADD KEY `product_ratings_product_visible_index` (`product_id`,`is_visible`),
  ADD KEY `product_ratings_rating_index` (`rating`);

--
-- Indexes for table `product_relations`
--
ALTER TABLE `product_relations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_relations_product_id_foreign` (`product_id`),
  ADD KEY `product_relations_related_product_id_foreign` (`related_product_id`);

--
-- Indexes for table `product_reports`
--
ALTER TABLE `product_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_reports_user_id_foreign` (`user_id`),
  ADD KEY `product_reports_handled_by_foreign` (`handled_by`),
  ADD KEY `product_reports_status_index` (`status`),
  ADD KEY `product_reports_product_status_index` (`product_id`,`status`);

--
-- Indexes for table `product_units`
--
ALTER TABLE `product_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_units_product_unit_variant_unique` (`product_id`,`unit_id`,`product_variant_id`),
  ADD KEY `product_units_unit_id_foreign` (`unit_id`),
  ADD KEY `product_units_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `product_units_product_id_index` (`product_id`);

--
-- Indexes for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_variants_sku_unique` (`sku`),
  ADD UNIQUE KEY `product_variants_slug_unique` (`slug`),
  ADD KEY `product_variants_product_active_index` (`product_id`,`is_active`);

--
-- Indexes for table `product_variant_values`
--
ALTER TABLE `product_variant_values`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_variant_values_product_variant_id_foreign` (`product_variant_id`),
  ADD KEY `product_variant_values_variant_option_id_foreign` (`variant_option_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `settings_key_unique` (`key`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supports`
--
ALTER TABLE `supports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supports_user_id_index` (`user_id`),
  ADD KEY `supports_assigned_admin_id_index` (`assigned_admin_id`),
  ADD KEY `supports_status_index` (`status`),
  ADD KEY `supports_last_message_at_index` (`last_message_at`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `support_messages_sender_id_foreign` (`sender_id`),
  ADD KEY `support_messages_support_id_created_at_index` (`support_id`,`created_at`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tickets_user_status_index` (`user_id`,`status`),
  ADD KEY `tickets_status_index` (`status`),
  ADD KEY `tickets_created_at_index` (`created_at`),
  ADD KEY `tickets_type_index` (`type`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_messages_sender_id_foreign` (`sender_id`),
  ADD KEY `ticket_messages_ticket_created_index` (`ticket_id`,`created_at`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `units_code_unique` (`code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_referred_by_id_foreign` (`referred_by_id`),
  ADD KEY `users_role_status_index` (`role`,`is_active`),
  ADD KEY `users_created_at_index` (`created_at`);

--
-- Indexes for table `variants`
--
ALTER TABLE `variants`
  ADD PRIMARY KEY (`id`),
  ADD KEY `variants_status_index` (`is_active`,`is_required`);

--
-- Indexes for table `variant_options`
--
ALTER TABLE `variant_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `variant_options_code_unique` (`code`),
  ADD KEY `variant_options_variant_id_foreign` (`variant_id`);

--
-- Indexes for table `verifications`
--
ALTER TABLE `verifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `verifications_user_id_foreign` (`user_id`);

--
-- Indexes for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `wallet_transactions_user_created_index` (`user_id`,`created_at`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `wishlists_user_id_product_id_unique` (`user_id`,`product_id`),
  ADD KEY `wishlists_product_id_foreign` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `brands`
--
ALTER TABLE `brands`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `data_transfer_batches`
--
ALTER TABLE `data_transfer_batches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `goal_achievements`
--
ALTER TABLE `goal_achievements`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `import_row_logs`
--
ALTER TABLE `import_row_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=75;

--
-- AUTO_INCREMENT for table `offers`
--
ALTER TABLE `offers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `offer_reward_products`
--
ALTER TABLE `offer_reward_products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `order_logs`
--
ALTER TABLE `order_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `order_ratings`
--
ALTER TABLE `order_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_refund_requests`
--
ALTER TABLE `order_refund_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `point_transactions`
--
ALTER TABLE `point_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_ratings`
--
ALTER TABLE `product_ratings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `product_relations`
--
ALTER TABLE `product_relations`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product_reports`
--
ALTER TABLE `product_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `product_units`
--
ALTER TABLE `product_units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `product_variants`
--
ALTER TABLE `product_variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_variant_values`
--
ALTER TABLE `product_variant_values`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supports`
--
ALTER TABLE `supports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `variants`
--
ALTER TABLE `variants`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `variant_options`
--
ALTER TABLE `variant_options`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `verifications`
--
ALTER TABLE `verifications`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `cart_items_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `data_transfer_batches`
--
ALTER TABLE `data_transfer_batches`
  ADD CONSTRAINT `data_transfer_batches_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `goal_achievements`
--
ALTER TABLE `goal_achievements`
  ADD CONSTRAINT `goal_achievements_goal_id_foreign` FOREIGN KEY (`goal_id`) REFERENCES `goals` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `goal_achievements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `import_row_logs`
--
ALTER TABLE `import_row_logs`
  ADD CONSTRAINT `import_row_logs_data_transfer_batch_id_foreign` FOREIGN KEY (`data_transfer_batch_id`) REFERENCES `data_transfer_batches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offer_reward_products`
--
ALTER TABLE `offer_reward_products`
  ADD CONSTRAINT `offer_reward_products_offer_id_foreign` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `offer_reward_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_address_id_foreign` FOREIGN KEY (`address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_gift_offer_id_foreign` FOREIGN KEY (`gift_offer_id`) REFERENCES `offers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_gift_product_id_foreign` FOREIGN KEY (`gift_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `order_items_product_unit_id_foreign` FOREIGN KEY (`product_unit_id`) REFERENCES `product_units` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_logs`
--
ALTER TABLE `order_logs`
  ADD CONSTRAINT `order_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `order_ratings`
--
ALTER TABLE `order_ratings`
  ADD CONSTRAINT `order_ratings_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_refund_requests`
--
ALTER TABLE `order_refund_requests`
  ADD CONSTRAINT `order_refund_requests_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_refund_requests_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `order_refund_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `point_transactions`
--
ALTER TABLE `point_transactions`
  ADD CONSTRAINT `point_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_ratings`
--
ALTER TABLE `product_ratings`
  ADD CONSTRAINT `product_ratings_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_ratings_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_relations`
--
ALTER TABLE `product_relations`
  ADD CONSTRAINT `product_relations_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_relations_related_product_id_foreign` FOREIGN KEY (`related_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_reports`
--
ALTER TABLE `product_reports`
  ADD CONSTRAINT `product_reports_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_reports_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_units`
--
ALTER TABLE `product_units`
  ADD CONSTRAINT `product_units_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_units_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `product_units_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_variant_values`
--
ALTER TABLE `product_variant_values`
  ADD CONSTRAINT `product_variant_values_product_variant_id_foreign` FOREIGN KEY (`product_variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `product_variant_values_variant_option_id_foreign` FOREIGN KEY (`variant_option_id`) REFERENCES `variant_options` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supports`
--
ALTER TABLE `supports`
  ADD CONSTRAINT `supports_assigned_admin_id_foreign` FOREIGN KEY (`assigned_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `supports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `support_messages_support_id_foreign` FOREIGN KEY (`support_id`) REFERENCES `supports` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `ticket_messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_messages_ticket_id_foreign` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_referred_by_id_foreign` FOREIGN KEY (`referred_by_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `variant_options`
--
ALTER TABLE `variant_options`
  ADD CONSTRAINT `variant_options_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `verifications`
--
ALTER TABLE `verifications`
  ADD CONSTRAINT `verifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallet_transactions`
--
ALTER TABLE `wallet_transactions`
  ADD CONSTRAINT `wallet_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
