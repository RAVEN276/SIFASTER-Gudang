-- ==========================================
-- 0. PERSIAPAN (MATIKAN FK CHECK)
-- ==========================================
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Buat Database
CREATE DATABASE IF NOT EXISTS sifaster_gudang;
USE sifaster_gudang;

-- --------------------------------------------------------
-- 2. STRUKTUR TABEL (DARI sifaster_gudang.sql - TANPA ENGINE)
-- --------------------------------------------------------

-- Table structure for table `barang`
CREATE TABLE `barang` (
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `stok` int DEFAULT '0',
  `lokasi_rak` varchar(50) DEFAULT NULL
);

-- Table structure for table `cms_categories`
CREATE TABLE `cms_categories` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL
);

-- Table structure for table `cms_menus`
CREATE TABLE `cms_menus` (
  `id` int NOT NULL,
  `label` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `role_access` varchar(255) NOT NULL COMMENT 'Comma separated roles',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1'
);

-- Table structure for table `cms_posts`
CREATE TABLE `cms_posts` (
  `id` int NOT NULL,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `author_id` int DEFAULT NULL
);

-- Table structure for table `detail_transaksi`
CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL,
  `no_transaksi` varchar(20) DEFAULT NULL,
  `kode_barang` varchar(20) DEFAULT NULL,
  `qty` int DEFAULT NULL
);

-- Table structure for table `transaksi`
CREATE TABLE `transaksi` (
  `no_transaksi` varchar(20) NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipe` enum('Masuk','Keluar','Retur') NOT NULL,
  `no_referensi` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `request_by` int DEFAULT NULL,
  `approved_by` int DEFAULT NULL
);

-- Table structure for table `users`
CREATE TABLE `users` (
  `id_user` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Produksi','Purchasing','Sales') NOT NULL,
  `status` enum('Aktif','Suspend') DEFAULT 'Aktif'
);

-- --------------------------------------------------------
-- 3. DATA DUMMY (DARI database.sql)
-- --------------------------------------------------------

-- Dumping data for table `users`
INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `status`) VALUES
(1, 'admin', MD5('123'), 'Admin', 'Aktif'),
(2, 'produksi', MD5('123'), 'Produksi', 'Aktif'),
(3, 'purchasing', MD5('123'), 'Purchasing', 'Aktif'),
(4, 'sales', MD5('123'), 'Sales', 'Aktif');

-- Dumping data for table `barang`
INSERT INTO `barang` (`kode_barang`, `nama_barang`, `kategori`, `satuan`, `stok`, `lokasi_rak`) VALUES 
('B001', 'Kertas HVS A4', 'Kertas', 'Rim', 50, 'Rak A-1'),
('B002', 'Tinta Cair Hitam', 'Tinta', 'Liter', 5, 'Rak B-2'),
('B003', 'Kayu Pensil', 'Bahan Baku', 'Kg', 100, 'Gudang C'),
('B004', 'Pensil 2B (Jadi)', 'Barang Jadi', 'Pcs', 0, 'Gudang A');

-- Dumping data for table `cms_categories`
INSERT INTO `cms_categories` (`id`, `name`, `slug`) VALUES
(1, 'Berita', 'berita'),
(2, 'Pengumuman', 'pengumuman'),
(3, 'Artikel', 'artikel');

-- Dumping data for table `cms_menus`
-- Menyesuaikan data dummy dengan struktur baru (label, url, role_access, sort_order, is_active)
INSERT INTO `cms_menus` (`label`, `url`, `role_access`, `sort_order`, `is_active`) VALUES
('Dashboard', 'index.php', 'All', 1, 1),
('Master Data', '#', 'Admin,Produksi', 2, 1),
('Barang', 'adminBarang.php', 'Admin,Produksi', 3, 1),
('Transaksi', '#', 'All', 4, 1),
('Barang Masuk', 'adminTransaksiMasuk.php', 'Admin,Purchasing,Produksi', 5, 1),
('Barang Keluar', 'adminTransaksiKeluar.php', 'Admin,Sales,Produksi', 6, 1),
('Retur Barang', 'adminRetur.php', 'Admin,Sales', 7, 1),
('Laporan', 'laporan.php', 'Admin,Manager', 8, 1),
('Kelola Web', 'adminWeb.php', 'Admin', 9, 1);

-- Dumping data for table `cms_posts`
INSERT INTO `cms_posts` (`title`, `content`, `category_id`, `author_id`) VALUES
('Selamat Datang di SIFASTER', '<p>Selamat datang di sistem manajemen gudang SIFASTER.</p>', 2, 1),
('Update Stok Akhir Tahun', '<p>Harap melakukan opname stok sebelum tanggal 31 Desember.</p>', 2, 1),
('Cara Menggunakan Scanner', '<p>Berikut adalah panduan menggunakan scanner barcode...</p>', 3, 1);

-- --------------------------------------------------------
-- 4. INDEXES & CONSTRAINTS (DARI sifaster_gudang.sql)
-- --------------------------------------------------------

-- Indexes for table `barang`
ALTER TABLE `barang` ADD PRIMARY KEY (`kode_barang`);

-- Indexes for table `cms_categories`
ALTER TABLE `cms_categories` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `slug` (`slug`);

-- Indexes for table `cms_menus`
ALTER TABLE `cms_menus` ADD PRIMARY KEY (`id`);

-- Indexes for table `cms_posts`
ALTER TABLE `cms_posts` ADD PRIMARY KEY (`id`), ADD KEY `category_id` (`category_id`);

-- Indexes for table `detail_transaksi`
ALTER TABLE `detail_transaksi` ADD PRIMARY KEY (`id_detail`), ADD KEY `no_transaksi` (`no_transaksi`), ADD KEY `kode_barang` (`kode_barang`);

-- Indexes for table `transaksi`
ALTER TABLE `transaksi` ADD PRIMARY KEY (`no_transaksi`), ADD KEY `request_by` (`request_by`), ADD KEY `approved_by` (`approved_by`);

-- Indexes for table `users`
ALTER TABLE `users` ADD PRIMARY KEY (`id_user`), ADD UNIQUE KEY `username` (`username`);

-- AUTO_INCREMENT for table `cms_categories`
ALTER TABLE `cms_categories` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- AUTO_INCREMENT for table `cms_menus`
ALTER TABLE `cms_menus` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

-- AUTO_INCREMENT for table `cms_posts`
ALTER TABLE `cms_posts` MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

-- AUTO_INCREMENT for table `detail_transaksi`
ALTER TABLE `detail_transaksi` MODIFY `id_detail` int NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `users`
ALTER TABLE `users` MODIFY `id_user` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

-- Constraints for table `cms_posts`
ALTER TABLE `cms_posts` ADD CONSTRAINT `cms_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `cms_categories` (`id`) ON DELETE SET NULL;

-- Constraints for table `detail_transaksi`
ALTER TABLE `detail_transaksi` ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`no_transaksi`) REFERENCES `transaksi` (`no_transaksi`) ON DELETE CASCADE, ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`kode_barang`) REFERENCES `barang` (`kode_barang`);

-- Constraints for table `transaksi`
ALTER TABLE `transaksi` ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`request_by`) REFERENCES `users` (`id_user`), ADD CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id_user`);

SET FOREIGN_KEY_CHECKS = 1;