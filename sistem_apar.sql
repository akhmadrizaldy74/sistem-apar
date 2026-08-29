-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 26, 2026 at 04:23 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sistem_apar`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint UNSIGNED DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `batch_uuid` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `properties`, `batch_uuid`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, NULL, 'default', 'Membuat Produk #1', 'App\\Models\\Produk', 1, 'created', '{\"attributes\": {\"id\": 1, \"nama\": \"APAR FIREFIX Powder 1 kg\", \"stok\": 50, \"harga\": 150000, \"merek\": \"FIREFIX\", \"kapasitas\": \"1 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(2, NULL, 'default', 'Membuat Produk #2', 'App\\Models\\Produk', 2, 'created', '{\"attributes\": {\"id\": 2, \"nama\": \"APAR GuardALL Powder 1 kg\", \"stok\": 50, \"harga\": 162000, \"merek\": \"GuardALL\", \"kapasitas\": \"1 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(3, NULL, 'default', 'Membuat Produk #3', 'App\\Models\\Produk', 3, 'created', '{\"attributes\": {\"id\": 3, \"nama\": \"APAR TONATA Powder 1 kg\", \"stok\": 50, \"harga\": 172500, \"merek\": \"TONATA\", \"kapasitas\": \"1 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(4, NULL, 'default', 'Membuat Produk #4', 'App\\Models\\Produk', 4, 'created', '{\"attributes\": {\"id\": 4, \"nama\": \"APAR FIREFIX Powder 2 kg\", \"stok\": 50, \"harga\": 200000, \"merek\": \"FIREFIX\", \"kapasitas\": \"2 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(5, NULL, 'default', 'Membuat Produk #5', 'App\\Models\\Produk', 5, 'created', '{\"attributes\": {\"id\": 5, \"nama\": \"APAR GuardALL Powder 2 kg\", \"stok\": 50, \"harga\": 216000, \"merek\": \"GuardALL\", \"kapasitas\": \"2 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(6, NULL, 'default', 'Membuat Produk #6', 'App\\Models\\Produk', 6, 'created', '{\"attributes\": {\"id\": 6, \"nama\": \"APAR TONATA Powder 2 kg\", \"stok\": 50, \"harga\": 230000, \"merek\": \"TONATA\", \"kapasitas\": \"2 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(7, NULL, 'default', 'Membuat Produk #7', 'App\\Models\\Produk', 7, 'created', '{\"attributes\": {\"id\": 7, \"nama\": \"APAR FIREFIX Powder 3 kg\", \"stok\": 50, \"harga\": 300000, \"merek\": \"FIREFIX\", \"kapasitas\": \"3 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(8, NULL, 'default', 'Membuat Produk #8', 'App\\Models\\Produk', 8, 'created', '{\"attributes\": {\"id\": 8, \"nama\": \"APAR GuardALL Powder 3 kg\", \"stok\": 50, \"harga\": 324000, \"merek\": \"GuardALL\", \"kapasitas\": \"3 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(9, NULL, 'default', 'Membuat Produk #9', 'App\\Models\\Produk', 9, 'created', '{\"attributes\": {\"id\": 9, \"nama\": \"APAR TONATA Powder 3 kg\", \"stok\": 50, \"harga\": 345000, \"merek\": \"TONATA\", \"kapasitas\": \"3 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(10, NULL, 'default', 'Membuat Produk #10', 'App\\Models\\Produk', 10, 'created', '{\"attributes\": {\"id\": 10, \"nama\": \"APAR FIREFIX Powder 4 kg\", \"stok\": 50, \"harga\": 400000, \"merek\": \"FIREFIX\", \"kapasitas\": \"4 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(11, NULL, 'default', 'Membuat Produk #11', 'App\\Models\\Produk', 11, 'created', '{\"attributes\": {\"id\": 11, \"nama\": \"APAR GuardALL Powder 4 kg\", \"stok\": 50, \"harga\": 432000, \"merek\": \"GuardALL\", \"kapasitas\": \"4 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(12, NULL, 'default', 'Membuat Produk #12', 'App\\Models\\Produk', 12, 'created', '{\"attributes\": {\"id\": 12, \"nama\": \"APAR TONATA Powder 4 kg\", \"stok\": 50, \"harga\": 460000, \"merek\": \"TONATA\", \"kapasitas\": \"4 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(13, NULL, 'default', 'Membuat Produk #13', 'App\\Models\\Produk', 13, 'created', '{\"attributes\": {\"id\": 13, \"nama\": \"APAR FIREFIX Powder 6 kg\", \"stok\": 50, \"harga\": 550000, \"merek\": \"FIREFIX\", \"kapasitas\": \"6 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(14, NULL, 'default', 'Membuat Produk #14', 'App\\Models\\Produk', 14, 'created', '{\"attributes\": {\"id\": 14, \"nama\": \"APAR GuardALL Powder 6 kg\", \"stok\": 50, \"harga\": 594000, \"merek\": \"GuardALL\", \"kapasitas\": \"6 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(15, NULL, 'default', 'Membuat Produk #15', 'App\\Models\\Produk', 15, 'created', '{\"attributes\": {\"id\": 15, \"nama\": \"APAR TONATA Powder 6 kg\", \"stok\": 50, \"harga\": 632500, \"merek\": \"TONATA\", \"kapasitas\": \"6 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(16, NULL, 'default', 'Membuat Produk #16', 'App\\Models\\Produk', 16, 'created', '{\"attributes\": {\"id\": 16, \"nama\": \"APAR FIREFIX Powder 9 kg\", \"stok\": 50, \"harga\": 750000, \"merek\": \"FIREFIX\", \"kapasitas\": \"9 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(17, NULL, 'default', 'Membuat Produk #17', 'App\\Models\\Produk', 17, 'created', '{\"attributes\": {\"id\": 17, \"nama\": \"APAR GuardALL Powder 9 kg\", \"stok\": 50, \"harga\": 810000, \"merek\": \"GuardALL\", \"kapasitas\": \"9 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(18, NULL, 'default', 'Membuat Produk #18', 'App\\Models\\Produk', 18, 'created', '{\"attributes\": {\"id\": 18, \"nama\": \"APAR TONATA Powder 9 kg\", \"stok\": 50, \"harga\": 862500, \"merek\": \"TONATA\", \"kapasitas\": \"9 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Perkantoran, rumah, kendaraan, gudang\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 1}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(19, NULL, 'default', 'Membuat Produk #19', 'App\\Models\\Produk', 19, 'created', '{\"attributes\": {\"id\": 19, \"nama\": \"APAR FIREFIX CO2 2 kg\", \"stok\": 50, \"harga\": 450000, \"merek\": \"FIREFIX\", \"kapasitas\": \"2 kg\", \"created_at\": \"2026-07-22 21:41:02\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:02\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(20, NULL, 'default', 'Membuat Produk #20', 'App\\Models\\Produk', 20, 'created', '{\"attributes\": {\"id\": 20, \"nama\": \"APAR GuardALL CO2 2 kg\", \"stok\": 50, \"harga\": 486000, \"merek\": \"GuardALL\", \"kapasitas\": \"2 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(21, NULL, 'default', 'Membuat Produk #21', 'App\\Models\\Produk', 21, 'created', '{\"attributes\": {\"id\": 21, \"nama\": \"APAR TONATA CO2 2 kg\", \"stok\": 50, \"harga\": 517500, \"merek\": \"TONATA\", \"kapasitas\": \"2 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(22, NULL, 'default', 'Membuat Produk #22', 'App\\Models\\Produk', 22, 'created', '{\"attributes\": {\"id\": 22, \"nama\": \"APAR FIREFIX CO2 3 kg\", \"stok\": 50, \"harga\": 550000, \"merek\": \"FIREFIX\", \"kapasitas\": \"3 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(23, NULL, 'default', 'Membuat Produk #23', 'App\\Models\\Produk', 23, 'created', '{\"attributes\": {\"id\": 23, \"nama\": \"APAR GuardALL CO2 3 kg\", \"stok\": 50, \"harga\": 594000, \"merek\": \"GuardALL\", \"kapasitas\": \"3 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(24, NULL, 'default', 'Membuat Produk #24', 'App\\Models\\Produk', 24, 'created', '{\"attributes\": {\"id\": 24, \"nama\": \"APAR TONATA CO2 3 kg\", \"stok\": 50, \"harga\": 632500, \"merek\": \"TONATA\", \"kapasitas\": \"3 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(25, NULL, 'default', 'Membuat Produk #25', 'App\\Models\\Produk', 25, 'created', '{\"attributes\": {\"id\": 25, \"nama\": \"APAR FIREFIX CO2 5 kg\", \"stok\": 50, \"harga\": 750000, \"merek\": \"FIREFIX\", \"kapasitas\": \"5 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(26, NULL, 'default', 'Membuat Produk #26', 'App\\Models\\Produk', 26, 'created', '{\"attributes\": {\"id\": 26, \"nama\": \"APAR GuardALL CO2 5 kg\", \"stok\": 50, \"harga\": 810000, \"merek\": \"GuardALL\", \"kapasitas\": \"5 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(27, NULL, 'default', 'Membuat Produk #27', 'App\\Models\\Produk', 27, 'created', '{\"attributes\": {\"id\": 27, \"nama\": \"APAR TONATA CO2 5 kg\", \"stok\": 50, \"harga\": 862500, \"merek\": \"TONATA\", \"kapasitas\": \"5 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(28, NULL, 'default', 'Membuat Produk #28', 'App\\Models\\Produk', 28, 'created', '{\"attributes\": {\"id\": 28, \"nama\": \"APAR FIREFIX CO2 6.8 kg\", \"stok\": 50, \"harga\": 950000, \"merek\": \"FIREFIX\", \"kapasitas\": \"6.8 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(29, NULL, 'default', 'Membuat Produk #29', 'App\\Models\\Produk', 29, 'created', '{\"attributes\": {\"id\": 29, \"nama\": \"APAR GuardALL CO2 6.8 kg\", \"stok\": 50, \"harga\": 1026000, \"merek\": \"GuardALL\", \"kapasitas\": \"6.8 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(30, NULL, 'default', 'Membuat Produk #30', 'App\\Models\\Produk', 30, 'created', '{\"attributes\": {\"id\": 30, \"nama\": \"APAR TONATA CO2 6.8 kg\", \"stok\": 50, \"harga\": 1092500, \"merek\": \"TONATA\", \"kapasitas\": \"6.8 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Ruang server, panel listrik, laboratorium\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 2}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(31, NULL, 'default', 'Membuat Produk #31', 'App\\Models\\Produk', 31, 'created', '{\"attributes\": {\"id\": 31, \"nama\": \"APAR FIREFIX Foam 6 kg\", \"stok\": 50, \"harga\": 500000, \"merek\": \"FIREFIX\", \"kapasitas\": \"6 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Dapur, SPBU, industri cairan mudah terbakar\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 3}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(32, NULL, 'default', 'Membuat Produk #32', 'App\\Models\\Produk', 32, 'created', '{\"attributes\": {\"id\": 32, \"nama\": \"APAR GuardALL Foam 6 kg\", \"stok\": 50, \"harga\": 540000, \"merek\": \"GuardALL\", \"kapasitas\": \"6 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Dapur, SPBU, industri cairan mudah terbakar\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 3}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(33, NULL, 'default', 'Membuat Produk #33', 'App\\Models\\Produk', 33, 'created', '{\"attributes\": {\"id\": 33, \"nama\": \"APAR TONATA Foam 6 kg\", \"stok\": 50, \"harga\": 575000, \"merek\": \"TONATA\", \"kapasitas\": \"6 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Dapur, SPBU, industri cairan mudah terbakar\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 3}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(34, NULL, 'default', 'Membuat Produk #34', 'App\\Models\\Produk', 34, 'created', '{\"attributes\": {\"id\": 34, \"nama\": \"APAR FIREFIX Foam 9 kg\", \"stok\": 50, \"harga\": 650000, \"merek\": \"FIREFIX\", \"kapasitas\": \"9 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Dapur, SPBU, industri cairan mudah terbakar\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 3}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(35, NULL, 'default', 'Membuat Produk #35', 'App\\Models\\Produk', 35, 'created', '{\"attributes\": {\"id\": 35, \"nama\": \"APAR GuardALL Foam 9 kg\", \"stok\": 50, \"harga\": 702000, \"merek\": \"GuardALL\", \"kapasitas\": \"9 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Dapur, SPBU, industri cairan mudah terbakar\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 3}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(36, NULL, 'default', 'Membuat Produk #36', 'App\\Models\\Produk', 36, 'created', '{\"attributes\": {\"id\": 36, \"nama\": \"APAR TONATA Foam 9 kg\", \"stok\": 50, \"harga\": 747500, \"merek\": \"TONATA\", \"kapasitas\": \"9 kg\", \"created_at\": \"2026-07-22 21:41:03\", \"penggunaan\": \"Dapur, SPBU, industri cairan mudah terbakar\", \"updated_at\": \"2026-07-22 21:41:03\", \"jenis_apar_id\": 3}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(37, NULL, 'default', 'Membuat Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'created', '{\"attributes\": {\"id\": 1, \"nama\": \"Akhmad Rizaldy\", \"no_wa\": \"087830665027\", \"alamat\": \"Bogor, Jawa Barat\", \"status\": \"tetap\", \"user_id\": 3, \"created_at\": \"2026-07-22 21:41:06\", \"updated_at\": \"2026-07-22 21:41:06\", \"sumber_data\": \"manual\", \"kategori_pelanggan\": \"lama\"}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:06', '2026-07-22 14:41:06'),
(38, NULL, 'default', 'Memperbarui Produk #1', 'App\\Models\\Produk', 1, 'updated', '{\"changes\": {\"harga\": {\"new\": 150000, \"old\": \"150000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(39, NULL, 'default', 'Memperbarui Produk #2', 'App\\Models\\Produk', 2, 'updated', '{\"changes\": {\"harga\": {\"new\": 162000, \"old\": \"162000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(40, NULL, 'default', 'Memperbarui Produk #3', 'App\\Models\\Produk', 3, 'updated', '{\"changes\": {\"harga\": {\"new\": 172500, \"old\": \"172500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(41, NULL, 'default', 'Memperbarui Produk #4', 'App\\Models\\Produk', 4, 'updated', '{\"changes\": {\"harga\": {\"new\": 200000, \"old\": \"200000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(42, NULL, 'default', 'Memperbarui Produk #5', 'App\\Models\\Produk', 5, 'updated', '{\"changes\": {\"harga\": {\"new\": 216000, \"old\": \"216000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(43, NULL, 'default', 'Memperbarui Produk #6', 'App\\Models\\Produk', 6, 'updated', '{\"changes\": {\"harga\": {\"new\": 230000, \"old\": \"230000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(44, NULL, 'default', 'Memperbarui Produk #7', 'App\\Models\\Produk', 7, 'updated', '{\"changes\": {\"harga\": {\"new\": 300000, \"old\": \"300000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(45, NULL, 'default', 'Memperbarui Produk #8', 'App\\Models\\Produk', 8, 'updated', '{\"changes\": {\"harga\": {\"new\": 324000, \"old\": \"324000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(46, NULL, 'default', 'Memperbarui Produk #9', 'App\\Models\\Produk', 9, 'updated', '{\"changes\": {\"harga\": {\"new\": 345000, \"old\": \"345000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(47, NULL, 'default', 'Memperbarui Produk #10', 'App\\Models\\Produk', 10, 'updated', '{\"changes\": {\"harga\": {\"new\": 400000, \"old\": \"400000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(48, NULL, 'default', 'Memperbarui Produk #11', 'App\\Models\\Produk', 11, 'updated', '{\"changes\": {\"harga\": {\"new\": 432000, \"old\": \"432000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(49, NULL, 'default', 'Memperbarui Produk #12', 'App\\Models\\Produk', 12, 'updated', '{\"changes\": {\"harga\": {\"new\": 460000, \"old\": \"460000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(50, NULL, 'default', 'Memperbarui Produk #13', 'App\\Models\\Produk', 13, 'updated', '{\"changes\": {\"harga\": {\"new\": 550000, \"old\": \"550000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(51, NULL, 'default', 'Memperbarui Produk #14', 'App\\Models\\Produk', 14, 'updated', '{\"changes\": {\"harga\": {\"new\": 594000, \"old\": \"594000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(52, NULL, 'default', 'Memperbarui Produk #15', 'App\\Models\\Produk', 15, 'updated', '{\"changes\": {\"harga\": {\"new\": 632500, \"old\": \"632500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(53, NULL, 'default', 'Memperbarui Produk #16', 'App\\Models\\Produk', 16, 'updated', '{\"changes\": {\"harga\": {\"new\": 750000, \"old\": \"750000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(54, NULL, 'default', 'Memperbarui Produk #17', 'App\\Models\\Produk', 17, 'updated', '{\"changes\": {\"harga\": {\"new\": 810000, \"old\": \"810000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(55, NULL, 'default', 'Memperbarui Produk #18', 'App\\Models\\Produk', 18, 'updated', '{\"changes\": {\"harga\": {\"new\": 862500, \"old\": \"862500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(56, NULL, 'default', 'Memperbarui Produk #19', 'App\\Models\\Produk', 19, 'updated', '{\"changes\": {\"harga\": {\"new\": 450000, \"old\": \"450000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(57, NULL, 'default', 'Memperbarui Produk #20', 'App\\Models\\Produk', 20, 'updated', '{\"changes\": {\"harga\": {\"new\": 486000, \"old\": \"486000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(58, NULL, 'default', 'Memperbarui Produk #21', 'App\\Models\\Produk', 21, 'updated', '{\"changes\": {\"harga\": {\"new\": 517500, \"old\": \"517500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(59, NULL, 'default', 'Memperbarui Produk #22', 'App\\Models\\Produk', 22, 'updated', '{\"changes\": {\"harga\": {\"new\": 550000, \"old\": \"550000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(60, NULL, 'default', 'Memperbarui Produk #23', 'App\\Models\\Produk', 23, 'updated', '{\"changes\": {\"harga\": {\"new\": 594000, \"old\": \"594000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(61, NULL, 'default', 'Memperbarui Produk #24', 'App\\Models\\Produk', 24, 'updated', '{\"changes\": {\"harga\": {\"new\": 632500, \"old\": \"632500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(62, NULL, 'default', 'Memperbarui Produk #25', 'App\\Models\\Produk', 25, 'updated', '{\"changes\": {\"harga\": {\"new\": 750000, \"old\": \"750000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(63, NULL, 'default', 'Memperbarui Produk #26', 'App\\Models\\Produk', 26, 'updated', '{\"changes\": {\"harga\": {\"new\": 810000, \"old\": \"810000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(64, NULL, 'default', 'Memperbarui Produk #27', 'App\\Models\\Produk', 27, 'updated', '{\"changes\": {\"harga\": {\"new\": 862500, \"old\": \"862500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(65, NULL, 'default', 'Memperbarui Produk #28', 'App\\Models\\Produk', 28, 'updated', '{\"changes\": {\"harga\": {\"new\": 950000, \"old\": \"950000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(66, NULL, 'default', 'Memperbarui Produk #29', 'App\\Models\\Produk', 29, 'updated', '{\"changes\": {\"harga\": {\"new\": 1026000, \"old\": \"1026000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(67, NULL, 'default', 'Memperbarui Produk #30', 'App\\Models\\Produk', 30, 'updated', '{\"changes\": {\"harga\": {\"new\": 1092500, \"old\": \"1092500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(68, NULL, 'default', 'Memperbarui Produk #31', 'App\\Models\\Produk', 31, 'updated', '{\"changes\": {\"harga\": {\"new\": 500000, \"old\": \"500000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(69, NULL, 'default', 'Memperbarui Produk #32', 'App\\Models\\Produk', 32, 'updated', '{\"changes\": {\"harga\": {\"new\": 540000, \"old\": \"540000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(70, NULL, 'default', 'Memperbarui Produk #33', 'App\\Models\\Produk', 33, 'updated', '{\"changes\": {\"harga\": {\"new\": 575000, \"old\": \"575000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(71, NULL, 'default', 'Memperbarui Produk #34', 'App\\Models\\Produk', 34, 'updated', '{\"changes\": {\"harga\": {\"new\": 650000, \"old\": \"650000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(72, NULL, 'default', 'Memperbarui Produk #35', 'App\\Models\\Produk', 35, 'updated', '{\"changes\": {\"harga\": {\"new\": 702000, \"old\": \"702000.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(73, NULL, 'default', 'Memperbarui Produk #36', 'App\\Models\\Produk', 36, 'updated', '{\"changes\": {\"harga\": {\"new\": 747500, \"old\": \"747500.00\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:12', '2026-07-22 14:41:12'),
(74, NULL, 'default', 'Memperbarui Produk #1', 'App\\Models\\Produk', 1, 'updated', '{\"changes\": {\"stok\": {\"new\": \"18\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:17', '2026-07-22 14:41:17'),
(75, NULL, 'default', 'Memperbarui Produk #2', 'App\\Models\\Produk', 2, 'updated', '{\"changes\": {\"stok\": {\"new\": \"24\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:17', '2026-07-22 14:41:17'),
(76, NULL, 'default', 'Memperbarui Produk #3', 'App\\Models\\Produk', 3, 'updated', '{\"changes\": {\"stok\": {\"new\": \"17\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:17', '2026-07-22 14:41:17'),
(77, NULL, 'default', 'Memperbarui Produk #4', 'App\\Models\\Produk', 4, 'updated', '{\"changes\": {\"stok\": {\"new\": \"21\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(78, NULL, 'default', 'Memperbarui Produk #5', 'App\\Models\\Produk', 5, 'updated', '{\"changes\": {\"stok\": {\"new\": \"27\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(79, NULL, 'default', 'Memperbarui Produk #6', 'App\\Models\\Produk', 6, 'updated', '{\"changes\": {\"stok\": {\"new\": \"15\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(80, NULL, 'default', 'Memperbarui Produk #7', 'App\\Models\\Produk', 7, 'updated', '{\"changes\": {\"stok\": {\"new\": \"19\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(81, NULL, 'default', 'Memperbarui Produk #8', 'App\\Models\\Produk', 8, 'updated', '{\"changes\": {\"stok\": {\"new\": \"25\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(82, NULL, 'default', 'Memperbarui Produk #9', 'App\\Models\\Produk', 9, 'updated', '{\"changes\": {\"stok\": {\"new\": \"18\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(83, NULL, 'default', 'Memperbarui Produk #10', 'App\\Models\\Produk', 10, 'updated', '{\"changes\": {\"stok\": {\"new\": \"22\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(84, NULL, 'default', 'Memperbarui Produk #11', 'App\\Models\\Produk', 11, 'updated', '{\"changes\": {\"stok\": {\"new\": \"23\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(85, NULL, 'default', 'Memperbarui Produk #12', 'App\\Models\\Produk', 12, 'updated', '{\"changes\": {\"stok\": {\"new\": \"16\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(86, NULL, 'default', 'Memperbarui Produk #13', 'App\\Models\\Produk', 13, 'updated', '{\"changes\": {\"stok\": {\"new\": \"20\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(87, NULL, 'default', 'Memperbarui Produk #14', 'App\\Models\\Produk', 14, 'updated', '{\"changes\": {\"stok\": {\"new\": \"26\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(88, NULL, 'default', 'Memperbarui Produk #15', 'App\\Models\\Produk', 15, 'updated', '{\"changes\": {\"stok\": {\"new\": \"19\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(89, NULL, 'default', 'Memperbarui Produk #16', 'App\\Models\\Produk', 16, 'updated', '{\"changes\": {\"stok\": {\"new\": \"18\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(90, NULL, 'default', 'Memperbarui Produk #17', 'App\\Models\\Produk', 17, 'updated', '{\"changes\": {\"stok\": {\"new\": \"24\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(91, NULL, 'default', 'Memperbarui Produk #18', 'App\\Models\\Produk', 18, 'updated', '{\"changes\": {\"stok\": {\"new\": \"17\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(92, NULL, 'default', 'Memperbarui Produk #19', 'App\\Models\\Produk', 19, 'updated', '{\"changes\": {\"stok\": {\"new\": \"21\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(93, NULL, 'default', 'Memperbarui Produk #20', 'App\\Models\\Produk', 20, 'updated', '{\"changes\": {\"stok\": {\"new\": \"27\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(94, NULL, 'default', 'Memperbarui Produk #21', 'App\\Models\\Produk', 21, 'updated', '{\"changes\": {\"stok\": {\"new\": \"15\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(95, NULL, 'default', 'Memperbarui Produk #22', 'App\\Models\\Produk', 22, 'updated', '{\"changes\": {\"stok\": {\"new\": \"19\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(96, NULL, 'default', 'Memperbarui Produk #23', 'App\\Models\\Produk', 23, 'updated', '{\"changes\": {\"stok\": {\"new\": \"25\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(97, NULL, 'default', 'Memperbarui Produk #24', 'App\\Models\\Produk', 24, 'updated', '{\"changes\": {\"stok\": {\"new\": \"18\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(98, NULL, 'default', 'Memperbarui Produk #25', 'App\\Models\\Produk', 25, 'updated', '{\"changes\": {\"stok\": {\"new\": \"22\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(99, NULL, 'default', 'Memperbarui Produk #26', 'App\\Models\\Produk', 26, 'updated', '{\"changes\": {\"stok\": {\"new\": \"23\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(100, NULL, 'default', 'Memperbarui Produk #27', 'App\\Models\\Produk', 27, 'updated', '{\"changes\": {\"stok\": {\"new\": \"16\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(101, NULL, 'default', 'Memperbarui Produk #28', 'App\\Models\\Produk', 28, 'updated', '{\"changes\": {\"stok\": {\"new\": \"20\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(102, NULL, 'default', 'Memperbarui Produk #29', 'App\\Models\\Produk', 29, 'updated', '{\"changes\": {\"stok\": {\"new\": \"26\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(103, NULL, 'default', 'Memperbarui Produk #30', 'App\\Models\\Produk', 30, 'updated', '{\"changes\": {\"stok\": {\"new\": \"19\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(104, NULL, 'default', 'Memperbarui Produk #31', 'App\\Models\\Produk', 31, 'updated', '{\"changes\": {\"stok\": {\"new\": \"18\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(105, NULL, 'default', 'Memperbarui Produk #32', 'App\\Models\\Produk', 32, 'updated', '{\"changes\": {\"stok\": {\"new\": \"24\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(106, NULL, 'default', 'Memperbarui Produk #33', 'App\\Models\\Produk', 33, 'updated', '{\"changes\": {\"stok\": {\"new\": \"17\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(107, NULL, 'default', 'Memperbarui Produk #34', 'App\\Models\\Produk', 34, 'updated', '{\"changes\": {\"stok\": {\"new\": \"21\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(108, NULL, 'default', 'Memperbarui Produk #35', 'App\\Models\\Produk', 35, 'updated', '{\"changes\": {\"stok\": {\"new\": \"27\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(109, NULL, 'default', 'Memperbarui Produk #36', 'App\\Models\\Produk', 36, 'updated', '{\"changes\": {\"stok\": {\"new\": \"15\", \"old\": 50}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:18', '2026-07-22 14:41:18'),
(110, NULL, 'default', 'Membuat UnitApar #1', 'App\\Models\\UnitApar', 1, 'created', '{\"attributes\": {\"id\": 1, \"bahan\": \"Liquid Foam (Busa)\", \"ukuran\": \"6 kg\", \"no_seri\": \"AKHMAD-21072026-02\", \"tgl_beli\": \"2026-04-22 00:00:00\", \"produk_id\": 32, \"created_at\": \"2026-07-22 21:41:51\", \"updated_at\": \"2026-07-22 21:41:51\", \"tgl_expired\": \"2026-08-04 00:00:00\", \"kondisi_awal\": \"layak\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 14:41:51', '2026-07-22 14:41:51'),
(111, 1, 'default', 'Memperbarui UnitApar #1', 'App\\Models\\UnitApar', 1, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": \"2026-07-22 21:44:05\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:44:05', '2026-07-22 14:44:05'),
(112, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat\": {\"new\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266 | Detail: Blok A1\", \"old\": \"Bogor, Jawa Barat\"}, \"alamat_lat\": {\"new\": -6.9530028, \"old\": null}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": null}, \"alamat_kota\": {\"new\": \"BANDUNG\", \"old\": null}, \"alamat_maps\": {\"new\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"old\": null}, \"alamat_detail\": {\"new\": \"Blok A1\", \"old\": null}, \"alamat_kode_pos\": {\"new\": \"40266\", \"old\": null}, \"alamat_provinsi\": {\"new\": \"JAWA BARAT\", \"old\": null}, \"alamat_kecamatan\": {\"new\": \"BATUNUNGGAL\", \"old\": null}, \"rajaongkir_destination_id\": {\"new\": \"4866\", \"old\": null}, \"rajaongkir_destination_label\": {\"new\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:44:24', '2026-07-22 14:44:24'),
(113, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat_lat\": {\"new\": -6.9530028, \"old\": \"-6.95300280\"}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": \"107.63814020\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:47:48', '2026-07-22 14:47:48'),
(114, 3, 'default', 'Membuat Pesanan #1', 'App\\Models\\Pesanan', 1, 'created', '{\"attributes\": {\"id\": 1, \"bank\": \"bca\", \"tipe\": \"produk\", \"total\": 0, \"ongkir\": 168000, \"status\": \"menunggu persetujuan\", \"is_nego\": true, \"tanggal\": \"2026-07-22 21:47:48\", \"user_id\": 3, \"kode_nego\": null, \"alamat_lat\": -6.9530028, \"alamat_lng\": 107.6381402, \"created_at\": \"2026-07-22 21:47:48\", \"keterangan\": \"Pembelian Produk [Promo Diskon 10%] [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]\", \"tipe_harga\": \"normal\", \"total_awal\": 5261100, \"updated_at\": \"2026-07-22 21:47:48\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"approved_at\": null, \"approved_by\": null, \"rejected_at\": null, \"rejected_by\": null, \"harga_normal\": 5659000, \"harga_usulan\": null, \"pelanggan_id\": 1, \"shipping_etd\": \"3-4 day\", \"alamat_detail\": \"Blok A1\", \"sumber_pesanan\": \"website\", \"shipping_weight\": 42000, \"shipping_courier\": \"sicepat\", \"shipping_service\": \"GOKIL - Cargo Per Kg (Minimal 10kg)\", \"harga_final_admin\": null, \"metode_pengiriman\": \"diantar_internal\", \"is_pengajuan_harga\": true, \"harga_setelah_diskon\": 5093100, \"shipping_distance_km\": null, \"kode_nego_terpakai_at\": null, \"service_admin_catatan\": \"test\", \"shipping_destination_id\": \"4866\", \"status_persetujuan_harga\": \"pending\", \"harga_penawaran_pelanggan\": 4000000, \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:47:48', '2026-07-22 14:47:48'),
(115, 3, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"total\": {\"new\": 5261100, \"old\": 0}, \"keterangan\": {\"new\": \"Pembelian Produk [Promo Diskon 10%: -Rp 565.900] [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]\", \"old\": \"Pembelian Produk [Promo Diskon 10%] [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]\"}, \"total_harga\": {\"new\": 5261100, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:47:48', '2026-07-22 14:47:48'),
(116, 1, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"total\": {\"new\": 4500000, \"old\": 5261100}, \"status\": {\"new\": \"disetujui\", \"old\": \"menunggu persetujuan\"}, \"is_nego\": {\"new\": true, \"old\": 1}, \"tipe_harga\": {\"new\": \"deal\", \"old\": \"normal\"}, \"approved_at\": {\"new\": \"2026-07-22 21:48:56\", \"old\": null}, \"approved_by\": {\"new\": 1, \"old\": null}, \"total_harga\": {\"new\": 4500000, \"old\": 5261100}, \"harga_usulan\": {\"new\": 4500000, \"old\": null}, \"catatan_admin\": {\"new\": \"oke\", \"old\": null}, \"harga_final_admin\": {\"new\": 4500000, \"old\": null}, \"status_persetujuan_harga\": {\"new\": \"approved\", \"old\": \"pending\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:48:56', '2026-07-22 14:48:56'),
(117, 3, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"status\": {\"new\": \"diproses\", \"old\": \"disetujui\"}, \"bukti_pembayaran\": {\"new\": \"bukti-pembayaran/gOSdFtBusQfMtZYEnCuyT3KeJaAm5YPktQelXFTR.jpg\", \"old\": null}, \"metode_pembayaran\": {\"new\": \"transfer\", \"old\": null}, \"kode_nego_terpakai_at\": {\"new\": \"2026-07-22 21:52:15\", \"old\": null}, \"pembayaran_terkonfirmasi_at\": {\"new\": \"2026-07-22 21:52:15\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(118, 3, 'default', 'Membuat UnitApar #2', 'App\\Models\\UnitApar', 2, 'created', '{\"attributes\": {\"id\": 2, \"bahan\": \"Carbon Dioxide (CO2)\", \"ukuran\": \"2 kg\", \"no_seri\": \"AKHMAD-22072026-01\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 21, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(119, 3, 'default', 'Membuat UnitApar #3', 'App\\Models\\UnitApar', 3, 'created', '{\"attributes\": {\"id\": 3, \"bahan\": \"Carbon Dioxide (CO2)\", \"ukuran\": \"2 kg\", \"no_seri\": \"AKHMAD-22072026-02\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 21, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(120, 3, 'default', 'Membuat UnitApar #4', 'App\\Models\\UnitApar', 4, 'created', '{\"attributes\": {\"id\": 4, \"bahan\": \"Carbon Dioxide (CO2)\", \"ukuran\": \"2 kg\", \"no_seri\": \"AKHMAD-22072026-03\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 21, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(121, 3, 'default', 'Membuat UnitApar #5', 'App\\Models\\UnitApar', 5, 'created', '{\"attributes\": {\"id\": 5, \"bahan\": \"Carbon Dioxide (CO2)\", \"ukuran\": \"2 kg\", \"no_seri\": \"AKHMAD-22072026-04\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 21, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(122, 3, 'default', 'Membuat UnitApar #6', 'App\\Models\\UnitApar', 6, 'created', '{\"attributes\": {\"id\": 6, \"bahan\": \"Carbon Dioxide (CO2)\", \"ukuran\": \"2 kg\", \"no_seri\": \"AKHMAD-22072026-05\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 21, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(123, 3, 'default', 'Membuat UnitApar #7', 'App\\Models\\UnitApar', 7, 'created', '{\"attributes\": {\"id\": 7, \"bahan\": \"Carbon Dioxide (CO2)\", \"ukuran\": \"2 kg\", \"no_seri\": \"AKHMAD-22072026-06\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 21, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(124, 3, 'default', 'Membuat UnitApar #8', 'App\\Models\\UnitApar', 8, 'created', '{\"attributes\": {\"id\": 8, \"bahan\": \"Liquid Foam (Busa)\", \"ukuran\": \"6 kg\", \"no_seri\": \"AKHMAD-22072026-07\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 33, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(125, 3, 'default', 'Membuat UnitApar #9', 'App\\Models\\UnitApar', 9, 'created', '{\"attributes\": {\"id\": 9, \"bahan\": \"Liquid Foam (Busa)\", \"ukuran\": \"6 kg\", \"no_seri\": \"AKHMAD-22072026-08\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 33, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2027-04-22 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-04-22 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(126, 3, 'default', 'Membuat UnitApar #10', 'App\\Models\\UnitApar', 10, 'created', '{\"attributes\": {\"id\": 10, \"bahan\": \"Liquid Foam (Busa)\", \"ukuran\": \"9 kg\", \"no_seri\": \"AKHMAD-22072026-09\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 35, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2026-08-06 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2025-08-06 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(127, 3, 'default', 'Membuat UnitApar #11', 'App\\Models\\UnitApar', 11, 'created', '{\"attributes\": {\"id\": 11, \"bahan\": \"Liquid Foam (Busa)\", \"ukuran\": \"9 kg\", \"no_seri\": \"AKHMAD-22072026-10\", \"tgl_beli\": \"2026-07-22 00:00:00\", \"hidden_at\": \"2026-07-22 21:52:15\", \"produk_id\": 35, \"created_at\": \"2026-07-22 21:52:15\", \"pesanan_id\": 1, \"updated_at\": \"2026-07-22 21:52:15\", \"tgl_expired\": \"2026-08-06 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2025-08-06 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(128, 3, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": true, \"old\": false}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(129, 1, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"status\": {\"new\": \"ditugaskan ke teknisi\", \"old\": \"diproses\"}, \"teknisi_id\": {\"new\": 2, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:52:45', '2026-07-22 14:52:45'),
(130, 2, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"status\": {\"new\": \"dikerjakan teknisi\", \"old\": \"ditugaskan ke teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:53:43', '2026-07-22 14:53:43'),
(131, 2, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai oleh teknisi\", \"old\": \"dikerjakan teknisi\"}, \"teknisi_selesai_at\": {\"new\": \"2026-07-22 21:53:46\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:53:46', '2026-07-22 14:53:46'),
(132, 1, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"status\": {\"new\": \"siap dikirim\", \"old\": \"selesai oleh teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:00', '2026-07-22 14:54:00'),
(133, 3, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai final\", \"old\": \"siap dikirim\"}, \"customer_confirmed_at\": {\"new\": \"2026-07-22 21:54:11\", \"old\": null}, \"customer_confirmed_by\": {\"new\": 3, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11');
INSERT INTO `activity_logs` (`id`, `user_id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `properties`, `batch_uuid`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(134, 3, 'default', 'Memperbarui UnitApar #2', 'App\\Models\\UnitApar', 2, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(135, 3, 'default', 'Memperbarui UnitApar #3', 'App\\Models\\UnitApar', 3, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(136, 3, 'default', 'Memperbarui UnitApar #4', 'App\\Models\\UnitApar', 4, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(137, 3, 'default', 'Memperbarui UnitApar #5', 'App\\Models\\UnitApar', 5, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(138, 3, 'default', 'Memperbarui UnitApar #6', 'App\\Models\\UnitApar', 6, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(139, 3, 'default', 'Memperbarui UnitApar #7', 'App\\Models\\UnitApar', 7, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(140, 3, 'default', 'Memperbarui UnitApar #8', 'App\\Models\\UnitApar', 8, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(141, 3, 'default', 'Memperbarui UnitApar #9', 'App\\Models\\UnitApar', 9, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(142, 3, 'default', 'Memperbarui UnitApar #10', 'App\\Models\\UnitApar', 10, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(143, 3, 'default', 'Memperbarui UnitApar #11', 'App\\Models\\UnitApar', 11, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-22T14:52:15.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(144, 3, 'default', 'Membuat Testimoni #1', 'App\\Models\\Testimoni', 1, 'created', '{\"attributes\": {\"id\": 1, \"rating\": \"5\", \"review\": \"TERBAIK\", \"status\": \"approved\", \"tanggal\": \"2026-07-22 21:54:38\", \"foto_path\": \"testimonis/jfm9gnZLpICGEuE48VqahnKoLJ0LQsISUZNTVXs1.jpg\", \"created_at\": \"2026-07-22 21:54:38\", \"updated_at\": \"2026-07-22 21:54:38\", \"is_anonymous\": false, \"pelanggan_id\": 1, \"transaksi_id\": 1, \"transaksi_type\": \"App\\\\Models\\\\Pesanan\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:38', '2026-07-22 14:54:38'),
(145, 3, 'default', 'Memperbarui Pesanan #1', 'App\\Models\\Pesanan', 1, 'updated', '{\"changes\": {\"testimonial_submitted_at\": {\"new\": \"2026-07-22 21:54:38\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:38', '2026-07-22 14:54:38'),
(146, 3, 'feedback', 'testimoni-order:1:pelanggan:1', 'App\\Models\\Testimoni', 1, 'linked_to_order', '{\"order_code\": \"TNTI22072026AJ001\", \"pesanan_id\": 1, \"pelanggan_id\": 1}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 14:54:38', '2026-07-22 14:54:38'),
(147, NULL, 'default', 'Memperbarui UnitApar #1', 'App\\Models\\UnitApar', 1, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-04-22 00:00:00\", \"old\": \"2026-08-03T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:05:09', '2026-07-22 15:05:09'),
(148, NULL, 'default', 'Memperbarui UnitApar #2', 'App\\Models\\UnitApar', 2, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(149, NULL, 'default', 'Memperbarui UnitApar #3', 'App\\Models\\UnitApar', 3, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(150, NULL, 'default', 'Memperbarui UnitApar #4', 'App\\Models\\UnitApar', 4, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(151, NULL, 'default', 'Memperbarui UnitApar #5', 'App\\Models\\UnitApar', 5, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(152, NULL, 'default', 'Memperbarui UnitApar #6', 'App\\Models\\UnitApar', 6, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(153, NULL, 'default', 'Memperbarui UnitApar #7', 'App\\Models\\UnitApar', 7, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(154, NULL, 'default', 'Memperbarui UnitApar #8', 'App\\Models\\UnitApar', 8, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(155, NULL, 'default', 'Memperbarui UnitApar #9', 'App\\Models\\UnitApar', 9, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2027-04-21T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(156, NULL, 'default', 'Memperbarui UnitApar #10', 'App\\Models\\UnitApar', 10, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2026-08-05T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(157, NULL, 'default', 'Memperbarui UnitApar #11', 'App\\Models\\UnitApar', 11, 'updated', '{\"changes\": {\"tgl_expired\": {\"new\": \"2027-07-22 00:00:00\", \"old\": \"2026-08-05T17:00:00.000000Z\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:06:45', '2026-07-22 15:06:45'),
(158, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat_lat\": {\"new\": -6.9530028, \"old\": \"-6.95300280\"}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": \"107.63814020\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:23:51', '2026-07-22 15:23:51'),
(159, 3, 'default', 'Membuat Pesanan #2', 'App\\Models\\Pesanan', 2, 'created', '{\"attributes\": {\"id\": 2, \"bank\": \"bca\", \"tipe\": \"service\", \"total\": 787784, \"ongkir\": 722784, \"status\": \"pending\", \"tanggal\": \"2026-07-22 22:23:51\", \"user_id\": 3, \"alamat_lat\": -6.9530028, \"alamat_lng\": 107.6381402, \"created_at\": \"2026-07-22 22:23:51\", \"keterangan\": \"Permintaan SERVICE Ganti Selang CO2 | Item: Ganti Selang CO2 | Jumlah: 1 unit | Metode: dijemput | Ongkir: Rp722.784 | Catatan: Rincian Service Manual | 1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Selang APAR CO2 x1 | - Segel Pengaman Plastik x1 | Total Service: Rp65.000 | Metode Penanganan: Dijemput | Catatan Pelanggan: test\", \"tipe_harga\": \"normal\", \"updated_at\": \"2026-07-22 22:23:51\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"total_harga\": 787784, \"pelanggan_id\": 1, \"service_foto\": \"service-request/nwZgaXPvFuAaVnFGACZjo5tRvtLUbI6fceaWpfAF.jpg\", \"shipping_etd\": null, \"alamat_detail\": \"Blok A1\", \"sumber_pesanan\": \"website\", \"service_keluhan\": \"Rincian Service Manual\\n1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Selang APAR CO2 x1\\n- Segel Pengaman Plastik x1\\nTotal Service: Rp65.000\\nMetode Penanganan: Dijemput\\nCatatan Pelanggan: test\", \"shipping_weight\": 0, \"service_paket_id\": 5, \"service_total_kg\": null, \"shipping_courier\": null, \"shipping_service\": null, \"metode_pengiriman\": \"diantar_internal\", \"service_jenis_apar\": \"Ganti Selang CO2\", \"service_jumlah_unit\": 1, \"service_ukuran_apar\": \"2 kg\", \"shipping_distance_km\": 206.51, \"service_admin_catatan\": null, \"service_jenis_layanan\": \"service\", \"service_estimasi_biaya\": 65000, \"service_jenis_refill_id\": null, \"shipping_destination_id\": \"4866\", \"service_metode_penanganan\": \"dijemput\", \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:23:51', '2026-07-22 15:23:51'),
(160, 3, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"status\": {\"new\": \"menunggu pengambilan\", \"old\": \"pending\"}, \"bukti_pembayaran\": {\"new\": \"bukti-pembayaran/kCrPe0ICeCXNLg6jVMg4uAkHmit4fB7m9dkWgK5d.jpg\", \"old\": null}, \"metode_pembayaran\": {\"new\": \"transfer\", \"old\": null}, \"pembayaran_terkonfirmasi_at\": {\"new\": \"2026-07-22 22:23:58\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:23:58', '2026-07-22 15:23:58'),
(161, 3, 'default', 'Membuat Service #1', 'App\\Models\\Service', 1, 'created', '{\"attributes\": {\"id\": 1, \"biaya\": 65000, \"created_at\": \"2026-07-22 22:23:58\", \"keterangan\": \"Rincian Service Manual\\n1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Selang APAR CO2 x1\\n- Segel Pengaman Plastik x1\\nTotal Service: Rp65.000\\nMetode Penanganan: Dijemput\\nCatatan Pelanggan: test\", \"pesanan_id\": 2, \"updated_at\": \"2026-07-22 22:23:58\", \"tgl_service\": \"2026-07-22 00:00:00\", \"laporan_foto\": null, \"jenis_service\": \"Ganti Selang CO2\", \"catatan_teknisi\": null, \"rincian_layanan\": \"Penggantian selang APAR CO2.\\r\\nPenggantian segel pengaman plastik.\", \"service_paket_id\": 5, \"status_konfirmasi\": \"pending\", \"tgl_selesai_admin\": null, \"actual_peralatan_json\": null, \"estimasi_peralatan_json\": \"[{\\\"peralatan_id\\\":9,\\\"nama\\\":\\\"Selang APAR CO2\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":10,\\\"stok_minimum\\\":3,\\\"harga_standar\\\":120000},{\\\"peralatan_id\\\":3,\\\"nama\\\":\\\"Segel Pengaman Plastik\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":50,\\\"stok_minimum\\\":10,\\\"harga_standar\\\":3000}]\", \"stok_kurang_history_json\": null}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:23:58', '2026-07-22 15:23:58'),
(162, 3, 'default', 'Memperbarui Service #1', 'App\\Models\\Service', 1, 'updated', '{\"changes\": {\"biaya\": {\"new\": 65000, \"old\": \"65000.00\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:23:58', '2026-07-22 15:23:58'),
(163, 3, 'default', 'Memperbarui Service #1', 'App\\Models\\Service', 1, 'updated', '{\"changes\": {\"actual_peralatan_json\": {\"new\": \"[{\\\"peralatan_id\\\":9,\\\"nama\\\":\\\"Selang APAR CO2\\\",\\\"jumlah\\\":1},{\\\"peralatan_id\\\":3,\\\"nama\\\":\\\"Segel Pengaman Plastik\\\",\\\"jumlah\\\":1}]\", \"old\": null}, \"stok_kurang_history_json\": {\"new\": \"[{\\\"peralatan_id\\\":9,\\\"nama\\\":\\\"Selang APAR CO2\\\",\\\"jumlah\\\":1,\\\"stok_sebelum\\\":10,\\\"stok_sesudah\\\":9},{\\\"peralatan_id\\\":3,\\\"nama\\\":\\\"Segel Pengaman Plastik\\\",\\\"jumlah\\\":1,\\\"stok_sebelum\\\":50,\\\"stok_sesudah\\\":49}]\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:23:58', '2026-07-22 15:23:58'),
(164, 3, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": true, \"old\": false}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:23:58', '2026-07-22 15:23:58'),
(165, 1, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"status\": {\"new\": \"ditugaskan ke teknisi\", \"old\": \"menunggu pengambilan\"}, \"teknisi_id\": {\"new\": 2, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:08', '2026-07-22 15:24:08'),
(166, 2, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"status\": {\"new\": \"dikerjakan teknisi\", \"old\": \"ditugaskan ke teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:15', '2026-07-22 15:24:15'),
(167, 2, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai oleh teknisi\", \"old\": \"dikerjakan teknisi\"}, \"teknisi_selesai_at\": {\"new\": \"2026-07-22 22:24:18\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:18', '2026-07-22 15:24:18'),
(168, 1, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"status\": {\"new\": \"siap dikirim\", \"old\": \"selesai oleh teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:23', '2026-07-22 15:24:23'),
(169, 3, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai final\", \"old\": \"siap dikirim\"}, \"customer_confirmed_at\": {\"new\": \"2026-07-22 22:24:31\", \"old\": null}, \"customer_confirmed_by\": {\"new\": 3, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:31', '2026-07-22 15:24:31'),
(170, 3, 'default', 'Memperbarui Service #1', 'App\\Models\\Service', 1, 'updated', '{\"changes\": {\"biaya\": {\"new\": 65000, \"old\": \"65000.00\"}, \"estimasi_peralatan_json\": {\"new\": \"[{\\\"peralatan_id\\\":9,\\\"nama\\\":\\\"Selang APAR CO2\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":9,\\\"stok_minimum\\\":3,\\\"harga_standar\\\":120000},{\\\"peralatan_id\\\":3,\\\"nama\\\":\\\"Segel Pengaman Plastik\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":49,\\\"stok_minimum\\\":10,\\\"harga_standar\\\":3000}]\", \"old\": \"[{\\\"peralatan_id\\\":9,\\\"nama\\\":\\\"Selang APAR CO2\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":10,\\\"stok_minimum\\\":3,\\\"harga_standar\\\":120000},{\\\"peralatan_id\\\":3,\\\"nama\\\":\\\"Segel Pengaman Plastik\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":50,\\\"stok_minimum\\\":10,\\\"harga_standar\\\":3000}]\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:31', '2026-07-22 15:24:31'),
(171, 3, 'default', 'Memperbarui Service #1', 'App\\Models\\Service', 1, 'updated', '{\"changes\": {\"status_konfirmasi\": {\"new\": \"confirmed\", \"old\": \"pending\"}, \"tgl_selesai_admin\": {\"new\": \"2026-07-22 22:24:31\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:31', '2026-07-22 15:24:31'),
(172, 3, 'default', 'Membuat Testimoni #2', 'App\\Models\\Testimoni', 2, 'created', '{\"attributes\": {\"id\": 2, \"rating\": \"4\", \"review\": \"BAGUSSS\", \"status\": \"approved\", \"tanggal\": \"2026-07-22 22:24:42\", \"foto_path\": \"testimonis/qnJ8QRbxTrSC6vmVRcWAsZJ2BvNaipgDntlEDkJ6.jpg\", \"created_at\": \"2026-07-22 22:24:42\", \"updated_at\": \"2026-07-22 22:24:42\", \"is_anonymous\": true, \"pelanggan_id\": 1, \"transaksi_id\": 2, \"transaksi_type\": \"App\\\\Models\\\\Pesanan\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:42', '2026-07-22 15:24:42'),
(173, 3, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"testimonial_submitted_at\": {\"new\": \"2026-07-22 22:24:42\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:42', '2026-07-22 15:24:42'),
(174, 3, 'feedback', 'testimoni-order:2:pelanggan:1', 'App\\Models\\Testimoni', 2, 'linked_to_order', '{\"order_code\": \"TNTI22072026AJ002\", \"pesanan_id\": 2, \"pelanggan_id\": 1}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:24:42', '2026-07-22 15:24:42'),
(175, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat_lat\": {\"new\": -6.9530028, \"old\": \"-6.95300280\"}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": \"107.63814020\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:36', '2026-07-22 15:38:36'),
(176, 3, 'default', 'Membuat Pesanan #3', 'App\\Models\\Pesanan', 3, 'created', '{\"attributes\": {\"id\": 3, \"bank\": \"bca\", \"tipe\": \"service\", \"total\": 65000, \"ongkir\": 0, \"status\": \"pending\", \"tanggal\": \"2026-07-22 22:38:36\", \"user_id\": 3, \"alamat_lat\": -6.9530028, \"alamat_lng\": 107.6381402, \"created_at\": \"2026-07-22 22:38:36\", \"keterangan\": \"Permintaan SERVICE Pasang/Ganti Bracket | Item: Pasang/Ganti Bracket | Jumlah: 1 unit | Metode: antar sendiri | Catatan: Rincian Service Manual | 1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Bracket/Gantungan APAR x1 | - Baut Bracket APAR x2 | Total Service: Rp65.000 | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: -\", \"tipe_harga\": \"normal\", \"updated_at\": \"2026-07-22 22:38:36\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"total_harga\": 65000, \"pelanggan_id\": 1, \"service_foto\": null, \"shipping_etd\": null, \"alamat_detail\": \"Blok A1\", \"sumber_pesanan\": \"website\", \"service_keluhan\": \"Rincian Service Manual\\n1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Bracket/Gantungan APAR x1\\n- Baut Bracket APAR x2\\nTotal Service: Rp65.000\\nMetode Penanganan: Antar Sendiri\\nCatatan Pelanggan: -\", \"shipping_weight\": 0, \"service_paket_id\": 8, \"service_total_kg\": null, \"shipping_courier\": null, \"shipping_service\": null, \"metode_pengiriman\": \"pickup\", \"service_jenis_apar\": \"Pasang/Ganti Bracket\", \"service_jumlah_unit\": 1, \"service_ukuran_apar\": \"2 kg\", \"shipping_distance_km\": null, \"service_admin_catatan\": null, \"service_jenis_layanan\": \"service\", \"service_estimasi_biaya\": 65000, \"service_jenis_refill_id\": null, \"shipping_destination_id\": \"4866\", \"service_metode_penanganan\": \"antar sendiri\", \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:36', '2026-07-22 15:38:36'),
(177, 3, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"status\": {\"new\": \"menunggu kedatangan unit\", \"old\": \"pending\"}, \"bukti_pembayaran\": {\"new\": \"bukti-pembayaran/0dpIAJacML766ahEu1CaWL1UncxUJZklfyVgNonL.jpg\", \"old\": null}, \"metode_pembayaran\": {\"new\": \"transfer\", \"old\": null}, \"pembayaran_terkonfirmasi_at\": {\"new\": \"2026-07-22 22:38:42\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:42', '2026-07-22 15:38:42'),
(178, 3, 'default', 'Membuat Service #2', 'App\\Models\\Service', 2, 'created', '{\"attributes\": {\"id\": 2, \"biaya\": 65000, \"created_at\": \"2026-07-22 22:38:42\", \"keterangan\": \"Rincian Service Manual\\n1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Bracket/Gantungan APAR x1\\n- Baut Bracket APAR x2\\nTotal Service: Rp65.000\\nMetode Penanganan: Antar Sendiri\\nCatatan Pelanggan: -\", \"pesanan_id\": 3, \"updated_at\": \"2026-07-22 22:38:42\", \"tgl_service\": \"2026-07-22 00:00:00\", \"laporan_foto\": null, \"jenis_service\": \"Pasang/Ganti Bracket\", \"catatan_teknisi\": null, \"rincian_layanan\": \"Pemasangan atau penggantian bracket APAR.\\r\\nPemasangan baut bracket APAR.\", \"service_paket_id\": 8, \"status_konfirmasi\": \"pending\", \"tgl_selesai_admin\": null, \"actual_peralatan_json\": null, \"estimasi_peralatan_json\": \"[{\\\"peralatan_id\\\":6,\\\"nama\\\":\\\"Bracket\\\\/Gantungan APAR\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":45,\\\"stok_minimum\\\":8,\\\"harga_standar\\\":25000},{\\\"peralatan_id\\\":5,\\\"nama\\\":\\\"Baut Bracket APAR\\\",\\\"jumlah_per_unit\\\":2,\\\"jumlah\\\":2,\\\"stok\\\":200,\\\"stok_minimum\\\":30,\\\"harga_standar\\\":5000}]\", \"stok_kurang_history_json\": null}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:42', '2026-07-22 15:38:42'),
(179, 3, 'default', 'Memperbarui Service #2', 'App\\Models\\Service', 2, 'updated', '{\"changes\": {\"biaya\": {\"new\": 65000, \"old\": \"65000.00\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:42', '2026-07-22 15:38:42'),
(180, 3, 'default', 'Memperbarui Service #2', 'App\\Models\\Service', 2, 'updated', '{\"changes\": {\"actual_peralatan_json\": {\"new\": \"[{\\\"peralatan_id\\\":6,\\\"nama\\\":\\\"Bracket\\\\/Gantungan APAR\\\",\\\"jumlah\\\":1},{\\\"peralatan_id\\\":5,\\\"nama\\\":\\\"Baut Bracket APAR\\\",\\\"jumlah\\\":2}]\", \"old\": null}, \"stok_kurang_history_json\": {\"new\": \"[{\\\"peralatan_id\\\":6,\\\"nama\\\":\\\"Bracket\\\\/Gantungan APAR\\\",\\\"jumlah\\\":1,\\\"stok_sebelum\\\":45,\\\"stok_sesudah\\\":44},{\\\"peralatan_id\\\":5,\\\"nama\\\":\\\"Baut Bracket APAR\\\",\\\"jumlah\\\":2,\\\"stok_sebelum\\\":200,\\\"stok_sesudah\\\":198}]\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:42', '2026-07-22 15:38:42'),
(181, 3, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": true, \"old\": false}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:42', '2026-07-22 15:38:42'),
(182, 1, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"status\": {\"new\": \"ditugaskan ke teknisi\", \"old\": \"menunggu kedatangan unit\"}, \"teknisi_id\": {\"new\": 2, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:50', '2026-07-22 15:38:50'),
(183, 2, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"status\": {\"new\": \"dikerjakan teknisi\", \"old\": \"ditugaskan ke teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:56', '2026-07-22 15:38:56'),
(184, 2, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai oleh teknisi\", \"old\": \"dikerjakan teknisi\"}, \"teknisi_selesai_at\": {\"new\": \"2026-07-22 22:38:59\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:38:59', '2026-07-22 15:38:59'),
(185, 1, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai final\", \"old\": \"selesai oleh teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:04', '2026-07-22 15:39:04'),
(186, 1, 'default', 'Memperbarui Service #2', 'App\\Models\\Service', 2, 'updated', '{\"changes\": {\"biaya\": {\"new\": 65000, \"old\": \"65000.00\"}, \"estimasi_peralatan_json\": {\"new\": \"[{\\\"peralatan_id\\\":6,\\\"nama\\\":\\\"Bracket\\\\/Gantungan APAR\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":44,\\\"stok_minimum\\\":8,\\\"harga_standar\\\":25000},{\\\"peralatan_id\\\":5,\\\"nama\\\":\\\"Baut Bracket APAR\\\",\\\"jumlah_per_unit\\\":2,\\\"jumlah\\\":2,\\\"stok\\\":198,\\\"stok_minimum\\\":30,\\\"harga_standar\\\":5000}]\", \"old\": \"[{\\\"peralatan_id\\\":6,\\\"nama\\\":\\\"Bracket\\\\/Gantungan APAR\\\",\\\"jumlah_per_unit\\\":1,\\\"jumlah\\\":1,\\\"stok\\\":45,\\\"stok_minimum\\\":8,\\\"harga_standar\\\":25000},{\\\"peralatan_id\\\":5,\\\"nama\\\":\\\"Baut Bracket APAR\\\",\\\"jumlah_per_unit\\\":2,\\\"jumlah\\\":2,\\\"stok\\\":200,\\\"stok_minimum\\\":30,\\\"harga_standar\\\":5000}]\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:04', '2026-07-22 15:39:04'),
(187, 1, 'default', 'Memperbarui Service #2', 'App\\Models\\Service', 2, 'updated', '{\"changes\": {\"status_konfirmasi\": {\"new\": \"confirmed\", \"old\": \"pending\"}, \"tgl_selesai_admin\": {\"new\": \"2026-07-22 22:39:04\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:04', '2026-07-22 15:39:04'),
(188, 3, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"customer_confirmed_at\": {\"new\": \"2026-07-22 22:39:25\", \"old\": null}, \"customer_confirmed_by\": {\"new\": 3, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:25', '2026-07-22 15:39:25'),
(189, 3, 'default', 'Memperbarui Service #2', 'App\\Models\\Service', 2, 'updated', '{\"changes\": {\"biaya\": {\"new\": 65000, \"old\": \"65000.00\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:25', '2026-07-22 15:39:25'),
(190, 3, 'default', 'Membuat Testimoni #3', 'App\\Models\\Testimoni', 3, 'created', '{\"attributes\": {\"id\": 3, \"rating\": \"5\", \"review\": \"MANTAPPP\", \"status\": \"approved\", \"tanggal\": \"2026-07-22 22:39:51\", \"foto_path\": \"testimonis/kLWaXf7pdwQSrF1EaYbveoF0WA3CBgEEqvhYA7NV.jpg\", \"created_at\": \"2026-07-22 22:39:51\", \"updated_at\": \"2026-07-22 22:39:51\", \"is_anonymous\": false, \"pelanggan_id\": 1, \"transaksi_id\": 3, \"transaksi_type\": \"App\\\\Models\\\\Pesanan\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:51', '2026-07-22 15:39:51'),
(191, 3, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"testimonial_submitted_at\": {\"new\": \"2026-07-22 22:39:51\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:51', '2026-07-22 15:39:51'),
(192, 3, 'feedback', 'testimoni-order:3:pelanggan:1', 'App\\Models\\Testimoni', 3, 'linked_to_order', '{\"order_code\": \"TNTI22072026AJ003\", \"pesanan_id\": 3, \"pelanggan_id\": 1}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:39:51', '2026-07-22 15:39:51'),
(193, NULL, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"keterangan\": {\"new\": \"Permintaan SERVICE Ganti Selang CO2 | Item: Ganti Selang CO2 | Jumlah: 1 unit | Metode: dijemput | Ongkir: Rp722.784 | Catatan: Rincian Service Manual | 1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Selang APAR CO2 x1 | - Segel Pengaman Plastik x1 | Total Service: Rp65.000 | Metode Penanganan: Dijemput | Catatan Pelanggan: test | Riwayat: AKHMAD-22072026-01\", \"old\": \"Permintaan SERVICE Ganti Selang CO2 | Item: Ganti Selang CO2 | Jumlah: 1 unit | Metode: dijemput | Ongkir: Rp722.784 | Catatan: Rincian Service Manual | 1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Selang APAR CO2 x1 | - Segel Pengaman Plastik x1 | Total Service: Rp65.000 | Metode Penanganan: Dijemput | Catatan Pelanggan: test\"}, \"service_keluhan\": {\"new\": \"Rincian Service Manual\\n1. AKHMAD-22072026-01 - Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Selang APAR CO2 x1\\n- Segel Pengaman Plastik x1\\nTotal Service: Rp65.000\\nMetode Penanganan: Dijemput\\nCatatan Pelanggan: test\", \"old\": \"Rincian Service Manual\\n1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Selang APAR CO2 x1\\n- Segel Pengaman Plastik x1\\nTotal Service: Rp65.000\\nMetode Penanganan: Dijemput\\nCatatan Pelanggan: test\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:41:14', '2026-07-22 15:41:14'),
(194, NULL, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"keterangan\": {\"new\": \"Permintaan SERVICE Pasang/Ganti Bracket | Item: Pasang/Ganti Bracket | Jumlah: 1 unit | Metode: antar sendiri | Catatan: Rincian Service Manual | 1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Bracket/Gantungan APAR x1 | - Baut Bracket APAR x2 | Total Service: Rp65.000 | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: - | Riwayat: AKHMAD-22072026-01\", \"old\": \"Permintaan SERVICE Pasang/Ganti Bracket | Item: Pasang/Ganti Bracket | Jumlah: 1 unit | Metode: antar sendiri | Catatan: Rincian Service Manual | 1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Bracket/Gantungan APAR x1 | - Baut Bracket APAR x2 | Total Service: Rp65.000 | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: -\"}, \"service_keluhan\": {\"new\": \"Rincian Service Manual\\n1. AKHMAD-22072026-01 - Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Bracket/Gantungan APAR x1\\n- Baut Bracket APAR x2\\nTotal Service: Rp65.000\\nMetode Penanganan: Antar Sendiri\\nCatatan Pelanggan: -\", \"old\": \"Rincian Service Manual\\n1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Bracket/Gantungan APAR x1\\n- Baut Bracket APAR x2\\nTotal Service: Rp65.000\\nMetode Penanganan: Antar Sendiri\\nCatatan Pelanggan: -\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:41:14', '2026-07-22 15:41:14'),
(195, NULL, 'default', 'Memperbarui Pesanan #2', 'App\\Models\\Pesanan', 2, 'updated', '{\"changes\": {\"keterangan\": {\"new\": \"Permintaan SERVICE | Status Unit: APAR Terdaftar | Riwayat: AKHMAD-22072026-01 Ganti Selang CO2 | Item: Ganti Selang CO2 | Jumlah: 1 unit | Metode: dijemput | Ongkir: Rp722.784 | Catatan: Rincian Service Manual | 1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Selang APAR CO2 x1 | - Segel Pengaman Plastik x1 | Total Service: Rp65.000 | Metode Penanganan: Dijemput | Catatan Pelanggan: test | Riwayat: AKHMAD-22072026-01\", \"old\": \"Permintaan SERVICE Ganti Selang CO2 | Item: Ganti Selang CO2 | Jumlah: 1 unit | Metode: dijemput | Ongkir: Rp722.784 | Catatan: Rincian Service Manual | 1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Selang APAR CO2 x1 | - Segel Pengaman Plastik x1 | Total Service: Rp65.000 | Metode Penanganan: Dijemput | Catatan Pelanggan: test | Riwayat: AKHMAD-22072026-01\"}, \"service_keluhan\": {\"new\": \"Rincian Service APAR Terdaftar\\n1. AKHMAD-22072026-01 - AKHMAD-22072026-01 - Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Selang APAR CO2 x1\\n- Segel Pengaman Plastik x1\\nTotal Service: Rp65.000\\nMetode Penanganan: Dijemput\\nCatatan Pelanggan: test\", \"old\": \"Rincian Service Manual\\n1. AKHMAD-22072026-01 - Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Selang APAR CO2 x1\\n- Segel Pengaman Plastik x1\\nTotal Service: Rp65.000\\nMetode Penanganan: Dijemput\\nCatatan Pelanggan: test\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:42:04', '2026-07-22 15:42:04'),
(196, NULL, 'default', 'Memperbarui Pesanan #3', 'App\\Models\\Pesanan', 3, 'updated', '{\"changes\": {\"keterangan\": {\"new\": \"Permintaan SERVICE | Status Unit: APAR Terdaftar | Riwayat: AKHMAD-22072026-01 Pasang/Ganti Bracket | Item: Pasang/Ganti Bracket | Jumlah: 1 unit | Metode: antar sendiri | Catatan: Rincian Service Manual | 1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Bracket/Gantungan APAR x1 | - Baut Bracket APAR x2 | Total Service: Rp65.000 | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: - | Riwayat: AKHMAD-22072026-01\", \"old\": \"Permintaan SERVICE Pasang/Ganti Bracket | Item: Pasang/Ganti Bracket | Jumlah: 1 unit | Metode: antar sendiri | Catatan: Rincian Service Manual | 1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Bracket/Gantungan APAR x1 | - Baut Bracket APAR x2 | Total Service: Rp65.000 | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: - | Riwayat: AKHMAD-22072026-01\"}, \"service_keluhan\": {\"new\": \"Rincian Service APAR Terdaftar\\n1. AKHMAD-22072026-01 - AKHMAD-22072026-01 - Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Bracket/Gantungan APAR x1\\n- Baut Bracket APAR x2\\nTotal Service: Rp65.000\\nMetode Penanganan: Antar Sendiri\\nCatatan Pelanggan: -\", \"old\": \"Rincian Service Manual\\n1. AKHMAD-22072026-01 - Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\\nPeralatan Paket:\\n- Bracket/Gantungan APAR x1\\n- Baut Bracket APAR x2\\nTotal Service: Rp65.000\\nMetode Penanganan: Antar Sendiri\\nCatatan Pelanggan: -\"}}}', NULL, '127.0.0.1', 'Symfony', '2026-07-22 15:42:04', '2026-07-22 15:42:04'),
(197, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat_lat\": {\"new\": -6.9530028, \"old\": \"-6.95300280\"}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": \"107.63814020\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:51:35', '2026-07-22 15:51:35'),
(198, 3, 'default', 'Membuat Pesanan #4', 'App\\Models\\Pesanan', 4, 'created', '{\"attributes\": {\"id\": 4, \"bank\": \"bca\", \"tipe\": \"service\", \"total\": 1150000, \"ongkir\": 0, \"status\": \"pending\", \"tanggal\": \"2026-07-22 22:51:35\", \"user_id\": 3, \"alamat_lat\": -6.9530028, \"alamat_lng\": 107.6381402, \"created_at\": \"2026-07-22 22:51:36\", \"keterangan\": \"Permintaan REFILL Powder | Item: Powder | Jumlah: 10 unit | Kebutuhan: 50 Kg | Metode: antar sendiri | Catatan: Rincian Refill Manual | 1. Powder | 5 kg | 10 unit - Rp1.150.000 | Total Refill: Rp1.150.000 | Total Kebutuhan Refill: 50 Kg | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: TEST\", \"tipe_harga\": \"normal\", \"updated_at\": \"2026-07-22 22:51:36\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"total_harga\": 1150000, \"pelanggan_id\": 1, \"service_foto\": \"service-request/R9EyiIDWqyjoMcQjdpyMBwdGh6YyumlZrHL5RzlZ.jpg\", \"shipping_etd\": null, \"alamat_detail\": \"Blok A1\", \"sumber_pesanan\": \"website\", \"service_keluhan\": \"Rincian Refill Manual\\n1. Powder | 5 kg | 10 unit - Rp1.150.000\\nTotal Refill: Rp1.150.000\\nTotal Kebutuhan Refill: 50 Kg\\nMetode Penanganan: Antar Sendiri\\nCatatan Pelanggan: TEST\", \"shipping_weight\": 0, \"service_paket_id\": null, \"service_total_kg\": 50, \"shipping_courier\": null, \"shipping_service\": null, \"metode_pengiriman\": \"pickup\", \"service_jenis_apar\": \"Powder\", \"service_jumlah_unit\": 10, \"service_ukuran_apar\": \"5 kg\", \"shipping_distance_km\": null, \"service_admin_catatan\": null, \"service_jenis_layanan\": \"refill\", \"service_estimasi_biaya\": 1150000, \"service_jenis_refill_id\": 1, \"shipping_destination_id\": \"4866\", \"service_metode_penanganan\": \"antar sendiri\", \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:51:36', '2026-07-22 15:51:36'),
(199, 3, 'default', 'Memperbarui Pesanan #4', 'App\\Models\\Pesanan', 4, 'updated', '{\"changes\": {\"status\": {\"new\": \"menunggu kedatangan unit\", \"old\": \"pending\"}, \"bukti_pembayaran\": {\"new\": \"bukti-pembayaran/TWywuE2wHDLNVAzTp2pyDUsVe7exVS4aAfiFY06L.jpg\", \"old\": null}, \"metode_pembayaran\": {\"new\": \"transfer\", \"old\": null}, \"pembayaran_terkonfirmasi_at\": {\"new\": \"2026-07-22 22:51:46\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:51:46', '2026-07-22 15:51:46'),
(200, 3, 'default', 'Memperbarui Pesanan #4', 'App\\Models\\Pesanan', 4, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": true, \"old\": false}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:51:46', '2026-07-22 15:51:46'),
(201, 1, 'default', 'Memperbarui Pesanan #4', 'App\\Models\\Pesanan', 4, 'updated', '{\"changes\": {\"status\": {\"new\": \"ditugaskan ke teknisi\", \"old\": \"menunggu kedatangan unit\"}, \"teknisi_id\": {\"new\": 2, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:52:07', '2026-07-22 15:52:07'),
(202, 2, 'default', 'Memperbarui Pesanan #4', 'App\\Models\\Pesanan', 4, 'updated', '{\"changes\": {\"status\": {\"new\": \"dikerjakan teknisi\", \"old\": \"ditugaskan ke teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:52:17', '2026-07-22 15:52:17'),
(203, 2, 'default', 'Memperbarui Pesanan #4', 'App\\Models\\Pesanan', 4, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai oleh teknisi\", \"old\": \"dikerjakan teknisi\"}, \"teknisi_selesai_at\": {\"new\": \"2026-07-22 22:52:20\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:52:20', '2026-07-22 15:52:20'),
(204, 1, 'default', 'Memperbarui Pesanan #4', 'App\\Models\\Pesanan', 4, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai final\", \"old\": \"selesai oleh teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:52:27', '2026-07-22 15:52:27'),
(205, 1, 'default', 'Membuat Service #3', 'App\\Models\\Service', 3, 'created', '{\"attributes\": {\"id\": 3, \"biaya\": 1150000, \"created_at\": \"2026-07-22 22:52:27\", \"pesanan_id\": 4, \"updated_at\": \"2026-07-22 22:52:27\", \"tgl_service\": \"2026-07-22 00:00:00\", \"unit_apar_id\": null, \"jenis_service\": \"Refill APAR\", \"status_konfirmasi\": \"confirmed\", \"tgl_selesai_admin\": \"2026-07-22 22:52:27\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-22 15:52:27', '2026-07-22 15:52:27'),
(206, NULL, 'default', 'Membuat Pelanggan #2', 'App\\Models\\Pelanggan', 2, 'created', '{\"attributes\": {\"id\": 2, \"nama\": \"test\", \"no_wa\": \"085128008030\", \"status\": \"tetap\", \"user_id\": 4, \"created_at\": \"2026-07-23 09:08:04\", \"updated_at\": \"2026-07-23 09:08:04\", \"sumber_data\": \"manual\", \"kategori_pelanggan\": \"baru_manual\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 02:08:04', '2026-07-23 02:08:04'),
(207, 1, 'default', 'Memperbarui Pelanggan #2', 'App\\Models\\Pelanggan', 2, 'updated', '{\"changes\": {\"user_id\": {\"new\": null, \"old\": 4}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 02:09:45', '2026-07-23 02:09:45'),
(208, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat_lat\": {\"new\": -6.9530028, \"old\": \"-6.95300280\"}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": \"107.63814020\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 03:36:41', '2026-07-23 03:36:41'),
(209, 3, 'default', 'Membuat Pesanan #5', 'App\\Models\\Pesanan', 5, 'created', '{\"attributes\": {\"id\": 5, \"bank\": \"bca\", \"tipe\": \"produk\", \"total\": 0, \"ongkir\": 40000, \"status\": \"pending\", \"is_nego\": false, \"tanggal\": \"2026-07-23 10:36:41\", \"user_id\": 3, \"kode_nego\": null, \"alamat_lat\": -6.9530028, \"alamat_lng\": 107.6381402, \"created_at\": \"2026-07-23 10:36:41\", \"keterangan\": \"Pembelian Produk [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]\", \"tipe_harga\": \"normal\", \"total_awal\": 787500, \"updated_at\": \"2026-07-23 10:36:41\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"approved_at\": null, \"approved_by\": null, \"rejected_at\": null, \"rejected_by\": null, \"harga_normal\": 747500, \"harga_usulan\": null, \"pelanggan_id\": 1, \"shipping_etd\": \"3-4 day\", \"alamat_detail\": \"Blok A1\", \"sumber_pesanan\": \"website\", \"shipping_weight\": 9000, \"shipping_courier\": \"sicepat\", \"shipping_service\": \"GOKIL - Cargo Per Kg (Minimal 10kg)\", \"harga_final_admin\": null, \"metode_pengiriman\": \"diantar_internal\", \"is_pengajuan_harga\": false, \"harga_setelah_diskon\": 747500, \"shipping_distance_km\": null, \"kode_nego_terpakai_at\": null, \"service_admin_catatan\": null, \"shipping_destination_id\": \"4866\", \"status_persetujuan_harga\": null, \"harga_penawaran_pelanggan\": null, \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 03:36:41', '2026-07-23 03:36:41'),
(210, 3, 'default', 'Memperbarui Pesanan #5', 'App\\Models\\Pesanan', 5, 'updated', '{\"changes\": {\"total\": {\"new\": 787500, \"old\": 0}, \"total_harga\": {\"new\": 787500, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 03:36:41', '2026-07-23 03:36:41'),
(211, 3, 'default', 'Memperbarui Pesanan #5', 'App\\Models\\Pesanan', 5, 'updated', '{\"changes\": {\"status\": {\"new\": \"diproses\", \"old\": \"pending\"}, \"bukti_pembayaran\": {\"new\": \"bukti-pembayaran/lqEhcL6hZyzAjD0cqKvnRL2dhShFfZIfSRm6xOw0.jpg\", \"old\": null}, \"metode_pembayaran\": {\"new\": \"transfer\", \"old\": null}, \"pembayaran_terkonfirmasi_at\": {\"new\": \"2026-07-23 10:37:09\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 03:37:09', '2026-07-23 03:37:09'),
(212, 3, 'default', 'Membuat UnitApar #12', 'App\\Models\\UnitApar', 12, 'created', '{\"attributes\": {\"id\": 12, \"bahan\": \"Liquid Foam (Busa)\", \"ukuran\": \"9 kg\", \"no_seri\": \"AKHMAD-23072026-01\", \"tgl_beli\": \"2026-07-23 00:00:00\", \"hidden_at\": \"2026-07-23 10:37:09\", \"produk_id\": 36, \"created_at\": \"2026-07-23 10:37:09\", \"pesanan_id\": 5, \"updated_at\": \"2026-07-23 10:37:09\", \"tgl_expired\": \"2027-07-23 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-07-23 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 03:37:09', '2026-07-23 03:37:09'),
(213, 3, 'default', 'Memperbarui Pesanan #5', 'App\\Models\\Pesanan', 5, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": true, \"old\": false}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-23 03:37:09', '2026-07-23 03:37:09'),
(214, 1, 'default', 'Memperbarui Pesanan #5', 'App\\Models\\Pesanan', 5, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": false, \"old\": true}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 10:08:15', '2026-07-25 10:08:15');
INSERT INTO `activity_logs` (`id`, `user_id`, `log_name`, `description`, `subject_type`, `subject_id`, `event`, `properties`, `batch_uuid`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(215, 1, 'default', 'Menghapus Pesanan #5', 'App\\Models\\Pesanan', 5, 'deleted', '{\"attributes\": {\"id\": 5, \"bank\": \"bca\", \"tipe\": \"produk\", \"total\": 787500, \"ongkir\": \"40000.00\", \"status\": \"diproses\", \"is_nego\": 0, \"jasa_id\": null, \"tanggal\": \"2026-07-23\", \"user_id\": 3, \"kode_nego\": null, \"alamat_lat\": \"-6.95300280\", \"alamat_lng\": \"107.63814020\", \"created_at\": \"2026-07-23 10:36:41\", \"keterangan\": \"Pembelian Produk [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]\", \"no_pesanan\": null, \"teknisi_id\": null, \"tipe_harga\": \"normal\", \"total_awal\": 787500, \"updated_at\": \"2026-07-25 17:08:15\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"approved_at\": null, \"approved_by\": null, \"rejected_at\": null, \"rejected_by\": null, \"total_harga\": \"787500.00\", \"harga_normal\": 747500, \"harga_usulan\": null, \"pelanggan_id\": 1, \"service_foto\": null, \"shipping_etd\": \"3-4 day\", \"tipe_pesanan\": null, \"unit_apar_id\": null, \"alamat_detail\": \"Blok A1\", \"catatan_admin\": null, \"nama_penerima\": null, \"stok_dikurangi\": 0, \"sumber_pesanan\": \"website\", \"is_pesanan_lama\": 0, \"service_keluhan\": null, \"shipping_weight\": 9000, \"teknisi_catatan\": null, \"bukti_pembayaran\": \"bukti-pembayaran/lqEhcL6hZyzAjD0cqKvnRL2dhShFfZIfSRm6xOw0.jpg\", \"invoice_snapshot\": null, \"service_paket_id\": null, \"service_total_kg\": null, \"shipping_courier\": \"sicepat\", \"shipping_service\": \"GOKIL - Cargo Per Kg (Minimal 10kg)\", \"alamat_pengiriman\": null, \"harga_final_admin\": null, \"metode_pembayaran\": \"transfer\", \"metode_pengiriman\": \"diantar_internal\", \"nomor_wa_penerima\": null, \"is_pengajuan_harga\": 0, \"service_jenis_apar\": null, \"teknisi_selesai_at\": null, \"service_jumlah_unit\": null, \"service_ukuran_apar\": null, \"harga_setelah_diskon\": 747500, \"shipping_distance_km\": null, \"customer_confirmed_at\": null, \"customer_confirmed_by\": null, \"kode_nego_terpakai_at\": null, \"service_admin_catatan\": null, \"service_jenis_layanan\": null, \"hidden_from_pesanan_at\": null, \"service_estimasi_biaya\": null, \"service_jenis_refill_id\": null, \"shipping_destination_id\": \"4866\", \"status_persetujuan_harga\": null, \"testimonial_submitted_at\": null, \"harga_penawaran_pelanggan\": null, \"service_metode_penanganan\": null, \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"link_pembayaran_terkirim_at\": null, \"pembayaran_terkonfirmasi_at\": \"2026-07-23 10:37:09\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-25 10:08:15', '2026-07-25 10:08:15'),
(216, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat_lat\": {\"new\": -6.9530028, \"old\": \"-6.95300280\"}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": \"107.63814020\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:39:51', '2026-07-29 04:39:51'),
(217, 3, 'default', 'Membuat Pesanan #6', 'App\\Models\\Pesanan', 6, 'created', '{\"attributes\": {\"id\": 6, \"bank\": \"bca\", \"tipe\": \"produk\", \"total\": 0, \"ongkir\": 21000, \"status\": \"pending\", \"is_nego\": false, \"tanggal\": \"2026-07-29 11:39:51\", \"user_id\": 3, \"kode_nego\": null, \"alamat_lat\": -6.9530028, \"alamat_lng\": 107.6381402, \"created_at\": \"2026-07-29 11:39:51\", \"keterangan\": \"Pembelian Produk [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]\", \"tipe_harga\": \"normal\", \"total_awal\": 471000, \"updated_at\": \"2026-07-29 11:39:51\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"approved_at\": null, \"approved_by\": null, \"rejected_at\": null, \"rejected_by\": null, \"harga_normal\": 450000, \"harga_usulan\": null, \"pelanggan_id\": 1, \"shipping_etd\": \"3-4 day\", \"alamat_detail\": \"Blok A1\", \"sumber_pesanan\": \"website\", \"shipping_weight\": 2000, \"shipping_courier\": \"sicepat\", \"shipping_service\": \"HALU - Harga Mulai Lima Ribu\", \"harga_final_admin\": null, \"metode_pengiriman\": \"diantar_internal\", \"is_pengajuan_harga\": false, \"harga_setelah_diskon\": 450000, \"shipping_distance_km\": null, \"kode_nego_terpakai_at\": null, \"service_admin_catatan\": null, \"shipping_destination_id\": \"4866\", \"status_persetujuan_harga\": null, \"harga_penawaran_pelanggan\": null, \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:39:51', '2026-07-29 04:39:51'),
(218, 3, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"total\": {\"new\": 471000, \"old\": 0}, \"total_harga\": {\"new\": 471000, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:39:51', '2026-07-29 04:39:51'),
(219, 3, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"status\": {\"new\": \"diproses\", \"old\": \"pending\"}, \"bukti_pembayaran\": {\"new\": \"bukti-pembayaran/B9uBCVIJ7r4w8se04Y4ZtTSSztIml9ENZUOzubBU.jpg\", \"old\": null}, \"metode_pembayaran\": {\"new\": \"transfer\", \"old\": null}, \"pembayaran_terkonfirmasi_at\": {\"new\": \"2026-07-29 11:40:05\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:40:05', '2026-07-29 04:40:05'),
(220, 3, 'default', 'Membuat UnitApar #13', 'App\\Models\\UnitApar', 13, 'created', '{\"attributes\": {\"id\": 13, \"bahan\": \"Carbon Dioxide (CO2)\", \"ukuran\": \"2 kg\", \"no_seri\": \"AKHMAD-29072026-01\", \"tgl_beli\": \"2026-07-29 00:00:00\", \"hidden_at\": \"2026-07-29 11:40:05\", \"produk_id\": 19, \"created_at\": \"2026-07-29 11:40:05\", \"pesanan_id\": 6, \"updated_at\": \"2026-07-29 11:40:05\", \"tgl_expired\": \"2027-07-23 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-07-23 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:40:05', '2026-07-29 04:40:05'),
(221, 3, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": true, \"old\": false}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:40:05', '2026-07-29 04:40:05'),
(222, 1, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"status\": {\"new\": \"ditugaskan ke teknisi\", \"old\": \"diproses\"}, \"teknisi_id\": {\"new\": 2, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:40:31', '2026-07-29 04:40:31'),
(223, 2, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"status\": {\"new\": \"dikerjakan teknisi\", \"old\": \"ditugaskan ke teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:40:56', '2026-07-29 04:40:56'),
(224, 2, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai oleh teknisi\", \"old\": \"dikerjakan teknisi\"}, \"teknisi_selesai_at\": {\"new\": \"2026-07-29 11:41:03\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:41:03', '2026-07-29 04:41:03'),
(225, 1, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"status\": {\"new\": \"siap dikirim\", \"old\": \"selesai oleh teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:41:14', '2026-07-29 04:41:14'),
(226, 3, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai final\", \"old\": \"siap dikirim\"}, \"customer_confirmed_at\": {\"new\": \"2026-07-29 11:41:43\", \"old\": null}, \"customer_confirmed_by\": {\"new\": 3, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:41:43', '2026-07-29 04:41:43'),
(227, 3, 'default', 'Memperbarui UnitApar #13', 'App\\Models\\UnitApar', 13, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-07-29T04:40:05.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:41:44', '2026-07-29 04:41:44'),
(228, 3, 'default', 'Membuat Testimoni #4', 'App\\Models\\Testimoni', 4, 'created', '{\"attributes\": {\"id\": 4, \"rating\": \"5\", \"review\": \"BAGUSS\", \"status\": \"approved\", \"tanggal\": \"2026-07-29 11:41:54\", \"foto_path\": \"testimonis/PMZQ6lvfDRaFOGgiBA4SBLWNHiVPdlRGElZd9hd7.jpg\", \"created_at\": \"2026-07-29 11:41:54\", \"updated_at\": \"2026-07-29 11:41:54\", \"is_anonymous\": false, \"pelanggan_id\": 1, \"transaksi_id\": 6, \"transaksi_type\": \"App\\\\Models\\\\Pesanan\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:41:54', '2026-07-29 04:41:54'),
(229, 3, 'default', 'Memperbarui Pesanan #6', 'App\\Models\\Pesanan', 6, 'updated', '{\"changes\": {\"testimonial_submitted_at\": {\"new\": \"2026-07-29 11:41:54\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:41:54', '2026-07-29 04:41:54'),
(230, 3, 'feedback', 'testimoni-order:6:pelanggan:1', 'App\\Models\\Testimoni', 4, 'linked_to_order', '{\"order_code\": \"TNTI29072026AJ006\", \"pesanan_id\": 6, \"pelanggan_id\": 1}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:41:54', '2026-07-29 04:41:54'),
(231, 3, 'default', 'Membuat Complain #1', 'App\\Models\\Complain', 1, 'created', '{\"attributes\": {\"id\": 1, \"tanggal\": \"2026-07-29 11:42:36\", \"foto_path\": \"complains/KPwSjfUorjENKD37jfpgPODHQkKGxw3cBLlJsVZ8.jpg\", \"created_at\": \"2026-07-29 11:42:36\", \"pesanan_id\": 6, \"service_id\": null, \"updated_at\": \"2026-07-29 11:42:36\", \"isi_complain\": \"segel nya lepas\", \"pelanggan_id\": 1}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:42:36', '2026-07-29 04:42:36'),
(232, 1, 'default', 'Memperbarui Complain #1', 'App\\Models\\Complain', 1, 'updated', '{\"changes\": {\"status_penyelesaian\": {\"new\": \"selesai\", \"old\": \"menunggu\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:42:50', '2026-07-29 04:42:50'),
(233, 1, 'default', 'Memperbarui Complain #1', 'App\\Models\\Complain', 1, 'updated', '{\"changes\": {\"status_penyelesaian\": {\"new\": \"diproses\", \"old\": \"selesai\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:42:52', '2026-07-29 04:42:52'),
(234, 1, 'default', 'Memperbarui Testimoni #4', 'App\\Models\\Testimoni', 4, 'updated', '{\"changes\": {\"admin_note\": {\"new\": \"TERIMA KASIH\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', '2026-07-29 04:43:13', '2026-07-29 04:43:13'),
(235, 3, 'default', 'Memperbarui Pelanggan #1', 'App\\Models\\Pelanggan', 1, 'updated', '{\"changes\": {\"alamat_lat\": {\"new\": -6.9530028, \"old\": \"-6.95300280\"}, \"alamat_lng\": {\"new\": 107.6381402, \"old\": \"107.63814020\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 14:50:55', '2026-08-22 14:50:55'),
(236, 3, 'default', 'Membuat Pesanan #7', 'App\\Models\\Pesanan', 7, 'created', '{\"attributes\": {\"id\": 7, \"bank\": \"bca\", \"tipe\": \"produk\", \"total\": 0, \"ongkir\": 40000, \"status\": \"pending\", \"is_nego\": false, \"tanggal\": \"2026-08-22 21:50:55\", \"user_id\": 3, \"kode_nego\": null, \"alamat_lat\": -6.9530028, \"alamat_lng\": 107.6381402, \"created_at\": \"2026-08-22 21:50:55\", \"keterangan\": \"Pembelian Produk [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]\", \"tipe_harga\": \"normal\", \"total_awal\": 787500, \"updated_at\": \"2026-08-22 21:50:55\", \"alamat_maps\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\", \"approved_at\": null, \"approved_by\": null, \"rejected_at\": null, \"rejected_by\": null, \"harga_normal\": 747500, \"harga_usulan\": null, \"pelanggan_id\": 1, \"shipping_etd\": \"3-4 day\", \"alamat_detail\": \"Blok A1\", \"sumber_pesanan\": \"website\", \"shipping_weight\": 9000, \"shipping_courier\": \"sicepat\", \"shipping_service\": \"GOKIL - Cargo Per Kg (Minimal 10kg)\", \"harga_final_admin\": null, \"metode_pengiriman\": \"diantar_internal\", \"is_pengajuan_harga\": false, \"harga_setelah_diskon\": 747500, \"shipping_distance_km\": null, \"kode_nego_terpakai_at\": null, \"service_admin_catatan\": null, \"shipping_destination_id\": \"4866\", \"status_persetujuan_harga\": null, \"harga_penawaran_pelanggan\": null, \"shipping_destination_label\": \"BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 14:50:55', '2026-08-22 14:50:55'),
(237, 3, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"total\": {\"new\": 787500, \"old\": 0}, \"total_harga\": {\"new\": 787500, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 14:50:55', '2026-08-22 14:50:55'),
(238, 3, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"status\": {\"new\": \"diproses\", \"old\": \"pending\"}, \"bukti_pembayaran\": {\"new\": \"bukti-pembayaran/peEnqUXxcGz5y5pLD8PUgqGd80U1TMmTriWiS8RO.png\", \"old\": null}, \"metode_pembayaran\": {\"new\": \"transfer\", \"old\": null}, \"pembayaran_terkonfirmasi_at\": {\"new\": \"2026-08-22 21:56:32\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 14:56:32', '2026-08-22 14:56:32'),
(239, 3, 'default', 'Membuat UnitApar #14', 'App\\Models\\UnitApar', 14, 'created', '{\"attributes\": {\"id\": 14, \"bahan\": \"Liquid Foam (Busa)\", \"ukuran\": \"9 kg\", \"no_seri\": \"AKHMAD-22082026-01\", \"tgl_beli\": \"2026-08-22 00:00:00\", \"hidden_at\": \"2026-08-22 21:56:33\", \"produk_id\": 36, \"created_at\": \"2026-08-22 21:56:33\", \"pesanan_id\": 7, \"updated_at\": \"2026-08-22 21:56:33\", \"tgl_expired\": \"2027-07-23 00:00:00\", \"pelanggan_id\": 1, \"tgl_produksi\": \"2026-07-23 00:00:00\"}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 14:56:33', '2026-08-22 14:56:33'),
(240, 3, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"stok_dikurangi\": {\"new\": true, \"old\": false}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 14:56:33', '2026-08-22 14:56:33'),
(241, 1, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"status\": {\"new\": \"ditugaskan ke teknisi\", \"old\": \"diproses\"}, \"teknisi_id\": {\"new\": 2, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 15:00:53', '2026-08-22 15:00:53'),
(242, 2, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"status\": {\"new\": \"dikerjakan teknisi\", \"old\": \"ditugaskan ke teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-22 15:01:27', '2026-08-22 15:01:27'),
(243, 2, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai oleh teknisi\", \"old\": \"dikerjakan teknisi\"}, \"teknisi_selesai_at\": {\"new\": \"2026-08-22 22:01:31\", \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', '2026-08-22 15:01:31', '2026-08-22 15:01:31'),
(244, 1, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"status\": {\"new\": \"siap dikirim\", \"old\": \"selesai oleh teknisi\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 15:01:38', '2026-08-22 15:01:38'),
(245, 3, 'default', 'Memperbarui Pesanan #7', 'App\\Models\\Pesanan', 7, 'updated', '{\"changes\": {\"status\": {\"new\": \"selesai final\", \"old\": \"siap dikirim\"}, \"customer_confirmed_at\": {\"new\": \"2026-08-22 22:01:51\", \"old\": null}, \"customer_confirmed_by\": {\"new\": 3, \"old\": null}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 15:01:51', '2026-08-22 15:01:51'),
(246, 3, 'default', 'Memperbarui UnitApar #14', 'App\\Models\\UnitApar', 14, 'updated', '{\"changes\": {\"hidden_at\": {\"new\": null, \"old\": \"2026-08-22T14:56:33.000000Z\"}}}', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', '2026-08-22 15:01:51', '2026-08-22 15:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `complains`
--

CREATE TABLE `complains` (
  `id` bigint UNSIGNED NOT NULL,
  `pelanggan_id` bigint UNSIGNED NOT NULL,
  `pesanan_id` bigint UNSIGNED DEFAULT NULL,
  `service_id` bigint UNSIGNED DEFAULT NULL,
  `isi_complain` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_penyelesaian` enum('menunggu','diproses','selesai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `tanggal` date NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `complains`
--

INSERT INTO `complains` (`id`, `pelanggan_id`, `pesanan_id`, `service_id`, `isi_complain`, `foto_path`, `status_penyelesaian`, `tanggal`, `created_at`, `updated_at`) VALUES
(1, 1, 6, NULL, 'segel nya lepas', 'complains/KPwSjfUorjENKD37jfpgPODHQkKGxw3cBLlJsVZ8.jpg', 'diproses', '2026-07-29', '2026-07-29 04:42:36', '2026-07-29 04:42:52');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jasa`
--

CREATE TABLE `jasa` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_jasa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `harga` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jasa`
--

INSERT INTO `jasa` (`id`, `nama_jasa`, `deskripsi`, `harga`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Service Ringan', 'Pengecekan ringan unit APAR.\r\nPenggantian safety pin APAR.\r\nPenggantian segel pengaman plastik.', '35000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(2, 'Service Standar', 'Inspeksi kondisi fisik tabung, segel, dan pin pengaman\nPemeriksaan selang, nozzle, dan valve\nPembersihan body tabung dan area kepala APAR\nPenggantian safety pin dan segel pengaman plastik sesuai standar paket', '90000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(3, 'Service Lengkap', 'Pembongkaran komponen utama APAR\nPemeriksaan valve, selang, nozzle, dan tekanan kerja\nPenggantian valve, safety pin, dan segel pengaman plastik sesuai standar paket\nPembersihan menyeluruh dan uji visual kebocoran ringan', '150000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(4, 'Ganti Selang Powder/Foam', 'Penggantian selang APAR Powder/Foam.\r\nPenggantian segel pengaman plastik.', '70000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(5, 'Ganti Selang CO2', 'Penggantian selang APAR CO2.\r\nPenggantian segel pengaman plastik.', '160000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(6, 'Ganti Valve APAR', 'Penggantian valve APAR.\r\nPenggantian O-Ring/karet seal.\r\nPenggantian segel pengaman plastik.', '100000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(7, 'Ganti Pressure Gauge', 'Penggantian pressure gauge APAR.\r\nPenggantian O-Ring/karet seal.\r\nPenggantian segel pengaman plastik.', '65000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(8, 'Pasang/Ganti Bracket', 'Pemasangan atau penggantian bracket APAR.\r\nPemasangan baut bracket APAR.', '60000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL),
(10, 'Pemasangan Unit', 'test', '100000.00', 'aktif', '2026-07-28 16:20:19', '2026-07-28 16:20:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jenis_apars`
--

CREATE TABLE `jenis_apars` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_apars`
--

INSERT INTO `jenis_apars` (`id`, `nama`, `created_at`, `updated_at`) VALUES
(1, 'Dry Chemical Powder', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(2, 'Carbon Dioxide (CO2)', '2026-07-22 14:41:02', '2026-07-22 14:41:02'),
(3, 'Liquid Foam (Busa)', '2026-07-22 14:41:02', '2026-07-22 14:41:02');

-- --------------------------------------------------------

--
-- Table structure for table `jenis_refills`
--

CREATE TABLE `jenis_refills` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stok` decimal(12,2) NOT NULL DEFAULT '0.00',
  `satuan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kg',
  `harga` bigint UNSIGNED NOT NULL DEFAULT '0',
  `service_price_rules_json` text COLLATE utf8mb4_unicode_ci,
  `stok_minimum` decimal(12,2) NOT NULL DEFAULT '5.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_refills`
--

INSERT INTO `jenis_refills` (`id`, `nama`, `stok`, `satuan`, `harga`, `service_price_rules_json`, `stok_minimum`, `created_at`, `updated_at`) VALUES
(1, 'Dry Chemical Powder', '500.00', 'Kg', 25000, '[{\"ukuran\":\"1 Kg\",\"harga\":30000},{\"ukuran\":\"2 Kg\",\"harga\":50000},{\"ukuran\":\"3 Kg\",\"harga\":70000},{\"ukuran\":\"4 Kg\",\"harga\":90000},{\"ukuran\":\"5 Kg\",\"harga\":115000},{\"ukuran\":\"6 Kg\",\"harga\":135000},{\"ukuran\":\"6.8 Kg\",\"harga\":150000},{\"ukuran\":\"9 Kg\",\"harga\":190000},{\"ukuran\":\"6 kg\",\"harga\":150000},{\"ukuran\":\"9 kg\",\"harga\":210000}]', '50.00', '2026-07-22 14:41:02', '2026-07-24 15:58:20'),
(2, 'CO2', '350.00', 'Kg', 35000, '[{\"ukuran\":\"1 Kg\",\"harga\":45000},{\"ukuran\":\"2 Kg\",\"harga\":65000},{\"ukuran\":\"3 Kg\",\"harga\":85000},{\"ukuran\":\"4 Kg\",\"harga\":110000},{\"ukuran\":\"5 Kg\",\"harga\":130000},{\"ukuran\":\"6 Kg\",\"harga\":150000},{\"ukuran\":\"6.8 Kg\",\"harga\":170000},{\"ukuran\":\"9 Kg\",\"harga\":230000},{\"ukuran\":\"6 kg\",\"harga\":160000},{\"ukuran\":\"9 kg\",\"harga\":235000}]', '30.00', '2026-07-22 14:41:02', '2026-07-29 04:30:17'),
(3, 'Foam', '500.00', 'L', 20000, '[{\"ukuran\":\"1 Kg\",\"harga\":35000},{\"ukuran\":\"2 Kg\",\"harga\":60000},{\"ukuran\":\"3 Kg\",\"harga\":80000},{\"ukuran\":\"4 Kg\",\"harga\":100000},{\"ukuran\":\"5 Kg\",\"harga\":125000},{\"ukuran\":\"6 Kg\",\"harga\":145000},{\"ukuran\":\"6.8 Kg\",\"harga\":165000},{\"ukuran\":\"9 Kg\",\"harga\":210000},{\"ukuran\":\"6 kg\",\"harga\":160000},{\"ukuran\":\"9 kg\",\"harga\":225000}]', '40.00', '2026-07-22 14:41:02', '2026-07-22 15:21:39');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(1, 'default', '{\"uuid\":\"41731d5b-156d-4132-8886-f44fd9d126b4\",\"displayName\":\"App\\\\Events\\\\PesananBaru\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\PesananBaru\\\":1:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:1;s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:6:\\\"status\\\";s:20:\\\"menunggu persetujuan\\\";s:6:\\\"sumber\\\";s:7:\\\"website\\\";s:9:\\\"pelanggan\\\";a:2:{s:4:\\\"nama\\\";s:14:\\\"Akhmad Rizaldy\\\";s:5:\\\"no_wa\\\";s:12:\\\"087830665027\\\";}s:5:\\\"total\\\";d:5261100;s:12:\\\"detail_count\\\";i:3;s:7:\\\"tanggal\\\";s:11:\\\"22 Jul 2026\\\";s:10:\\\"created_at\\\";s:25:\\\"2026-07-22T21:47:48+07:00\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784731668,\"delay\":null}', 0, NULL, 1784731668, 1784731668),
(2, 'default', '{\"uuid\":\"6ea787a4-6c04-4b26-a866-a8c52d9f320f\",\"displayName\":\"App\\\\Events\\\\StatusPesananDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:34:\\\"App\\\\Events\\\\StatusPesananDiperbarui\\\":2:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:1;s:6:\\\"status\\\";s:9:\\\"disetujui\\\";s:12:\\\"status_label\\\";s:30:\\\"Harga Disetujui - Siap Dibayar\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:12:\\\"pelanggan_id\\\";i:1;s:5:\\\"total\\\";d:4500000;s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:10:\\\"teknisi_id\\\";i:0;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T21:48:56+07:00\\\";}s:9:\\\"teknisiId\\\";i:0;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784731736,\"delay\":null}', 0, NULL, 1784731736, 1784731736),
(3, 'default', '{\"uuid\":\"a44dc618-9c2e-45eb-99d8-c3a1de630459\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:1;s:6:\\\"status\\\";s:21:\\\"ditugaskan ke teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T21:52:45+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784731965,\"delay\":null}', 0, NULL, 1784731965, 1784731965),
(4, 'default', '{\"uuid\":\"33c1f82c-72e0-413a-9d61-dbe3fb99cde2\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:1;s:6:\\\"status\\\";s:18:\\\"dikerjakan teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T21:53:43+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784732023,\"delay\":null}', 0, NULL, 1784732023, 1784732023),
(5, 'default', '{\"uuid\":\"6ba39cac-9239-4253-9110-0dc7ce88dc4d\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:1;s:6:\\\"status\\\";s:20:\\\"selesai oleh teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T21:53:46+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784732026,\"delay\":null}', 0, NULL, 1784732026, 1784732026),
(6, 'default', '{\"uuid\":\"5327e554-383a-4330-bf1c-f017b7f1330f\",\"displayName\":\"App\\\\Events\\\\PesananBaru\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\PesananBaru\\\":1:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:2;s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";s:6:\\\"sumber\\\";s:7:\\\"website\\\";s:9:\\\"pelanggan\\\";a:2:{s:4:\\\"nama\\\";s:14:\\\"Akhmad Rizaldy\\\";s:5:\\\"no_wa\\\";s:12:\\\"087830665027\\\";}s:5:\\\"total\\\";d:787784;s:12:\\\"detail_count\\\";i:0;s:7:\\\"tanggal\\\";s:11:\\\"22 Jul 2026\\\";s:10:\\\"created_at\\\";s:25:\\\"2026-07-22T22:23:51+07:00\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784733831,\"delay\":null}', 0, NULL, 1784733831, 1784733831),
(7, 'default', '{\"uuid\":\"c46b7169-cd2b-453e-8ace-f07bad94d141\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:2;s:6:\\\"status\\\";s:21:\\\"ditugaskan ke teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:24:08+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784733848,\"delay\":null}', 0, NULL, 1784733848, 1784733848),
(8, 'default', '{\"uuid\":\"b19ab770-5fa7-4045-a7b1-7653cd3448bf\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:2;s:6:\\\"status\\\";s:18:\\\"dikerjakan teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:24:15+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784733855,\"delay\":null}', 0, NULL, 1784733855, 1784733855),
(9, 'default', '{\"uuid\":\"34746337-09d6-4238-9713-804e4c65761d\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:2;s:6:\\\"status\\\";s:20:\\\"selesai oleh teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:24:18+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784733858,\"delay\":null}', 0, NULL, 1784733858, 1784733858),
(10, 'default', '{\"uuid\":\"cf1d2858-a078-4450-a963-23973917864a\",\"displayName\":\"App\\\\Events\\\\PesananBaru\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\PesananBaru\\\":1:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:3;s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";s:6:\\\"sumber\\\";s:7:\\\"website\\\";s:9:\\\"pelanggan\\\";a:2:{s:4:\\\"nama\\\";s:14:\\\"Akhmad Rizaldy\\\";s:5:\\\"no_wa\\\";s:12:\\\"087830665027\\\";}s:5:\\\"total\\\";d:65000;s:12:\\\"detail_count\\\";i:0;s:7:\\\"tanggal\\\";s:11:\\\"22 Jul 2026\\\";s:10:\\\"created_at\\\";s:25:\\\"2026-07-22T22:38:36+07:00\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784734716,\"delay\":null}', 0, NULL, 1784734716, 1784734716),
(11, 'default', '{\"uuid\":\"90b6c83d-c44e-4a22-a4b5-6ceb64c00b2b\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:3;s:6:\\\"status\\\";s:21:\\\"ditugaskan ke teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:38:50+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784734730,\"delay\":null}', 0, NULL, 1784734730, 1784734730),
(12, 'default', '{\"uuid\":\"bb54e8b3-07c2-4985-8147-f2714fccf10b\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:3;s:6:\\\"status\\\";s:18:\\\"dikerjakan teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:38:56+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784734736,\"delay\":null}', 0, NULL, 1784734736, 1784734736),
(13, 'default', '{\"uuid\":\"1bed3684-ff49-4a4c-853a-fe6074cf4cfa\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:3;s:6:\\\"status\\\";s:20:\\\"selesai oleh teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:38:59+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784734739,\"delay\":null}', 0, NULL, 1784734739, 1784734739),
(14, 'default', '{\"uuid\":\"3e73bec7-e71b-4025-9dbc-717f92afad6a\",\"displayName\":\"App\\\\Events\\\\PesananBaru\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\PesananBaru\\\":1:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:4;s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";s:6:\\\"sumber\\\";s:7:\\\"website\\\";s:9:\\\"pelanggan\\\";a:2:{s:4:\\\"nama\\\";s:14:\\\"Akhmad Rizaldy\\\";s:5:\\\"no_wa\\\";s:12:\\\"087830665027\\\";}s:5:\\\"total\\\";d:1150000;s:12:\\\"detail_count\\\";i:0;s:7:\\\"tanggal\\\";s:11:\\\"22 Jul 2026\\\";s:10:\\\"created_at\\\";s:25:\\\"2026-07-22T22:51:36+07:00\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784735496,\"delay\":null}', 0, NULL, 1784735496, 1784735496),
(15, 'default', '{\"uuid\":\"ac49dda2-b026-4b67-acaa-88eee038d5c9\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:4;s:6:\\\"status\\\";s:21:\\\"ditugaskan ke teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:52:07+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784735527,\"delay\":null}', 0, NULL, 1784735527, 1784735527),
(16, 'default', '{\"uuid\":\"f451099a-c5c7-42cf-a5ba-0eec3bc6f5fd\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:4;s:6:\\\"status\\\";s:18:\\\"dikerjakan teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:52:17+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784735537,\"delay\":null}', 0, NULL, 1784735537, 1784735537),
(17, 'default', '{\"uuid\":\"1c2e6b14-f27d-41bf-be8d-0dccfc2b16b6\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:4;s:6:\\\"status\\\";s:20:\\\"selesai oleh teknisi\\\";s:4:\\\"tipe\\\";s:7:\\\"service\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-22T22:52:20+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784735540,\"delay\":null}', 0, NULL, 1784735540, 1784735540),
(18, 'default', '{\"uuid\":\"7664ba9c-9d75-4e9e-a9ba-2568b39fdc90\",\"displayName\":\"App\\\\Events\\\\PesananBaru\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\PesananBaru\\\":1:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:5;s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";s:6:\\\"sumber\\\";s:7:\\\"website\\\";s:9:\\\"pelanggan\\\";a:2:{s:4:\\\"nama\\\";s:14:\\\"Akhmad Rizaldy\\\";s:5:\\\"no_wa\\\";s:12:\\\"087830665027\\\";}s:5:\\\"total\\\";d:787500;s:12:\\\"detail_count\\\";i:1;s:7:\\\"tanggal\\\";s:11:\\\"23 Jul 2026\\\";s:10:\\\"created_at\\\";s:25:\\\"2026-07-23T10:36:41+07:00\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1784777801,\"delay\":null}', 0, NULL, 1784777801, 1784777801),
(19, 'default', '{\"uuid\":\"d94b08bb-b8b3-496c-8dfb-258ad11b8133\",\"displayName\":\"App\\\\Events\\\\PesananBaru\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\PesananBaru\\\":1:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:6;s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";s:6:\\\"sumber\\\";s:7:\\\"website\\\";s:9:\\\"pelanggan\\\";a:2:{s:4:\\\"nama\\\";s:14:\\\"Akhmad Rizaldy\\\";s:5:\\\"no_wa\\\";s:12:\\\"087830665027\\\";}s:5:\\\"total\\\";d:471000;s:12:\\\"detail_count\\\";i:1;s:7:\\\"tanggal\\\";s:11:\\\"29 Jul 2026\\\";s:10:\\\"created_at\\\";s:25:\\\"2026-07-29T11:39:51+07:00\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1785299992,\"delay\":null}', 0, NULL, 1785299992, 1785299992),
(20, 'default', '{\"uuid\":\"d2bd3703-0f0d-4288-b8d3-58bc6dd1d65e\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:6;s:6:\\\"status\\\";s:21:\\\"ditugaskan ke teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-29T11:40:31+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1785300031,\"delay\":null}', 0, NULL, 1785300031, 1785300031),
(21, 'default', '{\"uuid\":\"c2a4932f-50ef-45d9-b41a-ed0186aa5255\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:6;s:6:\\\"status\\\";s:18:\\\"dikerjakan teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-29T11:40:56+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1785300056,\"delay\":null}', 0, NULL, 1785300056, 1785300056),
(22, 'default', '{\"uuid\":\"4edfe54a-a9fb-47df-8b9c-91e8de8e52b6\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:6;s:6:\\\"status\\\";s:20:\\\"selesai oleh teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-07-29T11:41:03+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1785300063,\"delay\":null}', 0, NULL, 1785300063, 1785300063),
(23, 'default', '{\"uuid\":\"be39734d-db0d-4472-a761-d2a07e9af4c4\",\"displayName\":\"App\\\\Events\\\\PesananBaru\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\PesananBaru\\\":1:{s:7:\\\"payload\\\";a:9:{s:2:\\\"id\\\";i:7;s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:6:\\\"status\\\";s:7:\\\"pending\\\";s:6:\\\"sumber\\\";s:7:\\\"website\\\";s:9:\\\"pelanggan\\\";a:2:{s:4:\\\"nama\\\";s:14:\\\"Akhmad Rizaldy\\\";s:5:\\\"no_wa\\\";s:12:\\\"087830665027\\\";}s:5:\\\"total\\\";d:787500;s:12:\\\"detail_count\\\";i:1;s:7:\\\"tanggal\\\";s:11:\\\"22 Aug 2026\\\";s:10:\\\"created_at\\\";s:25:\\\"2026-08-22T21:50:55+07:00\\\";}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1787410255,\"delay\":null}', 0, NULL, 1787410255, 1787410255),
(24, 'default', '{\"uuid\":\"673da031-626c-4826-a6a4-63d1a77ace42\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:7;s:6:\\\"status\\\";s:21:\\\"ditugaskan ke teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-08-22T22:00:53+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1787410853,\"delay\":null}', 0, NULL, 1787410853, 1787410853),
(25, 'default', '{\"uuid\":\"f53862c5-efc0-4971-928e-7aead0af6b30\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:7;s:6:\\\"status\\\";s:18:\\\"dikerjakan teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-08-22T22:01:27+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1787410887,\"delay\":null}', 0, NULL, 1787410887, 1787410887),
(26, 'default', '{\"uuid\":\"b5afa9c8-be77-4780-838e-5d9736ee1161\",\"displayName\":\"App\\\\Events\\\\TugasTeknisiDiperbarui\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:33:\\\"App\\\\Events\\\\TugasTeknisiDiperbarui\\\":2:{s:7:\\\"payload\\\";a:6:{s:2:\\\"id\\\";i:7;s:6:\\\"status\\\";s:20:\\\"selesai oleh teknisi\\\";s:4:\\\"tipe\\\";s:6:\\\"produk\\\";s:9:\\\"pelanggan\\\";s:14:\\\"Akhmad Rizaldy\\\";s:10:\\\"teknisi_id\\\";i:2;s:10:\\\"updated_at\\\";s:25:\\\"2026-08-22T22:01:31+07:00\\\";}s:9:\\\"teknisiId\\\";i:2;}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1787410891,\"delay\":null}', 0, NULL, 1787410891, 1787410891);

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
(1, '0001_01_01_000000_create_users_and_sessions_tables', 1),
(2, '0001_01_01_000001_create_cache_tables', 1),
(3, '0001_01_01_000002_create_job_tables', 1),
(4, '2026_05_11_000100_create_master_data_tables', 1),
(5, '2026_05_11_000200_create_sales_and_customer_asset_tables', 1),
(6, '2026_05_11_000300_create_service_inventory_and_support_tables', 1),
(7, '2026_05_25_100826_create_website_visits_table', 1),
(8, '2026_05_25_103303_add_tracking_columns_to_website_visits_table', 1),
(9, '2026_06_18_000100_add_hidden_from_pesanan_at_to_pesanans_table', 1),
(10, '2026_06_18_000200_add_hidden_at_to_unit_apars_table', 1),
(11, '2026_06_18_000300_add_rajaongkir_and_customer_feedback_columns', 1),
(12, '2026_06_19_000400_add_purchase_price_workflow_columns_to_pesanans', 1),
(13, '2026_06_19_000500_add_siap_dikirim_to_pesanan_status_enum', 1),
(14, '2026_07_24_000001_create_suppliers_table', 2),
(15, '2026_07_24_000002_create_purchase_orders_table', 3),
(16, '2026_07_24_000003_create_purchase_order_details_table', 3),
(17, '2026_07_24_000004_update_stok_batches_table', 3),
(18, '2026_07_24_000005_create_jasa_table', 4),
(19, '2026_07_24_000006_add_tipe_pesanan_to_pesanans_table', 5),
(20, '2026_07_25_000001_drop_obsolete_tables', 6),
(21, '2026_07_28_100000_restore_jasa_table_and_relations', 7);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_reset_tokens`
--

INSERT INTO `password_reset_tokens` (`email`, `token`, `created_at`) VALUES
('admin@gmail.com', '$2y$12$kGKGQyCzHTYhdQNR4IKaSeMtvWVOVaAVuJ.BQJJl977boq23eRxX6', '2026-07-25 10:34:25');

-- --------------------------------------------------------

--
-- Table structure for table `pelanggans`
--

CREATE TABLE `pelanggans` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perusahaan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_wa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `alamat_maps` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_detail` text COLLATE utf8mb4_unicode_ci,
  `alamat_lat` decimal(11,8) DEFAULT NULL,
  `alamat_lng` decimal(11,8) DEFAULT NULL,
  `alamat_provinsi` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_kota` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_kecamatan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_kode_pos` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rajaongkir_destination_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rajaongkir_destination_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('calon','tetap') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'calon',
  `sumber_data` enum('manual','whatsapp','telepon','arsip_lama') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `kategori_pelanggan` enum('lama','baru_manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lama',
  `catatan_internal` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelanggans`
--

INSERT INTO `pelanggans` (`id`, `user_id`, `nama`, `perusahaan`, `no_wa`, `alamat`, `alamat_maps`, `alamat_detail`, `alamat_lat`, `alamat_lng`, `alamat_provinsi`, `alamat_kota`, `alamat_kecamatan`, `alamat_kode_pos`, `rajaongkir_destination_id`, `rajaongkir_destination_label`, `status`, `sumber_data`, `kategori_pelanggan`, `catatan_internal`, `created_at`, `updated_at`) VALUES
(1, 3, 'Akhmad Rizaldy', NULL, '087830665027', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266 | Detail: Blok A1', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'Blok A1', '-6.95300280', '107.63814020', 'JAWA BARAT', 'BANDUNG', 'BATUNUNGGAL', '40266', '4866', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'tetap', 'manual', 'lama', NULL, '2026-07-22 14:41:06', '2026-08-22 14:50:55'),
(2, NULL, 'test', NULL, '085128008030', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'tetap', 'manual', 'baru_manual', NULL, '2026-07-23 02:08:04', '2026-07-23 02:09:45');

-- --------------------------------------------------------

--
-- Table structure for table `peralatans`
--

CREATE TABLE `peralatans` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stok` int UNSIGNED NOT NULL DEFAULT '0',
  `stok_minimum` int UNSIGNED NOT NULL DEFAULT '3',
  `harga_standar` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peralatans`
--

INSERT INTO `peralatans` (`id`, `nama`, `stok`, `stok_minimum`, `harga_standar`, `created_at`, `updated_at`) VALUES
(1, 'Valve APAR', 20, 3, '35000.00', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(2, 'Safety Pin APAR', 170, 10, '5000.00', '2026-07-22 14:41:03', '2026-07-22 14:42:34'),
(3, 'Segel Pengaman Plastik', 50, 10, '3000.00', '2026-07-22 14:41:03', '2026-07-24 19:28:31'),
(4, 'Selang APAR Powder/Foam', 50, 10, '35000.00', '2026-07-22 14:41:15', '2026-07-22 14:42:34'),
(5, 'Baut Bracket APAR', 198, 30, '5000.00', '2026-07-22 14:41:15', '2026-07-22 15:38:42'),
(6, 'Bracket/Gantungan APAR', 44, 8, '25000.00', '2026-07-22 14:41:15', '2026-07-22 15:38:42'),
(7, 'Safety Pin (Pin Pengaman)', 0, 20, '8000.00', '2026-07-22 14:41:15', '2026-07-22 14:42:34'),
(8, 'Nozzle Corong CO2', 25, 5, '45000.00', '2026-07-22 14:41:15', '2026-07-22 14:41:15'),
(9, 'Selang APAR CO2', 9, 3, '120000.00', '2026-07-22 14:42:34', '2026-07-22 15:23:58'),
(10, 'Pressure Gauge APAR', 0, 3, '20000.00', '2026-07-22 14:42:34', '2026-07-22 14:42:34'),
(11, 'O-Ring/Karet Seal', 0, 5, '5000.00', '2026-07-22 14:42:34', '2026-07-22 14:42:34'),
(12, '', 0, 51, '0.00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pesanans`
--

CREATE TABLE `pesanans` (
  `id` bigint UNSIGNED NOT NULL,
  `no_pesanan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pelanggan_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `nama_penerima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_wa_penerima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_pengiriman` text COLLATE utf8mb4_unicode_ci,
  `teknisi_id` bigint UNSIGNED DEFAULT NULL,
  `teknisi_selesai_at` timestamp NULL DEFAULT NULL,
  `teknisi_catatan` text COLLATE utf8mb4_unicode_ci,
  `tipe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'produk',
  `tipe_pesanan` enum('jual_produk','refill','jasa') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jasa_id` bigint UNSIGNED DEFAULT NULL,
  `unit_apar_id` bigint UNSIGNED DEFAULT NULL,
  `sumber_pesanan` enum('website','whatsapp','telepon','datang_langsung','input_admin','data_lama') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'input_admin',
  `is_pesanan_lama` tinyint(1) NOT NULL DEFAULT '0',
  `service_jenis_layanan` enum('service','refill') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_paket_id` bigint UNSIGNED DEFAULT NULL,
  `service_jenis_refill_id` bigint UNSIGNED DEFAULT NULL,
  `service_jenis_apar` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_ukuran_apar` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_jumlah_unit` int UNSIGNED DEFAULT NULL,
  `service_total_kg` decimal(12,2) DEFAULT NULL,
  `service_keluhan` text COLLATE utf8mb4_unicode_ci,
  `service_foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_metode_penanganan` enum('dijemput','antar sendiri','survey lokasi') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `service_estimasi_biaya` decimal(15,2) DEFAULT NULL,
  `service_admin_catatan` text COLLATE utf8mb4_unicode_ci,
  `total` bigint UNSIGNED NOT NULL,
  `total_harga` decimal(15,2) DEFAULT NULL,
  `tipe_harga` enum('deal','normal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `metode_pembayaran` enum('transfer','cash') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metode_pengiriman` enum('pickup','diantar_internal') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pickup',
  `ongkir` decimal(15,2) NOT NULL DEFAULT '0.00',
  `shipping_courier` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_service` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_etd` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_destination_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_destination_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_weight` int UNSIGNED DEFAULT NULL,
  `shipping_distance_km` decimal(10,2) DEFAULT NULL,
  `alamat_maps` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_detail` text COLLATE utf8mb4_unicode_ci,
  `alamat_lat` decimal(11,8) DEFAULT NULL,
  `alamat_lng` decimal(11,8) DEFAULT NULL,
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_pembayaran_terkirim_at` timestamp NULL DEFAULT NULL,
  `pembayaran_terkonfirmasi_at` timestamp NULL DEFAULT NULL,
  `customer_confirmed_at` timestamp NULL DEFAULT NULL,
  `customer_confirmed_by` bigint UNSIGNED DEFAULT NULL,
  `testimonial_submitted_at` timestamp NULL DEFAULT NULL,
  `harga_usulan` decimal(15,2) DEFAULT NULL,
  `harga_penawaran_pelanggan` decimal(15,2) DEFAULT NULL,
  `kode_nego` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kode_nego_terpakai_at` timestamp NULL DEFAULT NULL,
  `is_nego` tinyint(1) NOT NULL DEFAULT '0',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `catatan_admin` text COLLATE utf8mb4_unicode_ci,
  `status` enum('menunggu','menunggu persetujuan','pending','diproses','selesai','ditolak','menunggu diproses admin','ditugaskan ke teknisi','dikerjakan teknisi','selesai oleh teknisi','dikonfirmasi admin','selesai final','permintaan masuk','direview admin','menunggu penjadwalan','menunggu persetujuan biaya','disetujui','menunggu pengambilan','menunggu kedatangan unit','siap dikirim') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'menunggu',
  `stok_dikurangi` tinyint(1) NOT NULL DEFAULT '0',
  `hidden_from_pesanan_at` timestamp NULL DEFAULT NULL,
  `tanggal` date NOT NULL,
  `invoice_snapshot` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `harga_normal` double DEFAULT NULL,
  `harga_setelah_diskon` double DEFAULT NULL,
  `total_awal` double DEFAULT NULL,
  `harga_final_admin` double DEFAULT NULL,
  `status_persetujuan_harga` varchar(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint UNSIGNED DEFAULT NULL,
  `rejected_at` timestamp NULL DEFAULT NULL,
  `rejected_by` bigint UNSIGNED DEFAULT NULL,
  `is_pengajuan_harga` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanans`
--

INSERT INTO `pesanans` (`id`, `no_pesanan`, `pelanggan_id`, `user_id`, `nama_penerima`, `nomor_wa_penerima`, `alamat_pengiriman`, `teknisi_id`, `teknisi_selesai_at`, `teknisi_catatan`, `tipe`, `tipe_pesanan`, `jasa_id`, `unit_apar_id`, `sumber_pesanan`, `is_pesanan_lama`, `service_jenis_layanan`, `service_paket_id`, `service_jenis_refill_id`, `service_jenis_apar`, `service_ukuran_apar`, `service_jumlah_unit`, `service_total_kg`, `service_keluhan`, `service_foto`, `service_metode_penanganan`, `service_estimasi_biaya`, `service_admin_catatan`, `total`, `total_harga`, `tipe_harga`, `metode_pembayaran`, `bank`, `metode_pengiriman`, `ongkir`, `shipping_courier`, `shipping_service`, `shipping_etd`, `shipping_destination_id`, `shipping_destination_label`, `shipping_weight`, `shipping_distance_km`, `alamat_maps`, `alamat_detail`, `alamat_lat`, `alamat_lng`, `bukti_pembayaran`, `link_pembayaran_terkirim_at`, `pembayaran_terkonfirmasi_at`, `customer_confirmed_at`, `customer_confirmed_by`, `testimonial_submitted_at`, `harga_usulan`, `harga_penawaran_pelanggan`, `kode_nego`, `kode_nego_terpakai_at`, `is_nego`, `keterangan`, `catatan_admin`, `status`, `stok_dikurangi`, `hidden_from_pesanan_at`, `tanggal`, `invoice_snapshot`, `created_at`, `updated_at`, `harga_normal`, `harga_setelah_diskon`, `total_awal`, `harga_final_admin`, `status_persetujuan_harga`, `approved_at`, `approved_by`, `rejected_at`, `rejected_by`, `is_pengajuan_harga`) VALUES
(1, NULL, 1, 3, NULL, NULL, NULL, 2, '2026-07-22 14:53:46', NULL, 'produk', NULL, NULL, NULL, 'website', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'test', 4500000, '4500000.00', 'deal', 'transfer', 'bca', 'diantar_internal', '168000.00', 'sicepat', 'GOKIL - Cargo Per Kg (Minimal 10kg)', '3-4 day', '4866', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 42000, NULL, 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'Blok A1', '-6.95300280', '107.63814020', 'bukti-pembayaran/gOSdFtBusQfMtZYEnCuyT3KeJaAm5YPktQelXFTR.jpg', NULL, '2026-07-22 14:52:15', '2026-07-22 14:54:11', 3, '2026-07-22 14:54:38', '4500000.00', '4000000.00', NULL, '2026-07-22 14:52:15', 1, 'Pembelian Produk [Promo Diskon 10%: -Rp 565.900] [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]', 'oke', 'selesai final', 1, NULL, '2026-07-22', NULL, '2026-07-22 14:47:48', '2026-07-22 14:54:38', 5659000, 5093100, 5261100, 4500000, 'approved', '2026-07-22 14:48:56', 1, NULL, NULL, 1),
(2, NULL, 1, 3, NULL, NULL, NULL, 2, '2026-07-22 15:24:18', NULL, 'service', NULL, NULL, NULL, 'website', 0, 'service', 5, NULL, 'Ganti Selang CO2', '2 kg', 1, NULL, 'Rincian Service APAR Terdaftar\n1. AKHMAD-22072026-01 - AKHMAD-22072026-01 - Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\nPeralatan Paket:\n- Selang APAR CO2 x1\n- Segel Pengaman Plastik x1\nTotal Service: Rp65.000\nMetode Penanganan: Dijemput\nCatatan Pelanggan: test', 'service-request/nwZgaXPvFuAaVnFGACZjo5tRvtLUbI6fceaWpfAF.jpg', 'dijemput', '65000.00', NULL, 787784, '787784.00', 'normal', 'transfer', 'bca', 'diantar_internal', '722784.00', NULL, NULL, NULL, '4866', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 0, '206.51', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'Blok A1', '-6.95300280', '107.63814020', 'bukti-pembayaran/kCrPe0ICeCXNLg6jVMg4uAkHmit4fB7m9dkWgK5d.jpg', NULL, '2026-07-22 15:23:58', '2026-07-22 15:24:31', 3, '2026-07-22 15:24:42', NULL, NULL, NULL, NULL, 0, 'Permintaan SERVICE | Status Unit: APAR Terdaftar | Riwayat: AKHMAD-22072026-01 Ganti Selang CO2 | Item: Ganti Selang CO2 | Jumlah: 1 unit | Metode: dijemput | Ongkir: Rp722.784 | Catatan: Rincian Service Manual | 1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Selang APAR CO2 x1 | - Segel Pengaman Plastik x1 | Total Service: Rp65.000 | Metode Penanganan: Dijemput | Catatan Pelanggan: test | Riwayat: AKHMAD-22072026-01', NULL, 'selesai final', 1, NULL, '2026-07-22', NULL, '2026-07-22 15:23:51', '2026-07-22 15:42:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(3, NULL, 1, 3, NULL, NULL, NULL, 2, '2026-07-22 15:38:59', NULL, 'service', NULL, NULL, NULL, 'website', 0, 'service', 8, NULL, 'Pasang/Ganti Bracket', '2 kg', 1, NULL, 'Rincian Service APAR Terdaftar\n1. AKHMAD-22072026-01 - AKHMAD-22072026-01 - Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\nPeralatan Paket:\n- Bracket/Gantungan APAR x1\n- Baut Bracket APAR x2\nTotal Service: Rp65.000\nMetode Penanganan: Antar Sendiri\nCatatan Pelanggan: -', NULL, 'antar sendiri', '65000.00', NULL, 65000, '65000.00', 'normal', 'transfer', 'bca', 'pickup', '0.00', NULL, NULL, NULL, '4866', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 0, NULL, 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'Blok A1', '-6.95300280', '107.63814020', 'bukti-pembayaran/0dpIAJacML766ahEu1CaWL1UncxUJZklfyVgNonL.jpg', NULL, '2026-07-22 15:38:42', '2026-07-22 15:39:25', 3, '2026-07-22 15:39:51', NULL, NULL, NULL, NULL, 0, 'Permintaan SERVICE | Status Unit: APAR Terdaftar | Riwayat: AKHMAD-22072026-01 Pasang/Ganti Bracket | Item: Pasang/Ganti Bracket | Jumlah: 1 unit | Metode: antar sendiri | Catatan: Rincian Service Manual | 1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000 | Peralatan Paket: | - Bracket/Gantungan APAR x1 | - Baut Bracket APAR x2 | Total Service: Rp65.000 | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: - | Riwayat: AKHMAD-22072026-01', NULL, 'selesai final', 1, NULL, '2026-07-22', NULL, '2026-07-22 15:38:36', '2026-07-22 15:42:04', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(4, NULL, 1, 3, NULL, NULL, NULL, 2, '2026-07-22 15:52:20', NULL, 'service', NULL, NULL, NULL, 'website', 0, 'refill', NULL, 1, 'Powder', '5 kg', 10, '50.00', 'Rincian Refill Manual\n1. Powder | 5 kg | 10 unit - Rp1.150.000\nTotal Refill: Rp1.150.000\nTotal Kebutuhan Refill: 50 Kg\nMetode Penanganan: Antar Sendiri\nCatatan Pelanggan: TEST', 'service-request/R9EyiIDWqyjoMcQjdpyMBwdGh6YyumlZrHL5RzlZ.jpg', 'antar sendiri', '1150000.00', NULL, 1150000, '1150000.00', 'normal', 'transfer', 'bca', 'pickup', '0.00', NULL, NULL, NULL, '4866', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 0, NULL, 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'Blok A1', '-6.95300280', '107.63814020', 'bukti-pembayaran/TWywuE2wHDLNVAzTp2pyDUsVe7exVS4aAfiFY06L.jpg', NULL, '2026-07-22 15:51:46', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 'Permintaan REFILL Powder | Item: Powder | Jumlah: 10 unit | Kebutuhan: 50 Kg | Metode: antar sendiri | Catatan: Rincian Refill Manual | 1. Powder | 5 kg | 10 unit - Rp1.150.000 | Total Refill: Rp1.150.000 | Total Kebutuhan Refill: 50 Kg | Metode Penanganan: Antar Sendiri | Catatan Pelanggan: TEST', NULL, 'selesai final', 1, NULL, '2026-07-22', NULL, '2026-07-22 15:51:36', '2026-07-22 15:52:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(6, NULL, 1, 3, NULL, NULL, NULL, 2, '2026-07-29 04:41:03', NULL, 'produk', NULL, NULL, NULL, 'website', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 471000, '471000.00', 'normal', 'transfer', 'bca', 'diantar_internal', '21000.00', 'sicepat', 'HALU - Harga Mulai Lima Ribu', '3-4 day', '4866', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 2000, NULL, 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'Blok A1', '-6.95300280', '107.63814020', 'bukti-pembayaran/B9uBCVIJ7r4w8se04Y4ZtTSSztIml9ENZUOzubBU.jpg', NULL, '2026-07-29 04:40:05', '2026-07-29 04:41:43', 3, '2026-07-29 04:41:54', NULL, NULL, NULL, NULL, 0, 'Pembelian Produk [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]', NULL, 'selesai final', 1, NULL, '2026-07-29', NULL, '2026-07-29 04:39:51', '2026-07-29 04:41:54', 450000, 450000, 471000, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(7, NULL, 1, 3, NULL, NULL, NULL, 2, '2026-08-22 15:01:31', NULL, 'produk', NULL, NULL, NULL, 'website', 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 787500, '787500.00', 'normal', 'transfer', 'bca', 'diantar_internal', '40000.00', 'sicepat', 'GOKIL - Cargo Per Kg (Minimal 10kg)', '3-4 day', '4866', 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 9000, NULL, 'BATUNUNGGAL, BANDUNG KIDUL, BANDUNG, JAWA BARAT, 40266', 'Blok A1', '-6.95300280', '107.63814020', 'bukti-pembayaran/peEnqUXxcGz5y5pLD8PUgqGd80U1TMmTriWiS8RO.png', NULL, '2026-08-22 14:56:32', '2026-08-22 15:01:51', 3, NULL, NULL, NULL, NULL, NULL, 0, 'Pembelian Produk [Pengiriman: Diantar (Ekspedisi)] [Bank Tujuan: BCA]', NULL, 'selesai final', 1, NULL, '2026-08-22', NULL, '2026-08-22 14:50:55', '2026-08-22 15:01:51', 747500, 747500, 787500, NULL, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `pesanan_details`
--

CREATE TABLE `pesanan_details` (
  `id` bigint UNSIGNED NOT NULL,
  `pesanan_id` bigint UNSIGNED NOT NULL,
  `produk_id` bigint UNSIGNED NOT NULL,
  `merek` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kapasitas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int UNSIGNED NOT NULL,
  `harga` bigint UNSIGNED NOT NULL,
  `subtotal` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanan_details`
--

INSERT INTO `pesanan_details` (`id`, `pesanan_id`, `produk_id`, `merek`, `kapasitas`, `jumlah`, `harga`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 21, 'TONATA', '2 kg', 6, 517500, 3105000, '2026-07-22 14:47:48', '2026-07-22 14:47:48'),
(2, 1, 33, 'TONATA', '6 kg', 2, 575000, 1150000, '2026-07-22 14:47:48', '2026-07-22 14:47:48'),
(3, 1, 35, 'GuardALL', '9 kg', 2, 702000, 1404000, '2026-07-22 14:47:48', '2026-07-22 14:47:48'),
(5, 6, 19, 'FIREFIX', '2 kg', 1, 450000, 450000, '2026-07-29 04:39:51', '2026-07-29 04:39:51'),
(6, 7, 36, 'TONATA', '9 kg', 1, 747500, 747500, '2026-08-22 14:50:55', '2026-08-22 14:50:55');

-- --------------------------------------------------------

--
-- Table structure for table `produks`
--

CREATE TABLE `produks` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `merek` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'FIREFIX',
  `harga` decimal(15,2) NOT NULL DEFAULT '0.00',
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `gambar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_apar_id` bigint UNSIGNED NOT NULL,
  `stok` int UNSIGNED NOT NULL DEFAULT '0',
  `stok_minimum` int UNSIGNED NOT NULL DEFAULT '5',
  `kapasitas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `penggunaan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produks`
--

INSERT INTO `produks` (`id`, `nama`, `merek`, `harga`, `deskripsi`, `gambar`, `jenis_apar_id`, `stok`, `stok_minimum`, `kapasitas`, `penggunaan`, `created_at`, `updated_at`) VALUES
(1, 'APAR FIREFIX Powder 1 kg', 'FIREFIX', '150000.00', NULL, NULL, 1, 18, 5, '1 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:17'),
(2, 'APAR GuardALL Powder 1 kg', 'GuardALL', '162000.00', NULL, NULL, 1, 24, 5, '1 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:17'),
(3, 'APAR TONATA Powder 1 kg', 'TONATA', '172500.00', NULL, NULL, 1, 17, 5, '1 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:17'),
(4, 'APAR FIREFIX Powder 2 kg', 'FIREFIX', '200000.00', NULL, NULL, 1, 21, 5, '2 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(5, 'APAR GuardALL Powder 2 kg', 'GuardALL', '216000.00', NULL, NULL, 1, 27, 5, '2 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(6, 'APAR TONATA Powder 2 kg', 'TONATA', '230000.00', NULL, NULL, 1, 15, 5, '2 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(7, 'APAR FIREFIX Powder 3 kg', 'FIREFIX', '300000.00', NULL, NULL, 1, 19, 5, '3 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(8, 'APAR GuardALL Powder 3 kg', 'GuardALL', '324000.00', NULL, NULL, 1, 25, 5, '3 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(9, 'APAR TONATA Powder 3 kg', 'TONATA', '345000.00', NULL, NULL, 1, 18, 5, '3 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(10, 'APAR FIREFIX Powder 4 kg', 'FIREFIX', '400000.00', NULL, NULL, 1, 22, 5, '4 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(11, 'APAR GuardALL Powder 4 kg', 'GuardALL', '432000.00', NULL, NULL, 1, 23, 5, '4 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(12, 'APAR TONATA Powder 4 kg', 'TONATA', '460000.00', NULL, NULL, 1, 16, 5, '4 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(13, 'APAR FIREFIX Powder 6 kg', 'FIREFIX', '550000.00', NULL, NULL, 1, 20, 5, '6 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(14, 'APAR GuardALL Powder 6 kg', 'GuardALL', '594000.00', NULL, NULL, 1, 26, 5, '6 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(15, 'APAR TONATA Powder 6 kg', 'TONATA', '632500.00', NULL, NULL, 1, 19, 5, '6 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(16, 'APAR FIREFIX Powder 9 kg', 'FIREFIX', '750000.00', NULL, NULL, 1, 18, 5, '9 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(17, 'APAR GuardALL Powder 9 kg', 'GuardALL', '810000.00', NULL, NULL, 1, 24, 5, '9 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(18, 'APAR TONATA Powder 9 kg', 'TONATA', '862500.00', NULL, NULL, 1, 17, 5, '9 kg', 'Perkantoran, rumah, kendaraan, gudang', '2026-07-22 14:41:02', '2026-07-22 14:41:18'),
(19, 'APAR FIREFIX CO2 2 kg', 'FIREFIX', '450000.00', NULL, NULL, 2, 20, 5, '2 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:02', '2026-07-29 04:40:05'),
(20, 'APAR GuardALL CO2 2 kg', 'GuardALL', '486000.00', NULL, NULL, 2, 27, 5, '2 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(21, 'APAR TONATA CO2 2 kg', 'TONATA', '517500.00', NULL, NULL, 2, 9, 5, '2 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:52:15'),
(22, 'APAR FIREFIX CO2 3 kg', 'FIREFIX', '550000.00', NULL, NULL, 2, 19, 5, '3 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(23, 'APAR GuardALL CO2 3 kg', 'GuardALL', '594000.00', NULL, NULL, 2, 25, 5, '3 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(24, 'APAR TONATA CO2 3 kg', 'TONATA', '632500.00', NULL, NULL, 2, 18, 5, '3 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(25, 'APAR FIREFIX CO2 5 kg', 'FIREFIX', '750000.00', NULL, NULL, 2, 22, 5, '5 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(26, 'APAR GuardALL CO2 5 kg', 'GuardALL', '810000.00', NULL, NULL, 2, 23, 5, '5 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(27, 'APAR TONATA CO2 5 kg', 'TONATA', '862500.00', NULL, NULL, 2, 16, 5, '5 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(28, 'APAR FIREFIX CO2 6.8 kg', 'FIREFIX', '950000.00', NULL, NULL, 2, 20, 5, '6.8 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(29, 'APAR GuardALL CO2 6.8 kg', 'GuardALL', '1026000.00', NULL, NULL, 2, 26, 5, '6.8 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(30, 'APAR TONATA CO2 6.8 kg', 'TONATA', '1092500.00', NULL, NULL, 2, 19, 5, '6.8 kg', 'Ruang server, panel listrik, laboratorium', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(31, 'APAR FIREFIX Foam 6 kg', 'FIREFIX', '500000.00', NULL, NULL, 3, 18, 5, '6 kg', 'Dapur, SPBU, industri cairan mudah terbakar', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(32, 'APAR GuardALL Foam 6 kg', 'GuardALL', '540000.00', NULL, NULL, 3, 24, 5, '6 kg', 'Dapur, SPBU, industri cairan mudah terbakar', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(33, 'APAR TONATA Foam 6 kg', 'TONATA', '575000.00', NULL, NULL, 3, 15, 5, '6 kg', 'Dapur, SPBU, industri cairan mudah terbakar', '2026-07-22 14:41:03', '2026-07-22 14:52:15'),
(34, 'APAR FIREFIX Foam 9 kg', 'FIREFIX', '650000.00', NULL, NULL, 3, 21, 5, '9 kg', 'Dapur, SPBU, industri cairan mudah terbakar', '2026-07-22 14:41:03', '2026-07-22 14:41:18'),
(35, 'APAR GuardALL Foam 9 kg', 'GuardALL', '702000.00', NULL, NULL, 3, 25, 5, '9 kg', 'Dapur, SPBU, industri cairan mudah terbakar', '2026-07-22 14:41:03', '2026-07-22 14:52:15'),
(36, 'APAR TONATA Foam 9 kg', 'TONATA', '747500.00', NULL, NULL, 3, 19, 5, '9 kg', 'Dapur, SPBU, industri cairan mudah terbakar', '2026-07-22 14:41:03', '2026-08-22 14:56:33');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `id` bigint UNSIGNED NOT NULL,
  `nomor_po` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal_po` date NOT NULL,
  `supplier_id` bigint UNSIGNED NOT NULL,
  `total` decimal(15,2) NOT NULL DEFAULT '0.00',
  `status` enum('draft','dikirim','diterima') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `no_surat_jalan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_surat_jalan` date DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`id`, `nomor_po`, `tanggal_po`, `supplier_id`, `total`, `status`, `no_surat_jalan`, `tanggal_surat_jalan`, `catatan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'PO-20260724-001', '2026-07-24', 2, '302000.00', 'diterima', '192039120', '2026-07-24', 'test', '2026-07-24 14:28:45', '2026-07-24 14:39:39', NULL),
(2, 'PO-20260724-002', '2026-07-24', 2, '1250000.00', 'diterima', '21321321', '2026-07-24', NULL, '2026-07-24 15:51:22', '2026-07-24 15:58:20', NULL),
(3, 'PO-20260725-001', '2026-07-25', 2, '3000.00', 'diterima', 'ad', '2026-07-25', NULL, '2026-07-24 19:04:04', '2026-07-24 19:28:31', NULL),
(4, 'PO-20260729-001', '2026-07-29', 3, '1750000.00', 'diterima', '12345', '2026-07-29', 'test', '2026-07-29 04:29:12', '2026-07-29 04:30:17', NULL),
(5, 'PO-20260729-002', '2026-07-29', 3, '1750000.00', 'dikirim', NULL, NULL, NULL, '2026-07-29 04:30:47', '2026-07-29 04:34:45', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_details`
--

CREATE TABLE `purchase_order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `purchase_order_id` bigint UNSIGNED NOT NULL,
  `nama_item` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kategori` enum('produk','refill','peralatan') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(10,2) NOT NULL DEFAULT '1.00',
  `harga_satuan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchase_order_details`
--

INSERT INTO `purchase_order_details` (`id`, `purchase_order_id`, `nama_item`, `kategori`, `jumlah`, `harga_satuan`, `subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 'APAR FIREFIX Powder 1 kg - Dry Chemical Powder', 'produk', '2.00', '151000.00', '302000.00', '2026-07-24 14:28:45', '2026-07-24 14:28:45'),
(3, 2, 'Dry Chemical Powder - Kg', 'refill', '50.00', '25000.00', '1250000.00', '2026-07-24 15:51:37', '2026-07-24 15:51:37'),
(4, 3, 'Segel Pengaman Plastik', 'peralatan', '1.00', '3000.00', '3000.00', '2026-07-24 19:04:04', '2026-07-24 19:04:04'),
(5, 4, 'CO2 - Kg', 'refill', '50.00', '35000.00', '1750000.00', '2026-07-29 04:29:12', '2026-07-29 04:29:12'),
(6, 5, 'CO2 - Kg', 'refill', '50.00', '35000.00', '1750000.00', '2026-07-29 04:30:47', '2026-07-29 04:30:47');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` bigint UNSIGNED NOT NULL,
  `service_paket_id` bigint UNSIGNED DEFAULT NULL,
  `pesanan_id` bigint UNSIGNED DEFAULT NULL,
  `unit_apar_id` bigint UNSIGNED DEFAULT NULL,
  `jenis_service` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rincian_layanan` text COLLATE utf8mb4_unicode_ci,
  `estimasi_peralatan_json` text COLLATE utf8mb4_unicode_ci,
  `actual_peralatan_json` text COLLATE utf8mb4_unicode_ci,
  `tgl_service` date NOT NULL,
  `tgl_selesai_admin` timestamp NULL DEFAULT NULL,
  `status_konfirmasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stok_kurang_history_json` text COLLATE utf8mb4_unicode_ci,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `catatan_teknisi` text COLLATE utf8mb4_unicode_ci,
  `laporan_foto` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biaya` decimal(15,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `service_paket_id`, `pesanan_id`, `unit_apar_id`, `jenis_service`, `rincian_layanan`, `estimasi_peralatan_json`, `actual_peralatan_json`, `tgl_service`, `tgl_selesai_admin`, `status_konfirmasi`, `stok_kurang_history_json`, `keterangan`, `catatan_teknisi`, `laporan_foto`, `biaya`, `created_at`, `updated_at`) VALUES
(1, 5, 2, NULL, 'Ganti Selang CO2', 'Penggantian selang APAR CO2.\r\nPenggantian segel pengaman plastik.', '[{\"peralatan_id\":9,\"nama\":\"Selang APAR CO2\",\"jumlah_per_unit\":1,\"jumlah\":1,\"stok\":9,\"stok_minimum\":3,\"harga_standar\":120000},{\"peralatan_id\":3,\"nama\":\"Segel Pengaman Plastik\",\"jumlah_per_unit\":1,\"jumlah\":1,\"stok\":49,\"stok_minimum\":10,\"harga_standar\":3000}]', '[{\"peralatan_id\":9,\"nama\":\"Selang APAR CO2\",\"jumlah\":1},{\"peralatan_id\":3,\"nama\":\"Segel Pengaman Plastik\",\"jumlah\":1}]', '2026-07-22', '2026-07-22 15:24:31', 'confirmed', '[{\"peralatan_id\":9,\"nama\":\"Selang APAR CO2\",\"jumlah\":1,\"stok_sebelum\":10,\"stok_sesudah\":9},{\"peralatan_id\":3,\"nama\":\"Segel Pengaman Plastik\",\"jumlah\":1,\"stok_sebelum\":50,\"stok_sesudah\":49}]', 'Rincian Service Manual\n1. Ganti Selang CO2 | 2 kg | 1 unit - Rp65.000\nPeralatan Paket:\n- Selang APAR CO2 x1\n- Segel Pengaman Plastik x1\nTotal Service: Rp65.000\nMetode Penanganan: Dijemput\nCatatan Pelanggan: test', NULL, NULL, '65000.00', '2026-07-22 15:23:58', '2026-07-22 15:24:31'),
(2, 8, 3, NULL, 'Pasang/Ganti Bracket', 'Pemasangan atau penggantian bracket APAR.\r\nPemasangan baut bracket APAR.', '[{\"peralatan_id\":6,\"nama\":\"Bracket\\/Gantungan APAR\",\"jumlah_per_unit\":1,\"jumlah\":1,\"stok\":44,\"stok_minimum\":8,\"harga_standar\":25000},{\"peralatan_id\":5,\"nama\":\"Baut Bracket APAR\",\"jumlah_per_unit\":2,\"jumlah\":2,\"stok\":198,\"stok_minimum\":30,\"harga_standar\":5000}]', '[{\"peralatan_id\":6,\"nama\":\"Bracket\\/Gantungan APAR\",\"jumlah\":1},{\"peralatan_id\":5,\"nama\":\"Baut Bracket APAR\",\"jumlah\":2}]', '2026-07-22', '2026-07-22 15:39:04', 'confirmed', '[{\"peralatan_id\":6,\"nama\":\"Bracket\\/Gantungan APAR\",\"jumlah\":1,\"stok_sebelum\":45,\"stok_sesudah\":44},{\"peralatan_id\":5,\"nama\":\"Baut Bracket APAR\",\"jumlah\":2,\"stok_sebelum\":200,\"stok_sesudah\":198}]', 'Rincian Service Manual\n1. Pasang/Ganti Bracket | 2 kg | 1 unit - Rp65.000\nPeralatan Paket:\n- Bracket/Gantungan APAR x1\n- Baut Bracket APAR x2\nTotal Service: Rp65.000\nMetode Penanganan: Antar Sendiri\nCatatan Pelanggan: -', NULL, NULL, '65000.00', '2026-07-22 15:38:42', '2026-07-22 15:39:25'),
(3, NULL, 4, NULL, 'Refill APAR', NULL, NULL, NULL, '2026-07-22', '2026-07-22 15:52:27', 'confirmed', NULL, NULL, NULL, NULL, '1150000.00', '2026-07-22 15:52:27', '2026-07-22 15:52:27');

-- --------------------------------------------------------

--
-- Table structure for table `service_pakets`
--

CREATE TABLE `service_pakets` (
  `id` bigint UNSIGNED NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `harga` decimal(15,2) NOT NULL,
  `jenis_refill_id` bigint UNSIGNED DEFAULT NULL,
  `refill_ratio` decimal(5,2) NOT NULL DEFAULT '0.00',
  `rincian_layanan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_pakets`
--

INSERT INTO `service_pakets` (`id`, `nama`, `label`, `harga`, `jenis_refill_id`, `refill_ratio`, `rincian_layanan`, `created_at`, `updated_at`) VALUES
(1, 'Service Ringan', 'Ringan', '35000.00', NULL, '0.00', 'Pengecekan ringan unit APAR.\r\nPenggantian safety pin APAR.\r\nPenggantian segel pengaman plastik.', '2026-07-22 14:41:03', '2026-07-22 14:42:34'),
(2, 'Service Standar', 'Paket B', '90000.00', NULL, '0.00', 'Inspeksi kondisi fisik tabung, segel, dan pin pengaman\nPemeriksaan selang, nozzle, dan valve\nPembersihan body tabung dan area kepala APAR\nPenggantian safety pin dan segel pengaman plastik sesuai standar paket', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(3, 'Service Lengkap', 'Paket C', '150000.00', NULL, '0.00', 'Pembongkaran komponen utama APAR\nPemeriksaan valve, selang, nozzle, dan tekanan kerja\nPenggantian valve, safety pin, dan segel pengaman plastik sesuai standar paket\nPembersihan menyeluruh dan uji visual kebocoran ringan', '2026-07-22 14:41:03', '2026-07-22 14:41:03'),
(4, 'Ganti Selang Powder/Foam', 'Selang Powder/Foam', '70000.00', NULL, '0.00', 'Penggantian selang APAR Powder/Foam.\r\nPenggantian segel pengaman plastik.', '2026-07-22 14:42:34', '2026-07-22 14:42:34'),
(5, 'Ganti Selang CO2', 'Selang CO2', '160000.00', NULL, '0.00', 'Penggantian selang APAR CO2.\r\nPenggantian segel pengaman plastik.', '2026-07-22 14:42:34', '2026-07-22 14:42:34'),
(6, 'Ganti Valve APAR', 'Valve', '100000.00', NULL, '0.00', 'Penggantian valve APAR.\r\nPenggantian O-Ring/karet seal.\r\nPenggantian segel pengaman plastik.', '2026-07-22 14:42:34', '2026-07-22 14:42:34'),
(7, 'Ganti Pressure Gauge', 'Pressure Gauge', '65000.00', NULL, '0.00', 'Penggantian pressure gauge APAR.\r\nPenggantian O-Ring/karet seal.\r\nPenggantian segel pengaman plastik.', '2026-07-22 14:42:34', '2026-07-22 14:42:34'),
(8, 'Pasang/Ganti Bracket', 'Bracket', '60000.00', NULL, '0.00', 'Pemasangan atau penggantian bracket APAR.\r\nPemasangan baut bracket APAR.', '2026-07-22 14:42:34', '2026-07-22 14:42:34'),
(10, 'Pemasangan Unit', 'tes', '100000.00', NULL, '0.00', 'test', '2026-07-28 03:07:41', '2026-07-28 03:07:41');

-- --------------------------------------------------------

--
-- Table structure for table `service_paket_peralatan`
--

CREATE TABLE `service_paket_peralatan` (
  `id` bigint UNSIGNED NOT NULL,
  `service_paket_id` bigint UNSIGNED NOT NULL,
  `peralatan_id` bigint UNSIGNED NOT NULL,
  `jumlah_estimasi` int UNSIGNED NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `service_paket_peralatan`
--

INSERT INTO `service_paket_peralatan` (`id`, `service_paket_id`, `peralatan_id`, `jumlah_estimasi`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 1, NULL, NULL),
(2, 1, 3, 1, NULL, NULL),
(3, 2, 2, 1, NULL, NULL),
(4, 2, 3, 1, NULL, NULL),
(5, 3, 2, 1, NULL, NULL),
(6, 3, 3, 1, NULL, NULL),
(7, 3, 1, 1, NULL, NULL),
(8, 4, 4, 1, NULL, NULL),
(9, 4, 3, 1, NULL, NULL),
(10, 5, 9, 1, NULL, NULL),
(11, 5, 3, 1, NULL, NULL),
(12, 6, 1, 1, NULL, NULL),
(13, 6, 11, 1, NULL, NULL),
(14, 6, 3, 1, NULL, NULL),
(15, 7, 10, 1, NULL, NULL),
(16, 7, 11, 1, NULL, NULL),
(17, 7, 3, 1, NULL, NULL),
(18, 8, 6, 1, NULL, NULL),
(19, 8, 5, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stok_batches`
--

CREATE TABLE `stok_batches` (
  `id` bigint UNSIGNED NOT NULL,
  `produk_id` bigint UNSIGNED NOT NULL,
  `jumlah_masuk` int NOT NULL,
  `sisa_qty` int NOT NULL,
  `tgl_produksi` date NOT NULL,
  `tgl_expired` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `sumber` enum('manual','purchase_order') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'manual',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `purchase_order_id` bigint UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stok_batches`
--

INSERT INTO `stok_batches` (`id`, `produk_id`, `jumlah_masuk`, `sisa_qty`, `tgl_produksi`, `tgl_expired`, `keterangan`, `sumber`, `created_at`, `updated_at`, `purchase_order_id`) VALUES
(1, 1, 20, 15, '2026-07-23', '2027-01-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:17', '2026-07-23 15:14:00', NULL),
(2, 1, 10, 3, '2026-07-23', '2027-01-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:17', '2026-07-23 15:14:00', NULL),
(3, 2, 21, 16, '2026-07-23', '2027-01-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:17', '2026-07-23 15:14:00', NULL),
(4, 2, 15, 8, '2026-07-23', '2027-01-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:17', '2026-07-23 15:14:00', NULL),
(5, 3, 22, 17, '2026-07-23', '2027-01-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:17', '2026-07-23 15:14:00', NULL),
(6, 4, 23, 18, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:17', '2026-07-23 15:14:00', NULL),
(7, 4, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(8, 5, 24, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(9, 5, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(10, 6, 20, 15, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(11, 7, 21, 16, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(12, 7, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(13, 8, 22, 17, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(14, 8, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(15, 9, 23, 18, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(16, 10, 24, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(17, 10, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(18, 11, 20, 15, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(19, 11, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(20, 12, 21, 16, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(21, 13, 22, 17, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(22, 13, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(23, 14, 23, 18, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(24, 14, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(25, 15, 24, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(26, 16, 20, 15, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(27, 16, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(28, 17, 21, 16, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(29, 17, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(30, 18, 22, 17, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(31, 19, 23, 17, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-29 04:40:05', NULL),
(32, 19, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(33, 20, 24, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(34, 20, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(35, 21, 20, 9, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(36, 22, 21, 16, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(37, 22, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(38, 23, 22, 17, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(39, 23, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(40, 24, 23, 18, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(41, 25, 24, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(42, 25, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(43, 26, 20, 15, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(44, 26, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(45, 27, 21, 16, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(46, 28, 22, 17, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(47, 28, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(48, 29, 23, 18, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(49, 29, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(50, 30, 24, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(51, 31, 20, 15, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(52, 31, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(53, 32, 21, 16, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(54, 32, 15, 8, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(55, 33, 22, 15, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(56, 34, 23, 18, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(57, 34, 10, 3, '2026-07-23', '2027-07-23', 'Sisa Batch Cuci Gudang - Vendor B', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(58, 35, 24, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(59, 35, 15, 6, '2026-07-23', '2027-07-23', 'Batch Khusus Siap Distribusi - Vendor C', 'manual', '2026-07-22 14:41:18', '2026-07-23 15:14:00', NULL),
(60, 36, 25, 19, '2026-07-23', '2027-07-23', 'Batch Restock Gudang Utama - Vendor A', 'manual', '2026-07-22 14:41:18', '2026-08-22 14:56:32', NULL),
(62, 1, 2, 2, '2026-07-24', '2027-07-24', 'Penerimaan PO PO-20260724-001 (Surat Jalan: 192039120)', 'purchase_order', '2026-07-24 14:39:39', '2026-07-24 14:39:39', 1);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint UNSIGNED NOT NULL,
  `nama_supplier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kontak_person` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_telepon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_wa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `status` enum('aktif','nonaktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `nama_supplier`, `kontak_person`, `no_telepon`, `no_wa`, `email`, `alamat`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(2, 'PT SENTOSA', 'BUDI', '08111111', '087830665027', 'budi@gmail.com', 'test', 'aktif', '2026-07-24 13:22:58', '2026-07-24 13:36:05', NULL),
(3, 'PT MAJU INDAH', 'RIZAL', '085128008030', '085128008030', 'rizaldy@gmail.com', 'Bandung', 'aktif', '2026-07-29 04:28:19', '2026-07-29 04:28:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `testimonis`
--

CREATE TABLE `testimonis` (
  `id` bigint UNSIGNED NOT NULL,
  `pelanggan_id` bigint UNSIGNED NOT NULL,
  `transaksi_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaksi_id` bigint UNSIGNED DEFAULT NULL,
  `rating` tinyint UNSIGNED NOT NULL DEFAULT '5',
  `review` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `foto_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_anonymous` tinyint(1) NOT NULL DEFAULT '0',
  `tanggal` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `testimonis`
--

INSERT INTO `testimonis` (`id`, `pelanggan_id`, `transaksi_type`, `transaksi_id`, `rating`, `review`, `foto_path`, `is_anonymous`, `tanggal`, `status`, `admin_note`, `created_at`, `updated_at`) VALUES
(1, 1, 'App\\Models\\Pesanan', 1, 5, 'TERBAIK', 'testimonis/jfm9gnZLpICGEuE48VqahnKoLJ0LQsISUZNTVXs1.jpg', 0, '2026-07-22', 'approved', NULL, '2026-07-22 14:54:38', '2026-07-22 14:54:38'),
(2, 1, 'App\\Models\\Pesanan', 2, 4, 'BAGUSSS', 'testimonis/qnJ8QRbxTrSC6vmVRcWAsZJ2BvNaipgDntlEDkJ6.jpg', 1, '2026-07-22', 'approved', NULL, '2026-07-22 15:24:42', '2026-07-22 15:24:42'),
(3, 1, 'App\\Models\\Pesanan', 3, 5, 'MANTAPPP', 'testimonis/kLWaXf7pdwQSrF1EaYbveoF0WA3CBgEEqvhYA7NV.jpg', 0, '2026-07-22', 'approved', NULL, '2026-07-22 15:39:51', '2026-07-22 15:39:51'),
(4, 1, 'App\\Models\\Pesanan', 6, 5, 'BAGUSS', 'testimonis/PMZQ6lvfDRaFOGgiBA4SBLWNHiVPdlRGElZd9hd7.jpg', 0, '2026-07-29', 'approved', 'TERIMA KASIH', '2026-07-29 04:41:54', '2026-07-29 04:43:13');

-- --------------------------------------------------------

--
-- Table structure for table `unit_apars`
--

CREATE TABLE `unit_apars` (
  `id` bigint UNSIGNED NOT NULL,
  `pelanggan_id` bigint UNSIGNED NOT NULL,
  `pesanan_id` bigint UNSIGNED DEFAULT NULL,
  `produk_id` bigint UNSIGNED NOT NULL,
  `no_seri` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lokasi_unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_beli` date DEFAULT NULL,
  `tgl_produksi` date NOT NULL,
  `ukuran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bahan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kondisi_awal` enum('layak','perlu_servis','tidak_aktif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'layak',
  `catatan_unit` text COLLATE utf8mb4_unicode_ci,
  `tgl_expired` date NOT NULL,
  `hidden_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `unit_apars`
--

INSERT INTO `unit_apars` (`id`, `pelanggan_id`, `pesanan_id`, `produk_id`, `no_seri`, `lokasi_unit`, `tgl_beli`, `tgl_produksi`, `ukuran`, `bahan`, `kondisi_awal`, `catatan_unit`, `tgl_expired`, `hidden_at`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 32, 'AKHMAD-21072026-02', NULL, '2026-04-22', '2026-04-22', '6 kg', 'Liquid Foam (Busa)', 'layak', NULL, '2027-04-22', '2026-07-22 14:44:05', '2026-07-22 14:41:51', '2026-07-22 15:05:09'),
(2, 1, 1, 21, 'AKHMAD-22072026-01', NULL, '2026-07-22', '2026-04-22', '2 kg', 'Carbon Dioxide (CO2)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:44'),
(3, 1, 1, 21, 'AKHMAD-22072026-02', NULL, '2026-07-22', '2026-04-22', '2 kg', 'Carbon Dioxide (CO2)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(4, 1, 1, 21, 'AKHMAD-22072026-03', NULL, '2026-07-22', '2026-04-22', '2 kg', 'Carbon Dioxide (CO2)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(5, 1, 1, 21, 'AKHMAD-22072026-04', NULL, '2026-07-22', '2026-04-22', '2 kg', 'Carbon Dioxide (CO2)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(6, 1, 1, 21, 'AKHMAD-22072026-05', NULL, '2026-07-22', '2026-04-22', '2 kg', 'Carbon Dioxide (CO2)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(7, 1, 1, 21, 'AKHMAD-22072026-06', NULL, '2026-07-22', '2026-04-22', '2 kg', 'Carbon Dioxide (CO2)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(8, 1, 1, 33, 'AKHMAD-22072026-07', NULL, '2026-07-22', '2026-04-22', '6 kg', 'Liquid Foam (Busa)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(9, 1, 1, 33, 'AKHMAD-22072026-08', NULL, '2026-07-22', '2026-04-22', '6 kg', 'Liquid Foam (Busa)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(10, 1, 1, 35, 'AKHMAD-22072026-09', NULL, '2026-07-22', '2025-08-06', '9 kg', 'Liquid Foam (Busa)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(11, 1, 1, 35, 'AKHMAD-22072026-10', NULL, '2026-07-22', '2025-08-06', '9 kg', 'Liquid Foam (Busa)', 'layak', NULL, '2027-07-22', NULL, '2026-07-22 14:52:15', '2026-07-22 15:06:45'),
(13, 1, 6, 19, 'AKHMAD-29072026-01', NULL, '2026-07-29', '2026-07-23', '2 kg', 'Carbon Dioxide (CO2)', 'layak', NULL, '2027-07-23', NULL, '2026-07-29 04:40:05', '2026-07-29 04:41:44'),
(14, 1, 7, 36, 'AKHMAD-22082026-01', NULL, '2026-08-22', '2026-07-23', '9 kg', 'Liquid Foam (Busa)', 'layak', NULL, '2027-07-23', NULL, '2026-08-22 14:56:33', '2026-08-22 15:01:51');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_telpon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','teknisi','pelanggan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pelanggan',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `no_telpon`, `email`, `email_verified_at`, `password`, `role`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Admin', '081111111111', 'admin@gmail.com', NULL, '$2y$12$82j0./.94vt.lfPzuX8V7uqPyGTKIXyanGpZlbwmehbrmIqw7z4RS', 'admin', NULL, '2026-07-22 14:41:02', '2026-07-22 14:44:42'),
(2, 'Mulyono', '082222222222', 'teknisi@gmail.com', NULL, '$2y$12$BbMXUgoORrs3LkYrr7VOm.K9KoXN8jgwcgWNaWW.GJTvsftMzeIWK', 'teknisi', NULL, '2026-07-22 14:41:02', '2026-07-23 03:19:53'),
(3, 'Akhmad Rizaldy', '087830665027', 'akhmadrizaldy69@gmail.com', NULL, '$2y$12$Cyrm0m4Olg8wOCGkVwd37eHuHN0uy9P4IMr90tj3hFQgLbTo3H7QW', 'pelanggan', NULL, '2026-07-22 14:41:06', '2026-07-22 14:41:51');

-- --------------------------------------------------------

--
-- Table structure for table `website_visits`
--

CREATE TABLE `website_visits` (
  `id` bigint UNSIGNED NOT NULL,
  `visitor_id` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `page_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visited_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `website_visits`
--

INSERT INTO `website_visits` (`id`, `visitor_id`, `session_id`, `page_url`, `ip_address`, `user_agent`, `event_type`, `product_id`, `page_title`, `visited_at`, `created_at`, `updated_at`) VALUES
(1, 'd0bd7c0f-1602-4d34-b3e3-1f4168cb9bbc', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:42:52', '2026-07-22 14:42:52', '2026-07-22 14:42:52'),
(2, 'e8a81127-8139-4bdf-9417-cd791990afc1', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:42:55', '2026-07-22 14:42:55', '2026-07-22 14:42:55'),
(3, 'abdaf34e-a892-4e8d-8db5-d604d677ce6c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:42:56', '2026-07-22 14:42:56', '2026-07-22 14:42:56'),
(4, '11f1ea96-7d64-48f0-a667-e4077147ab9c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:42:56', '2026-07-22 14:42:56', '2026-07-22 14:42:56'),
(5, 'edda1b90-7414-4236-af82-3af08b3cf7f5', NULL, '/riwayat-apar/ajukan-service', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:43:04', '2026-07-22 14:43:04', '2026-07-22 14:43:04'),
(6, '42cb8a44-9e39-48e8-bef3-9b31e1902a3c', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:43:04', '2026-07-22 14:43:04', '2026-07-22 14:43:04'),
(7, '7feae8bc-ca76-41af-a428-6d042a1211f4', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:43:20', '2026-07-22 14:43:20', '2026-07-22 14:43:20'),
(8, '1e10a935-0889-4cf1-95a5-291845f334b7', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:43:41', '2026-07-22 14:43:41', '2026-07-22 14:43:41'),
(9, 'e7e3c565-2573-4691-af5d-c8d392985b67', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:43:41', '2026-07-22 14:43:41', '2026-07-22 14:43:41'),
(10, '06d3f7e9-1229-4e11-890a-6d405531e0e1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:43:42', '2026-07-22 14:43:42', '2026-07-22 14:43:42'),
(11, '3bfdf983-1897-4214-9a5d-3e31097f2971', NULL, '/profile', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:43:48', '2026-07-22 14:43:48', '2026-07-22 14:43:48'),
(12, '7345fb9c-a87b-44c9-a8e6-6a926328a436', NULL, '/order/address/suggest', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:44:19', '2026-07-22 14:44:19', '2026-07-22 14:44:19'),
(13, '90ce2d98-f0da-48a9-bc47-5a5f5b3bb0f7', NULL, '/profile', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:44:24', '2026-07-22 14:44:24', '2026-07-22 14:44:24'),
(14, '7dc0e114-8a1e-4507-a9a4-2662624676bf', NULL, '/profile', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:44:24', '2026-07-22 14:44:24', '2026-07-22 14:44:24'),
(15, '0f12c9b4-e3b1-4885-bbe1-eaa5093a529b', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:44:52', '2026-07-22 14:44:52', '2026-07-22 14:44:52'),
(16, 'e0e166b9-0f31-4d72-ac24-337a866d7d6d', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:00', '2026-07-22 14:45:00', '2026-07-22 14:45:00'),
(17, '79457ae6-c2a6-41d5-8a62-180b9a98fc5e', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:01', '2026-07-22 14:45:01', '2026-07-22 14:45:01'),
(18, '600b6386-8693-4a7c-bf94-8bce4be0e8a0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:01', '2026-07-22 14:45:01', '2026-07-22 14:45:01'),
(19, '84f909e3-939c-4b5b-bd3b-87e812819413', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:03', '2026-07-22 14:45:03', '2026-07-22 14:45:03'),
(20, '6fbef02a-7b83-4add-a1be-4cd0b04fb8ce', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:05', '2026-07-22 14:45:05', '2026-07-22 14:45:05'),
(21, 'f9f7eedf-cf0f-4b12-811c-b48edbcd95a4', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:07', '2026-07-22 14:45:07', '2026-07-22 14:45:07'),
(22, 'b345202c-2d5d-4b7f-8904-d77c4c07f825', NULL, '/produk/21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'product_view', 21, 'APAR TONATA CO2 2 kg', '2026-07-22 14:45:27', '2026-07-22 14:45:27', '2026-07-22 14:45:27'),
(23, '1feeb6f4-e64d-4e48-80d5-8fc6da073460', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'add_to_cart', 21, 'APAR TONATA CO2 2 kg (x5)', '2026-07-22 14:45:34', '2026-07-22 14:45:34', '2026-07-22 14:45:34'),
(24, '1feeb6f4-e64d-4e48-80d5-8fc6da073460', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:34', '2026-07-22 14:45:34', '2026-07-22 14:45:34'),
(25, '1ce435f0-b086-4c21-9dd0-1ccdba65cd88', NULL, '/produk/21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'product_view', 21, 'APAR TONATA CO2 2 kg', '2026-07-22 14:45:34', '2026-07-22 14:45:34', '2026-07-22 14:45:34'),
(26, '3c2f3fca-658c-4316-9a3a-47deef05d795', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:37', '2026-07-22 14:45:37', '2026-07-22 14:45:37'),
(27, 'dee122b1-5754-4191-b17f-7fd7b6c5b911', NULL, '/produk/33', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'product_view', 33, 'APAR TONATA Foam 6 kg', '2026-07-22 14:45:47', '2026-07-22 14:45:47', '2026-07-22 14:45:47'),
(28, '5ed1c955-3df7-4330-b2da-3e0c088d5e8e', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'add_to_cart', 33, 'APAR TONATA Foam 6 kg (x2)', '2026-07-22 14:45:52', '2026-07-22 14:45:52', '2026-07-22 14:45:52'),
(29, '5ed1c955-3df7-4330-b2da-3e0c088d5e8e', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:52', '2026-07-22 14:45:52', '2026-07-22 14:45:52'),
(30, '8894a703-2afc-4cfd-88ab-40283e064220', NULL, '/produk/33', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'product_view', 33, 'APAR TONATA Foam 6 kg', '2026-07-22 14:45:52', '2026-07-22 14:45:52', '2026-07-22 14:45:52'),
(31, '9236d712-37b8-425a-9e45-434cd166e1f2', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:54', '2026-07-22 14:45:54', '2026-07-22 14:45:54'),
(32, '42264303-d486-4287-b37e-c68e0571c4eb', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:45:59', '2026-07-22 14:45:59', '2026-07-22 14:45:59'),
(33, '4516dbbb-3f21-47b9-9629-a6370bee841f', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:46:33', '2026-07-22 14:46:33', '2026-07-22 14:46:33'),
(34, '3d651eed-84c0-40fd-802a-6970c14250eb', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'add_to_cart', 35, 'APAR GuardALL Foam 9 kg (x1)', '2026-07-22 14:46:38', '2026-07-22 14:46:38', '2026-07-22 14:46:38'),
(35, '3d651eed-84c0-40fd-802a-6970c14250eb', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:46:38', '2026-07-22 14:46:38', '2026-07-22 14:46:38'),
(36, '50d2b5e5-8c60-420f-b241-668d75a96eba', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:46:38', '2026-07-22 14:46:38', '2026-07-22 14:46:38'),
(37, '8589ff67-368c-4ef9-9e38-618c9e0da58c', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'add_to_cart', 35, 'APAR GuardALL Foam 9 kg (x1)', '2026-07-22 14:46:42', '2026-07-22 14:46:42', '2026-07-22 14:46:42'),
(38, '8589ff67-368c-4ef9-9e38-618c9e0da58c', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:46:42', '2026-07-22 14:46:42', '2026-07-22 14:46:42'),
(39, '85222341-244d-45ac-85a7-5f55ac8a91c1', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:46:42', '2026-07-22 14:46:42', '2026-07-22 14:46:42'),
(40, 'd14abc06-7e71-4b99-bb23-1e6f3bab7ff4', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:46:46', '2026-07-22 14:46:46', '2026-07-22 14:46:46'),
(41, '03ef0242-56f3-4a0e-a679-6b200828f0b5', NULL, '/keranjang/21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:46:57', '2026-07-22 14:46:57', '2026-07-22 14:46:57'),
(42, '1da45f85-cf02-4786-926c-51a8d9d11527', NULL, '/keranjang/21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:47:00', '2026-07-22 14:47:00', '2026-07-22 14:47:00'),
(43, '9733f81f-0de3-4d1c-af10-659551c81fa5', NULL, '/keranjang/21', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:47:02', '2026-07-22 14:47:02', '2026-07-22 14:47:02'),
(44, 'cca7a138-82a4-4daa-abf2-9c7dfbc8b28f', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:47:07', '2026-07-22 14:47:07', '2026-07-22 14:47:07'),
(45, '30ecf9fa-16c0-4bd5-8a3d-0fd3155d4888', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:47:48', '2026-07-22 14:47:48', '2026-07-22 14:47:48'),
(46, '7057b80c-9a8e-4be5-80fd-09b5d1ad6088', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:47:49', '2026-07-22 14:47:49', '2026-07-22 14:47:49'),
(47, 'cff6926b-6e5a-472e-941e-0b4e19d5864a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:47:49', '2026-07-22 14:47:50', '2026-07-22 14:47:50'),
(48, '6833ca13-b0e5-44ef-8210-e27d926a4e2c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:47:50', '2026-07-22 14:47:50', '2026-07-22 14:47:50'),
(49, '8ce8c8de-e33e-4241-a3b3-d09b7a8165f1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:48:19', '2026-07-22 14:48:19', '2026-07-22 14:48:19'),
(50, 'f825c700-5781-463e-aa97-d6ac42681b42', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:48:20', '2026-07-22 14:48:20', '2026-07-22 14:48:20'),
(51, '0ee00b49-93e3-485a-9b97-79b494595ac7', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:48:49', '2026-07-22 14:48:49', '2026-07-22 14:48:49'),
(52, 'dde1a681-96a6-4781-b65c-2b28ad3d1b0b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:48:50', '2026-07-22 14:48:50', '2026-07-22 14:48:50'),
(53, '60be7847-343f-420d-8809-d32c30c1b730', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:49:20', '2026-07-22 14:49:20', '2026-07-22 14:49:20'),
(54, '54cb5b8f-a6c4-47d7-84a3-ca7f02f3c619', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:49:20', '2026-07-22 14:49:20', '2026-07-22 14:49:20'),
(55, 'c090f051-5616-411e-b2e5-bd8ee79d1d4c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:49:49', '2026-07-22 14:49:49', '2026-07-22 14:49:49'),
(56, 'f77a5675-c892-49bc-afa1-8ea489c22bbb', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:49:50', '2026-07-22 14:49:50', '2026-07-22 14:49:50'),
(57, 'd5f5a7ac-8fcb-4bd8-b370-c862c67555f6', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:50:19', '2026-07-22 14:50:19', '2026-07-22 14:50:19'),
(58, 'a88cfa39-94d7-4abe-8759-0ba01fed8486', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:50:20', '2026-07-22 14:50:20', '2026-07-22 14:50:20'),
(59, '454795c6-8118-4fbc-9123-f3a9337e1ddc', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:50:50', '2026-07-22 14:50:50', '2026-07-22 14:50:50'),
(60, 'f3268bd6-1fbd-4da8-a787-909128977cf9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:50:50', '2026-07-22 14:50:50', '2026-07-22 14:50:50'),
(61, '83121fd2-f42b-4b60-a8c1-0a26aefa5644', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:51:19', '2026-07-22 14:51:19', '2026-07-22 14:51:19'),
(62, '5a503027-0b05-4b38-9016-56dc5572d4b2', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:51:20', '2026-07-22 14:51:20', '2026-07-22 14:51:20'),
(63, '6f8eb7b3-091b-4b59-b256-ccda1b03660e', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:51:49', '2026-07-22 14:51:49', '2026-07-22 14:51:49'),
(64, '67026066-8b2e-4d71-83ee-8a0e95063b49', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:51:50', '2026-07-22 14:51:50', '2026-07-22 14:51:50'),
(65, '41af5217-89ea-470e-a50d-99d52ad45f57', NULL, '/invoice/1', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:51:55', '2026-07-22 14:51:55', '2026-07-22 14:51:55'),
(66, '148c57ae-8ddd-4746-9f0f-ca8e44afe06d', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:52:03', '2026-07-22 14:52:03', '2026-07-22 14:52:03'),
(67, 'be0091bd-a36e-4562-8cc9-49816dc03715', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:52:04', '2026-07-22 14:52:04', '2026-07-22 14:52:04'),
(68, '4aa25fca-9cfc-41a3-8cd3-a09c5a5ead8e', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:52:04', '2026-07-22 14:52:04', '2026-07-22 14:52:04'),
(69, '4c41d036-8080-4bba-b39c-70cdfbb61c65', NULL, '/order/1/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:52:05', '2026-07-22 14:52:05', '2026-07-22 14:52:05'),
(70, '80b56800-79d4-4409-b716-6bf653d3f887', NULL, '/order/1/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:52:15', '2026-07-22 14:52:15', '2026-07-22 14:52:15'),
(71, '784bdb59-6c67-4bd0-9a51-bd0de015305c', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:52:17', '2026-07-22 14:52:17', '2026-07-22 14:52:17'),
(72, '7c32dd95-a477-4642-bf6b-06d03fd9ab46', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:52:55', '2026-07-22 14:52:55', '2026-07-22 14:52:55'),
(73, 'f10e2985-1fe4-4b26-923c-002a12a139cb', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:53:53', '2026-07-22 14:53:53', '2026-07-22 14:53:53'),
(74, '1ad7f2cf-58af-47b5-bf84-a2cdd92cbd88', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:53:54', '2026-07-22 14:53:54', '2026-07-22 14:53:54'),
(75, 'b7ccb873-5d7f-44ad-bb67-30780d4414e0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:53:54', '2026-07-22 14:53:54', '2026-07-22 14:53:54'),
(76, '4991f854-a186-468a-8af4-0a8d763afbdc', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:07', '2026-07-22 14:54:07', '2026-07-22 14:54:07'),
(77, '5d915f30-730c-4112-adfc-deb291324616', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:07', '2026-07-22 14:54:07', '2026-07-22 14:54:07'),
(78, '5b2c089f-036c-4bda-bbba-f07f8a7e5950', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:08', '2026-07-22 14:54:08', '2026-07-22 14:54:08'),
(79, '2b0fff87-c095-42f2-becb-a4489c95b59b', NULL, '/riwayat-apar/1/confirm-received', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:11', '2026-07-22 14:54:11', '2026-07-22 14:54:11'),
(80, '5ee8402c-4ba6-483f-bf0a-b65b2568cd1b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:12', '2026-07-22 14:54:12', '2026-07-22 14:54:12'),
(81, 'f865f0a7-ec89-4a70-b7fc-125866bf657a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:37', '2026-07-22 14:54:37', '2026-07-22 14:54:37'),
(82, 'd37dee2d-20af-43a2-95af-c826d1e926e1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:38', '2026-07-22 14:54:38', '2026-07-22 14:54:38'),
(83, 'e1c250b9-19c6-45f1-b5d9-3824e3499123', NULL, '/testimoni', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:38', '2026-07-22 14:54:38', '2026-07-22 14:54:38'),
(84, '5e8aafea-b2bf-40ab-81c0-243f926d61b9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:39', '2026-07-22 14:54:39', '2026-07-22 14:54:39'),
(85, '4e4e3815-72d2-4b0b-b08f-f6bcb403d70b', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:41', '2026-07-22 14:54:41', '2026-07-22 14:54:41'),
(86, 'f7f24037-aeee-448f-9341-f805a76924e9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:42', '2026-07-22 14:54:42', '2026-07-22 14:54:42'),
(87, '107b2104-e7ff-415e-b356-f6a1de151a8f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:54:42', '2026-07-22 14:54:42', '2026-07-22 14:54:42'),
(88, '345f3297-1297-46bd-bbfc-e5ad46caca57', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:55:11', '2026-07-22 14:55:11', '2026-07-22 14:55:11'),
(89, '57fa6135-ed63-4628-b02b-cfc3faf78622', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:55:12', '2026-07-22 14:55:12', '2026-07-22 14:55:12'),
(90, '9d083410-b2ff-4040-adc5-eed95af37136', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:55:42', '2026-07-22 14:55:42', '2026-07-22 14:55:42'),
(91, '9312a5f1-3302-4c35-8271-54ec7a89c838', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:55:42', '2026-07-22 14:55:42', '2026-07-22 14:55:42'),
(92, '5a47d1cd-8410-41ab-8c5c-f046cb86791f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:56:12', '2026-07-22 14:56:12', '2026-07-22 14:56:12'),
(93, '7c235f50-fdf0-4e19-8535-86d8728613e4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:56:12', '2026-07-22 14:56:12', '2026-07-22 14:56:12'),
(94, 'de49b5e1-6a14-45ec-bdb7-36e6afda7ef0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:56:42', '2026-07-22 14:56:42', '2026-07-22 14:56:42'),
(95, 'f324beac-d0a4-4764-9a4d-4be621259e48', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:56:42', '2026-07-22 14:56:42', '2026-07-22 14:56:42'),
(96, 'd53e5cb3-75b6-4a67-ad0f-516f9da87581', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:57:12', '2026-07-22 14:57:12', '2026-07-22 14:57:12'),
(97, 'a9d1774a-e5de-4404-870c-f591333f93ab', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:57:12', '2026-07-22 14:57:12', '2026-07-22 14:57:12'),
(98, 'de4a1c32-3fde-4059-bb6d-f58e493bb8b1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:57:41', '2026-07-22 14:57:41', '2026-07-22 14:57:41'),
(99, '66e483cd-b1c0-47b0-adb9-106462f067f5', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:57:42', '2026-07-22 14:57:42', '2026-07-22 14:57:42'),
(100, 'cfb5b800-7349-4cda-b557-c8fa40982e38', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:58:12', '2026-07-22 14:58:12', '2026-07-22 14:58:12'),
(101, 'b4fdfd39-8b55-4486-ad53-1f5d57485bbe', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:58:12', '2026-07-22 14:58:12', '2026-07-22 14:58:12'),
(102, '2f024759-d3f5-426c-acb2-dbe58f025ae3', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:58:41', '2026-07-22 14:58:41', '2026-07-22 14:58:41'),
(103, '281994a6-19db-46d0-8385-7c7b585b4614', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:58:42', '2026-07-22 14:58:42', '2026-07-22 14:58:42'),
(104, 'c661175f-b339-4033-adc3-5dc0e178c5e9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:59:12', '2026-07-22 14:59:12', '2026-07-22 14:59:12'),
(105, '3f6bb2f5-1b7a-48b2-b84f-aea0577fd653', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:59:12', '2026-07-22 14:59:12', '2026-07-22 14:59:12'),
(106, '3e5bedee-230c-4634-9b60-2ea304ea61a9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:59:41', '2026-07-22 14:59:41', '2026-07-22 14:59:41'),
(107, 'e50c18ba-432d-4005-8574-8956edf6bde0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 14:59:42', '2026-07-22 14:59:42', '2026-07-22 14:59:42'),
(108, '38f6a218-8586-43d4-887d-3d707be5044d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:00:12', '2026-07-22 15:00:12', '2026-07-22 15:00:12'),
(109, 'ddab6dc2-ebab-4401-acea-8de319450fc7', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:00:12', '2026-07-22 15:00:12', '2026-07-22 15:00:12'),
(110, 'b4c7e2f3-58e0-40bd-a8ac-ec591e40ad56', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:00:41', '2026-07-22 15:00:41', '2026-07-22 15:00:41'),
(111, '4f51855c-b5d6-44b3-adea-36a42e16e056', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:00:42', '2026-07-22 15:00:42', '2026-07-22 15:00:42'),
(112, '0430208e-95b6-44a3-815b-5f6c03d6771c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:01:11', '2026-07-22 15:01:11', '2026-07-22 15:01:11'),
(113, 'ccf72097-f298-4861-8d7e-2e3a94102239', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:01:12', '2026-07-22 15:01:12', '2026-07-22 15:01:12'),
(114, 'a10c8b79-7374-4cf8-bc69-15cc5d330341', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:01:41', '2026-07-22 15:01:41', '2026-07-22 15:01:41'),
(115, '5588cb8a-5c9a-400e-99ac-5405ca834677', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:01:42', '2026-07-22 15:01:42', '2026-07-22 15:01:42'),
(116, 'c8ce06d2-89d0-48a6-abf1-d7e5ea021175', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:02:12', '2026-07-22 15:02:12', '2026-07-22 15:02:12'),
(117, 'ec2ff009-7580-4e59-b63f-f94f3dd25db0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:02:12', '2026-07-22 15:02:12', '2026-07-22 15:02:12'),
(118, '17a25a61-bf94-4ac8-823c-312ed26afafb', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:02:42', '2026-07-22 15:02:42', '2026-07-22 15:02:42'),
(119, '32a01e9e-861d-4f25-9430-b990c33bd384', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:02:42', '2026-07-22 15:02:42', '2026-07-22 15:02:42'),
(120, '03488508-a5cb-4222-a7b1-c1ed954eff86', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:03:12', '2026-07-22 15:03:12', '2026-07-22 15:03:12'),
(121, 'edda4563-4f46-47d7-b82e-39a26cd0dc8d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:03:12', '2026-07-22 15:03:12', '2026-07-22 15:03:12'),
(122, '3dd6f8e5-7e80-4477-b3d7-051651aa6e83', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:03:41', '2026-07-22 15:03:41', '2026-07-22 15:03:41'),
(123, '790666e4-493d-4d52-85cd-4cef8e18cb98', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:03:42', '2026-07-22 15:03:42', '2026-07-22 15:03:42'),
(124, 'b0cfa8b1-ae17-4cfa-81c0-4e7778c3e9f6', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:04:11', '2026-07-22 15:04:11', '2026-07-22 15:04:11'),
(125, '2e99f239-aacc-4497-a735-789a89d5fde8', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:04:12', '2026-07-22 15:04:12', '2026-07-22 15:04:12'),
(126, '66f37fd3-4472-4728-b9f3-69412556846f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:04:42', '2026-07-22 15:04:42', '2026-07-22 15:04:42'),
(127, '62dd691d-cfdb-4ded-91f7-0942e5543456', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:04:42', '2026-07-22 15:04:42', '2026-07-22 15:04:42'),
(128, '166a60d9-a92d-4be5-8707-2ac04b989bd5', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:05:12', '2026-07-22 15:05:12', '2026-07-22 15:05:12'),
(129, '7b0ab53d-a60a-41ef-af98-f80f2d91c8bf', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:05:12', '2026-07-22 15:05:12', '2026-07-22 15:05:12'),
(130, 'c22e1b84-cb21-4823-92d0-2d7381140ca9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:05:42', '2026-07-22 15:05:42', '2026-07-22 15:05:42'),
(131, '85efca4a-4ebf-423e-8271-6d117cc253a4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:05:42', '2026-07-22 15:05:42', '2026-07-22 15:05:42'),
(132, '1508dc9c-091f-4f20-9b3d-4a3a001f3925', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:06:02', '2026-07-22 15:06:02', '2026-07-22 15:06:02'),
(133, 'bce549b4-f743-4526-b149-916e6f22ee3b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:06:02', '2026-07-22 15:06:02', '2026-07-22 15:06:02'),
(134, '021c2b09-6fda-49e4-90b9-aa1ad54b0a62', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:06:02', '2026-07-22 15:06:02', '2026-07-22 15:06:02'),
(135, '7c54ee38-31bc-45f9-a1f3-3a9a628511f7', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:06:32', '2026-07-22 15:06:32', '2026-07-22 15:06:32'),
(136, '71736ee2-a816-4466-bab1-4f04a256bea3', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:06:33', '2026-07-22 15:06:33', '2026-07-22 15:06:33'),
(137, '69590cd9-fe2d-464c-a596-4f97f192f881', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:07:02', '2026-07-22 15:07:02', '2026-07-22 15:07:02'),
(138, 'c5297032-1b66-4156-9c41-1d46ec578c9b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:07:02', '2026-07-22 15:07:02', '2026-07-22 15:07:02'),
(139, '2a39de36-694c-4e18-bbf5-d33d9950630b', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:07:03', '2026-07-22 15:07:03', '2026-07-22 15:07:03'),
(140, 'eef1aa3e-a8ac-4deb-b5f2-64f556e95d58', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:07:03', '2026-07-22 15:07:03', '2026-07-22 15:07:03'),
(141, 'b26c6b48-761d-4970-b09f-0ef7eb262bfc', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:07:04', '2026-07-22 15:07:04', '2026-07-22 15:07:04'),
(142, 'd94882a8-55ec-47ed-9e37-9870dff75db5', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:07:12', '2026-07-22 15:07:12', '2026-07-22 15:07:12'),
(143, 'c049fbb4-8519-4d6a-a972-804807a6b89a', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:08:42', '2026-07-22 15:08:42', '2026-07-22 15:08:42'),
(144, '6c11beee-1107-40b4-b846-10ef013cd95c', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:11:20', '2026-07-22 15:11:20', '2026-07-22 15:11:20'),
(145, '6a3a8da9-018e-483f-a3ae-4b472373ccce', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 15:11:57', '2026-07-23 15:11:57', '2026-07-23 15:11:57'),
(146, '193f40a1-aeb0-4ea0-94ae-5a9194f36e91', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 15:13:41', '2026-07-23 15:13:41', '2026-07-23 15:13:41'),
(147, 'bab14051-4f9a-41e7-b1ea-a11f062680d3', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 15:13:44', '2026-07-23 15:13:44', '2026-07-23 15:13:44'),
(148, 'd39a5a42-0708-4360-bc33-778971163214', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 15:13:46', '2026-07-23 15:13:46', '2026-07-23 15:13:46'),
(149, 'f9a90f68-5fd5-415c-aed6-e8c9a7fd4e3e', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 15:15:35', '2026-07-23 15:15:35', '2026-07-23 15:15:35'),
(150, '1aef9431-e111-47fd-a1a9-638fe1331a49', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:15:58', '2026-07-22 15:15:58', '2026-07-22 15:15:58'),
(151, 'cacabc9e-8dd3-4b8b-ab3c-2ef2aaa3b71d', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:16:00', '2026-07-22 15:16:00', '2026-07-22 15:16:00'),
(152, 'bcbebf1d-a403-4d7a-8e5d-6713e5bde922', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:17:54', '2026-07-22 15:17:54', '2026-07-22 15:17:54'),
(153, '56de48d7-802e-4672-ab5a-2282d50cf354', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:17:54', '2026-07-22 15:17:54', '2026-07-22 15:17:54'),
(154, 'ed27e8fd-5a2e-4606-a077-dc3bea5f17c4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:17:54', '2026-07-22 15:17:54', '2026-07-22 15:17:54'),
(155, 'ff34b55e-c644-40c5-88e7-df31ada084d4', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:18:08', '2026-07-22 15:18:08', '2026-07-22 15:18:08'),
(156, 'e5e6e44e-77d4-488d-8a96-26725f368714', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:19:36', '2026-07-22 15:19:36', '2026-07-22 15:19:36'),
(157, '604bf6f1-49d7-4c3f-ae59-ae2bbc536604', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:17', '2026-07-22 15:23:17', '2026-07-22 15:23:17'),
(158, 'ab67e89b-5d89-443e-8d3b-1069a429599e', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:19', '2026-07-22 15:23:19', '2026-07-22 15:23:19'),
(159, '08ae99e4-1e22-449c-9682-d36bfc66228d', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:21', '2026-07-22 15:23:21', '2026-07-22 15:23:21'),
(160, 'cb267871-9477-42f9-9a13-8217451ae1a4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:21', '2026-07-22 15:23:21', '2026-07-22 15:23:21'),
(161, 'dd782e97-1702-47b0-9773-55490ff4a971', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:22', '2026-07-22 15:23:22', '2026-07-22 15:23:22'),
(162, 'a34496fc-47a6-4798-b0f9-4ed432c41baf', NULL, '/riwayat-apar/ajukan-service', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:24', '2026-07-22 15:23:24', '2026-07-22 15:23:24'),
(163, 'cad2ea16-8f17-4da7-9c13-386d77280b5f', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:24', '2026-07-22 15:23:24', '2026-07-22 15:23:24'),
(164, '77d2246d-f745-4d8a-861c-f1192ead1d0e', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:51', '2026-07-22 15:23:51', '2026-07-22 15:23:51'),
(165, '63cada28-1f49-4ec3-b92a-5d94b3f562cc', NULL, '/order/2/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:52', '2026-07-22 15:23:52', '2026-07-22 15:23:52'),
(166, '3e609208-d8cd-405f-95dc-53509724fd17', NULL, '/order/2/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:23:58', '2026-07-22 15:23:58', '2026-07-22 15:23:58'),
(167, '20d43ecc-4e3f-46c2-a328-b950db677056', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:00', '2026-07-22 15:24:00', '2026-07-22 15:24:00'),
(168, 'd834dd90-25f9-4fab-babb-a34debc1ac35', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:27', '2026-07-22 15:24:27', '2026-07-22 15:24:27'),
(169, '2f0f4589-003e-40e5-b494-d13ae2a38996', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:27', '2026-07-22 15:24:27', '2026-07-22 15:24:27'),
(170, 'fc8507d5-b3b1-4e28-9f14-c3a04d76edc5', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:28', '2026-07-22 15:24:28', '2026-07-22 15:24:28');
INSERT INTO `website_visits` (`id`, `visitor_id`, `session_id`, `page_url`, `ip_address`, `user_agent`, `event_type`, `product_id`, `page_title`, `visited_at`, `created_at`, `updated_at`) VALUES
(171, 'ac238c97-cbf0-42dd-a97d-518e8adf53b3', NULL, '/riwayat-apar/2/confirm-received', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:31', '2026-07-22 15:24:31', '2026-07-22 15:24:31'),
(172, 'd0b6246a-3489-4862-9f87-4e2d96c4eb32', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:31', '2026-07-22 15:24:31', '2026-07-22 15:24:31'),
(173, '2609b77a-805d-4376-bb85-a843de5ed725', NULL, '/testimoni', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:42', '2026-07-22 15:24:42', '2026-07-22 15:24:42'),
(174, '20481dd7-c032-4880-a966-0ab0b9b343c0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:43', '2026-07-22 15:24:43', '2026-07-22 15:24:43'),
(175, '2772bcde-f53b-423c-aefa-e4c4b335ed9c', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:45', '2026-07-22 15:24:45', '2026-07-22 15:24:45'),
(176, '08e8df0a-f6b3-4d1e-9653-c8964453c983', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:45', '2026-07-22 15:24:45', '2026-07-22 15:24:45'),
(177, '592924a3-fafc-4c4a-980a-288763561948', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:24:46', '2026-07-22 15:24:46', '2026-07-22 15:24:46'),
(178, 'a71a4b16-f8cf-4a14-a27c-773d0dde7d84', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:25:15', '2026-07-22 15:25:15', '2026-07-22 15:25:15'),
(179, '9743797d-3382-4b50-b91e-c78abe373388', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:25:16', '2026-07-22 15:25:16', '2026-07-22 15:25:16'),
(180, '36c3b321-28cc-4e28-9794-478ce39c3b65', NULL, '/invoice/2', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:25:16', '2026-07-22 15:25:16', '2026-07-22 15:25:16'),
(181, '80858383-eb79-42cf-8db1-b6ff0774e6d4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:25:45', '2026-07-22 15:25:45', '2026-07-22 15:25:45'),
(182, '01458c5a-42c5-4643-a57f-65ff081ee320', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:25:46', '2026-07-22 15:25:46', '2026-07-22 15:25:46'),
(183, '4ecc352a-9cd3-4ca3-a5c3-8a01a1a7d512', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:26:15', '2026-07-22 15:26:15', '2026-07-22 15:26:15'),
(184, 'ff657a64-09cb-4a5f-a457-89d1063f5f50', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:26:16', '2026-07-22 15:26:16', '2026-07-22 15:26:16'),
(185, 'bf95e898-6a48-4f6a-9498-682f80981bb5', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:26:45', '2026-07-22 15:26:45', '2026-07-22 15:26:45'),
(186, 'b6a51451-3b5d-4c63-b559-692610e20a30', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:26:46', '2026-07-22 15:26:46', '2026-07-22 15:26:46'),
(187, '68d9c673-0d10-4a73-bd26-f10fe701acfe', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:27:15', '2026-07-22 15:27:15', '2026-07-22 15:27:15'),
(188, '7f78ecf3-c281-4427-b347-557e1c956420', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:27:16', '2026-07-22 15:27:16', '2026-07-22 15:27:16'),
(189, 'bf6e8af7-c1a9-475a-82eb-84e04901030d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:27:45', '2026-07-22 15:27:45', '2026-07-22 15:27:45'),
(190, '9c8b319a-7464-4f98-972c-7c84595c99e6', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:27:46', '2026-07-22 15:27:46', '2026-07-22 15:27:46'),
(191, 'd54545ca-bfae-4915-a5d8-1c60ea8dfcfb', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:28:15', '2026-07-22 15:28:15', '2026-07-22 15:28:15'),
(192, '8fb84f0e-ba44-488f-ae5d-13db7f0b0542', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:28:16', '2026-07-22 15:28:16', '2026-07-22 15:28:16'),
(193, '19368673-b9e8-4a5a-bf14-ad18de898a16', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:28:45', '2026-07-22 15:28:45', '2026-07-22 15:28:45'),
(194, 'ad425011-a233-4489-a7b8-4b86537f151a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:28:46', '2026-07-22 15:28:46', '2026-07-22 15:28:46'),
(195, '582fcbbf-a0f4-4cb7-b3b8-cdee8f8ca87d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:29:41', '2026-07-22 15:29:41', '2026-07-22 15:29:41'),
(196, '7f98aa68-e6fc-407b-8ff7-4d1e5e2d95cb', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:29:41', '2026-07-22 15:29:41', '2026-07-22 15:29:41'),
(197, '49e8fb5f-a597-4504-9b4a-d57ecf23cef2', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:29:45', '2026-07-22 15:29:45', '2026-07-22 15:29:45'),
(198, '217d9fc9-95bf-4bd3-b6d0-57e52c1349d4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:29:46', '2026-07-22 15:29:46', '2026-07-22 15:29:46'),
(199, 'ade346f0-009f-4eb1-945e-7c161bf70b17', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:30:15', '2026-07-22 15:30:15', '2026-07-22 15:30:15'),
(200, '63061b20-4c98-4b16-bc68-de7ec4afaf8d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:30:16', '2026-07-22 15:30:16', '2026-07-22 15:30:16'),
(201, 'e68c6bb7-7906-42b1-abe6-431e53e69731', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:30:45', '2026-07-22 15:30:45', '2026-07-22 15:30:45'),
(202, '9e900f78-ed53-4b59-93f1-58890de8bffb', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:30:46', '2026-07-22 15:30:46', '2026-07-22 15:30:46'),
(203, '9aac480f-90dd-44db-b325-caefc7c8aa7d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:31:15', '2026-07-22 15:31:15', '2026-07-22 15:31:15'),
(204, '3736c9aa-86d6-4086-acf4-b0b0e08701b3', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:31:16', '2026-07-22 15:31:16', '2026-07-22 15:31:16'),
(205, '4050895c-6b39-49a0-8257-68c5c977ef02', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:31:45', '2026-07-22 15:31:45', '2026-07-22 15:31:45'),
(206, '36bac7ce-1499-4691-9fc5-f6ed070821d4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:31:46', '2026-07-22 15:31:46', '2026-07-22 15:31:46'),
(207, '4c36be02-0f42-4b16-8569-df9abe264f5d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:32:15', '2026-07-22 15:32:15', '2026-07-22 15:32:15'),
(208, 'b5dbaa59-ec8c-4003-bfa2-bf9d1ee6ffe0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:32:16', '2026-07-22 15:32:16', '2026-07-22 15:32:16'),
(209, '0cfb477f-fae6-4141-b7a8-6ec180249718', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:32:45', '2026-07-22 15:32:45', '2026-07-22 15:32:45'),
(210, '89b9adc2-5be7-49ee-9a77-9dff7f50df5c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:32:46', '2026-07-22 15:32:46', '2026-07-22 15:32:46'),
(211, '6c9175f5-1103-4aa0-ad74-a43a8260a598', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:33:15', '2026-07-22 15:33:15', '2026-07-22 15:33:15'),
(212, '0c531c58-2d73-4e2d-9f0e-8932e1fdb501', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:33:16', '2026-07-22 15:33:16', '2026-07-22 15:33:16'),
(213, '65131e39-bdd5-4918-ad5e-88504952f436', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:33:45', '2026-07-22 15:33:45', '2026-07-22 15:33:45'),
(214, 'd51533d9-5930-4388-96e8-9cd98461157f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:33:46', '2026-07-22 15:33:46', '2026-07-22 15:33:46'),
(215, '79f27954-e042-46c1-9cbe-3e8bdbe4f5f2', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:34:15', '2026-07-22 15:34:15', '2026-07-22 15:34:15'),
(216, 'ce6002eb-a2f6-478e-9f08-d082ef5e91c1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:34:16', '2026-07-22 15:34:16', '2026-07-22 15:34:16'),
(217, '508e1040-46aa-4a1e-9180-fbe1a71b21b8', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:34:45', '2026-07-22 15:34:45', '2026-07-22 15:34:45'),
(218, '92eaba58-beaa-4a86-90a6-93a99d4cfcc7', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:34:46', '2026-07-22 15:34:46', '2026-07-22 15:34:46'),
(219, '0d7dfdf6-16c2-4080-ba52-7b2b79f1258a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:35:15', '2026-07-22 15:35:15', '2026-07-22 15:35:15'),
(220, '813b662c-591a-4bf1-9783-3cb9f3ae7fed', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:35:16', '2026-07-22 15:35:16', '2026-07-22 15:35:16'),
(221, 'aa930921-f84f-4092-829e-a58e2a0a9117', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:35:48', '2026-07-22 15:35:48', '2026-07-22 15:35:48'),
(222, 'e2c8eaaf-6f85-424b-a618-82116a1858a1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:35:48', '2026-07-22 15:35:48', '2026-07-22 15:35:48'),
(223, '6b57e3a2-2780-43bc-b107-349599a7da64', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:36:16', '2026-07-22 15:36:16', '2026-07-22 15:36:16'),
(224, '500b0635-2abf-45b7-8e04-9b9b19566528', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:36:16', '2026-07-22 15:36:16', '2026-07-22 15:36:16'),
(225, '7b2f9a3e-ac20-48a2-b36d-071413565117', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:36:45', '2026-07-22 15:36:45', '2026-07-22 15:36:45'),
(226, 'fe464be6-93f7-4278-bb80-e0bf7fe8ad73', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:36:46', '2026-07-22 15:36:46', '2026-07-22 15:36:46'),
(227, '6e3f9e98-16d8-449b-b840-82a523638951', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:37:15', '2026-07-22 15:37:15', '2026-07-22 15:37:15'),
(228, 'a356d672-d407-48fa-883e-33e8d71a68f1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:37:16', '2026-07-22 15:37:16', '2026-07-22 15:37:16'),
(229, '32502748-a735-4104-882b-c4f9cd5be229', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:37:45', '2026-07-22 15:37:45', '2026-07-22 15:37:45'),
(230, '7de79ebb-7036-40e4-865a-09d17b44f80e', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:37:46', '2026-07-22 15:37:46', '2026-07-22 15:37:46'),
(231, 'f6d9d0c0-3854-4ecf-b2f0-4782190e272a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:15', '2026-07-22 15:38:15', '2026-07-22 15:38:15'),
(232, '5cd6944c-7748-4ebc-a73b-2dfe357f059d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:16', '2026-07-22 15:38:16', '2026-07-22 15:38:16'),
(233, 'fc34269e-82b0-455c-9f89-596469a620c7', NULL, '/riwayat-apar/ajukan-service', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:22', '2026-07-22 15:38:22', '2026-07-22 15:38:22'),
(234, '1d7c1126-408b-4ce2-b111-5b0e46d7acf1', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:24', '2026-07-22 15:38:24', '2026-07-22 15:38:24'),
(235, 'ba06ec48-c30a-4911-85ee-bd2765899938', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:36', '2026-07-22 15:38:36', '2026-07-22 15:38:36'),
(236, '66edb298-a2f1-4588-be32-7cf632210855', NULL, '/order/3/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:37', '2026-07-22 15:38:37', '2026-07-22 15:38:37'),
(237, '10029c62-0673-48f9-b5f6-d09007e379f5', NULL, '/order/3/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:42', '2026-07-22 15:38:42', '2026-07-22 15:38:42'),
(238, '8a595fd0-75e3-4f49-9e67-410a7b1eb3c2', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:38:44', '2026-07-22 15:38:44', '2026-07-22 15:38:44'),
(239, '6aec19b8-ce3f-4d3d-968f-1c5d7b7698c0', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:17', '2026-07-22 15:39:17', '2026-07-22 15:39:17'),
(240, 'b57a050a-842c-44d8-9c95-6916e3b8e210', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:17', '2026-07-22 15:39:17', '2026-07-22 15:39:17'),
(241, 'd81eb00f-ae2d-4de9-92c0-02e9fc490f97', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:18', '2026-07-22 15:39:18', '2026-07-22 15:39:18'),
(242, '8ba956d9-ed78-4bd2-8a82-17134193c2de', NULL, '/riwayat-apar/3/confirm-received', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:25', '2026-07-22 15:39:25', '2026-07-22 15:39:25'),
(243, '94b0f98a-3e91-4ed4-8322-cb26c3758cc5', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:25', '2026-07-22 15:39:25', '2026-07-22 15:39:25'),
(244, 'c83afee0-ef5e-4ec7-972b-0aa0a6deea1a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:47', '2026-07-22 15:39:47', '2026-07-22 15:39:47'),
(245, 'e9547228-f44a-460f-b7b9-a1b5cb5dc710', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:48', '2026-07-22 15:39:48', '2026-07-22 15:39:48'),
(246, 'f5d5cb09-f27d-4ff0-89bd-317bf01b88d1', NULL, '/testimoni', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:51', '2026-07-22 15:39:51', '2026-07-22 15:39:51'),
(247, '9a04fc6f-31d0-4fb1-be04-87ed427fcf3c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:52', '2026-07-22 15:39:52', '2026-07-22 15:39:52'),
(248, 'a066f393-ac68-4197-ab29-b415dc0dac2b', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:54', '2026-07-22 15:39:54', '2026-07-22 15:39:54'),
(249, '7db92ac8-898b-4950-9cb8-5faa65d00e4f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:54', '2026-07-22 15:39:54', '2026-07-22 15:39:54'),
(250, '230c8983-a2c2-412a-a470-286a6830e55c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:39:54', '2026-07-22 15:39:54', '2026-07-22 15:39:54'),
(251, '762b8c4e-10b0-4c0d-bd0e-d2e3966b7881', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:40:24', '2026-07-22 15:40:24', '2026-07-22 15:40:24'),
(252, '8e6eda9a-32c7-47bd-9d33-9ea05268e815', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:40:24', '2026-07-22 15:40:24', '2026-07-22 15:40:24'),
(253, 'fe8606db-8bfd-45eb-8470-0e083ffed6a2', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:40:54', '2026-07-22 15:40:54', '2026-07-22 15:40:54'),
(254, 'c196171d-7fd5-4921-b1d4-1c1133f5852d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:40:54', '2026-07-22 15:40:55', '2026-07-22 15:40:55'),
(255, '16aa6c0c-b294-4528-a622-d91842783893', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:41:24', '2026-07-22 15:41:24', '2026-07-22 15:41:24'),
(256, 'ddde44fb-0e72-414a-9800-9f597b9bd16f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:41:25', '2026-07-22 15:41:25', '2026-07-22 15:41:25'),
(257, 'de897e72-eb88-40df-907d-34e75554f5f4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:41:54', '2026-07-22 15:41:54', '2026-07-22 15:41:54'),
(258, '90df9206-2c7d-406d-a852-3da91d7d3e35', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:41:55', '2026-07-22 15:41:55', '2026-07-22 15:41:55'),
(259, '45ac0e9b-1a16-4207-b530-923f39afadc4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:42:24', '2026-07-22 15:42:24', '2026-07-22 15:42:24'),
(260, '55b732b2-24ef-4d40-8a59-1efb38d36fee', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:42:25', '2026-07-22 15:42:25', '2026-07-22 15:42:25'),
(261, 'e9d0f909-829a-4067-b795-961fd5adb0bd', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:42:54', '2026-07-22 15:42:54', '2026-07-22 15:42:54'),
(262, '1c906321-d2cd-412a-afef-1d754f666048', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:42:55', '2026-07-22 15:42:55', '2026-07-22 15:42:55'),
(263, 'f8b36934-7f9f-4e15-abe7-e0a128089d81', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:43:25', '2026-07-22 15:43:25', '2026-07-22 15:43:25'),
(264, 'e9774132-b29d-4476-bc23-ab0298e35587', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:43:25', '2026-07-22 15:43:25', '2026-07-22 15:43:25'),
(265, '442f0ac9-6d9d-4df0-9799-0dcedd178879', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:43:54', '2026-07-22 15:43:54', '2026-07-22 15:43:54'),
(266, '1a4bf4ac-3c7c-430e-bed4-351971e40991', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:43:55', '2026-07-22 15:43:55', '2026-07-22 15:43:55'),
(267, 'b0008e84-7f47-4217-9785-6e53af91a7a1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:44:24', '2026-07-22 15:44:24', '2026-07-22 15:44:24'),
(268, 'e1f3ecaf-36cb-42c4-9b36-4b4626820b88', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:44:25', '2026-07-22 15:44:25', '2026-07-22 15:44:25'),
(269, '1f006df9-c924-4e3f-8ed3-926fac10aeee', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:44:54', '2026-07-22 15:44:54', '2026-07-22 15:44:54'),
(270, '7d01f8c0-9ee6-40c6-b389-13dcde1932c8', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:44:55', '2026-07-22 15:44:55', '2026-07-22 15:44:55'),
(271, 'a917ff1a-9602-4f6b-a09c-fae0052cec71', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:45:24', '2026-07-22 15:45:24', '2026-07-22 15:45:24'),
(272, 'a2c67c87-e40c-4e51-a4f5-0ef0c8a46264', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:45:25', '2026-07-22 15:45:25', '2026-07-22 15:45:25'),
(273, '23ad4202-99ed-4f55-9f82-7cc5f750847a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:45:55', '2026-07-22 15:45:55', '2026-07-22 15:45:55'),
(274, 'e1065227-cc4a-4bb1-8346-f59b9b886cd3', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:45:55', '2026-07-22 15:45:55', '2026-07-22 15:45:55'),
(275, '9f773ad2-3038-414f-a72f-e558d69e5292', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:46:24', '2026-07-22 15:46:24', '2026-07-22 15:46:24'),
(276, '8a8841e4-ad19-4c5f-ad3a-2989e45c1239', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:46:25', '2026-07-22 15:46:25', '2026-07-22 15:46:25'),
(277, '6dfb93be-71b8-49c1-b837-6d20734cee43', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:46:54', '2026-07-22 15:46:54', '2026-07-22 15:46:54'),
(278, 'ef8c58d1-6ebd-4a44-adc5-2fb469c6b301', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:46:55', '2026-07-22 15:46:55', '2026-07-22 15:46:55'),
(279, 'fcb25522-882b-4ea9-a519-1d1d6064d6f1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:47:24', '2026-07-22 15:47:24', '2026-07-22 15:47:24'),
(280, '2119e8e3-7d63-4045-869c-5f0a3a60c3b6', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:47:24', '2026-07-22 15:47:24', '2026-07-22 15:47:24'),
(281, '5094642c-4dde-47ea-9e7d-f3d292c6a777', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:47:54', '2026-07-22 15:47:54', '2026-07-22 15:47:54'),
(282, '80623af6-a35f-4cf7-9ac8-9a5c86ab3939', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:47:54', '2026-07-22 15:47:54', '2026-07-22 15:47:54'),
(283, 'c8e75d1d-3b42-423f-a8fc-d8ba8714304b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:48:24', '2026-07-22 15:48:24', '2026-07-22 15:48:24'),
(284, '72142d95-6de4-4ccc-8c19-87cc582fba6d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:48:24', '2026-07-22 15:48:24', '2026-07-22 15:48:24'),
(285, '8912cd7e-42be-4e9f-94f9-00c082106bbc', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:48:54', '2026-07-22 15:48:54', '2026-07-22 15:48:54'),
(286, 'da9906eb-44b0-4a01-9326-6f789693d3c1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:48:55', '2026-07-22 15:48:55', '2026-07-22 15:48:55'),
(287, '9f77e62f-4c88-4e15-8d26-455f33d90b2b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:49:24', '2026-07-22 15:49:24', '2026-07-22 15:49:24'),
(288, '43ad818f-3ea0-459b-9ca8-a792cac67d13', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:49:25', '2026-07-22 15:49:25', '2026-07-22 15:49:25'),
(289, 'a5e88197-fd4c-4f31-bc43-f883998e2072', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:49:54', '2026-07-22 15:49:54', '2026-07-22 15:49:54'),
(290, '351cb362-da6c-4703-b2f9-1698a946104b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:49:55', '2026-07-22 15:49:55', '2026-07-22 15:49:55'),
(291, '5f3466dd-0084-46f5-9bed-513f9214c3db', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:24', '2026-07-22 15:50:24', '2026-07-22 15:50:24'),
(292, '8fb8ea69-28ca-4ed9-b75d-f6d0f7ad5121', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:25', '2026-07-22 15:50:25', '2026-07-22 15:50:25'),
(293, '07f84f00-047f-4776-a3b7-9c51a3248afc', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:44', '2026-07-22 15:50:44', '2026-07-22 15:50:44'),
(294, 'a595ea4d-0ba3-49ee-b639-3d5d30395c5f', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:46', '2026-07-22 15:50:46', '2026-07-22 15:50:46'),
(295, '363c7eb2-2f2f-4d43-b486-de2f36f90330', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:46', '2026-07-22 15:50:46', '2026-07-22 15:50:46'),
(296, '4c04eba5-6222-429c-918e-f34d075d3698', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:47', '2026-07-22 15:50:47', '2026-07-22 15:50:47'),
(297, '28f2a01a-b9d3-41b4-bbb7-92a40857600b', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:49', '2026-07-22 15:50:49', '2026-07-22 15:50:49'),
(298, '147570c2-6530-47d6-b5da-08bac8a4f8b2', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:50', '2026-07-22 15:50:50', '2026-07-22 15:50:50'),
(299, '0c96a049-b663-4d74-ac21-cf66225bd88e', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:51', '2026-07-22 15:50:51', '2026-07-22 15:50:51'),
(300, '41a7553a-8b36-4ab1-acfd-4d83dd1d20c8', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:51', '2026-07-22 15:50:51', '2026-07-22 15:50:51'),
(301, '728fd3fb-292b-44a6-abef-c4399e7fca30', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:52', '2026-07-22 15:50:52', '2026-07-22 15:50:52'),
(302, '4c511a66-c0f0-4b2d-bc75-4ffaf5b87c64', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:50:53', '2026-07-22 15:50:53', '2026-07-22 15:50:53'),
(303, 'b7485b62-f3a1-40f2-bf1d-002aeee76086', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:51:36', '2026-07-22 15:51:36', '2026-07-22 15:51:36'),
(304, '3661c6c6-b537-486e-9e06-be64dfe2d5e1', NULL, '/order/4/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:51:36', '2026-07-22 15:51:36', '2026-07-22 15:51:36'),
(305, '9fc36076-a4b3-4bf3-a554-d9ad96041fef', NULL, '/order/4/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:51:46', '2026-07-22 15:51:46', '2026-07-22 15:51:46'),
(306, '073be65e-6013-4c7c-9817-e7230676bab2', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:51:47', '2026-07-22 15:51:47', '2026-07-22 15:51:47'),
(307, 'e02a3f2d-2d74-41d9-908b-9569b79198d9', NULL, '/invoice/4', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:05', '2026-07-22 15:52:05', '2026-07-22 15:52:05'),
(308, 'a6d0d0a5-95e4-4512-858b-40c23168aaa0', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:13', '2026-07-22 15:52:13', '2026-07-22 15:52:13'),
(309, '68598948-6e00-4063-a423-0b16f37145b4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:13', '2026-07-22 15:52:13', '2026-07-22 15:52:13'),
(310, '3463c6c1-49db-4744-8c9a-b64f12ccdb3d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:14', '2026-07-22 15:52:14', '2026-07-22 15:52:14'),
(311, 'ad184e75-a493-4aed-8e98-9def14433e19', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:32', '2026-07-22 15:52:32', '2026-07-22 15:52:32'),
(312, '3d3518ff-d464-42e2-93ac-3ec0c92549c9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:33', '2026-07-22 15:52:33', '2026-07-22 15:52:33'),
(313, '65b6cedb-b857-459f-a7bc-aca79f8f8bc7', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:33', '2026-07-22 15:52:33', '2026-07-22 15:52:33'),
(314, '5e64c1d0-1843-43c3-93b7-6b900a5f2aa3', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:52:50', '2026-07-22 15:52:50', '2026-07-22 15:52:50'),
(315, '777cdaad-0921-44cb-bd2e-150ad7d73a00', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:53:08', '2026-07-22 15:53:08', '2026-07-22 15:53:08'),
(316, 'c7930dbd-d3a4-477b-9fbe-259d2dc11e5c', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:53:12', '2026-07-22 15:53:12', '2026-07-22 15:53:12'),
(317, '8334dbe4-d5dd-443e-81c6-95492a2be088', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:53:30', '2026-07-22 15:53:30', '2026-07-22 15:53:30'),
(318, '007847f1-d711-4ed5-a3a3-95b27e50940c', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:53:32', '2026-07-22 15:53:32', '2026-07-22 15:53:32'),
(319, 'c12137be-a848-4c2e-b121-df8a015fefee', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:58:37', '2026-07-22 15:58:37', '2026-07-22 15:58:37'),
(320, 'b8df6e5a-b9b7-4355-9473-2a6714a4e4d6', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-22 15:59:22', '2026-07-22 15:59:22', '2026-07-22 15:59:22'),
(321, 'ffbfae1e-d9a2-4926-af32-6735fa44ce89', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:44:21', '2026-07-23 01:44:21', '2026-07-23 01:44:21'),
(322, '6848e5e7-0ea5-4e3b-bdae-44880c83e908', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:45:02', '2026-07-23 01:45:02', '2026-07-23 01:45:02'),
(323, '069e4f13-3b43-4cc7-93e9-f07642f75d5f', NULL, '/profile', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:45:12', '2026-07-23 01:45:12', '2026-07-23 01:45:12'),
(324, '6cec870b-1754-46be-bfcb-e7db52b29fa0', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:45:19', '2026-07-23 01:45:19', '2026-07-23 01:45:19'),
(325, 'f4aa6f4e-e191-446a-a241-c12759825bef', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:46:33', '2026-07-23 01:46:33', '2026-07-23 01:46:33'),
(326, 'ec7e8f1e-6248-44da-aee4-9e42969dceb4', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:46:39', '2026-07-23 01:46:39', '2026-07-23 01:46:39'),
(327, 'e08843ca-e9fe-4d88-a164-fcdcc6f03873', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:46:44', '2026-07-23 01:46:44', '2026-07-23 01:46:44'),
(328, '029a2833-4bc3-4186-a09b-8c38d4bf6419', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 01:46:53', '2026-07-23 01:46:53', '2026-07-23 01:46:53'),
(329, '4e5bd7b1-ed21-4e5e-ba93-d9df9b07c0f5', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:07:37', '2026-07-23 02:07:37', '2026-07-23 02:07:37'),
(330, '3fb2f120-e2cf-4693-838f-c0e64b708c76', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:08:05', '2026-07-23 02:08:05', '2026-07-23 02:08:05'),
(331, '6f3298fa-d5c7-40d1-b1d3-141d50a42eeb', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:08:22', '2026-07-23 02:08:22', '2026-07-23 02:08:22'),
(332, '2d758436-8ca3-4f58-8fcf-61f54a9639f1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:08:23', '2026-07-23 02:08:23', '2026-07-23 02:08:23'),
(333, 'fb893be7-8d29-4e71-a4b5-90566dec38d2', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:08:23', '2026-07-23 02:08:23', '2026-07-23 02:08:23'),
(334, '31bd5b38-f5f2-4a32-8ca5-67187cd1b991', NULL, '/profile', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:08:29', '2026-07-23 02:08:29', '2026-07-23 02:08:29'),
(335, '08daee16-ba93-4fb9-b81d-b3b9c89151ef', NULL, '/order/address/suggest', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:09:04', '2026-07-23 02:09:04', '2026-07-23 02:09:04'),
(336, '76fdfdc8-0c69-4843-afe7-1e083d0c3544', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:09:15', '2026-07-23 02:09:15', '2026-07-23 02:09:15'),
(337, 'b596f416-04f3-46b3-a3b3-8b982e571f4a', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:09:56', '2026-07-23 02:09:56', '2026-07-23 02:09:56'),
(338, '17a0aba8-3003-4c60-aefb-704a9bceb3ba', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:10:20', '2026-07-23 02:10:20', '2026-07-23 02:10:20'),
(339, 'a8a4b1ed-d1e8-4c87-8f79-6f7488774fd1', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:17:31', '2026-07-23 02:17:31', '2026-07-23 02:17:31');
INSERT INTO `website_visits` (`id`, `visitor_id`, `session_id`, `page_url`, `ip_address`, `user_agent`, `event_type`, `product_id`, `page_title`, `visited_at`, `created_at`, `updated_at`) VALUES
(340, 'b781a092-e9cb-465f-9ab0-b183ffa2a161', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:17:32', '2026-07-23 02:17:32', '2026-07-23 02:17:32'),
(341, '2da594cd-8b87-408b-ad20-5b7637b98440', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:17:36', '2026-07-23 02:17:36', '2026-07-23 02:17:36'),
(342, '1af74871-6486-41be-99ad-b98871e12150', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 02:57:11', '2026-07-23 02:57:11', '2026-07-23 02:57:11'),
(343, '11396c17-cedb-4c4e-a98f-44348862dc66', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:33:54', '2026-07-23 03:33:54', '2026-07-23 03:33:54'),
(344, '927bb15c-503e-4558-9d59-e1fc2e9a4a07', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:33:58', '2026-07-23 03:33:58', '2026-07-23 03:33:58'),
(345, '00fbc879-151c-4637-a202-edaecd2ea746', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:34:24', '2026-07-23 03:34:24', '2026-07-23 03:34:24'),
(346, 'b884e9d3-cf01-44de-a39a-b979f10f851c', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:34:36', '2026-07-23 03:34:36', '2026-07-23 03:34:36'),
(347, '1b7fa3dc-ba77-4969-a137-2658783be806', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:34:42', '2026-07-23 03:34:42', '2026-07-23 03:34:42'),
(348, 'c4b55f09-09ab-4e95-84a5-0fb81c8d89b9', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'add_to_cart', 36, 'APAR TONATA Foam 9 kg (x1)', '2026-07-23 03:34:50', '2026-07-23 03:34:50', '2026-07-23 03:34:50'),
(349, 'c4b55f09-09ab-4e95-84a5-0fb81c8d89b9', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:34:50', '2026-07-23 03:34:50', '2026-07-23 03:34:50'),
(350, 'd8d256d9-3c1c-4a25-81eb-99f352a67636', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:34:51', '2026-07-23 03:34:51', '2026-07-23 03:34:51'),
(351, '4115fa96-a19b-4697-a341-321c30eb7430', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:34:58', '2026-07-23 03:34:58', '2026-07-23 03:34:58'),
(352, 'a45d4a46-1ad5-4b1f-bc0f-09eb05769055', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:35:01', '2026-07-23 03:35:01', '2026-07-23 03:35:01'),
(353, 'cfa53dd6-1808-465b-8c11-bf739e97d0e4', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:36:41', '2026-07-23 03:36:41', '2026-07-23 03:36:41'),
(354, '2a1eaf87-b12a-44e6-95a4-c3d3a13bc90c', NULL, '/order/5/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:36:42', '2026-07-23 03:36:42', '2026-07-23 03:36:42'),
(355, '0c749a20-8a66-4f98-9201-5322770b5971', NULL, '/order/5/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:37:09', '2026-07-23 03:37:09', '2026-07-23 03:37:09'),
(356, '7a4fc0c3-f112-4083-b81b-71e4eb45973e', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:37:11', '2026-07-23 03:37:11', '2026-07-23 03:37:11'),
(357, '3aa07617-e08f-4e5e-9f4e-0ffa5806dc7b', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:37:17', '2026-07-23 03:37:17', '2026-07-23 03:37:17'),
(358, '6f190e70-8ab2-42a5-a80e-900f0d66a89c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:37:17', '2026-07-23 03:37:17', '2026-07-23 03:37:17'),
(359, 'de9fd658-522a-4287-99a8-9e6d8fb8ad5f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:37:17', '2026-07-23 03:37:17', '2026-07-23 03:37:17'),
(360, '00a495ab-71e1-483e-b410-0b820dbd45a7', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:37:47', '2026-07-23 03:37:47', '2026-07-23 03:37:47'),
(361, 'b31b78b4-b030-4581-a270-ed8be2803ea1', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:37:48', '2026-07-23 03:37:48', '2026-07-23 03:37:48'),
(362, 'a75e3ebe-afad-4865-b620-590391abf699', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:38:17', '2026-07-23 03:38:17', '2026-07-23 03:38:17'),
(363, '6876b56c-5314-49c8-b188-26bfe1af097a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:38:18', '2026-07-23 03:38:18', '2026-07-23 03:38:18'),
(364, 'f7873256-7152-40bb-9e27-c300de39630e', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:38:47', '2026-07-23 03:38:47', '2026-07-23 03:38:47'),
(365, '785e5ec7-2e76-491b-ad08-7d3a3d991757', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:38:48', '2026-07-23 03:38:48', '2026-07-23 03:38:48'),
(366, 'dadf2c6d-7013-40a9-b6d6-46b6fc35f252', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:39:17', '2026-07-23 03:39:17', '2026-07-23 03:39:17'),
(367, '77704e9b-1df9-474b-9300-63beffa84817', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:39:18', '2026-07-23 03:39:18', '2026-07-23 03:39:18'),
(368, 'f3f09ecf-9116-4688-be4c-c52bc8d6bd25', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:39:47', '2026-07-23 03:39:47', '2026-07-23 03:39:47'),
(369, '11b9a4d3-1133-4cd0-b50f-3ca7dab58846', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:39:48', '2026-07-23 03:39:48', '2026-07-23 03:39:48'),
(370, 'b84b5209-c0e2-4197-807b-2b0b9777980c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:40:17', '2026-07-23 03:40:17', '2026-07-23 03:40:17'),
(371, '62b281b8-d07f-4a15-922e-ded35219757a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:40:18', '2026-07-23 03:40:18', '2026-07-23 03:40:18'),
(372, 'e2a6cd70-2f7a-4fd0-99d3-b254142221e9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:40:47', '2026-07-23 03:40:47', '2026-07-23 03:40:47'),
(373, 'ab1fa4d0-a107-4fc7-b732-91b321076b95', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:40:48', '2026-07-23 03:40:48', '2026-07-23 03:40:48'),
(374, '672b259d-c6a4-475d-aedf-4c862567176b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:44:58', '2026-07-23 03:44:58', '2026-07-23 03:44:58'),
(375, '7a5fa2ff-4f6f-4a89-b1e7-8ed781c238d3', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-23 03:45:10', '2026-07-23 03:45:10', '2026-07-23 03:45:10'),
(376, 'fec8a4e1-715e-491b-a27f-6c12822a046e', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 13:12:08', '2026-07-24 13:12:08', '2026-07-24 13:12:08'),
(377, '6aa2c70a-7167-4327-9408-8b3aa105da9c', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:21:30', '2026-07-24 14:21:30', '2026-07-24 14:21:30'),
(378, '7fec8fbf-4f44-494e-bc0c-3515a86d0d8a', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:21:33', '2026-07-24 14:21:33', '2026-07-24 14:21:33'),
(379, '436e1d47-2188-4c8d-8cb6-7c7015ab90e8', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:55:26', '2026-07-24 14:55:26', '2026-07-24 14:55:26'),
(380, 'd32b48f8-2241-4d05-9b56-15e08568c217', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:55:26', '2026-07-24 14:55:26', '2026-07-24 14:55:26'),
(381, '80a742ed-f8e3-4b2a-b6fa-c6e08448e197', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:55:47', '2026-07-24 14:55:47', '2026-07-24 14:55:47'),
(382, '347ea1f5-1ad2-470d-9ad8-50876fd1d0c4', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:07', '2026-07-24 14:56:07', '2026-07-24 14:56:07'),
(383, 'd67eb4d5-7531-4273-8207-1fa853b81129', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:07', '2026-07-24 14:56:07', '2026-07-24 14:56:07'),
(384, '04a3bdc1-d95b-4719-a825-73772fbc5f21', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:07', '2026-07-24 14:56:07', '2026-07-24 14:56:07'),
(385, '98114c2a-9681-4309-990d-0aa5b773ef38', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:20', '2026-07-24 14:56:20', '2026-07-24 14:56:20'),
(386, '20458026-e2e5-430b-b6c1-7b26e08bd88e', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:20', '2026-07-24 14:56:20', '2026-07-24 14:56:20'),
(387, '4d06fd0f-bba8-4216-9a7e-25504ac194bf', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:25', '2026-07-24 14:56:25', '2026-07-24 14:56:25'),
(388, 'e041f92c-2c5d-4455-85af-b2f7cdfef0a3', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:25', '2026-07-24 14:56:25', '2026-07-24 14:56:25'),
(389, 'ccd72f50-0760-479a-b456-22e1cb326539', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:36', '2026-07-24 14:56:36', '2026-07-24 14:56:36'),
(390, '4117f0ab-76e6-41dd-942b-1beea6618be4', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:56:37', '2026-07-24 14:56:37', '2026-07-24 14:56:37'),
(391, 'a03594ff-4cc9-4232-9af0-5ce768fd7a4d', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:57:47', '2026-07-24 14:57:47', '2026-07-24 14:57:47'),
(392, '64146f4b-d2f1-4747-85e6-242854f4cf61', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:57:47', '2026-07-24 14:57:47', '2026-07-24 14:57:47'),
(393, '5f8e7b23-7ee5-4d7d-966e-9f812a5aa3e8', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 14:59:04', '2026-07-24 14:59:04', '2026-07-24 14:59:04'),
(394, '420588a7-dda9-4789-a670-08fd1e5b202c', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 15:00:17', '2026-07-24 15:00:17', '2026-07-24 15:00:17'),
(395, 'fb5973f3-3a82-4813-af0c-060b62df24f1', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-24 15:00:20', '2026-07-24 15:00:20', '2026-07-24 15:00:20'),
(396, '6e5fa77e-b8e8-40e6-8a9b-00baf79e623a', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 09:25:19', '2026-07-25 09:25:19', '2026-07-25 09:25:19'),
(397, 'ce2abc64-9c12-4e1c-841d-cf564e898852', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 09:40:28', '2026-07-25 09:40:28', '2026-07-25 09:40:28'),
(398, 'cd0a4170-39e9-4333-8c44-4cfea4102b22', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 09:41:29', '2026-07-25 09:41:29', '2026-07-25 09:41:29'),
(399, '29789b77-4b7e-4118-8b49-ec71a8cd9cb0', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 09:41:31', '2026-07-25 09:41:31', '2026-07-25 09:41:31'),
(400, '85bd77d4-7f63-4455-8498-a58e22f21cea', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 09:53:23', '2026-07-25 09:53:23', '2026-07-25 09:53:23'),
(401, 'b0b6c53c-448a-4bf6-8f5f-1f93faaf6f5f', NULL, '/forgot-password', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 10:34:12', '2026-07-25 10:34:12', '2026-07-25 10:34:12'),
(402, '7e4d135e-8e01-499e-af71-35fa390a3880', NULL, '/forgot-password', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 10:34:50', '2026-07-25 10:34:50', '2026-07-25 10:34:50'),
(403, '405ed348-9b19-4ba2-ac69-c4d91d8ce442', NULL, '/forgot-password', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-25 10:34:50', '2026-07-25 10:34:50', '2026-07-25 10:34:50'),
(404, '4751d87b-bd0b-49df-b002-f70e4ac5c659', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-27 03:29:31', '2026-07-27 03:29:31', '2026-07-27 03:29:31'),
(405, '0deacc6f-2c79-4ab1-a939-50cba1952c23', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-27 05:45:54', '2026-07-27 05:45:54', '2026-07-27 05:45:54'),
(406, '4493311a-61e3-4808-b3e3-3c2d758b0742', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-27 05:46:22', '2026-07-27 05:46:22', '2026-07-27 05:46:22'),
(407, '9e253334-1b25-47e4-869c-500f735e75db', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-27 10:43:59', '2026-07-27 10:43:59', '2026-07-27 10:43:59'),
(408, 'ba06bc4a-8491-4b81-a687-dafca06f565e', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-27 11:06:26', '2026-07-27 11:06:26', '2026-07-27 11:06:26'),
(409, '3f17adff-1954-46b4-b6fb-7e4decad9bab', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 02:10:44', '2026-07-28 02:10:44', '2026-07-28 02:10:44'),
(410, '8d0b2767-57cf-413e-ad01-b3a9f5bb7abe', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:07:51', '2026-07-28 03:07:51', '2026-07-28 03:07:51'),
(411, 'fc52ffe2-b332-4860-a367-b5b41f5fefba', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:07:54', '2026-07-28 03:07:54', '2026-07-28 03:07:54'),
(412, '5a8e0489-947a-48f2-b01d-cbb0a522e3fa', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:07:57', '2026-07-28 03:07:57', '2026-07-28 03:07:57'),
(413, 'db6575b3-26a6-4df7-8298-05b72841b5ff', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:08:22', '2026-07-28 03:08:22', '2026-07-28 03:08:22'),
(414, 'f5274cfa-e21c-4551-b70b-8dd2f6a46f64', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:08:29', '2026-07-28 03:08:29', '2026-07-28 03:08:29'),
(415, '9336d4ae-19eb-4653-a0cd-590783b46542', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:09:00', '2026-07-28 03:09:00', '2026-07-28 03:09:00'),
(416, '33242a81-4d80-4538-a55f-d237b34a9adc', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:09:40', '2026-07-28 03:09:40', '2026-07-28 03:09:40'),
(417, '3e87e3dc-e4b5-414c-8b6b-af717741546f', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:10:01', '2026-07-28 03:10:01', '2026-07-28 03:10:01'),
(418, '31125973-b3cd-46f1-93cc-27bf56e0788f', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:11:17', '2026-07-28 03:11:17', '2026-07-28 03:11:17'),
(419, '2448f753-ecea-4ff2-a587-3526ef775458', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-28 03:11:58', '2026-07-28 03:11:58', '2026-07-28 03:11:58'),
(420, '1c4e0431-0649-4e98-a3c8-241933a57e4e', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:24:56', '2026-07-29 04:24:56', '2026-07-29 04:24:56'),
(421, '849560da-93d5-4994-ae93-37ecd1da72bf', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:25:46', '2026-07-29 04:25:46', '2026-07-29 04:25:46'),
(422, 'b7136122-d06b-4e29-b502-3f7d6b4a1ff1', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:26:16', '2026-07-29 04:26:16', '2026-07-29 04:26:16'),
(423, '32bc745f-5581-4279-b149-1c90c707f2a9', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:26:27', '2026-07-29 04:26:27', '2026-07-29 04:26:27'),
(424, 'ddb3f3a5-2b69-49d3-a8a2-6add6824e8b6', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:38:07', '2026-07-29 04:38:07', '2026-07-29 04:38:07'),
(425, '0f564d95-d367-4e2e-b0cd-005387335229', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:38:12', '2026-07-29 04:38:12', '2026-07-29 04:38:12'),
(426, 'd43f12d6-87fd-4639-a98e-c68370c7972a', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:38:24', '2026-07-29 04:38:24', '2026-07-29 04:38:24'),
(427, '55fcb308-c7f0-440c-ac2a-9ce914feae7d', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:00', '2026-07-29 04:39:00', '2026-07-29 04:39:00'),
(428, '79cdf93a-e9b7-4ec0-9d5f-99530a842754', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:00', '2026-07-29 04:39:00', '2026-07-29 04:39:00'),
(429, 'a44f0623-b39e-4055-ab95-223febf1b915', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'add_to_cart', 19, 'APAR FIREFIX CO2 2 kg (x1)', '2026-07-29 04:39:26', '2026-07-29 04:39:26', '2026-07-29 04:39:26'),
(430, 'a44f0623-b39e-4055-ab95-223febf1b915', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:26', '2026-07-29 04:39:26', '2026-07-29 04:39:26'),
(431, '0352ab6f-8d9e-48be-afab-96a3543465e2', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:27', '2026-07-29 04:39:27', '2026-07-29 04:39:27'),
(432, '88a797af-8175-4b0c-9907-fab9b09f4d1c', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:30', '2026-07-29 04:39:30', '2026-07-29 04:39:30'),
(433, 'c2a8710c-7ad1-4767-902f-1b31e6eabb0f', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:36', '2026-07-29 04:39:36', '2026-07-29 04:39:36'),
(434, '649bc617-9963-45bf-974b-642fd192ee58', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:52', '2026-07-29 04:39:52', '2026-07-29 04:39:52'),
(435, 'f1a64b0d-9688-43a8-b01e-575259324968', NULL, '/order/6/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:39:53', '2026-07-29 04:39:53', '2026-07-29 04:39:53'),
(436, 'c6f7ca49-e29d-4067-9569-d6cda0a17dd4', NULL, '/order/6/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:05', '2026-07-29 04:40:05', '2026-07-29 04:40:05'),
(437, '01ac0901-7da8-40a0-8d2b-5f79144450e8', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:07', '2026-07-29 04:40:07', '2026-07-29 04:40:07'),
(438, '17f30fc8-0432-45cd-a497-110ee6f5acaa', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:19', '2026-07-29 04:40:19', '2026-07-29 04:40:19'),
(439, 'd1389d66-2168-4fd7-827b-d5ac30a2960b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:20', '2026-07-29 04:40:20', '2026-07-29 04:40:20'),
(440, '43050a0b-04d1-4a82-93c4-546015bed4fd', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:20', '2026-07-29 04:40:20', '2026-07-29 04:40:20'),
(441, '91477f49-910d-4d2c-9489-2f037f6aeab3', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:36', '2026-07-29 04:40:36', '2026-07-29 04:40:36'),
(442, '121e6364-cb36-4dc4-85ad-e200d066e2b8', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:50', '2026-07-29 04:40:50', '2026-07-29 04:40:50'),
(443, 'cb9b427c-d2ce-419f-b026-251c30892633', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:40:50', '2026-07-29 04:40:50', '2026-07-29 04:40:50'),
(444, 'f689b720-fe98-4453-a5a2-149359410f6b', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:01', '2026-07-29 04:41:01', '2026-07-29 04:41:01'),
(445, '8116b55b-1e88-44f5-9d35-6ce414a5b417', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:01', '2026-07-29 04:41:01', '2026-07-29 04:41:01'),
(446, '04145ba4-3af6-4802-8076-23d3b3308d2b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:01', '2026-07-29 04:41:01', '2026-07-29 04:41:01'),
(447, '75c8a768-1d83-479e-8613-2a3481166d8a', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:23', '2026-07-29 04:41:23', '2026-07-29 04:41:23'),
(448, '773bbe3e-0781-45c3-bf88-3c65a7c54d03', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:24', '2026-07-29 04:41:24', '2026-07-29 04:41:24'),
(449, 'cb3ba9f1-363d-4aa7-891f-aa8cdf74c87f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:24', '2026-07-29 04:41:24', '2026-07-29 04:41:24'),
(450, '9ed6f6a9-f737-4902-ac55-4fcad3f4d3aa', NULL, '/riwayat-apar/6/confirm-received', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:44', '2026-07-29 04:41:44', '2026-07-29 04:41:44'),
(451, '38049dc5-e0f7-4d07-a8e5-768ad91a470f', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:44', '2026-07-29 04:41:44', '2026-07-29 04:41:44'),
(452, '7631ac9b-39a5-4e3d-8a1a-4c9f8633f880', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:54', '2026-07-29 04:41:54', '2026-07-29 04:41:54'),
(453, '341e78e6-a398-4543-bb40-b603995791a6', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:54', '2026-07-29 04:41:54', '2026-07-29 04:41:54'),
(454, 'ea02cef7-2938-4a7e-acac-19ce038e69ee', NULL, '/testimoni', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:54', '2026-07-29 04:41:54', '2026-07-29 04:41:54'),
(455, 'dbe5276f-569f-4415-bafe-ba873874b2b4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:55', '2026-07-29 04:41:55', '2026-07-29 04:41:55'),
(456, '30165f9d-5589-48ce-9ecd-226979a49e45', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:57', '2026-07-29 04:41:57', '2026-07-29 04:41:57'),
(457, '3315f6bf-3fac-4459-b52c-de3a0a0c4dce', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:57', '2026-07-29 04:41:57', '2026-07-29 04:41:57'),
(458, '98d311e9-5218-455c-a34a-ae5ffeae8806', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:41:58', '2026-07-29 04:41:58', '2026-07-29 04:41:58'),
(459, '6ae6eeb0-8aa6-4a79-886c-0166908c45be', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:27', '2026-07-29 04:42:27', '2026-07-29 04:42:27'),
(460, '0a55090c-ca82-4982-ac3a-58f77854a9dd', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:28', '2026-07-29 04:42:28', '2026-07-29 04:42:28'),
(461, '9dbe42a6-7e5d-4d44-88d8-34d8c67fc52d', NULL, '/complain', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:36', '2026-07-29 04:42:36', '2026-07-29 04:42:36'),
(462, '82db0f17-a9f9-4220-8583-2bcf96ffc478', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:36', '2026-07-29 04:42:36', '2026-07-29 04:42:36'),
(463, '54ffa52f-dae3-4a8d-babd-18962bbfe1d5', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:38', '2026-07-29 04:42:38', '2026-07-29 04:42:38'),
(464, '49d862b6-2ebf-4ddc-bd02-7f8736f7d7d3', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:39', '2026-07-29 04:42:39', '2026-07-29 04:42:39'),
(465, '000d1bc7-3314-443a-91d3-2c4b6e091be0', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:39', '2026-07-29 04:42:39', '2026-07-29 04:42:39'),
(466, '28bae26e-9ce3-43a6-9e14-9ed1382d2618', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:54', '2026-07-29 04:42:54', '2026-07-29 04:42:54'),
(467, '796013c8-f0f7-47f9-a68d-082843d128e9', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:54', '2026-07-29 04:42:54', '2026-07-29 04:42:54'),
(468, 'f3ec7179-21d4-4d1c-82d0-e28d740aba8a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:42:55', '2026-07-29 04:42:55', '2026-07-29 04:42:55'),
(469, '1be9e140-fa11-4e58-b298-797a8235d0bb', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:43:16', '2026-07-29 04:43:16', '2026-07-29 04:43:16'),
(470, 'add7f361-1170-481b-ac78-ea929c0b6bf6', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:43:17', '2026-07-29 04:43:17', '2026-07-29 04:43:17'),
(471, 'b200986f-b583-4ab0-839f-ebd0ceec367a', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:43:17', '2026-07-29 04:43:17', '2026-07-29 04:43:17'),
(472, '6e890c00-7e07-401f-bc4a-96b72f4a9dbd', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:43:47', '2026-07-29 04:43:47', '2026-07-29 04:43:47'),
(473, 'fb96433a-c02b-4522-a7b1-f209bac8932b', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:43:47', '2026-07-29 04:43:47', '2026-07-29 04:43:47'),
(474, 'a7a2f572-df1a-4fb3-a723-2cde9d7ae20c', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:43:47', '2026-07-29 04:43:47', '2026-07-29 04:43:47'),
(475, 'ca5952d0-090a-4799-98f1-34c8c25cba21', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:44:39', '2026-07-29 04:44:39', '2026-07-29 04:44:39'),
(476, 'e9a6899a-656f-4c76-be52-dba0269241fb', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 04:44:42', '2026-07-29 04:44:42', '2026-07-29 04:44:42'),
(477, '87d47002-586f-489b-81ca-8c7fe477c1fd', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-07-29 08:18:58', '2026-07-29 08:18:58', '2026-07-29 08:18:58'),
(478, 'df51c194-47c0-4f85-b546-40ff8ca6610c', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-04 13:30:46', '2026-08-04 13:30:46', '2026-08-04 13:30:46'),
(479, 'f98c6487-8dce-47f0-b5d2-69fcc1623cc2', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 04:38:50', '2026-08-05 04:38:50', '2026-08-05 04:38:50'),
(480, 'c1c248d8-8900-4c1c-b594-7d8a66661c6f', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:42:00', '2026-08-05 07:42:00', '2026-08-05 07:42:00'),
(481, '9634a808-166a-4598-9ad2-07352340492d', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:42:00', '2026-08-05 07:42:00', '2026-08-05 07:42:00'),
(482, '0d6ce2c0-a2cb-48b0-91ab-14a4802905b4', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:42:01', '2026-08-05 07:42:01', '2026-08-05 07:42:01'),
(483, '134900cd-85d8-4a6d-b871-1b8a9400952b', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:43:20', '2026-08-05 07:43:20', '2026-08-05 07:43:20'),
(484, 'ec5a8180-34d6-40a3-85d1-93c727f51bab', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:43:20', '2026-08-05 07:43:20', '2026-08-05 07:43:20'),
(485, 'c75c464a-db5f-4568-a435-3fb6ab8bd23e', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:44:57', '2026-08-05 07:44:57', '2026-08-05 07:44:57'),
(486, 'dfad4d4d-c66b-4589-a83d-004ba0b7698f', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:44:57', '2026-08-05 07:44:57', '2026-08-05 07:44:57'),
(487, 'c8c503e9-e2fa-434e-a14c-adbb6619c3b8', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:45:17', '2026-08-05 07:45:17', '2026-08-05 07:45:17'),
(488, '4a2a7fbe-73f3-43ae-b361-5787fb6caf81', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:45:18', '2026-08-05 07:45:18', '2026-08-05 07:45:18'),
(489, 'ab7d891c-a687-4de0-92d1-5e406202982d', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:46:30', '2026-08-05 07:46:30', '2026-08-05 07:46:30'),
(490, '32b94c87-3584-4b4a-9492-20afe822e26c', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-05 07:46:30', '2026-08-05 07:46:30', '2026-08-05 07:46:30'),
(491, '1914ba3c-4aed-4f9c-bdb9-d3f7ff5db7b6', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-09 13:53:21', '2026-08-09 13:53:21', '2026-08-09 13:53:21'),
(492, 'ce262238-1048-4fc7-b708-c48f463b741e', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-09 13:55:13', '2026-08-09 13:55:13', '2026-08-09 13:55:13'),
(493, '43d52f3c-5257-4dc8-a865-6b6617a46036', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-09 13:55:14', '2026-08-09 13:55:14', '2026-08-09 13:55:14'),
(494, 'ca7ae93c-83a3-4299-b1fa-61cdd75c2805', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-09 13:55:56', '2026-08-09 13:55:56', '2026-08-09 13:55:56'),
(495, 'f477c696-3bdc-43e6-8660-5d03649c9691', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-09 13:55:56', '2026-08-09 13:55:56', '2026-08-09 13:55:56'),
(496, 'eaff2790-9d67-47f7-8416-1c17e4d46412', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-17 13:44:05', '2026-08-17 13:44:05', '2026-08-17 13:44:05'),
(497, 'd9b5de52-e14d-4725-9d74-39668240e1ac', NULL, '/.well-known/appspecific/com.chrome.devtools.json', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-17 13:51:35', '2026-08-17 13:51:35', '2026-08-17 13:51:35'),
(498, '65d21a5f-5dca-400e-84cb-c72aecf98107', NULL, '/vendor/leaflet/leaflet.js.map', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-17 13:51:36', '2026-08-17 13:51:36', '2026-08-17 13:51:36'),
(499, '66ef215e-9f7c-432b-b101-5053f4a6dbaf', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:24:55', '2026-08-22 14:24:55', '2026-08-22 14:24:55'),
(500, 'e4e5746d-5035-4c4f-b904-3a349ba57e2f', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:29:06', '2026-08-22 14:29:06', '2026-08-22 14:29:06'),
(501, '21edda22-00a4-4ee8-9905-1e1bf2f714a2', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:29:08', '2026-08-22 14:29:08', '2026-08-22 14:29:08'),
(502, '207dcafa-9c60-4541-b487-e1a6d9af31b4', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:41:06', '2026-08-22 14:41:06', '2026-08-22 14:41:06'),
(503, 'ad415631-f6b5-4c08-95f4-68a1e5d01228', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:41:13', '2026-08-22 14:41:13', '2026-08-22 14:41:13'),
(504, '2558cdb3-661c-4ea9-87b8-66bde0df5ef5', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:41:15', '2026-08-22 14:41:15', '2026-08-22 14:41:15'),
(505, '18b15276-abfe-4454-a571-cc310b9d3c62', NULL, '/produk/36', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'product_view', 36, 'APAR TONATA Foam 9 kg', '2026-08-22 14:42:44', '2026-08-22 14:42:44', '2026-08-22 14:42:44'),
(506, 'ab864a02-e9d7-4e1b-af96-9946c62bdbd4', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'add_to_cart', 36, 'APAR TONATA Foam 9 kg (x1)', '2026-08-22 14:43:59', '2026-08-22 14:43:59', '2026-08-22 14:43:59'),
(507, 'ab864a02-e9d7-4e1b-af96-9946c62bdbd4', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:43:59', '2026-08-22 14:43:59', '2026-08-22 14:43:59'),
(508, '08dd9f26-6e6a-4e34-9009-5760640b67ce', NULL, '/produk/36', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'product_view', 36, 'APAR TONATA Foam 9 kg', '2026-08-22 14:43:59', '2026-08-22 14:43:59', '2026-08-22 14:43:59'),
(509, '0292ab0b-b4e6-4b56-8885-17d189ab6b97', NULL, '/keranjang', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:44:32', '2026-08-22 14:44:32', '2026-08-22 14:44:32');
INSERT INTO `website_visits` (`id`, `visitor_id`, `session_id`, `page_url`, `ip_address`, `user_agent`, `event_type`, `product_id`, `page_title`, `visited_at`, `created_at`, `updated_at`) VALUES
(510, '82afe989-4990-476d-9c08-3113d0b8a94b', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:47:53', '2026-08-22 14:47:53', '2026-08-22 14:47:53'),
(511, 'd8cd6d24-0151-4b92-88a9-5fa9855a1c20', NULL, '/order', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:50:55', '2026-08-22 14:50:55', '2026-08-22 14:50:55'),
(512, '9634652f-72f0-4bb7-a6b8-c8b0151ae8ae', NULL, '/order/7/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:50:56', '2026-08-22 14:50:56', '2026-08-22 14:50:56'),
(513, 'cba10225-6626-4cb9-9ffe-efe5ee67b8b4', NULL, '/order/7/payment', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:56:33', '2026-08-22 14:56:33', '2026-08-22 14:56:33'),
(514, '82d98553-75d6-4554-8368-65a0357fafb7', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:56:35', '2026-08-22 14:56:35', '2026-08-22 14:56:35'),
(515, '6e35b26d-83c3-44cc-b346-d6a630000836', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 14:56:37', '2026-08-22 14:56:37', '2026-08-22 14:56:37'),
(516, '473d10c9-5f40-4668-a9c6-4929c1067a97', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:00:18', '2026-08-22 15:00:18', '2026-08-22 15:00:18'),
(517, 'd6eb68fa-4e5b-4a88-9f43-082a5df215e5', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:00:27', '2026-08-22 15:00:27', '2026-08-22 15:00:27'),
(518, 'd36c9107-22b3-4812-a93a-a518ac0400e3', NULL, '/produk', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:00:37', '2026-08-22 15:00:37', '2026-08-22 15:00:37'),
(519, '21b5a37a-159c-49bb-825c-6681617b3cec', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:00:40', '2026-08-22 15:00:40', '2026-08-22 15:00:40'),
(520, 'ea9dca14-419b-4d5e-a059-19ffa0a42ace', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:00:40', '2026-08-22 15:00:40', '2026-08-22 15:00:40'),
(521, '9e854c4b-8397-4a9d-aee8-416a5cf7193d', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:00:41', '2026-08-22 15:00:41', '2026-08-22 15:00:41'),
(522, 'c11384fc-0b35-40b6-ba79-5be06d4a5794', NULL, '/', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36 Edg/151.0.0.0', 'page_view', NULL, NULL, '2026-08-22 15:01:11', '2026-08-22 15:01:11', '2026-08-22 15:01:11'),
(523, '54bcc6fc-da74-42a8-ac92-330c1a368f33', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:11', '2026-08-22 15:01:11', '2026-08-22 15:01:11'),
(524, '7bbc7b44-0e0b-4e27-954f-2cfa223980fe', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:12', '2026-08-22 15:01:12', '2026-08-22 15:01:12'),
(525, '1a5563dc-3572-4af9-ae35-ca28168f43d4', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:41', '2026-08-22 15:01:41', '2026-08-22 15:01:41'),
(526, '8c9b3bfb-a15e-4285-8865-d433de0dba97', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:42', '2026-08-22 15:01:42', '2026-08-22 15:01:42'),
(527, 'affb7bb2-b5dd-4815-a081-163d5600d980', NULL, '/riwayat-apar', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:46', '2026-08-22 15:01:46', '2026-08-22 15:01:46'),
(528, '37610647-dcaa-4ccd-b26f-87b10895bc9e', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:47', '2026-08-22 15:01:47', '2026-08-22 15:01:47'),
(529, '49de6532-ded3-4aef-943a-2dbe1bbbff64', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:47', '2026-08-22 15:01:47', '2026-08-22 15:01:47'),
(530, '404710de-a402-42e2-aec9-dd4a07a9f19a', NULL, '/riwayat-apar/7/confirm-received', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:51', '2026-08-22 15:01:51', '2026-08-22 15:01:51'),
(531, 'f0aa80a0-9cad-4b6b-882e-43f3ecc8a1ce', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:01:52', '2026-08-22 15:01:52', '2026-08-22 15:01:52'),
(532, '7f40bb3a-ccd6-4b10-943c-7e85c51402bb', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:02:17', '2026-08-22 15:02:17', '2026-08-22 15:02:17'),
(533, '0eec26e8-80a4-42f1-a166-9d04cdc10324', NULL, '/riwayat-apar/status', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36', 'page_view', NULL, NULL, '2026-08-22 15:02:17', '2026-08-22 15:02:17', '2026-08-22 15:02:17');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_log_name_subject_type_subject_id_index` (`log_name`,`subject_type`,`subject_id`),
  ADD KEY `activity_logs_user_id_index` (`user_id`),
  ADD KEY `activity_logs_created_at_index` (`created_at`);

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
-- Indexes for table `complains`
--
ALTER TABLE `complains`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complains_pelanggan_id_foreign` (`pelanggan_id`),
  ADD KEY `complains_pesanan_id_foreign` (`pesanan_id`),
  ADD KEY `complains_service_id_foreign` (`service_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `jasa`
--
ALTER TABLE `jasa`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jenis_apars`
--
ALTER TABLE `jenis_apars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jenis_refills`
--
ALTER TABLE `jenis_refills`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pelanggans`
--
ALTER TABLE `pelanggans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pelanggans_user_id_foreign` (`user_id`);

--
-- Indexes for table `peralatans`
--
ALTER TABLE `peralatans`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pesanans`
--
ALTER TABLE `pesanans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanans_pelanggan_id_foreign` (`pelanggan_id`),
  ADD KEY `pesanans_user_id_foreign` (`user_id`),
  ADD KEY `pesanans_teknisi_id_foreign` (`teknisi_id`),
  ADD KEY `pesanans_service_paket_id_foreign` (`service_paket_id`),
  ADD KEY `pesanans_service_jenis_refill_id_foreign` (`service_jenis_refill_id`),
  ADD KEY `pesanans_unit_apar_id_foreign` (`unit_apar_id`),
  ADD KEY `pesanans_jasa_id_foreign` (`jasa_id`);

--
-- Indexes for table `pesanan_details`
--
ALTER TABLE `pesanan_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_details_pesanan_id_foreign` (`pesanan_id`),
  ADD KEY `pesanan_details_produk_id_foreign` (`produk_id`);

--
-- Indexes for table `produks`
--
ALTER TABLE `produks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produks_jenis_apar_id_foreign` (`jenis_apar_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `purchase_orders_nomor_po_unique` (`nomor_po`),
  ADD KEY `purchase_orders_supplier_id_foreign` (`supplier_id`);

--
-- Indexes for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_order_details_purchase_order_id_foreign` (`purchase_order_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD KEY `services_service_paket_id_foreign` (`service_paket_id`),
  ADD KEY `services_pesanan_id_foreign` (`pesanan_id`),
  ADD KEY `services_unit_apar_id_foreign` (`unit_apar_id`);

--
-- Indexes for table `service_pakets`
--
ALTER TABLE `service_pakets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_pakets_jenis_refill_id_foreign` (`jenis_refill_id`);

--
-- Indexes for table `service_paket_peralatan`
--
ALTER TABLE `service_paket_peralatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `service_paket_peralatan_service_paket_id_foreign` (`service_paket_id`),
  ADD KEY `service_paket_peralatan_peralatan_id_foreign` (`peralatan_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stok_batches`
--
ALTER TABLE `stok_batches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stok_batches_produk_id_foreign` (`produk_id`),
  ADD KEY `stok_batches_purchase_order_id_foreign` (`purchase_order_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonis`
--
ALTER TABLE `testimonis`
  ADD PRIMARY KEY (`id`),
  ADD KEY `testimonis_pelanggan_id_foreign` (`pelanggan_id`);

--
-- Indexes for table `unit_apars`
--
ALTER TABLE `unit_apars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_apars_no_seri_unique` (`no_seri`),
  ADD KEY `unit_apars_pelanggan_id_foreign` (`pelanggan_id`),
  ADD KEY `unit_apars_pesanan_id_foreign` (`pesanan_id`),
  ADD KEY `unit_apars_produk_id_foreign` (`produk_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_no_telpon_unique` (`no_telpon`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `website_visits`
--
ALTER TABLE `website_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `website_visits_visitor_id_index` (`visitor_id`),
  ADD KEY `website_visits_session_id_index` (`session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT for table `complains`
--
ALTER TABLE `complains`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jasa`
--
ALTER TABLE `jasa`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `jenis_apars`
--
ALTER TABLE `jenis_apars`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jenis_refills`
--
ALTER TABLE `jenis_refills`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pelanggans`
--
ALTER TABLE `pelanggans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `peralatans`
--
ALTER TABLE `peralatans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pesanans`
--
ALTER TABLE `pesanans`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pesanan_details`
--
ALTER TABLE `pesanan_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produks`
--
ALTER TABLE `produks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `service_pakets`
--
ALTER TABLE `service_pakets`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `service_paket_peralatan`
--
ALTER TABLE `service_paket_peralatan`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `stok_batches`
--
ALTER TABLE `stok_batches`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `testimonis`
--
ALTER TABLE `testimonis`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `unit_apars`
--
ALTER TABLE `unit_apars`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `website_visits`
--
ALTER TABLE `website_visits`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=534;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `complains`
--
ALTER TABLE `complains`
  ADD CONSTRAINT `complains_pelanggan_id_foreign` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `complains_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `complains_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pelanggans`
--
ALTER TABLE `pelanggans`
  ADD CONSTRAINT `pelanggans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesanans`
--
ALTER TABLE `pesanans`
  ADD CONSTRAINT `pesanans_jasa_id_foreign` FOREIGN KEY (`jasa_id`) REFERENCES `jasa` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanans_pelanggan_id_foreign` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanans_service_jenis_refill_id_foreign` FOREIGN KEY (`service_jenis_refill_id`) REFERENCES `jenis_refills` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanans_service_paket_id_foreign` FOREIGN KEY (`service_paket_id`) REFERENCES `service_pakets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanans_teknisi_id_foreign` FOREIGN KEY (`teknisi_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanans_unit_apar_id_foreign` FOREIGN KEY (`unit_apar_id`) REFERENCES `unit_apars` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pesanans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pesanan_details`
--
ALTER TABLE `pesanan_details`
  ADD CONSTRAINT `pesanan_details_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pesanan_details_produk_id_foreign` FOREIGN KEY (`produk_id`) REFERENCES `produks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produks`
--
ALTER TABLE `produks`
  ADD CONSTRAINT `produks_jenis_apar_id_foreign` FOREIGN KEY (`jenis_apar_id`) REFERENCES `jenis_apars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD CONSTRAINT `purchase_order_details_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `services_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `services_service_paket_id_foreign` FOREIGN KEY (`service_paket_id`) REFERENCES `service_pakets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `services_unit_apar_id_foreign` FOREIGN KEY (`unit_apar_id`) REFERENCES `unit_apars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `service_pakets`
--
ALTER TABLE `service_pakets`
  ADD CONSTRAINT `service_pakets_jenis_refill_id_foreign` FOREIGN KEY (`jenis_refill_id`) REFERENCES `jenis_refills` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `service_paket_peralatan`
--
ALTER TABLE `service_paket_peralatan`
  ADD CONSTRAINT `service_paket_peralatan_peralatan_id_foreign` FOREIGN KEY (`peralatan_id`) REFERENCES `peralatans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `service_paket_peralatan_service_paket_id_foreign` FOREIGN KEY (`service_paket_id`) REFERENCES `service_pakets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stok_batches`
--
ALTER TABLE `stok_batches`
  ADD CONSTRAINT `stok_batches_produk_id_foreign` FOREIGN KEY (`produk_id`) REFERENCES `produks` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stok_batches_purchase_order_id_foreign` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `testimonis`
--
ALTER TABLE `testimonis`
  ADD CONSTRAINT `testimonis_pelanggan_id_foreign` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggans` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unit_apars`
--
ALTER TABLE `unit_apars`
  ADD CONSTRAINT `unit_apars_pelanggan_id_foreign` FOREIGN KEY (`pelanggan_id`) REFERENCES `pelanggans` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unit_apars_pesanan_id_foreign` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanans` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `unit_apars_produk_id_foreign` FOREIGN KEY (`produk_id`) REFERENCES `produks` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
