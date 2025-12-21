-- ==========================================
-- 0. PERSIAPAN (MATIKAN FK CHECK)
-- ==========================================
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Buat Database
CREATE DATABASE IF NOT EXISTS sifaster_gudang;
USE sifaster_gudang;

-- 1. Buat Tabel dulu (ditutup dengan kurung dan titik koma)
CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('Admin', 'Produksi', 'Purchasing', 'Sales') NOT NULL,
    status ENUM('Aktif', 'Suspend') DEFAULT 'Aktif'
); 

-- 2. Baru jalankan Insert (terpisah)
INSERT INTO users (username, password, role, status) VALUES 
('admin', MD5('123'), 'Admin', 'Aktif'),
('produksi', MD5('123'), 'Produksi', 'Aktif'),
('purchasing', MD5('123'), 'Purchasing', 'Aktif'),
('sales', MD5('123'), 'Sales', 'Aktif');

-- Tabel Barang
CREATE TABLE barang (
    kode_barang VARCHAR(20) PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    satuan VARCHAR(20),
    stok INT DEFAULT 0,
    lokasi_rak VARCHAR(50)
);

-- Tabel Transaksi
CREATE TABLE transaksi (
    no_transaksi VARCHAR(20) PRIMARY KEY,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipe ENUM('Masuk', 'Keluar', 'Retur') NOT NULL,
    no_referensi VARCHAR(50), 
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    request_by INT,
    approved_by INT,
    FOREIGN KEY (request_by) REFERENCES users(id_user),
    FOREIGN KEY (approved_by) REFERENCES users(id_user)
);

-- Tabel Detail Transaksi
CREATE TABLE detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(20),
    kode_barang VARCHAR(20),
    qty INT,
    FOREIGN KEY (no_transaksi) REFERENCES transaksi(no_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (kode_barang) REFERENCES barang(kode_barang)
);

-- ==========================================
-- 3. INSERT DATA DUMMY
-- ==========================================
-- CATATAN: TRUNCATE DIHAPUS KARENA TABEL BARU DIBUAT (PASTI KOSONG)

-- Insert Barang
INSERT INTO barang (kode_barang, nama_barang, kategori, satuan, stok, lokasi_rak) VALUES 
('B001', 'Kertas HVS A4', 'Kertas', 'Rim', 50, 'Rak A-1'),
('B002', 'Tinta Cair Hitam', 'Tinta', 'Liter', 5, 'Rak B-2'),
('B003', 'Kayu Pensil', 'Bahan Baku', 'Kg', 100, 'Gudang C'),
('B004', 'Pensil 2B (Jadi)', 'Barang Jadi', 'Pcs', 0, 'Gudang A');

-- ==========================================
-- 3.1. TABEL & DATA CMS (KELOLA WEB)
-- ==========================================

-- Tabel CMS Menus
CREATE TABLE cms_menus (
    id INT AUTO_INCREMENT PRIMARY KEY,
    menu_name VARCHAR(50) NOT NULL,
    link VARCHAR(255) NOT NULL,
    icon VARCHAR(50),
    parent_id INT DEFAULT NULL,
    order_position INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    role_access VARCHAR(255) DEFAULT 'All'
);

-- Tabel CMS Categories
CREATE TABLE cms_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
);

-- Tabel CMS Posts
CREATE TABLE cms_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT,
    category_id INT,
    status ENUM('Draft', 'Published', 'Archived') DEFAULT 'Draft',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES cms_categories(id) ON DELETE SET NULL
);

-- Insert CMS Menus
INSERT INTO cms_menus (menu_name, link, icon, parent_id, order_position, role_access) VALUES
('Dashboard', 'index.php', 'fa-tachometer-alt', NULL, 1, 'All'),
('Master Data', '#', 'fa-box', NULL, 2, 'Admin,Produksi'),
('Barang', 'adminBarang.php', 'fa-circle', 2, 1, 'Admin,Produksi'),
('Transaksi', '#', 'fa-exchange-alt', NULL, 3, 'All'),
('Barang Masuk', 'adminTransaksiMasuk.php', 'fa-arrow-down', 4, 1, 'Admin,Purchasing,Produksi'),
('Barang Keluar', 'adminTransaksiKeluar.php', 'fa-arrow-up', 4, 2, 'Admin,Sales,Produksi'),
('Retur Barang', 'adminRetur.php', 'fa-undo', 4, 3, 'Admin,Sales'),
('Laporan', 'laporan.php', 'fa-file-alt', NULL, 4, 'Admin,Manager'),
('Kelola Web', 'adminWeb.php', 'fa-globe', NULL, 5, 'Admin');

-- Insert CMS Categories
INSERT INTO cms_categories (name, slug, description) VALUES
('Berita', 'berita', 'Kategori untuk berita terkini'),
('Pengumuman', 'pengumuman', 'Pengumuman penting perusahaan'),
('Artikel', 'artikel', 'Artikel umum dan tutorial');

-- Insert CMS Posts
INSERT INTO cms_posts (title, slug, content, category_id, status) VALUES
('Selamat Datang di SIFASTER', 'welcome-sifaster', '<p>Selamat datang di sistem manajemen gudang SIFASTER.</p>', 2, 'Published'),
('Update Stok Akhir Tahun', 'update-stok-2025', '<p>Harap melakukan opname stok sebelum tanggal 31 Desember.</p>', 2, 'Published'),
('Cara Menggunakan Scanner', 'tutorial-scanner', '<p>Berikut adalah panduan menggunakan scanner barcode...</p>', 3, 'Draft');

-- ==========================================
-- 4. FINISHING
-- ==========================================
SET FOREIGN_KEY_CHECKS = 1;