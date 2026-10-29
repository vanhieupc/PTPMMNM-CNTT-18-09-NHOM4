-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 10:41 AM
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
-- Database: `file_sharing_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `download_logs`
--

CREATE TABLE `download_logs` (
  `id` int(11) NOT NULL,
  `share_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `status` varchar(50) NOT NULL,
  `downloaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`id`, `user_id`, `filename`, `stored_name`, `file_size`, `created_at`, `is_deleted`) VALUES
(6, 1, 'BTL_HQTCSDL_Phạm Anh Quân_Ngô Văn Hiếu_BaoCao (AutoRecovered).docx', '6adef48d8862c.docx', 6097354, '2026-10-26 06:34:53', 0),
(8, 1, 'Pham_Anh_Quan_Nhom6_BTL_BackEnd.pdf', '6adef901c2d4c.pdf', 1618627, '2026-10-26 06:53:53', 0),
(9, 1, 'Screenshot 2026-10-23 114109.png', '6adef91590958.png', 1473793, '2026-10-26 06:54:13', 1),
(10, 1, 'Screenshot 2026-03-14 164710.png', '6adf10527d583.png', 2596203, '2026-10-26 08:33:22', 0),
(11, 1, 'Du_lieu_bai_Nen_tang_hoc_lap_trinh.xlsx', '6adf106e68481.xlsx', 31239, '2026-10-26 08:33:50', 0),
(12, 1, 'BÀI KIỂM TRA GIỮA HỌC PHẦN.docx', '6adf107f02f20.docx', 2025726, '2026-10-26 08:34:07', 0),
(13, 1, 'baitaplon_tkw.rar', '6adf1097d251c.rar', 12877540, '2026-10-26 08:34:31', 0),
(14, 1, 'baitaplon_tkw.rar', '6adf11307c32e.rar', 12877540, '2026-10-26 08:37:04', 1),
(15, 1, 'Hiếu.docx', '6adf12f8635b4.docx', 1333531, '2026-10-26 08:44:40', 0),
(16, 1, 'Mau bao cao BTL CTDLGT.docx', '6adf137305afd.docx', 1312482, '2026-10-26 08:46:43', 0),
(17, 1, 'Phạm Anh Quân.pdf', '6adf137e070e2.pdf', 300409, '2026-10-26 08:46:54', 0),
(18, 1, 'Nhom_7_Pham_Anh_Quan_BaoCao_CTDL-DT_1871020471.pdf', '6adf1efebfa84.pdf', 1422362, '2026-10-26 09:35:58', 0),
(19, 1, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1af9e3c104.pptx', 1069883, '2026-10-28 08:17:34', 0),
(20, 1, 'ktra.html', '6ae1b9250be9a.html', 3512, '2026-10-28 08:58:13', 0),
(21, 1, 'BaoCao_BTL_PyThon_Phạm Anh Quân.zip', '6ae1b9a7777c7.zip', 748325, '2026-10-28 09:00:23', 0),
(22, 1, 'Nhom_7_Pham_Anh_Quan_BaoCao_CTDL-DT_1871020471.pdf', '6ae1b9c0cd9d1.pdf', 1422362, '2026-10-28 09:00:48', 0),
(23, 1, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1b9d1b801e.pptx', 1069883, '2026-10-28 09:01:05', 0),
(24, 1, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bb9a75613.pptx', 1069883, '2026-10-28 09:08:42', 0),
(25, 1, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bbb859dad.pptx', 1069883, '2026-10-28 09:09:12', 0),
(26, 1, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bc0a22313.pptx', 1069883, '2026-10-28 09:10:34', 0),
(27, 1, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bc379792b.pptx', 1069883, '2026-10-28 09:11:19', 0),
(28, 1, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bc41aa474.pptx', 1069883, '2026-10-28 09:11:29', 0),
(29, 1, 'Phạm Anh Quân Msv1871020471.docx', '6ae1bc9d041de.docx', 9423464, '2026-10-28 09:13:01', 0),
(30, 1, 'snow.png', '6ae1be384a763.png', 363369, '2026-10-28 09:19:52', 0),
(31, 1, 'snow.png', '6ae1bea68a365.png', 363369, '2026-10-28 09:21:42', 0),
(32, 1, 'snow.png', '6ae1beb855eb9.png', 363369, '2026-10-28 09:22:00', 0);

-- --------------------------------------------------------

--
-- Table structure for table `shares`
--

CREATE TABLE `shares` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `max_downloads` int(11) DEFAULT 0,
  `download_count` int(11) DEFAULT 0,
  `expires_at` datetime DEFAULT NULL,
  `allow_download` tinyint(1) DEFAULT 1,
  `status` enum('active','revoked') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `permission_type` varchar(20) DEFAULT 'download'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shares`
--

INSERT INTO `shares` (`id`, `file_id`, `token`, `password`, `max_downloads`, `download_count`, `expires_at`, `allow_download`, `status`, `created_at`, `permission_type`) VALUES
(4, 6, '5be29adebb3635e449a5368e49fd134d', NULL, 5, 1, '2026-10-27 23:59:59', 1, 'active', '2026-10-26 06:35:45', 'download'),
(5, 18, '14940a9340994ce693b25de97485e851', NULL, 2, 1, '2026-10-29 23:59:59', 1, 'active', '2026-10-28 06:33:21', 'blocked'),
(6, 17, 'b03265e47bb098aa135dbd55e225b470', '$2y$10$CdMeuGCsUuj/UICm8BEb..CYov7Cs5W.yV89Pc6kGb6pxfrZIzXS.', 1, 1, '2026-10-30 23:59:59', 1, 'active', '2026-10-28 08:48:56', 'download'),
(7, 15, 'f2021257a9345f06d4e551fc36c66922', '$2y$10$SY/r6N2tjTrMcC9oeXni2e5nPkoUEOUASlfyLLHiNHABQdMoUOGam', 2, 0, '2026-10-30 23:59:59', 1, 'active', '2026-10-28 08:51:09', 'download');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'quan', 'aquan755@gmail.com', '$2y$10$V9PfAGNjMP0TWUbut43g6.z9gGMWELA4FpDS9WshPgkBN16.bZJoq', '2026-10-23 02:31:18'),
(2, 'hiếu', '24101839@st.phenikaa-uni.edu.vn', '$2y$10$PkYLq9CmGvF7GpZciNESf.KGp8C2IT8jkCF51cn3djJ2AgmmPMpBO', '2026-10-23 04:12:29'),
(3, 'hiihi', '1871020471@dnu.edu.vn', '$2y$10$1Hy1nHllRZbPFMpE0Hd38eoSZOTti5XZQbJ9wvUI9ybHXRRUz49qO', '2026-10-28 09:22:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `download_logs`
--
ALTER TABLE `download_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `share_id` (`share_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `download_logs`
--
ALTER TABLE `download_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `shares`
--
ALTER TABLE `shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `download_logs`
--
ALTER TABLE `download_logs`
  ADD CONSTRAINT `download_logs_ibfk_1` FOREIGN KEY (`share_id`) REFERENCES `shares` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shares`
--
ALTER TABLE `shares`
  ADD CONSTRAINT `shares_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
