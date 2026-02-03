-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 03 Feb 2026 pada 04.38
-- Versi server: 8.0.30
-- Versi PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bappeda_ruangan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admins`
--

CREATE TABLE `admins` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(3, 'bappedajateng', '$2y$10$59kn9QrUbt75f3R7DQZRuua7jXUhJ8mErD1XWSiLcxTMpSGN7p1/O');

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int NOT NULL,
  `room_id` int NOT NULL,
  `nama_peminjam` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `divisi` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `instansi` enum('bappeda','lainnya') COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kegiatan` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `jumlah_peserta` int DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `waktu_mulai` time DEFAULT NULL,
  `waktu_selesai` time DEFAULT NULL,
  `file_pendukung` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('menunggu','disetujui','ditolak','dibatalkan') COLLATE utf8mb4_general_ci DEFAULT 'menunggu',
  `is_recurring` tinyint(1) DEFAULT '0',
  `parent_booking_id` int DEFAULT NULL,
  `recurrence_instance_date` date DEFAULT NULL,
  `cancel_reason` text COLLATE utf8mb4_general_ci,
  `rejection_reason` text COLLATE utf8mb4_general_ci,
  `qr_token` varchar(64) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `room_id`, `nama_peminjam`, `user_email`, `phone_number`, `divisi`, `instansi`, `kegiatan`, `jumlah_peserta`, `tanggal`, `waktu_mulai`, `waktu_selesai`, `file_pendukung`, `status`, `is_recurring`, `parent_booking_id`, `recurrence_instance_date`, `cancel_reason`, `rejection_reason`, `qr_token`, `created_at`, `updated_at`) VALUES
(1, 4, 'syakir', NULL, NULL, 'program', 'lainnya', 'rapat internal', 30, '2026-01-20', '12:00:00', '13:00:00', 'assets/files/1769242408_Tugas Pokja Bappeda 2026_new.docx', 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh Admin', NULL, 'db548981590471fd3c0faa98370792c7', '2026-01-24 08:13:28', '2026-01-31 06:51:59'),
(2, 1, 'sasa', NULL, NULL, 'inspektorat', 'lainnya', 'rapat akhir bulan', 20, '2026-01-26', '08:00:00', '09:00:00', 'assets/files/1769389192_Tugas Pokja Bappeda 2026.docx', 'disetujui', 0, NULL, NULL, NULL, NULL, '5ffd619dd88dea18becee0f11971e3a1', '2026-01-26 00:59:52', '2026-01-28 05:53:40'),
(3, 1, 'aa', 'arjuridho77@gmail.com', '085163520603', 'aa', 'bappeda', 'aa', 10, '2026-10-10', '10:10:00', '12:12:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, 'f79b0f1035533c008dfb3c4714eb8b63', '2026-01-26 08:29:27', '2026-01-28 05:49:28'),
(4, 2, 'aa', 'arjuridho77@gmail.com', '', 'aa', 'bappeda', 'aa', 10, '2026-10-10', '10:10:00', '12:12:00', NULL, 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh peminjam', NULL, 'eebb5e20c7526eae3faff4835eb19492', '2026-01-28 04:30:12', '2026-01-28 04:54:29'),
(5, 3, 'aa', 'arjuridho77@gmail.com', '', 'aa', 'bappeda', 'aa', 15, '2026-01-26', '16:00:00', '18:00:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, 'e07db7a83cf4a6f90e511135cb9ae1d8', '2026-01-28 07:42:14', '2026-02-02 01:53:26'),
(6, 3, 'a', 'screet121@gmail.com', '', 'a', 'lainnya', 'a', 10, '2026-01-30', '07:00:00', '12:00:00', 'assets/files/1769615844_03-Latarbelakang.pdf', 'disetujui', 0, NULL, NULL, NULL, NULL, 'c1c6f570cb915f26c3bf8525ef3c4db2', '2026-01-28 15:57:24', '2026-01-28 16:12:30'),
(7, 3, 'aa', 'screet121@gmail.com', '', 'aa', 'lainnya', 'aa', 10, '2026-01-26', '00:00:00', '00:00:00', 'assets/files/1769615936_02-Strukturskripsi.pdf', 'disetujui', 0, NULL, NULL, NULL, NULL, '736a4877d3cc43a021de47200f6ec46a', '2026-01-28 15:58:56', '2026-01-29 16:17:07'),
(8, 4, 'a', 'screet121@gmail.com', '', 'a', 'lainnya', 'a', 50, '2026-10-30', '10:01:00', '12:12:00', 'assets/files/1769617261_Template_Penulisan_Jurnal_SISTEMASI.docx', 'disetujui', 0, NULL, NULL, NULL, NULL, 'be50784114721bc89900deba2a27c40a', '2026-01-28 16:21:01', '2026-01-28 16:30:15'),
(9, 2, 's', 'screet121@gmail.com', '', 's', 'lainnya', 's', 20, '2026-02-10', '12:12:00', '12:56:00', 'assets/files/1769622374_05-KajianPustaka.pdf', 'ditolak', 0, NULL, NULL, NULL, 'renovasi', '99afe32851745bc428fcacb5947e2bce', '2026-01-28 17:46:14', '2026-01-28 17:49:39'),
(10, 2, 'd', 'screet121@gmail.com', '', 'd', 'lainnya', 'd', 10, '2026-01-26', '07:00:00', '12:00:00', 'assets/files/1769622710_06-Metodologi.pdf', 'disetujui', 0, NULL, NULL, NULL, NULL, '32c3eeebef334bc5f5f6d224fc8503da', '2026-01-28 17:51:50', '2026-02-02 01:54:10'),
(11, 2, 'f', 'screet121@gmail.com', '', 'f', 'lainnya', 'f', 3, '2026-02-20', '12:00:00', '14:00:00', 'assets/files/1769654329_05-KajianPustaka.pdf', 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh peminjam', NULL, '40c1e93ade766a85497c0510a0e76582', '2026-01-29 02:38:49', '2026-01-29 03:32:22'),
(12, 5, 'gg', 'screet121@gmail.com', '', 'g', 'lainnya', 'q', 5, '2026-11-11', '19:00:00', '20:00:00', 'assets/files/1769655195_11-Mendesainslidepresentasi.pdf', 'disetujui', 0, NULL, NULL, NULL, NULL, 'aa4f4bb5afa1133c658a6be29f167255', '2026-01-29 02:53:15', '2026-01-29 02:53:51'),
(13, 2, 'aa', 'screet121@gmail.com', NULL, 'a', 'lainnya', 'a', 20, '2026-01-31', '12:00:00', '13:00:00', 'assets/files/1769674584_05-KajianPustaka.pdf', 'disetujui', 0, NULL, NULL, NULL, NULL, '803f8db227c184dbcc8230067c9fcbdf', '2026-01-29 08:16:24', '2026-01-31 10:22:56'),
(14, 3, 'aa', 'screet121@gmail.com', NULL, 'aa', 'bappeda', 'aa', 12, '2026-04-15', '12:00:00', '13:00:00', NULL, 'ditolak', 0, NULL, NULL, NULL, 'RENOVASI', '761adb6934300cf30cca56b06c9ebfed', '2026-01-30 06:06:27', '2026-01-30 06:08:15'),
(15, 6, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', 'test', 100, '2026-02-01', '09:00:00', '14:00:00', NULL, 'dibatalkan', 1, NULL, NULL, 'Dibatalkan oleh Admin', NULL, '62845a551e3252db9fb31a8f1936e4af', '2026-01-31 09:39:07', '2026-01-31 09:41:31'),
(16, 6, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', 'test', 100, '2026-03-01', '09:00:00', '14:00:00', NULL, 'dibatalkan', 0, 15, '2026-03-01', 'Dibatalkan oleh Admin', NULL, '3a7d950723b683f246eabb13987d46f7', '2026-01-31 09:39:07', '2026-01-31 09:41:47'),
(17, 6, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', 'test', 100, '2026-04-01', '09:00:00', '14:00:00', NULL, 'dibatalkan', 0, 15, '2026-04-01', 'Dibatalkan oleh Admin', NULL, '0604a379d86be4971b25e78fe4928428', '2026-01-31 09:39:07', '2026-01-31 09:47:03'),
(18, 12, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', '3', 33, '2026-02-01', '09:00:00', '14:00:00', NULL, 'dibatalkan', 1, NULL, NULL, 'Dibatalkan oleh Admin', NULL, '888d7589658ee6e71a3d8cf667e9065f', '2026-01-31 09:58:48', '2026-01-31 10:00:09'),
(19, 12, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', '3', 33, '2026-03-01', '09:00:00', '14:00:00', NULL, 'dibatalkan', 0, 18, '2026-03-01', 'Dibatalkan oleh Admin', NULL, 'ccd2d4c0cbb676310cfd5af9de663c75', '2026-01-31 09:58:48', '2026-01-31 10:00:17'),
(20, 12, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', '3', 33, '2026-04-01', '09:00:00', '14:00:00', NULL, 'dibatalkan', 0, 18, '2026-04-01', 'Dibatalkan oleh Admin', NULL, 'b994a6a93595812953b7082f53fd7055', '2026-01-31 09:58:48', '2026-01-31 10:00:21'),
(21, 11, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', '3', 40, '2026-02-01', '09:00:00', '14:00:00', NULL, 'disetujui', 1, NULL, NULL, NULL, NULL, '5f6634c2f6664f876f15255d6472d9b0', '2026-01-31 10:06:16', '2026-01-31 10:18:05'),
(22, 11, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', '3', 40, '2026-02-04', '09:00:00', '14:00:00', NULL, 'disetujui', 0, 21, '2026-03-01', NULL, NULL, '529d5dce1b0e7402965635d7827cb097', '2026-01-31 10:06:16', '2026-02-02 01:26:30'),
(23, 11, 'test3', 'screet121@gmail.com', NULL, '3', 'bappeda', '3', 40, '2026-03-18', '09:00:00', '14:00:00', NULL, 'disetujui', 0, 21, '2026-04-01', NULL, NULL, 'e419259c67d29375721a4c28a92e6137', '2026-01-31 10:06:16', '2026-02-02 01:42:24'),
(24, 12, 'test4', 'screet121@gmail.com', NULL, '4', 'bappeda', 'test', 12, '2026-02-03', '07:00:00', '12:00:00', NULL, 'disetujui', 1, NULL, NULL, NULL, NULL, '4388c5fd8ee24b603172241222066201', '2026-01-31 10:20:27', '2026-02-02 01:18:15'),
(25, 12, 'test4', 'screet121@gmail.com', NULL, '4', 'bappeda', 'test', 12, '2026-02-16', '08:00:00', '12:00:00', NULL, 'disetujui', 0, 24, '2026-02-09', NULL, NULL, 'aa816e0c7e5b07554bf1a171b9f0626a', '2026-01-31 10:20:27', '2026-02-02 01:38:20'),
(26, 12, 'test4', 'screet121@gmail.com', NULL, '4', 'bappeda', 'test', 12, '2026-02-09', '12:00:00', '14:00:00', NULL, 'dibatalkan', 0, 24, '2026-02-16', 'Dibatalkan oleh user', NULL, '2f173f34200154f4e251861191f2f037', '2026-01-31 10:20:27', '2026-02-03 03:10:23'),
(27, 12, 'test4', 'screet121@gmail.com', NULL, '4', 'bappeda', 'test', 12, '2026-02-23', '07:00:00', '12:00:00', NULL, 'disetujui', 0, 24, '2026-02-23', NULL, NULL, 'b7c82c1c224d76503c6e8fa256bc1a3d', '2026-01-31 10:20:27', '2026-02-02 01:38:30'),
(28, 4, 'test5', 'screet121@gmail.com', NULL, '3', 'bappeda', '5', 12, '2026-02-02', '07:00:00', '12:00:00', NULL, 'disetujui', 1, NULL, NULL, NULL, NULL, '250ad989f6fc0ba343e7933402a8ad04', '2026-01-31 10:34:45', '2026-02-02 01:25:36'),
(29, 4, 'test5', 'screet121@gmail.com', NULL, '3', 'bappeda', '5', 12, '2026-02-10', '07:00:00', '12:00:00', NULL, 'disetujui', 0, 28, '2026-02-10', NULL, NULL, '910e27eafe95639500fff4a70004b26b', '2026-01-31 10:34:45', '2026-02-02 00:44:58'),
(30, 4, 'test5', 'screet121@gmail.com', NULL, '3', 'bappeda', '5', 12, '2026-02-17', '07:00:00', '12:00:00', NULL, 'disetujui', 0, 28, '2026-02-17', NULL, NULL, '53e24b3ab20c21462304f875d2b29304', '2026-01-31 10:34:45', '2026-02-02 00:44:58'),
(31, 4, 'test5', 'screet121@gmail.com', NULL, '3', 'bappeda', '5', 12, '2026-02-26', '07:00:00', '12:00:00', NULL, 'disetujui', 0, 28, '2026-02-24', NULL, NULL, 'ddc5a4d104a4d9fbc2f21aee13eb8c6e', '2026-01-31 10:34:45', '2026-02-02 05:40:49'),
(32, 3, 'faqih uji coba', 'ahmadfaqihnajmuddin@gmail.com', NULL, 'uin walisongo', 'lainnya', 'rapat', 15, '2026-03-01', '09:00:00', '12:00:00', 'assets/files/1769956112_166934381PB.pdf', 'disetujui', 0, NULL, NULL, NULL, NULL, 'db1c778758b86f36a2870706f038087c', '2026-02-01 14:28:32', '2026-02-02 01:19:36'),
(33, 6, 'test 6', 'screet121@gmail.com', NULL, 'q', 'bappeda', 'test', 12, '2026-02-04', '09:00:00', '16:00:00', NULL, 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh peminjam', NULL, 'b441141de81f03b36903e59c27c48f3d', '2026-02-02 00:43:54', '2026-02-02 01:01:25'),
(34, 2, 'test1', 'screet121@gmail.com', NULL, '1', 'bappeda', '3', 12, '2026-02-03', '11:00:00', '13:00:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, 'a643ad6f6ad860c81cf620f85b16c68a', '2026-02-02 01:02:10', '2026-02-02 01:33:25'),
(35, 4, '1', 'screet121@gmail.com', NULL, '1', 'bappeda', '1', 1, '2026-03-31', '12:00:00', '15:00:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, '66a388d46e3875c0a8c510423a377019', '2026-02-02 01:39:38', '2026-02-02 01:42:28'),
(36, 4, '4', 'screet121@gmail.com', NULL, '4', 'bappeda', '4', 4, '2026-03-20', '08:00:00', '14:30:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, '5620fbc14028487bcaf5730c52dc86e6', '2026-02-02 01:41:39', '2026-02-02 01:42:16'),
(37, 3, 'a', NULL, NULL, 'a', 'bappeda', 'a', 10, '2026-03-02', '08:00:00', '10:00:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, 'be700d8cb28a94ca5b87817600c68eef', '2026-02-02 02:25:15', NULL),
(38, 12, '5', 'screet121@gmail.com', NULL, '5', 'bappeda', '5', 5, '2026-02-19', '12:00:00', '13:00:00', NULL, 'menunggu', 0, NULL, NULL, NULL, NULL, '794d2acfbcb962ae0ee2f8ed94037e19', '2026-02-02 04:01:52', NULL),
(39, 4, 'g', 'screet121@gmail.com', NULL, '2', 'bappeda', '2', 2, '2026-02-19', '18:00:00', '19:00:00', NULL, 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh peminjam', NULL, '95e58834f39bb9c749258040a8579224', '2026-02-02 04:34:35', '2026-02-02 04:35:59'),
(40, 12, '6', 'screet121@gmail.com', NULL, '6', 'bappeda', '6', 6, '2026-02-18', '15:00:00', '18:00:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, '6662f15a4a1dc62e2dac26f4c148c336', '2026-02-02 04:36:29', '2026-02-03 01:18:14'),
(41, 4, 'w', 'screet121@gmail.com', NULL, 'w', 'bappeda', '5', 3, '2026-02-13', '12:00:00', '13:00:00', NULL, 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh peminjam', NULL, 'c124e41bce2ec1259fb6fa6667b2f282', '2026-02-02 04:50:23', '2026-02-02 04:55:40'),
(42, 10, 'test4', 'screet121@gmail.com', NULL, '6', 'bappeda', '6', 6, '2026-02-19', '12:00:00', '04:00:00', NULL, 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh peminjam', NULL, 'FEB19-1200', '2026-02-02 04:54:26', '2026-02-02 04:55:11'),
(43, 4, 'test2', 'screet121@gmail.com', NULL, '4', 'bappeda', '4', 40, '2026-02-02', '12:00:00', '13:00:00', NULL, 'disetujui', 1, NULL, NULL, NULL, NULL, 'FEB02-1200', '2026-02-02 05:35:49', '2026-02-02 05:40:15'),
(44, 4, 'test2', 'screet121@gmail.com', NULL, '4', 'bappeda', '4', 40, '2026-03-02', '12:00:00', '13:00:00', NULL, 'disetujui', 0, 43, '2026-03-02', NULL, NULL, 'MAR02-1200', '2026-02-02 05:35:49', '2026-02-02 05:40:15'),
(45, 4, 'test2', 'screet121@gmail.com', NULL, '4', 'bappeda', '4', 40, '2026-04-02', '12:00:00', '13:00:00', NULL, 'disetujui', 0, 43, '2026-04-02', NULL, NULL, 'APR02-1200', '2026-02-02 05:35:49', '2026-02-02 05:40:15'),
(46, 4, 'test2', 'screet121@gmail.com', NULL, '4', 'bappeda', '4', 40, '2026-05-02', '12:00:00', '13:00:00', NULL, 'disetujui', 0, 43, '2026-05-02', NULL, NULL, 'MAY02-1200', '2026-02-02 05:35:49', '2026-02-02 05:40:15'),
(47, 1, 'a', NULL, NULL, 'a', 'bappeda', 'a', 10, '2026-02-04', '08:00:00', '10:00:00', NULL, 'disetujui', 0, NULL, NULL, NULL, NULL, 'FEB04-0800', '2026-02-03 03:36:26', NULL),
(48, 1, 'test2', 'screet121@gmail.com', NULL, '4', 'bappeda', '4', 60, '2026-02-11', '12:00:00', '14:00:00', NULL, 'dibatalkan', 0, NULL, NULL, 'Dibatalkan oleh user', NULL, 'FEB11-1200', '2026-02-03 03:40:34', '2026-02-03 03:44:05'),
(49, 1, '1', 'screet121@gmail.com', NULL, '1', 'lainnya', '1', 12, '2026-02-05', '12:00:00', '13:00:00', 'assets/files/1770091001_LaporanFebruary20261.pdf', 'menunggu', 0, NULL, NULL, NULL, NULL, 'FEB05-1200', '2026-02-03 03:56:41', NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `recurring_patterns`
--

CREATE TABLE `recurring_patterns` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `recurrence_type` enum('daily','weekly','monthly') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recurrence_interval` int DEFAULT '1',
  `days_of_week` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'For weekly: comma-separated days (0=Sun, 1=Mon, etc)',
  `day_of_month` int DEFAULT NULL COMMENT 'For monthly: day of month (1-31)',
  `end_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `recurring_patterns`
--

INSERT INTO `recurring_patterns` (`id`, `booking_id`, `recurrence_type`, `recurrence_interval`, `days_of_week`, `day_of_month`, `end_date`, `created_at`) VALUES
(1, 15, 'monthly', 1, NULL, NULL, '2026-04-01', '2026-01-31 09:39:07'),
(2, 18, 'monthly', 1, NULL, NULL, '2026-04-01', '2026-01-31 09:58:48'),
(3, 21, 'monthly', 1, NULL, NULL, '2026-04-01', '2026-01-31 10:06:16'),
(4, 24, 'weekly', 1, NULL, NULL, '2026-02-23', '2026-01-31 10:20:27'),
(5, 28, 'weekly', 1, NULL, NULL, '2026-02-24', '2026-01-31 10:34:45'),
(6, 43, 'monthly', 1, NULL, NULL, '2026-05-02', '2026-02-02 05:35:49');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rooms`
--

CREATE TABLE `rooms` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `capacity` int NOT NULL,
  `area_size` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `short_desc` text COLLATE utf8mb4_general_ci,
  `description` text COLLATE utf8mb4_general_ci,
  `facilities` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `capacity`, `area_size`, `image`, `short_desc`, `description`, `facilities`, `created_at`) VALUES
(1, 'Ruang Rapat Lantai 5', 50, '', 'assets/images/rooms/1770084816_RuangRapatLantai5.jpeg', 'Ruang rapat untuk skala menengah ke atas.', 'Ruang rapat serbaguna yang didesain untuk kenyamanan diskusi tim besar maupun presentasi formal. Dilengkapi dengan fasilitas yang memadai.', '[\"WiFi\", \"Layar Proyektor\", \"AC\", \"Meja\", \"Kursi\", \"Toilet\"]', '2026-01-22 02:38:37'),
(2, 'Ruang Rapat Lantai 4', 30, '', 'assets/images/rooms/1770084590_RuangRapatLantai4.jpeg', 'Ruang Rapat skala menengah.', 'Ruang rapat serbaguna yang didesain untuk kenyamanan diskusi tim besar maupun presentasi formal. Dilengkapi dengan fasilitas yang memadai.', '[\"WiFi\", \"Proyektor\", \"Proyektor\", \"Layar Proyektor\", \"TV Ptoyektor\", \"Audio System\", \"AC\"]', '2026-01-22 02:38:37'),
(3, 'Workspace Lantai 5', 20, '', 'assets/images/rooms/1770084957_WorkspaceLantai5.jpeg', 'Ruang diskusi skala kecil.', 'Ruangan nyaman untuk diskusi internal dan koordinasi.', '[\"WiFi\", \"Karpet\", \"AC\", \"Meja Panjang\"]', '2026-01-22 02:38:37'),
(4, 'Ruang Rapat Lantai 1', 40, '', 'assets/images/rooms/1770084319_RuangRapatLantai1.jpeg', 'Ruang rapat serbaguna skala menengah.', 'Ruang rapat serbaguna yang didesain untuk kenyamanan diskusi tim besar maupun presentasi formal. Dilengkapi dengan fasilitas yang memadai.', '[\"WiFi\", \"Layar Proyektor\", \"Sound System\", \"AC\", \"Meja\", \"Kursi\"]', '2026-01-22 02:38:37'),
(5, 'Ruang Rapat Lantai 6A', 150, '', 'assets/images/rooms/1770085502_RuangRapatLantai6A.jpeg', 'Ruang rapat skala besar.', 'Ruang Rapat skala besar dengan desain interior modern yang ideal untuk pertemuan akbar atau acara seremonial penting. Dilengkapi dengan audio dan video profesional serta sistem pencahayaan yang memadai. Terdapat fasilitas tambahan seperti ruang transit, toilet dan musholla yang menambah kenyamanan peserta rapat.', '[\"WiFi\", \"AC\", \"Meja\", \"Kursi\", \"Audiosystem\", \"Mimbar\", \"Layar Proyektor\", \"Layar Monitor\", \"Komputer\", \"Dispenser\", \"Ruang Transit\", \"Toilet\", \"Mushola\"]', '2026-01-26 01:16:08'),
(12, 'Ruang Rapat Lantai 6B', 50, '', 'assets/images/rooms/1770085800_RuangRapatLantai6B.jpeg', 'Ruang Rapat skala menengah.', 'Ruang Rapat skala besar dengan desain interior modern yang ideal untuk pertemuan akbar atau acara seremonial penting. Dilengkapi dengan audio dan video profesional serta sistem pencahayaan yang memadai. Terdapat fasilitas tambahan seperti toilet dan musholla yang menambah kenyamanan peserta rapat.', '[\"WiFi\", \"AC\", \"Meja\", \"Kursi\", \"Audiosystem\", \"Layar Monitor\", \"Standing Mic\", \"Dispenser\", \"Toilet\", \"Mushola\"]', '2026-01-31 06:37:04');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_room_date` (`room_id`,`tanggal`),
  ADD KEY `idx_qr_token` (`qr_token`),
  ADD KEY `idx_parent_booking` (`parent_booking_id`),
  ADD KEY `idx_is_recurring` (`is_recurring`);

--
-- Indeks untuk tabel `recurring_patterns`
--
ALTER TABLE `recurring_patterns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_id` (`booking_id`);

--
-- Indeks untuk tabel `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT untuk tabel `recurring_patterns`
--
ALTER TABLE `recurring_patterns`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_parent_booking` FOREIGN KEY (`parent_booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `recurring_patterns`
--
ALTER TABLE `recurring_patterns`
  ADD CONSTRAINT `recurring_patterns_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
