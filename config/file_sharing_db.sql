-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th10 08, 2026 lúc 12:23 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `file_sharing_db`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `download_logs`
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
-- Cấu trúc bảng cho bảng `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `folder_id` int(11) DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `stored_name` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` int(11) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `files`
--

INSERT INTO `files` (`id`, `user_id`, `folder_id`, `filename`, `stored_name`, `file_size`, `created_at`, `is_deleted`, `deleted_at`) VALUES
(8, 1, NULL, 'Pham_Anh_Quan_Nhom6_BTL_BackEnd.pdf', '6adef901c2d4c.pdf', 1618627, '2026-10-26 06:53:53', 0, NULL),
(10, 1, NULL, 'Screenshot 2026-03-14 164710.png', '6adf10527d583.png', 2596203, '2026-10-26 08:33:22', 0, NULL),
(11, 1, NULL, 'Du_lieu_bai_Nen_tang_hoc_lap_trinh.xlsx', '6adf106e68481.xlsx', 31239, '2026-10-26 08:33:50', 0, NULL),
(12, 1, NULL, 'BÀI KIỂM TRA GIỮA HỌC PHẦN.docx', '6adf107f02f20.docx', 2025726, '2026-10-26 08:34:07', 0, NULL),
(13, 1, NULL, 'baitaplon_tkw.rar', '6adf1097d251c.rar', 12877540, '2026-10-26 08:34:31', 0, NULL),
(15, 1, NULL, 'Hiếu.docx', '6adf12f8635b4.docx', 1333531, '2026-10-26 08:44:40', 0, NULL),
(16, 1, NULL, 'Mau bao cao BTL CTDLGT.docx', '6adf137305afd.docx', 1312482, '2026-10-26 08:46:43', 0, NULL),
(17, 1, NULL, 'Phạm Anh Quân.pdf', '6adf137e070e2.pdf', 300409, '2026-10-26 08:46:54', 0, NULL),
(18, 1, NULL, 'Nhom_7_Pham_Anh_Quan_BaoCao_CTDL-DT_1871020471.pdf', '6adf1efebfa84.pdf', 1422362, '2026-10-26 09:35:58', 0, NULL),
(19, 1, NULL, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1af9e3c104.pptx', 1069883, '2026-10-28 08:17:34', 0, NULL),
(20, 1, NULL, 'ktra.html', '6ae1b9250be9a.html', 3512, '2026-10-28 08:58:13', 0, NULL),
(21, 1, NULL, 'BaoCao_BTL_PyThon_Phạm Anh Quân.zip', '6ae1b9a7777c7.zip', 748325, '2026-10-28 09:00:23', 0, NULL),
(22, 1, NULL, 'Nhom_7_Pham_Anh_Quan_BaoCao_CTDL-DT_1871020471.pdf', '6ae1b9c0cd9d1.pdf', 1422362, '2026-10-28 09:00:48', 0, NULL),
(23, 1, NULL, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1b9d1b801e.pptx', 1069883, '2026-10-28 09:01:05', 0, NULL),
(24, 1, NULL, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bb9a75613.pptx', 1069883, '2026-10-28 09:08:42', 0, NULL),
(25, 1, NULL, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bbb859dad.pptx', 1069883, '2026-10-28 09:09:12', 0, NULL),
(26, 1, NULL, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bc0a22313.pptx', 1069883, '2026-10-28 09:10:34', 0, NULL),
(27, 1, NULL, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bc379792b.pptx', 1069883, '2026-10-28 09:11:19', 0, NULL),
(28, 1, NULL, 'bao_cao_bai_tap_nhom_so7_CNTT18_02.pptx', '6ae1bc41aa474.pptx', 1069883, '2026-10-28 09:11:29', 0, NULL),
(29, 1, NULL, 'Phạm Anh Quân Msv1871020471.docx', '6ae1bc9d041de.docx', 9423464, '2026-10-28 09:13:01', 0, NULL),
(30, 1, NULL, 'snow.png', '6ae1be384a763.png', 363369, '2026-10-28 09:19:52', 0, NULL),
(31, 1, NULL, 'snow.png', '6ae1bea68a365.png', 363369, '2026-10-28 09:21:42', 0, NULL),
(32, 1, NULL, 'snow.png', '6ae1beb855eb9.png', 363369, '2026-10-28 09:22:00', 0, NULL),
(45, 1, NULL, 'Đề 9.docx', '6ae2255af4051.docx', 395465, '2026-10-28 16:39:55', 0, NULL),
(46, 1, NULL, 'NHAP.xlsx', '6ae225664d979.xlsx', 15633, '2026-10-28 16:40:06', 0, NULL),
(47, 1, NULL, '5. Links hỗ trợ online.docx', '6ae2257a63cfa.docx', 23005, '2026-10-28 16:40:26', 0, NULL),
(48, 1, NULL, 'Mẫu báo cáo BTN.docx', '6ae2261ae48e9.docx', 195406, '2026-10-28 16:43:06', 0, NULL),
(49, 1, NULL, 'Mẫu báo cáo bài tập lớn Python.docx', '6ae2262421a16.docx', 178762, '2026-10-28 16:43:16', 0, NULL),
(50, 1, NULL, 'thuyết trình CNPM buổi 1.pptx', '6ae22630e503e.pptx', 8448786, '2026-10-28 16:43:28', 0, NULL),
(53, 1, NULL, 'MÔ HÌNH TỔNG QUAN HỆ THỐNG.pptx', '6ae2272399dc3.pptx', 37678, '2026-10-28 16:47:31', 0, NULL),
(55, 1, NULL, 'NHAP.xlsx', '6ae232ed37f70.xlsx', 15633, '2026-10-28 17:37:49', 1, NULL),
(56, 1, NULL, 'NHAP.xlsx', '6ae232f6db192.xlsx', 15633, '2026-10-28 17:37:58', 0, NULL),
(57, 1, NULL, 'NHAP.xlsx', '6ae2f24d262cb.xlsx', 15633, '2026-10-29 07:14:21', 0, NULL),
(58, 1, NULL, 'NHAP.xlsx', '6ae2f25293207.xlsx', 15633, '2026-10-29 07:14:26', 0, NULL),
(59, 1, NULL, 'NHAP.xlsx', '6ae2f257ef12a.xlsx', 15633, '2026-10-29 07:14:31', 0, NULL),
(60, 1, NULL, 'Đề 9.docx', '6ae30cf300d68.docx', 395465, '2026-10-29 09:08:03', 0, NULL),
(61, 1, NULL, 'NgoVanHieu_1871020234.zip', '6ae30d2171c86.zip', 770795, '2026-10-29 09:08:49', 1, NULL),
(68, 1, 6, 'Đề 9.docx', '6ae770318693d.docx', 395465, '2026-11-01 17:00:33', 0, NULL),
(69, 1, 8, 'Đề 9.docx', '6ae77079412a2.docx', 395465, '2026-11-01 17:01:45', 0, NULL),
(70, 1, 8, 'NHAP.xlsx', '6ae7708356a11.xlsx', 15633, '2026-11-01 17:01:55', 0, NULL),
(71, 1, NULL, 'NHAP.xlsx', '6ae77092b9f1c.xlsx', 15633, '2026-11-01 17:02:10', 0, NULL),
(72, 1, 6, 'Đề 9.docx', '6ae770a84c85a.docx', 395465, '2026-11-01 17:02:32', 0, NULL),
(73, 1, 9, 'Đề 9.docx', '6ae7711d66abc.docx', 395465, '2026-11-01 17:04:29', 1, '2026-11-02 00:04:33'),
(74, 1, NULL, 'test 8.pdf', '6ae7748ed3349.pdf', 697379, '2026-11-01 17:19:10', 0, NULL),
(75, 1, NULL, 'Đề 9.docx', '6ae774957336c.docx', 395465, '2026-11-01 17:19:17', 0, NULL),
(76, 1, NULL, 'thuyết trình CNPM buổi 1.pptx', '6ae774ea8d4a0.pptx', 8448786, '2026-11-01 17:20:42', 0, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `folders`
--

CREATE TABLE `folders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `folder_name` varchar(255) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `folders`
--

INSERT INTO `folders` (`id`, `user_id`, `folder_name`, `parent_id`, `created_at`, `is_deleted`, `deleted_at`) VALUES
(6, 1, 'hi', NULL, '2026-11-01 16:50:20', 0, NULL),
(7, 1, 'You', NULL, '2026-11-01 17:01:27', 0, NULL),
(8, 1, 'My', NULL, '2026-11-01 17:01:34', 0, NULL),
(9, 1, 'Me', NULL, '2026-11-01 17:03:13', 1, '2026-11-02 00:04:33');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `shares`
--

CREATE TABLE `shares` (
  `id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `access_type` varchar(50) DEFAULT 'public',
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
-- Đang đổ dữ liệu cho bảng `shares`
--

INSERT INTO `shares` (`id`, `file_id`, `token`, `access_type`, `password`, `max_downloads`, `download_count`, `expires_at`, `allow_download`, `status`, `created_at`, `permission_type`) VALUES
(5, 18, '14940a9340994ce693b25de97485e851', 'public', NULL, 2, 1, '2026-10-29 23:59:59', 1, 'active', '2026-10-28 06:33:21', 'blocked'),
(6, 17, 'b03265e47bb098aa135dbd55e225b470', 'restricted', NULL, 10, 14, '2026-11-03 23:59:59', 1, 'active', '2026-10-28 08:48:56', 'download'),
(7, 15, 'f2021257a9345f06d4e551fc36c66922', 'public', '$2y$10$SY/r6N2tjTrMcC9oeXni2e5nPkoUEOUASlfyLLHiNHABQdMoUOGam', 2, 0, '2026-10-30 23:59:59', 1, 'active', '2026-10-28 08:51:09', 'download'),
(8, 53, '56ebfb41c8860f947b77e9e80ccb45a4', 'public', NULL, 10, 0, '2026-11-03 23:59:59', 1, 'active', '2026-10-29 09:11:14', 'view_only'),
(9, 60, '5cd2d58f6f56b1b2cd2d3f0957ad894d', 'public', NULL, 5, 0, '2026-10-30 23:59:59', 1, 'active', '2026-10-29 09:27:02', 'view_only'),
(10, 8, '32d50569aa0c189ef3e7fd0935e22ba3', 'public', NULL, 5, 0, '2026-10-30 23:59:59', 1, 'active', '2026-10-29 09:27:31', 'view_only'),
(11, 57, '65fb7578733a263d2a2bd79b94c9a600', 'public', NULL, 5, 0, '2026-10-30 23:59:59', 1, 'active', '2026-10-29 09:29:09', 'view_only'),
(12, 76, 'e5e8ba2ac97418f6e9f938a1c44214c9', 'public', NULL, 20, 1, '2026-11-03 23:59:59', 1, 'active', '2026-11-01 17:20:56', 'download'),
(13, 75, '8993cae52a202b786e737bbba7c0ccb4', 'restricted', NULL, 5, 1, '2026-11-03 23:59:59', 1, 'active', '2026-11-01 17:21:50', 'download'),
(14, 71, 'a51f26384fd2d3303e8a10750334292d', 'public', '$2y$10$NBZFx32hTEzXV7xoWMN.quaGTf8qU2gC7vhBR2TpwIPTECmuGgl9.', 10, 7, '2026-11-03 23:59:59', 1, 'active', '2026-11-01 17:22:58', 'download');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(1, 'quan', 'aquan755@gmail.com', '$2y$10$V9PfAGNjMP0TWUbut43g6.z9gGMWELA4FpDS9WshPgkBN16.bZJoq', '2026-10-23 02:31:18'),
(2, 'hiếu', '24101839@st.phenikaa-uni.edu.vn', '$2y$10$PkYLq9CmGvF7GpZciNESf.KGp8C2IT8jkCF51cn3djJ2AgmmPMpBO', '2026-10-23 04:12:29'),
(3, 'hiihi', '1871020471@dnu.edu.vn', '$2y$10$1Hy1nHllRZbPFMpE0Hd38eoSZOTti5XZQbJ9wvUI9ybHXRRUz49qO', '2026-10-28 09:22:31'),
(5, 'thai', 'ngovanhieu10a2@gmail.com', '$2y$10$OqeMw1CSgbCv4kBhbqj3RO5DFOrEbVREQzLQQEMmY8573ClQygSTC', '2026-11-01 15:28:40');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `download_logs`
--
ALTER TABLE `download_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `share_id` (`share_id`);

--
-- Chỉ mục cho bảng `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Chỉ mục cho bảng `folders`
--
ALTER TABLE `folders`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `shares`
--
ALTER TABLE `shares`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `file_id` (`file_id`);

--
-- Chỉ mục cho bảng `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `download_logs`
--
ALTER TABLE `download_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT cho bảng `folders`
--
ALTER TABLE `folders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT cho bảng `shares`
--
ALTER TABLE `shares`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT cho bảng `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `download_logs`
--
ALTER TABLE `download_logs`
  ADD CONSTRAINT `download_logs_ibfk_1` FOREIGN KEY (`share_id`) REFERENCES `shares` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `shares`
--
ALTER TABLE `shares`
  ADD CONSTRAINT `shares_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
