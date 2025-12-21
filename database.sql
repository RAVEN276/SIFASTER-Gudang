-- ==========================================
-- 0. PERSIAPAN
-- ==========================================
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- Buat Database
CREATE DATABASE IF NOT EXISTS sifaster_gudang;
USE sifaster_gudang;

-- ==========================================
-- 1. TABEL: users
-- ==========================================
CREATE TABLE `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Produksi','Purchasing','Sales') NOT NULL,
  `status` enum('Aktif','Suspend') DEFAULT 'Aktif',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `users` (`username`, `password`, `role`, `status`) VALUES 
('admin', MD5('123'), 'Admin', 'Aktif'),
('produksi', MD5('123'), 'Produksi', 'Aktif'),
('purchasing', MD5('123'), 'Purchasing', 'Aktif'),
('sales', MD5('123'), 'Sales', 'Aktif');

-- ==========================================
-- 2. TABEL: barang
-- ==========================================
CREATE TABLE `barang` (
  `kode_barang` varchar(20) NOT NULL,
  `nama_barang` varchar(100) NOT NULL,
  `kategori` varchar(50) DEFAULT NULL,
  `satuan` varchar(20) DEFAULT NULL,
  `stok` int DEFAULT '0',
  `lokasi_rak` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `barang` (`kode_barang`, `nama_barang`, `kategori`, `satuan`, `stok`, `lokasi_rak`) VALUES 
('B001', 'Kertas HVS A4', 'Kertas', 'Rim', 50, 'Rak A-1'),
('B002', 'Tinta Cair Hitam', 'Tinta', 'Liter', 5, 'Rak B-2'),
('B003', 'Kayu Pensil', 'Bahan Baku', 'Kg', 100, 'Gudang C'),
('B004', 'Pensil 2B (Jadi)', 'Barang Jadi', 'Pcs', 0, 'Gudang A');

-- ==========================================
-- 3. TABEL: transaksi
-- ==========================================
CREATE TABLE `transaksi` (
  `no_transaksi` varchar(20) NOT NULL,
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  `tipe` enum('Masuk','Keluar','Retur') NOT NULL,
  `no_referensi` varchar(50) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `request_by` int DEFAULT NULL,
  `approved_by` int DEFAULT NULL,
  PRIMARY KEY (`no_transaksi`),
  KEY `request_by` (`request_by`),
  KEY `approved_by` (`approved_by`),
  CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`request_by`) REFERENCES `users` (`id_user`),
  CONSTRAINT `transaksi_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ==========================================
-- 4. TABEL: detail_transaksi
-- ==========================================
CREATE TABLE `detail_transaksi` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `no_transaksi` varchar(20) DEFAULT NULL,
  `kode_barang` varchar(20) DEFAULT NULL,
  `qty` int DEFAULT NULL,
  PRIMARY KEY (`id_detail`),
  KEY `no_transaksi` (`no_transaksi`),
  KEY `kode_barang` (`kode_barang`),
  CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`no_transaksi`) REFERENCES `transaksi` (`no_transaksi`) ON DELETE CASCADE,
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`kode_barang`) REFERENCES `barang` (`kode_barang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ==========================================
-- 5. TABEL: cms_categories
-- ==========================================
CREATE TABLE `cms_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `cms_categories` (`name`, `slug`) VALUES
('Berita', 'berita'),
('Pengumuman', 'pengumuman'),
('Artikel', 'artikel');

-- ==========================================
-- 6. TABEL: cms_menus
-- ==========================================
CREATE TABLE `cms_menus` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  `url` varchar(255) NOT NULL,
  `role_access` varchar(255) NOT NULL COMMENT 'Comma separated roles',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `cms_menus` (`label`, `url`, `sort_order`, `role_access`, `is_active`) VALUES
('Dashboard', 'index.php', 1, 'Admin,Purchasing,Produksi,Sales', 1),
('Master Data', 'adminBarang.php', 2, 'Admin,Produksi', 1),
('Barang Masuk', 'adminTransaksiMasuk.php', 3, 'Admin,Purchasing,Produksi', 1),
('Barang Keluar', 'adminTransaksiKeluar.php', 4, 'Admin,Sales,Produksi', 1),
('Retur Barang', 'adminRetur.php', 5, 'Admin,Sales', 1),
('Laporan', 'laporan.php', 6, 'Admin', 1),
('Kelola Web', 'adminWeb.php', 7, 'Admin', 1);

-- ==========================================
-- 7. TABEL: cms_posts
-- ==========================================
CREATE TABLE `cms_posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category_id` int DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `author_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `cms_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `cms_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

INSERT INTO `cms_posts` (`title`, `content`, `category_id`, `author_id`) VALUES
('Selamat Datang di SIFASTER', '<p>Selamat datang di sistem manajemen gudang SIFASTER.</p>', 2, 1),
('Update Stok Akhir Tahun', '<p>Harap melakukan opname stok sebelum tanggal 31 Desember.</p>', 2, 1);

-- ==========================================
-- 8. FINISHING
-- ==========================================
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;