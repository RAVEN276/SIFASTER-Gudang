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
    role ENUM('Admin', 'Produksi', 'Purchasing', 'Sales') NOT NULL
); 

-- 2. Baru jalankan Insert (terpisah)
INSERT INTO users (username, password, role) VALUES 
('admin', MD5('123'), 'Admin'),
('produksi', MD5('123'), 'Produksi'),
('purchasing', MD5('123'), 'Purchasing'),
('sales', MD5('123'), 'Sales');

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
    tipe ENUM('Masuk', 'Keluar') NOT NULL,
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

-- Insert Users (Password: admin)
INSERT INTO users (username, password, role) VALUES 
('admin', '21232f297a57a5a743894a0e4a801fc3', 'Admin'),
('produksi', '21232f297a57a5a743894a0e4a801fc3', 'Produksi'),
('purchasing', '21232f297a57a5a743894a0e4a801fc3', 'Purchasing'),
('sales', '21232f297a57a5a743894a0e4a801fc3', 'Sales');

-- Insert Barang
INSERT INTO barang (kode_barang, nama_barang, kategori, satuan, stok, lokasi_rak) VALUES 
('B001', 'Kertas HVS A4', 'Kertas', 'Rim', 50, 'Rak A-1'),
('B002', 'Tinta Cair Hitam', 'Tinta', 'Liter', 5, 'Rak B-2'),
('B003', 'Kayu Pensil', 'Bahan Baku', 'Kg', 100, 'Gudang C'),
('B004', 'Pensil 2B (Jadi)', 'Barang Jadi', 'Pcs', 0, 'Gudang A');

-- ==========================================
-- 4. FINISHING
-- ==========================================
SET FOREIGN_KEY_CHECKS = 1;