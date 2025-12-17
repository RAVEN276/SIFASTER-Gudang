CREATE DATABASE IF NOT EXISTS sifaster_gudang;
USE sifaster_gudang;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id_admin INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL
);

-- Tabel Barang (Sesuai Class Diagram + Atribut SRS)
CREATE TABLE IF NOT EXISTS barang (
    kode_barang VARCHAR(20) PRIMARY KEY,
    nama_barang VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    satuan VARCHAR(20),
    stok INT DEFAULT 0,
    lokasi_rak VARCHAR(50)
);

-- Tabel Transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    no_transaksi VARCHAR(20) PRIMARY KEY,
    tanggal DATETIME DEFAULT CURRENT_TIMESTAMP,
    tipe ENUM('Masuk', 'Keluar') NOT NULL,
    id_admin INT,
    FOREIGN KEY (id_admin) REFERENCES admin(id_admin)
);

-- Tabel Detail Transaksi
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    no_transaksi VARCHAR(20),
    kode_barang VARCHAR(20),
    qty INT,
    FOREIGN KEY (no_transaksi) REFERENCES transaksi(no_transaksi),
    FOREIGN KEY (kode_barang) REFERENCES barang(kode_barang)
);

-- Insert Data Dummy Admin (Password: admin)
-- Catatan: Di aplikasi nyata gunakan password_hash(), tapi untuk prototype ini kita pakai MD5 sesuai request SRS lama atau plain text untuk kemudahan simulasi awal.
INSERT INTO admin (username, password) VALUES ('admin', '21232f297a57a5a743894a0e4a801fc3'); 

-- Insert Data Dummy Barang
INSERT INTO barang (kode_barang, nama_barang, kategori, satuan, stok, lokasi_rak) VALUES 
('B001', 'Kertas HVS A4', 'Kertas', 'Rim', 50, 'Rak A-1'),
('B002', 'Tinta Cair Hitam', 'Tinta', 'Liter', 5, 'Rak B-2'),
('B003', 'Kayu Pensil', 'Bahan Baku', 'Kg', 100, 'Gudang C');
