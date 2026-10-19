-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 09, 2026 at 03:10 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `crm_email`
--

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` bigint UNSIGNED NOT NULL,
  `pic` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_phone` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci NOT NULL,
  `case_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `pic`, `customer_name`, `customer_email`, `customer_phone`, `case_id`, `subject`, `created_at`, `updated_at`) VALUES
(1, 'Ulin Nuha', 'Arief Budiman', 'sem.abudiman@cma-cgm.com', '08156567560', '5100082109', 'Dukungan HP Ref. 5100082109– Konfirmasi Jadwal Kunjungan', '2026-03-17 07:51:09', '2026-03-17 07:51:09'),
(2, 'Ulin Nuha', 'Tony', 'it.desktop@ioh.co.id', '082220764372', '5100082080', 'Dukungan HP Ref. 5100082080– Konfirmasi Jadwal Kunjungan', '2026-03-17 07:55:11', '2026-03-17 07:55:11'),
(3, 'Ulin Nuha', 'Ilham Fajrul Haq', 'helpdesk@erakomp.com', '082328939466', '5100082101', 'Dukungan HP Ref. 5100082101– Konfirmasi Jadwal Kunjungan', '2026-03-17 08:31:00', '2026-03-17 08:31:00'),
(4, 'Ulin Nuha', 'Alvian Irmandhika', 'alvian.irmandhika@gmail.com', '089697526099', '5100082044', 'Dukungan HP Ref. 5100082044 – Penutupan Kasus\r\n', '2026-03-18 02:31:10', '2026-03-18 02:31:10'),
(5, 'Ayu Astari', 'Bagus Iman Judiesanto', 'bagus.judiesanto@ptbpi.co.id', '081313397677', '5100082102', 'Dukungan HP Ref. 5100082102– Konfirmasi Jadwal Kunjungan', '2026-03-18 03:25:17', '2026-03-18 03:25:17'),
(6, 'Ayu Astari', 'Bagus Iman Judiesanto', 'bagus.judiesanto@ptbpi.co.id', '081313397677', '5100082102', 'Dukungan HP Ref. 5100082102– Konfirmasi Jadwal Kunjungan', '2026-03-18 03:25:25', '2026-03-18 03:25:25'),
(7, 'Ayu Astari', 'Bagus Iman Judiesanto', 'bagus.judiesanto@ptbpi.co.id', '081313397677', '5100082102', 'Dukungan HP Ref. 5100082102– Konfirmasi Jadwal Kunjungan', '2026-03-18 03:25:31', '2026-03-18 03:25:31'),
(8, 'Ulin Nuha', 'Heri Tri Putranto', '16373@fifgroup.co.id', '081359923909', '5100082078', 'Dukungan HP Ref. 5100082078 – Penutupan Kasus\r\n', '2026-03-18 03:56:53', '2026-03-18 03:56:53'),
(9, 'Ulin Nuha', 'Randi', 'randi.wahyu.prasetyo@polytron.co.id', '087834688269', '5100082079', 'Dukungan HP Ref. 5100082079 – Penutupan Kasus\r\n', '2026-03-18 04:00:26', '2026-03-18 04:00:26'),
(10, 'Ayu Astari', 'Dionisius Robert Mulyono', 'bagus.judiesanto@ptbpi.co.id', '081228505824', '5100082102', 'Pengingat Jadwal Layanan HP Ref. 5100082102', '2026-03-25 09:15:34', '2026-03-25 09:15:34'),
(11, 'Ayu Astari', 'Agus Khoirul Huda', 'syg.helpdesk@polytron.co.id', '082313657641', '5100082132', 'Dukungan HP Ref. 5100082132– Konfirmasi Jadwal Kunjungan', '2026-03-27 01:58:30', '2026-03-27 01:58:30'),
(12, 'Ulin Nuha', 'Tony', 'it.desktop@ioh.co.id', '082220764372', '5100082080', 'Dukungan HP Ref. 5100082080 – Penutupan Kasus\r\n', '2026-03-27 02:54:45', '2026-03-27 02:54:45'),
(13, 'Ulin Nuha', 'Arief Budiman', 'sem.abudiman@cma-cgm.com', '08156567560', '5100082109', 'Dukungan HP Ref. 5100082109 – Penutupan Kasus\r\n', '2026-03-27 03:12:33', '2026-03-27 03:12:33'),
(14, 'Ulin Nuha', 'Ilham Fajrul Haq', 'helpdesk@erakomp.com', '082328939466', '5100082101', 'Dukungan HP Ref. 5100082101 – Penutupan Kasus\r\n', '2026-03-27 03:18:16', '2026-03-27 03:18:16'),
(15, 'Ulin Nuha', 'Dona', 'servicekat@karyaanugrah.com', '081294671483', '5100082073', 'Pengingat Jadwal Layanan HP Ref. 5100082073', '2026-03-28 04:45:45', '2026-03-28 04:45:45'),
(16, 'Ayu Astari', 'Aditya Chandra Eko Yunianto', '36070@fifgroup.co.id', '085740033189', '5100082127', 'Pengingat Jadwal Layanan HP Ref. 5100082127', '2026-03-30 01:36:51', '2026-03-30 01:36:51'),
(17, 'Ayu Astari', 'Andre', 'andreas.wibowo@trans-cosmos.co.id', '08971093355', '5100082084', 'Pengingat Jadwal Layanan HP Ref. 5100082084', '2026-03-30 01:38:05', '2026-03-30 01:38:05'),
(18, 'Ulin Nuha', 'Adi Prasetyo', '63691@fifgroup.co.id', '08122523243', '5100082128', 'Dukungan HP Ref. 5100082128– Konfirmasi Jadwal Kunjungan', '2026-03-30 01:50:27', '2026-03-30 01:50:27'),
(19, 'Ulin Nuha', 'Supriyono', 'supriyono@gmail.com', '085225556777', '5100082163', 'Dukungan HP Ref. 5100082163 – Selesai Perbaikan\r\n', '2026-03-31 02:10:41', '2026-03-31 02:10:41'),
(20, 'Ulin Nuha', 'Candra Ananda Pratama', 'candraanandapratama@gmail.com', '085175310093', '5100082158', 'Dukungan HP Ref. 5100082158 – Selesai Perbaikan\r\n', '2026-03-31 02:12:27', '2026-03-31 02:12:27'),
(21, 'Ulin Nuha', 'Rauf', 'rauf@gmail.com', '085103378378', '5100082145', 'Dukungan HP Ref. 5100082145 – Selesai Perbaikan\r\n', '2026-03-31 02:14:15', '2026-03-31 02:14:15'),
(22, 'Ulin Nuha', 'Andre Setiawan', 'andresetiawan@gmail.com', '0882006166006', '5100082047', 'Dukungan HP Ref. 5100082047 – Selesai Perbaikan\r\n', '2026-03-31 02:16:19', '2026-03-31 02:16:19'),
(23, 'Ulin Nuha', 'Aditya Chandra Eko Yunianto', '36070@fifgroup.co.id', '085740033189', '5100082127', 'Dukungan HP Ref. 5100082127– Konfirmasi Jadwal Kunjungan', '2026-03-31 06:37:25', '2026-03-31 06:37:25'),
(24, 'Ulin Nuha', 'Aditya Chandra Eko Yunianto', '36070@fifgroup.co.id', '085740033189', '5100082127', 'Pengingat Jadwal Layanan HP Ref. 5100082127', '2026-04-01 01:15:45', '2026-04-01 01:15:45'),
(25, 'Ulin Nuha', 'Andre', 'andreas.wibowo@trans-cosmos.co.id', '08971093355', '5100082084', 'Dukungan HP Ref. 5100082084 – Penutupan Kasus\r\n', '2026-04-01 02:15:57', '2026-04-01 02:15:57'),
(26, 'Ulin Nuha', 'Angel', 'cahayakomputer@gmail.com', '08988526743', '5100082173', 'Dukungan HP Ref. 5100082173 – Selesai Perbaikan\r\n', '2026-04-01 02:31:23', '2026-04-01 02:31:23'),
(27, 'Ayu Astari', 'Ilham Fajrul Haq', 'helpdesk@erakomp.com', '082328939466', '5100082164', 'Dukungan HP Ref. 5100082164– Konfirmasi Jadwal Kunjungan', '2026-04-01 03:38:59', '2026-04-01 03:38:59'),
(28, 'Ulin Nuha', 'Destu Darma', 'destudarma@gmail.com', '085191294906', '5100082180', 'Dukungan HP Ref. 5100082180 – Selesai Perbaikan\r\n', '2026-04-01 07:45:53', '2026-04-01 07:45:53'),
(29, 'Ayu Astari', 'Delbert', 'delbert.emmanuel@polytron.co.id', '081272638183', '5100082181', 'Dukungan HP Ref. 5100082181– Konfirmasi Jadwal Kunjungan', '2026-04-01 08:46:59', '2026-04-01 08:46:59'),
(30, 'Ulin Nuha', 'Agus Khoirul Huda', 'syg.helpdesk@polytron.co.id', '082313657641', '5100082132', 'Dukungan HP Ref. 5100082132 – Penutupan Kasus\r\n', '2026-04-02 02:57:35', '2026-04-02 02:57:35'),
(31, 'Ulin Nuha', 'Bagus Iman Judiesanto', 'bagus.judiesanto@ptbpi.co.id', '081313397677', '5100082102', 'Dukungan HP Ref. 5100082102 – Penutupan Kasus\r\n', '2026-04-02 03:06:26', '2026-04-02 03:06:26'),
(32, 'Ulin Nuha', 'Adi Prasetyo', '63691@fifgroup.co.id', '08122523243', '5100082128', 'Dukungan HP Ref. 5100082128 – Penutupan Kasus\r\n', '2026-04-02 03:09:39', '2026-04-02 03:09:39'),
(33, 'Ayu Astari', 'Warih Suprihanto', 'warih123@gmail.com', '081315045599', '5100082154', 'Dukungan HP Ref. 5100082154 – Selesai Perbaikan\r\n', '2026-04-02 07:27:22', '2026-04-02 07:27:22'),
(34, 'Ulin Nuha', 'Lili', 'dwialiliq@gmail.com', '088232175910', '5100082224', 'Dukungan HP Ref. 5100082224 – Selesai Perbaikan\r\n', '2026-04-04 02:40:37', '2026-04-04 02:40:37'),
(35, 'Ulin Nuha', 'Ratih', 'ratih@gmail.com', '085725983088', '5100082225', 'Dukungan HP Ref. 5100082225 – Selesai Perbaikan\r\n', '2026-04-07 01:42:26', '2026-04-07 01:42:26'),
(36, 'Ulin Nuha', 'Yuni', 'elscomputer@gmail.com', '081327572800', '5100082207', 'Dukungan HP Ref. 5100082207 – Selesai Perbaikan\r\n', '2026-04-07 01:44:04', '2026-04-07 01:44:04'),
(37, 'Ulin Nuha', 'Solikhin', 'icipaint@gmail.com', '081326777906', '5100082203', 'Dukungan HP Ref. 5100082203 – Selesai Perbaikan\r\n', '2026-04-07 01:45:41', '2026-04-07 01:45:41'),
(38, 'Ulin Nuha', 'Lilis', 'lilis@gmail.com', '087824291512', '5100082183', 'Dukungan HP Ref. 5100082183 – Selesai Perbaikan\r\n', '2026-04-07 01:46:50', '2026-04-07 01:46:50'),
(39, 'Ulin Nuha', 'Dwi', 'dwi@gmail.com', '085640666251', '5100082182', 'Dukungan HP Ref. 5100082182 – Selesai Perbaikan\r\n', '2026-04-07 02:12:25', '2026-04-07 02:12:25'),
(40, 'Ulin Nuha', 'Ahlun', 'albumi@gmail.com', '089635292437', '5100082179', 'Dukungan HP Ref. 5100082179 – Selesai Perbaikan\r\n', '2026-04-07 02:14:14', '2026-04-07 02:14:14'),
(41, 'Ulin Nuha', 'Ahlun', 'albumi@gmail.com', '089635292437', '5100082178', 'Dukungan HP Ref. 5100082178 – Selesai Perbaikan\r\n', '2026-04-07 02:15:30', '2026-04-07 02:15:30'),
(42, 'Ulin Nuha', 'Beni', 'beni@gmail.com', '081296516253', '5100082176', 'Dukungan HP Ref. 5100082176 – Selesai Perbaikan\r\n', '2026-04-07 02:17:35', '2026-04-07 02:17:35'),
(43, 'Ulin Nuha', 'Hendri', 'rajawaliberdikari@gmail.com', '085200080093', '5100082144', 'Dukungan HP Ref. 5100082144 – Selesai Perbaikan\r\n', '2026-04-07 02:18:39', '2026-04-07 02:18:39'),
(44, 'Ulin Nuha', 'Fathur Rohman', 'fathurrohman@gmail.com', '081388226630', '5100082103', 'Dukungan HP Ref. 5100082103 – Selesai Perbaikan\r\n', '2026-04-07 02:39:08', '2026-04-07 02:39:08'),
(45, 'Ulin Nuha', 'Alifin Prima Ranidya', 'alifinprimaranidya@gmail.com', '083115151155', '5100082162', 'Dukungan HP Ref. 5100082162 – Selesai Perbaikan\r\n', '2026-04-07 03:15:36', '2026-04-07 03:15:36'),
(46, 'Ulin Nuha', 'Taufik', 'taufik.adhi@iconpln.co.id', '08983275959', '5100082247', 'Dukungan HP Ref. 5100082247– Konfirmasi Jadwal Kunjungan', '2026-04-07 06:06:54', '2026-04-07 06:06:54'),
(47, 'Ulin Nuha', 'Aditya Chandra Eko Yunianto', '36070@fifgroup.co.id', '085740033189', '5100082127', 'Dukungan HP Ref. 5100082127 – Penutupan Kasus\r\n', '2026-04-07 06:12:43', '2026-04-07 06:12:43'),
(48, 'Ulin Nuha', 'Ilham Fajrul Haq', 'helpdesk@erakomp.com', '082328939466', '5100082164', 'Dukungan HP Ref. 5100082164 – Penutupan Kasus\r\n', '2026-04-07 06:15:07', '2026-04-07 06:15:07'),
(49, 'Ulin Nuha', 'Delbert', 'delbert.emmanuel@polytron.co.id', '081272638183', '5100082181', 'Dukungan HP Ref. 5100082181 – Penutupan Kasus\r\n', '2026-04-07 06:23:48', '2026-04-07 06:23:48'),
(50, 'Ulin Nuha', 'Taufik', 'taufik.adhi@iconpln.co.id', '08983275959', '5100082247', 'Pengingat Jadwal Layanan HP Ref. 5100082247', '2026-04-08 01:38:43', '2026-04-08 01:38:43'),
(51, 'Ayu Astari', 'Mohammad Ibnu Khamidun', 'ibnukhamidun@gmail.com', '087781097862', '5100082198', 'Dukungan HP Ref. 5100082198 – Selesai Perbaikan\r\n', '2026-04-08 01:40:02', '2026-04-08 01:40:02'),
(52, 'Ulin Nuha', 'Pradita Iswara', 'pradita.iswara@ptbpi.co.id', '085229738307', '5100082227', 'Dukungan HP Ref. 5100082227– Konfirmasi Jadwal Kunjungan', '2026-04-08 02:40:04', '2026-04-08 02:40:04'),
(53, 'Ulin Nuha', 'Axel', 'axel@gmail.com', '0895320379365', '5100082266', 'Dukungan HP Ref. 5100082266 – Selesai Perbaikan', '2026-04-09 02:01:00', '2026-04-09 02:01:00'),
(54, 'Ulin Nuha', 'Anisa', 'anisa@gmail.com', '085641575166', '5100082255', 'Dukungan HP Ref. 5100082255 – Selesai Perbaikan', '2026-04-09 02:02:26', '2026-04-09 02:02:26'),
(55, 'Ulin Nuha', 'Awan', 'awan@gmail.com', '081327978389', '5100082238', 'Dukungan HP Ref. 5100082238 – Selesai Perbaikan', '2026-04-09 02:03:37', '2026-04-09 02:03:37'),
(56, 'Ayu Astari', 'Azhra', 'azhra123@gmail.com', '087888051088', '5100082232', 'Dukungan HP Ref. 5100082232 – Selesai Perbaikan', '2026-04-09 02:11:47', '2026-04-09 02:11:47'),
(57, 'Ulin Nuha', 'Bayu Indra', 'bayuindra@gmail.com', '085640497633', '5100082216', 'Dukungan HP Ref. 5100082216 – Selesai Perbaikan', '2026-04-09 02:26:20', '2026-04-09 02:26:20');

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `body` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `wa_body` longtext CHARACTER SET utf8mb3 COLLATE utf8mb3_unicode_ci NOT NULL,
  `required_fields` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `email_templates`
--

INSERT INTO `email_templates` (`id`, `name`, `subject`, `body`, `wa_body`, `required_fields`, `created_at`, `updated_at`) VALUES
(1, '[1001] Service Confirmation - Konfirmasi Layanan', 'Tanggapan atas Laporan Dukungan HP Ref. {{workorder}}– Konfirmasi Layanan', '<p>Yth. Bapak/Ibu <strong>{{customer_name}}</strong>,</p>\r\n<p>\r\nPerkenalkan, saya <strong>{{pic}}</strong> dari <strong>PT. Harmoni Putra Solusindo</strong>, mitra resmi penyedia layanan HP. Kasus Bapak/Ibu telah ditugaskan kepada kami, dan saya akan menjadi <strong>PIC</strong>  selama proses penanganan ini.\r\n</p>\r\n<p>\r\nSaat ini kami telah melakukan pemesanan part yang dibutuhkan, dengan estimasi kedatangan pada <strong>{{eta}}</strong>. Setelah part diterima, kami mengusulkan jadwal layanan pada <strong>{{service_date}}</strong> atau <strong>{{service_date1}}</strong>.\r\n</p>\r\n<p>\r\nMohon kesediaan Bapak/Ibu untuk menghubungi kami di nomor whatsapp <strong>085747009467</strong> guna mengonfirmasi jadwal yang paling sesuai.\r\n</p>\r\n<p>\r\nKami berkomitmen untuk memberikan pengalaman layanan terbaik bagi Bapak/Ibu.\r\n</p>\r\n\r\n<p>Hormat kami,<br>\r\n{{pic}}<br>\r\n{{sender_position}}<br>\r\n{{company_name}}<br>\r\nTelp: {{sender_phone}}<br>\r\n<img src=\"{{company_logo}}\" \r\n     alt=\"Company Logo\" \r\n     width=\"120\" \r\n     style=\"display:block; width:120px; height:auto; margin-top:10px;\"></p>', 'Yth. Bapak/Ibu {{customer_name}},\r\n\r\nSaya {{pic}} dari HP Service Center Semarang, ingin konfirmasi bahwa Case Onsite Bapak/Ibu dengan nomor {{workorder}} telah ditugaskan kepada kami, dan saya akan menjadi PIC selama proses penanganan ini berlangsung.\r\n\r\nSaat ini kami telah melakukan pemesanan part yang dibutuhkan, dengan estimasi kedatangan pada {{eta}}. Setelah part diterima, kami mengusulkan jadwal kunjungan onsite pada {{service_date}} atau {{service_date1}}.\r\nMohon kesediaan Bapak/Ibu untuk mengkonfirmasi kepada kami perihal jadwal yang paling sesuai.\r\n\r\nTerima kasih,\r\nSalam,\r\n{{pic}}\r\n{{sender_position}}\r\n{{company_name}}', 'null', NULL, '2026-04-08 07:17:11'),
(2, '[1002] Part Delay Notification - Notifikasi Keterlambatan Part', 'Pembaruan Penting Dukungan HP Ref. {{workorder}} – Keterlambatan Part', '<p>Yth. Bapak/Ibu <strong>{{customer_name}}</strong>,</p>\r\n<p>Kami ingin menginformasikan bahwa terdapat keterlambatan pada pengiriman part yang dibutuhkan untuk layanan Bapak/Ibu. Kami memahami hal ini dapat menimbulkan ketidaknyamanan dan <strong>kami mohon maaf</strong> atas situasi tersebut.\r\n</p>\r\n<p>\r\nTim kami saat ini sedang berkoordinasi untuk mendapatkan jadwal pengiriman terbaru. Begitu informasi tersedia, kami akan segera menghubungi Bapak/Ibu untuk penjadwalan ulang layanan.\r\n</p>\r\n<p>\r\nTerima kasih atas pengertian dan kesabaran Bapak/Ibu.\r\n</p>\r\n\r\n<p>Hormat kami,<br>\r\n{{pic}}<br>\r\n{{company_name}}<br>\r\n<img src=\"{{company_logo}}\" \r\n     alt=\"Company Logo\" \r\n     width=\"120\" \r\n     style=\"display:block; width:120px; height:auto; margin-top:10px;\"></p>', 'Yth Ibu/Bapak {{customer_name}}\r\n\r\nKami ingin menginformasikan bahwa Case Onsite dengan nomor {{workorder}} terdapat keterlambatan pada pengiriman part {{part_kosong}} yang dibutuhkan untuk unit Bapak/Ibu. Kami memahami hal ini dan kami mohon maaf atas ketidaknyamannya.\r\n\r\nSaat ini kami sedang berkoordinasi dengan tim terkait untuk mendapatkan informasi terbaru perihal estimasi tanggal pengiriman part. Begitu informasi tersedia, kami akan segera menghubungi Bapak/Ibu kembali untuk penjadwalan ulang kunjungan layanan ini.\r\n\r\nTerima kasih atas pengertian Bapak/Ibu.\r\n\r\nSalam,\r\n{{pic}}\r\n{{sender_position}}\r\n{{company_name}}', '[\"customer_name\", \"workorder\"]', NULL, '2026-04-08 07:17:20'),
(3, '[1003] Confirm Visit Schedule - Konfirmasi Jadwal Kunjungan', 'Dukungan HP Ref. {{workorder}}– Konfirmasi Jadwal Kunjungan', '<p>Yth. Bapak/Ibu <strong>{{customer_name}}</strong>,</p>\r\n<p>Terima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.<br>\r\nTeknisi kami, <strong>{{technician_id}}</strong>, dijadwalkan hadir pada:</p>\r\n    <p>•Tanggal: {{appointment_date}}<br>\r\n       •Waktu: {{appointment_time}}</p>\r\n<p>Mohon diinformasikan <strong>PIC di lokasi</strong> serta akses yang diperlukan agar proses layanan dapat berjalan lancar. Perlu kami informasikan bahwa selama layanan berlangsung mungkin akan terdapat <strong>potensi gangguan operasional, jika ada.</strong></p>\r\n<p>Apabila terdapat perubahan atau pertanyaan, silakan menghubungi saya di <strong>085747009467</strong>.</P\r\n\r\n<p>Hormat kami,<br>\r\n{{pic}}<br>\r\n{{company_name}}<br>\r\n<img src=\"{{company_logo}}\" \r\n     alt=\"Company Logo\" \r\n     width=\"120\" \r\n     style=\"display:block; width:120px; height:auto; margin-top:10px;\"></p>', 'Yth Ibu/Bapak {{customer_name}}\r\n\r\nTerima kasih atas konfirmasi jadwal layanan Case ID *{{workorder}}* melalui percakapan sebelumnya.\r\nTeknisi kami, {{technician_id}}, dijadwalkan hadir pada:\r\n     •Tanggal : {{appointment_date}}\r\n     •Waktu   : {{appointment_time}}\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\nSalam,\r\n{{pic}}\r\n{{sender_position}}\r\n{{company_name}}', 'null', NULL, '2026-04-08 09:01:53'),
(4, '[1004] Service Reminder - Appointment', 'Pengingat Jadwal Layanan HP Ref. {{workorder}}', '<p>Yth. Bapak/Ibu <strong>{{customer_name}}</strong>,</p>\r\n<p>Kami ingin mengingatkan kembali bahwa layanan HP Bapak/Ibu dijadwalkan pada <strong>{{appointment_date}}</strong> & <strong>{{appointment_time}}</strong> dengan teknisi kami <strong>{{technician_id}}</strong>.</p>\r\n<p>Teknisi akan menghubungi Bapak/Ibu <strong>{{customer_name}}</strong> setibanya di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.</p>\r\n<p>Kami menantikan kesempatan untuk mendukung kebutuhan layanan Bapak/Ibu.</p>\r\n\r\n<p>Hormat kami,<br>\r\n{{pic}}<br>\r\n{{company_name}}<br>\r\n<img src=\"{{company_logo}}\" \r\n     alt=\"Company Logo\" \r\n     width=\"120\" \r\n     style=\"display:block; width:120px; height:auto; margin-top:10px;\"></p>', 'Yth Ibu/Bapak *{{customer_name}}*,\r\n\r\nKami ingin mengingatkan kembali bahwa layanan Bapak/Ibu dijadwalkan pada *{{appointment_date}}* & *{{appointment_time}}* dengan teknisi kami *{{technician_id}}*.\r\nTeknisi akan menghubungi Bapak/Ibu *{{customer_name}}* saat tiba di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.\r\n\r\nTerima kasih,\r\nSalam,\r\n{{pic}}\r\n{{sender_position}}\r\n{{company_name}}', 'null', NULL, '2026-04-08 09:02:14'),
(5, '[1005] Follow Up & Penutupan Kasus', 'Dukungan HP Ref. {{workorder}} – Penutupan Kasus', '<p>Yth. Bapak/Ibu <strong>{{customer_name}}</strong>,</p>\r\n<p>Terima kasih telah memberikan kesempatan kepada <strong>PT. Harmoni Putra Solusindo</strong> untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.</p>\r\n<p>Kami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses <strong>penutupan kasus</strong> ini.</p>\r\n<p>Jika masih terdapat pertanyaan atau kebutuhan tambahan, jangan ragu untuk menghubungi kami.</p>\r\n\r\n<p>Hormat kami,<br>\r\n{{pic}}<br>\r\n{{company_name}}<br>\r\n<img src=\"{{company_logo}}\" \r\n     alt=\"Company Logo\" \r\n     width=\"120\" \r\n     style=\"display:block; width:120px; height:auto; margin-top:10px;\"></p>', 'Yth Ibu/Bapak *{{customer_name}}*,\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami *PT. Harmoni Putra Solusindo* untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses *penutupan kasus* ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\n{{pic}}\r\n{{sender_position}}\r\n{{company_name}}', 'null', NULL, '2026-04-08 07:17:50'),
(6, '[1006] Finish Repair Bench', 'Dukungan HP Ref. {{workorder}} – Selesai Perbaikan', '<p>Yth. Bapak/Ibu <strong>{{customer_name}}</strong>,</p>\r\n<p>Kami dari <strong>HP Service Center Semarang</strong> menginformasikan bahwa unit Anda dengan Case ID <strong>{{workorder}}</strong> sudah selesai service dan sudah bisa diambil.</p>\r\n<p>Mohon info kapan estimasi unit akan diambil?<br>\r\nPengambilan <strong>WAJIB</strong> membawa tanda terima.</p>\r\n<p>Jam operasional:<br>\r\nSenin–Jumat : 08.30–16.30 <br>\r\nSabtu       :08.30–13.30 <br>\r\nKami tunggu konfirmasinya.</p>\r\n<p>Terima kasih,<br>\r\nSalam,<br>\r\n{{pic}}<br>\r\n{{company_name}}<br>\r\n<img src=\"{{company_logo}}\" \r\n     alt=\"Company Logo\" \r\n     width=\"120\" \r\n     style=\"display:block; width:120px; height:auto; margin-top:10px;\"></p>', 'Yth. Bapak/Ibu *{{customer_name}}*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *{{workorder}}* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\n{{pic}}\r\n{{company_name}}', 'null', NULL, '2026-04-08 09:29:50'),
(7, '[1007] Finish Repair ARC', 'Dukungan HP Ref. {{workorder}} – Selesai Perbaikan ARC', '<p>Yth. Bapak/Ibu <strong>{{customer_name}}</strong>,</p>\r\n\r\n<p>Kami dari <strong>HP Service Center Semarang</strong> menginformasikan bahwa part cartridge/toner/printhead <strong>{{supplies}}</strong> Anda dengan Case ID <strong>{{workorder}}</strong> sudah selesai service dan sudah bisa diambil.<br>\r\n<p>Mohon info kapan estimasi unit akan diambil?<br>\r\nUntuk pengambilan <strong>WAJIB</strong> membawa tanda terima service pada saat memasukkan unit.</P\r\n<p>Jam operasional:<br>\r\nSenin–Jumat : 08.30–16.30<br>\r\nSabtu       : 08.30–13.30<br>\r\nKami tunggu konfirmasinya.</p>\r\n<p>Terima kasih.<br>\r\nSalam,<br>\r\n{{pic}}<br>\r\n{{company_name}}<br>\r\n<img src=\"{{company_logo}}\" \r\n     alt=\"Company Logo\" \r\n     width=\"120\" \r\n     style=\"display:block; width:120px; height:auto; margin-top:10px;\"></p>', 'Yth. Bapak/Ibu *{{customer_name}}*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa part cartridge/toner/printhead *{{supplies}}* Anda dengan Case ID *{{workorder}}* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nUntuk pengambilan *WAJIB* membawa tanda terima service pada saat memasukkan unit.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\n{{pic}}\r\n{{company_name}}', 'null', NULL, '2026-04-08 09:30:23');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2026_02_13_025028_create_email_templates_table', 2),
(6, '2026_02_13_034802_create_email_logs_table', 3),
(7, '2026_02_25_033508_add_case_id_to_email_logs_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pics`
--

CREATE TABLE `pics` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pics`
--

INSERT INTO `pics` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'Ayu Astari', '2026-04-07 08:24:45', '2026-04-07 08:24:45'),
(2, 'Ulin Nuha', '2026-04-07 08:24:53', '2026-04-07 08:24:53');

-- --------------------------------------------------------

--
-- Table structure for table `technicians`
--

CREATE TABLE `technicians` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `technicians`
--

INSERT INTO `technicians` (`id`, `name`, `phone`, `created_at`, `updated_at`) VALUES
(1, 'Farhan', '081225417707', '2026-04-07 03:32:51', '2026-04-07 03:32:51'),
(2, 'Bayu', '085803033251', '2026-04-07 03:47:58', '2026-04-07 03:47:58'),
(3, 'Agung', '085928962199', '2026-04-07 03:49:03', '2026-04-07 03:49:03'),
(4, 'Catur', '0882008406580', '2026-04-07 03:49:37', '2026-04-07 03:49:37'),
(5, 'Purwanto', '085640112436', '2026-04-07 03:50:10', '2026-04-07 03:50:10'),
(6, 'Yogi', '081225253369', '2026-04-07 03:50:26', '2026-04-07 03:50:26');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cs'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`) VALUES
(1, 'Admin', 'admin@gmail.com', NULL, '$2y$12$BqFkSk9W81yP4MeB38NPCutx20pkW5OXFSCATlAbjH/8FTJpm5Yom', NULL, '2026-03-14 14:37:18', '2026-04-08 09:14:40', 'admin'),
(2, 'CS1', 'cs1@gmail.com', NULL, '$2y$12$b45W916cxha4VtfdKT.JfujFW9uB7MI302THGrLnTz1dFIKCAkPTS', NULL, '2026-03-17 01:51:11', '2026-04-08 09:07:48', 'cs'),
(3, 'CS2', 'cs2@gmail.com', NULL, '$2y$12$hg4XSmemIx5MMfkYlbgKm.ipKhB.Vqpu.msUnvpoJ5mdnEc/CdXam', NULL, '2026-03-17 01:56:50', '2026-03-17 01:56:50', 'cs'),
(4, 'CS0', 'cs0@gmail.com', NULL, '$2y$12$.Tj0pv2gK3cjxi1stWvBmeXxHcAjaSyJRldh9H2SLyn3goEXvdIKO', NULL, '2026-03-17 01:58:41', '2026-03-17 01:58:41', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `wa_logs`
--

CREATE TABLE `wa_logs` (
  `id` bigint NOT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `pic` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `case_id` varchar(100) DEFAULT NULL,
  `message` text,
  `status` varchar(50) DEFAULT 'sent',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `wa_logs`
--

INSERT INTO `wa_logs` (`id`, `customer_name`, `pic`, `customer_phone`, `case_id`, `message`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Arief Budiman', NULL, '08156567560', '5100082109', 'Yth Ibu/Bapak Arief Budiman\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Yogi, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-18\r\n     •Waktu   : 11:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-17 07:51:09', '2026-03-17 07:51:09'),
(2, 'Tony', NULL, '082220764372', '5100082080', 'Yth Ibu/Bapak Tony\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Yogi, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-18\r\n     •Waktu   : 14:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-17 07:55:11', '2026-03-17 07:55:11'),
(3, 'Ilham Fajrul Haq', NULL, '082328939466', '5100082101', 'Yth Ibu/Bapak Ilham Fajrul Haq\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Agung, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-25\r\n     •Waktu   : 09:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-17 08:31:00', '2026-03-17 08:31:00'),
(4, 'Bagus Iman Judiesanto', NULL, '081313397677', '5100082102', 'Yth Ibu/Bapak Bagus Iman Judiesanto\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Bayu, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-26\r\n     •Waktu   : 11:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-18 03:25:17', '2026-03-18 03:25:17'),
(5, 'Bagus Iman Judiesanto', NULL, '081313397677', '5100082102', 'Yth Ibu/Bapak Bagus Iman Judiesanto\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Bayu, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-26\r\n     •Waktu   : 11:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-18 03:25:25', '2026-03-18 03:25:25'),
(6, 'Bagus Iman Judiesanto', NULL, '081313397677', '5100082102', 'Yth Ibu/Bapak Bagus Iman Judiesanto\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Bayu, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-26\r\n     •Waktu   : 11:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-18 03:25:31', '2026-03-18 03:25:31'),
(7, 'Dionisius Robert Mulyono', 'Ayu Astari', '081228505824', '5100082102', 'Yth Ibu/Bapak Dionisius Robert Mulyono\r\n\r\nKami ingin mengingatkan kembali bahwa layanan Bapak/Ibu dijadwalkan pada 2026-03-26 & 11:00 dengan teknisi kami Bayu.\r\nTeknisi akan menghubungi Dionisius Robert Mulyono saat tiba di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.\r\n\r\nTerima kasih,\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-25 09:15:34', '2026-03-25 09:15:34'),
(8, 'Agus Khoirul Huda', 'Ayu Astari', '082313657641', '5100082132', 'Yth Ibu/Bapak Agus Khoirul Huda\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Farhan, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-27\r\n     •Waktu   : 10:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-27 01:58:30', '2026-03-27 01:58:30'),
(9, 'Tony', 'Ulin Nuha', '082220764372', '5100082080', 'Yth Ibu/Bapak Tony\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami PT. Harmoni Putra Solusindo untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses penutupan kasus ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-27 02:54:45', '2026-03-27 02:54:45'),
(10, 'Arief Budiman', 'Ulin Nuha', '08156567560', '5100082109', 'Yth Ibu/Bapak Arief Budiman\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami PT. Harmoni Putra Solusindo untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses penutupan kasus ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-27 03:12:33', '2026-03-27 03:12:33'),
(11, 'Ilham Fajrul Haq', 'Ulin Nuha', '082328939466', '5100082101', 'Yth Ibu/Bapak Ilham Fajrul Haq\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami PT. Harmoni Putra Solusindo untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses penutupan kasus ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-27 03:18:16', '2026-03-27 03:18:16'),
(12, 'Dona', 'Ulin Nuha', '081294671483', '5100082073', 'Yth Ibu/Bapak Dona\r\n\r\nKami ingin mengingatkan kembali bahwa layanan Bapak/Ibu dijadwalkan pada 2026-03-30 & 11:00 dengan teknisi kami Catur.\r\nTeknisi akan menghubungi Bapak/Ibu Dona saat tiba di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-28 04:45:45', '2026-03-28 04:45:45'),
(13, 'Aditya Chandra Eko Yunianto', 'Ayu Astari', '085740033189', '5100082127', 'Yth Ibu/Bapak Aditya Chandra Eko Yunianto\r\n\r\nKami ingin mengingatkan kembali bahwa layanan Bapak/Ibu dijadwalkan pada 2026-03-30 & 09:00 dengan teknisi kami Yogi.\r\nTeknisi akan menghubungi Bapak/Ibu Aditya Chandra Eko Yunianto saat tiba di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.\r\n\r\nTerima kasih,\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-30 01:36:51', '2026-03-30 01:36:51'),
(14, 'Andre', 'Ayu Astari', '08971093355', '5100082084', 'Yth Ibu/Bapak Andre\r\n\r\nKami ingin mengingatkan kembali bahwa layanan Bapak/Ibu dijadwalkan pada 2026-03-30 & 14:00 dengan teknisi kami Yogi.\r\nTeknisi akan menghubungi Bapak/Ibu Andre saat tiba di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.\r\n\r\nTerima kasih,\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-30 01:38:05', '2026-03-30 01:38:05'),
(15, 'Adi Prasetyo', 'Ulin Nuha', '08122523243', '5100082128', 'Yth Ibu/Bapak Adi Prasetyo\r\n\r\nTerima kasih atas konfirmasi jadwal layanan melalui percakapan sebelumnya.\r\nTeknisi kami, Agung, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-03-31\r\n     •Waktu   : 10:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-30 01:50:27', '2026-03-30 01:50:27'),
(16, 'Supriyono', 'Ulin Nuha', '085225556777', '5100082163', 'Yth. Bapak/Ibu *Supriyono*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082163* sudah selesai service dan sudah bisa diambil.\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\nTerima kasih.\r\n\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-31 02:10:41', '2026-03-31 02:10:41'),
(17, 'Candra Ananda Pratama', 'Ulin Nuha', '085175310093', '5100082158', 'Yth. Bapak/Ibu *Candra Ananda Pratama*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082158* sudah selesai service dan sudah bisa diambil.\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\nTerima kasih.\r\n\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-31 02:12:27', '2026-03-31 02:12:27'),
(18, 'Rauf', 'Ulin Nuha', '085103378378', '5100082145', 'Yth. Bapak/Ibu *Rauf*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082145* sudah selesai service dan sudah bisa diambil.\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\nTerima kasih.\r\n\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-31 02:14:15', '2026-03-31 02:14:15'),
(19, 'Andre Setiawan', 'Ulin Nuha', '0882006166006', '5100082047', 'Yth. Bapak/Ibu *Andre Setiawan*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082047* sudah selesai service dan sudah bisa diambil.\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\nTerima kasih.\r\n\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-31 02:16:19', '2026-03-31 02:16:19'),
(20, 'Aditya Chandra Eko Yunianto', 'Ulin Nuha', '6285740033189', '5100082127', 'Yth Ibu/Bapak Aditya Chandra Eko Yunianto\r\n\r\nTerima kasih atas konfirmasi jadwal layanan Case ID *5100082127* melalui percakapan sebelumnya.\r\nTeknisi kami, Yogi, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-04-01\r\n     •Waktu   : 10:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-03-31 06:37:25', '2026-03-31 06:37:25'),
(21, 'Aditya Chandra Eko Yunianto', 'Ulin Nuha', '6285740033189', '5100082127', 'Yth Ibu/Bapak Aditya Chandra Eko Yunianto\r\n\r\nKami ingin mengingatkan kembali bahwa layanan Bapak/Ibu dijadwalkan pada 2026-04-01 & 10:00 dengan teknisi kami Yogi.\r\nTeknisi akan menghubungi Bapak/Ibu Aditya Chandra Eko Yunianto saat tiba di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-01 01:15:45', '2026-04-01 01:15:45'),
(22, 'Andre', 'Ulin Nuha', '628971093355', '5100082084', 'Yth Ibu/Bapak Andre\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami PT. Harmoni Putra Solusindo untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses penutupan kasus ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-01 02:15:57', '2026-04-01 02:15:57'),
(23, 'Angel', 'Ulin Nuha', '628988526743', '5100082173', 'Yth. Bapak/Ibu *Angel*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082173* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\nTerima kasih.\r\n\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-01 02:31:23', '2026-04-01 02:31:23'),
(24, 'Ilham Fajrul Haq', 'Ayu Astari', '6282328939466', '5100082164', 'Yth Ibu/Bapak Ilham Fajrul Haq\r\n\r\nTerima kasih atas konfirmasi jadwal layanan Case ID *5100082164* melalui percakapan sebelumnya.\r\nTeknisi kami, Agung, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-04-02\r\n     •Waktu   : 10:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-01 03:38:59', '2026-04-01 03:38:59'),
(25, 'Destu Darma', 'Ulin Nuha', '6285191294906', '5100082180', 'Yth. Bapak/Ibu *Destu Darma*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082180* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\nTerima kasih.\r\n\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-01 07:45:53', '2026-04-01 07:45:53'),
(26, 'Delbert', 'Ayu Astari', '6281272638183', '5100082181', 'Yth Ibu/Bapak Delbert\r\n\r\nTerima kasih atas konfirmasi jadwal layanan Case ID *5100082181* melalui percakapan sebelumnya.\r\nTeknisi kami, Catur, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-04-02\r\n     •Waktu   : 13:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\n\r\nSalam,\r\nAyu Astari\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-01 08:46:59', '2026-04-01 08:46:59'),
(27, 'Agus Khoirul Huda', 'Ulin Nuha', '6282313657641', '5100082132', 'Yth Ibu/Bapak Agus Khoirul Huda\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami PT. Harmoni Putra Solusindo untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses penutupan kasus ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-02 02:57:35', '2026-04-02 02:57:35'),
(28, 'Bagus Iman Judiesanto', 'Ulin Nuha', '6281313397677', '5100082102', 'Yth Ibu/Bapak *Bagus Iman Judiesanto*,\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami *PT. Harmoni Putra Solusindo* untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses *penutupan kasus* ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-02 03:06:26', '2026-04-02 03:06:26'),
(29, 'Adi Prasetyo', 'Ulin Nuha', '628122523243', '5100082128', 'Yth Ibu/Bapak *Adi Prasetyo*,\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami *PT. Harmoni Putra Solusindo* untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses *penutupan kasus* ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-02 03:09:39', '2026-04-02 03:09:39'),
(30, 'Warih Suprihanto', 'Ayu Astari', '6281315045599', '5100082154', 'Yth. Bapak/Ibu *Warih Suprihanto*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082154* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nAyu Astari\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-02 07:27:22', '2026-04-02 07:27:22'),
(31, 'Lili', 'Ulin Nuha', '6288232175910', '5100082224', 'Yth. Bapak/Ibu *Lili*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082224* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-04 02:40:37', '2026-04-04 02:40:37'),
(32, 'Ratih', 'Ulin Nuha', '6285725983088', '5100082225', 'Yth. Bapak/Ibu *Ratih*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082225* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 01:42:26', '2026-04-07 01:42:26'),
(33, 'Yuni', 'Ulin Nuha', '6281327572800', '5100082207', 'Yth. Bapak/Ibu *Yuni*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082207* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 01:44:04', '2026-04-07 01:44:04'),
(34, 'Solikhin', 'Ulin Nuha', '6281326777906', '5100082203', 'Yth. Bapak/Ibu *Solikhin*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082203* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 01:45:41', '2026-04-07 01:45:41'),
(35, 'Lilis', 'Ulin Nuha', '6287824291512', '5100082183', 'Yth. Bapak/Ibu *Lilis*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082183* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 01:46:50', '2026-04-07 01:46:50'),
(36, 'Dwi', 'Ulin Nuha', '6285640666251', '5100082182', 'Yth. Bapak/Ibu *Dwi*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082182* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 02:12:25', '2026-04-07 02:12:25'),
(37, 'Ahlun', 'Ulin Nuha', '6289635292437', '5100082179', 'Yth. Bapak/Ibu *Ahlun*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082179* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 02:14:14', '2026-04-07 02:14:14'),
(38, 'Ahlun', 'Ulin Nuha', '6289635292437', '5100082178', 'Yth. Bapak/Ibu *Ahlun*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082178* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 02:15:30', '2026-04-07 02:15:30'),
(39, 'Beni', 'Ulin Nuha', '6281296516253', '5100082176', 'Yth. Bapak/Ibu *Beni*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082176* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 02:17:35', '2026-04-07 02:17:35'),
(40, 'Hendri', 'Ulin Nuha', '6285200080093', '5100082144', 'Yth. Bapak/Ibu *Hendri*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082144* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 02:18:39', '2026-04-07 02:18:39'),
(41, 'Fathur Rohman', 'Ulin Nuha', '6281388226630', '5100082103', 'Yth. Bapak/Ibu *Fathur Rohman*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082103* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 02:39:08', '2026-04-07 02:39:08'),
(42, 'Alifin Prima Ranidya', 'Ulin Nuha', '6283115151155', '5100082162', 'Yth. Bapak/Ibu *Alifin Prima Ranidya*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082162* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 03:15:36', '2026-04-07 03:15:36'),
(43, 'Taufik', 'Ulin Nuha', '628983275959', '5100082247', 'Yth Ibu/Bapak Taufik\r\n\r\nTerima kasih atas konfirmasi jadwal layanan Case ID *5100082247* melalui percakapan sebelumnya.\r\nTeknisi kami, Yogi, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-04-08\r\n     •Waktu   : 09:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 06:06:54', '2026-04-07 06:06:54'),
(44, 'Aditya Chandra Eko Yunianto', 'Ulin Nuha', '6285740033189', '5100082127', 'Yth Ibu/Bapak *Aditya Chandra Eko Yunianto*,\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami *PT. Harmoni Putra Solusindo* untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses *penutupan kasus* ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 06:12:43', '2026-04-07 06:12:43'),
(45, 'Ilham Fajrul Haq', 'Ulin Nuha', '6282328939466', '5100082164', 'Yth Ibu/Bapak *Ilham Fajrul Haq*,\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami *PT. Harmoni Putra Solusindo* untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses *penutupan kasus* ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 06:15:07', '2026-04-07 06:15:07'),
(46, 'Delbert', 'Ulin Nuha', '6281272638183', '5100082181', 'Yth Ibu/Bapak *Delbert*,\r\n\r\nTerima kasih telah memberikan kesempatan kepada kami *PT. Harmoni Putra Solusindo* untuk mendukung kebutuhan layanan Bapak/Ibu atas nama HP.\r\nKami berharap layanan yang telah diberikan memenuhi harapan Bapak/Ibu. Apabila tidak terdapat kendala lanjutan, kami akan melanjutkan proses *penutupan kasus* ini.\r\nJika masih terdapat pertanyaan, dapat menghubungi kami kembali ke nomor ini.\r\nKami siap membantu dengan senang hati.\r\n\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-07 06:23:48', '2026-04-07 06:23:48'),
(47, 'Taufik', 'Ulin Nuha', '628983275959', '5100082247', 'Yth Ibu/Bapak *Taufik*,\r\n\r\nKami ingin mengingatkan kembali bahwa layanan Bapak/Ibu dijadwalkan pada *2026-04-08* & *09:00* dengan teknisi kami *Yogi*.\r\nTeknisi akan menghubungi Bapak/Ibu *Taufik* saat tiba di lokasi. Mohon memastikan kesiapan akses sesuai dengan informasi sebelumnya.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-08 01:38:43', '2026-04-08 01:38:43'),
(48, 'Mohammad Ibnu Khamidun', 'Ayu Astari', '6287781097862', '5100082198', 'Yth. Bapak/Ibu *Mohammad Ibnu Khamidun*,\r\n\r\nKami dari *HP Service Center Semarang* menginformasikan bahwa unit Anda dengan Case ID *5100082198* sudah selesai service dan sudah bisa diambil.\r\n\r\nMohon info kapan estimasi unit akan diambil?\r\nPengambilan *WAJIB* membawa tanda terima.\r\n\r\nJam operasional:\r\nSenin–Jumat 08.30–16.30\r\nSabtu 08.30–13.30\r\nKami tunggu konfirmasinya.\r\n\r\nTerima kasih,\r\nSalam,\r\nAyu Astari\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-08 01:40:02', '2026-04-08 01:40:02'),
(49, 'Pradita Iswara', 'Ulin Nuha', '6285229738307', '5100082227', 'Yth Ibu/Bapak Pradita Iswara\r\n\r\nTerima kasih atas konfirmasi jadwal layanan Case ID *5100082227* melalui percakapan sebelumnya.\r\nTeknisi kami, Agung, dijadwalkan hadir pada:\r\n     •Tanggal : 2026-04-09\r\n     •Waktu   : 08:00\r\nMohon diinformasikan Nama PIC yang berada di lokasi, share location, serta akses yang diperlukan agar proses layanan ini dapat berjalan lancar. \r\nApabila terdapat perubahan atau pertanyaan, silakan menghubungi saya kembali di nomor ini.\r\n\r\nTerima kasih,\r\nSalam,\r\nUlin Nuha\r\nCustomer Service\r\nPT. Harmoni Putra Solusindo\r\n', 'sent', '2026-04-08 02:40:04', '2026-04-08 02:40:04');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

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
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pics`
--
ALTER TABLE `pics`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `technicians`
--
ALTER TABLE `technicians`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `wa_logs`
--
ALTER TABLE `wa_logs`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pics`
--
ALTER TABLE `pics`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `technicians`
--
ALTER TABLE `technicians`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `wa_logs`
--
ALTER TABLE `wa_logs`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
