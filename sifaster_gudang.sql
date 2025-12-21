-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 21, 2025 at 04:52 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sifaster_gudang`
--

-- --------------------------------------------------------

--
-- Table structure for table `barang`
--

CREATE TABLE `barang` (
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `stok` int DEFAULT '0',
  `lokasi_rak` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `barang`
--

INSERT INTO `barang` (`kode_barang`, `nama_barang`, `kategori`, `satuan`, `stok`, `lokasi_rak`) VALUES
('B001', 'Kertas HVS A4', 'Kertas', 'Rim', 62, 'Rak A-1'),
('B002', 'Tinta Cair Hitam', 'Tinta', 'Liter', 66, 'Rak B-2'),
('B003', 'Kayu Pensil', 'Bahan Baku', 'Kg', 109, 'Gudang C'),
('B004', 'Pensil 2B (Jadi)', 'Barang Jadi', 'Pcs', 60, 'Gudang A');

-- --------------------------------------------------------

--
-- Table structure for table `cms_categories`
--

CREATE TABLE `cms_categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cms_categories`
--

INSERT INTO `cms_categories` (`id`, `name`, `slug`) VALUES
(1, 'Berita Gudang', 'berita'),
(2, 'Pengumuman', 'pengumuman'),
(3, 'Tips Manajemen Stok', 'tips'),
(4, 'Tutorial', 'tutorial');

-- --------------------------------------------------------

--
-- Table structure for table `cms_menus`
--

CREATE TABLE `cms_menus` (
  `id` int NOT NULL,
  `label` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `role_access` varchar(255) NOT NULL COMMENT 'Comma separated roles',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cms_menus`
--

INSERT INTO `cms_menus` (`id`, `label`, `url`, `role_access`, `sort_order`, `is_active`) VALUES
(1, 'Dashboard', 'index.php', 'Admin,Purchasing,Produksi,Sales', 1, 1),
(2, 'Master Data & Stok', 'adminBarang.php', 'Admin', 2, 1),
(3, 'Kelola User', 'adminUsers.php', 'Admin', 3, 1),
(4, 'Kelola Web', 'adminWeb.php', 'Admin', 4, 1),
(5, 'Transaksi Masuk (PO)', 'adminTransaksiMasuk.php', 'Admin,Purchasing,Produksi', 5, 1),
(6, 'Transaksi Keluar (SPK/SO)', 'adminTransaksiKeluar.php', 'Admin,Produksi,Sales', 6, 1),
(7, 'Retur Barang', 'adminRetur.php', 'Admin,Sales', 7, 1),
(8, 'Laporan & Monitoring', 'laporan.php', 'Admin', 8, 1),
(9, 'Logout', 'logout.php', 'Admin,Purchasing,Produksi,Sales', 9, 1);

-- --------------------------------------------------------

--
-- Table structure for table `cms_posts`
--

CREATE TABLE `cms_posts` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `author_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cms_posts`
--

INSERT INTO `cms_posts` (`id`, `category_id`, `title`, `content`, `created_at`, `author_id`) VALUES
(1, 1, 'Peningkatan Efisiensi Stok Opname', '<p>Bulan ini tim gudang berhasil meningkatkan efisiensi stok opname sebesar 15% berkat penggunaan sistem barcode baru.</p><ul><li>Panji Goblok</li></ul><ol><li>Fizar Gobvlok</li></ol>', '2025-12-21 21:22:55', 1),
(2, 2, 'Libur Operasional Akhir Tahun', 'Sehubungan dengan cuti bersama akhir tahun, operasional gudang akan diliburkan pada tanggal 30-31 Desember 2025.', '2025-12-21 21:22:55', 1),
(3, 3, 'Metode FIFO (First In First Out)', 'Pastikan barang yang pertama kali masuk adalah yang pertama kali keluar.', '2025-12-21 21:22:55', 1),
(4, 4, 'Cara Input Barang Masuk', 'Login sebagai Purchasing atau Admin. Buka menu Transaksi Masuk (PO).', '2025-12-21 21:22:55', 1);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL,
  `no_transaksi` varchar(20) DEFAULT NULL,
  `kode_barang` varchar(20) DEFAULT NULL,
  `qty` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `no_transaksi`, `kode_barang`, `qty`) VALUES
(1, 'TK-20251218161754', 'B003', 11),
(3, 'TR-20251221122924', 'B004', 20),
(4, 'TM-20251221123821', 'B002', 50),
(5, 'TK-20251221133041', 'B002', 20),
(6, 'TR-20251221160240', 'B003', 20),
(7, 'TR-20251221160804', 'B002', 11),
(8, 'TR-20251221161251', 'B004', 20),
(9, 'TR-20251221161518', 'B004', 20),
(10, 'TR-20251221161839', 'B001', 1),
(11, 'TR-20251221161840', 'B001', 1),
(12, 'TR-20251221163827', 'B001', 12);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `no_transaksi` varchar(20) NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipe` enum('Masuk','Keluar','Retur') NOT NULL,
  `no_referensi` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `request_by` int DEFAULT NULL,
  `approved_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`no_transaksi`, `tanggal`, `tipe`, `no_referensi`, `status`, `request_by`, `approved_by`) VALUES
('TK-20251218161754', '2025-12-18 16:17:00', 'Keluar', 'SO-2025-001', 'Approved', 4, 1),
('TK-20251221133041', '2025-12-21 13:30:00', 'Keluar', 'SPK-2025-B002', 'Rejected', 2, 1),
('TM-20251221123821', '2025-12-21 12:37:00', 'Masuk', 'PO-2025-002', 'Approved', 1, 1),
('TR-20251221122924', '2025-12-21 12:29:00', 'Retur', 'Rusak Bjir', 'Approved', 1, 1),
('TR-20251221160240', '2025-12-21 16:02:00', 'Retur', 'Ada dildo', 'Approved', 4, 1),
('TR-20251221160804', '2025-12-21 16:07:00', 'Retur', 'Rusak Bjir', 'Approved', 4, 1),
('TR-20251221161251', '2025-12-21 16:12:00', 'Retur', 'Ada dildo', 'Approved', 4, 1),
('TR-20251221161518', '2025-12-21 16:15:00', 'Retur', 'Kebanyakan', 'Approved', 4, 1),
('TR-20251221161839', '2025-12-21 16:18:00', 'Retur', 'Kebanyakan', 'Rejected', 4, 1),
('TR-20251221161840', '2025-12-21 16:18:00', 'Retur', 'Kebanyakan', 'Rejected', 4, 1),
('TR-20251221163827', '2025-12-21 16:38:00', 'Retur', 'Kebanyakan', 'Approved', 4, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Produksi','Purchasing','Sales') NOT NULL,
  `status` enum('Aktif','Suspend') DEFAULT 'Aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `status`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', 'Admin', 'Aktif'),
(2, 'produksi', '202cb962ac59075b964b07152d234b70', 'Produksi', 'Aktif'),
(3, 'purchasing', '202cb962ac59075b964b07152d234b70', 'Purchasing', 'Aktif'),
(4, 'sales', '202cb962ac59075b964b07152d234b70', 'Sales', 'Aktif');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `barang`
--
ALTER TABLE `barang`
  ADD PRIMARY KEY (`kode_barang`);

--
-- Indexes for table `cms_categories`
--
ALTER TABLE `cms_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `cms_menus`
--
ALTER TABLE `cms_menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cms_posts`
--
ALTER TABLE `cms_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `no_transaksi` (`no_transaksi`),
  ADD KEY `kode_barang` (`kode_barang`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`no_transaksi`),
  ADD KEY `request_by` (`request_by`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cms_categories`
--
ALTER TABLE `cms_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cms_menus`
--
ALTER TABLE `cms_menus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `cms_posts`
--
ALTER TABLE `cms_posts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cms_posts`
--
ALTER TABLE `cms_posts`
  ADD CONSTRAINT `cms_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `cms_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`no_transaksi`) REFERENCES `transaksi` (`no_transaksi`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`kode_barang`) REFERENCES `barang` (`kode_barang`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`request_by`) REFERENCES `users` (`id_user`),
  ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id_user`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
