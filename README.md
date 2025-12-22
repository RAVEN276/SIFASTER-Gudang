# SIFASTER - Sistem Informasi Gudang (Advanced WMS)

SIFASTER adalah aplikasi Sistem Informasi Manajemen Gudang (WMS) tingkat lanjut berbasis web yang dirancang untuk mengelola stok barang, transaksi masuk (inbound), transaki keluar (outbound), dan retur barang dengan alur kerja persetujuan (approval workflow) yang terintegrasi.

Aplikasi ini dibangun menggunakan **PHP Native** dan **MySQL** dengan antarmuka modern yang responsif (**Blue Gradient Universe Theme**), dilengkapi dengan fitur analitik visual dan notifikasi real-time.

## 🎓 Identitas Proyek

Proyek ini dikembangkan untuk memenuhi **Ujian Akhir Semester (UAS)** pada mata kuliah:

- Rekayasa Perangkat Lunak
- Pengelolaan Informasi Berbasis Script
- Konsep dan Perancangan Berbasis Data

**Universitas Pembangunan Jaya - Prodi Sistem Informasi**

### 👥 Anggota Kelompok

1.  **Panji Kurnia Akbar** (NIM: 2024081024)
2.  **Arae Mahesa Armera** (NIM: 2024081015)
3.  **Mochammad Lintar Arya Dwiputra** (NIM: 2024081032)
4.  **Fizar Erlansyah** (NIM: 2024081041)

## 🌟 Fitur Utama

### 1. Dashboard Eksekutif

- **Visualisasi Data**: Grafik batang (Bar Chart) untuk aktivitas Masuk/Keluar/Retur 7 hari terakhir.
- **Live Statistik**: Kartu ringkasan untuk Total SKU, Stok Menipis, dan volume transaksi bulanan.
- **Top 5 Terlaris**: Menampilkan barang dengan perputaran keluar tertinggi.
- **Aktivitas Terbaru**: Log real-time dari setiap transaksi yang terjadi.

### 2. Manajemen Stok & Produk (Master Data)

- Pengelolaan SKU (Kode Barang), Nama, Kategori, Satuan, dan Lokasi Rak.
- **Low Stock Alert**: Notifikasi otomatis jika stok barang berada di bawah batas aman (Safety Stock < 10).

### 3. Transaksi dengan Approval Workflow

- **Role-Based Input**: Transaksi tidak langsung mengurangi/menambah stok.
  - **Inbound (Masuk)**: Diajukan oleh _Purchasing/Produksi_ → Butuh Approval Admin.
  - **Outbound (Keluar)**: Diajukan oleh _Sales/Produksi_ (SPK/SO) → Butuh Approval Admin (Cek Stok Otomatis).
- **Status Tracking**: `Pending` → `Approved` (Stok Berubah) atau `Rejected`.

### 4. Manajemen Retur Barang

- Pencatatan barang rusak/kembali dari pelanggan atau produksi.
- Mekanisme persetujuan untuk memastikan validasi retur.

### 5. Keamanan & Hak Akses (RBAC)

Sistem pembagian hak akses (Role-Based Access Control) yang ketat:

- **Admin**: Akses Penuh (Approval, Manajemen User, Master Data, Laporan).
- **Purchasing**: Khusus Input Barang Masuk (PO).
- **Produksi**: Input Hasil Produksi (Masuk) & Permintaan Bahan Baku (Keluar).
- **Sales**: Input Penjualan (SO - Barang Keluar).

### 6. Notifikasi Sistem

- **Bell Notification**: Pusat notifikasi untuk approval yang tertunda, stok menipis, dan status request user.
- **Real-time Badge**: Indikator jumlah notifikasi yang belum dibaca.

## 🛠️ Teknologi yang Digunakan

- **Backend**: PHP 8.x (Native)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, Modern CSS3 (Variables, Flexbox, Grid, Animations)
- **Libraries**:
  - _Chart.js_ (Grafik Statistik)
  - _FontAwesome 6_ (Ikon)
  - _SweetAlert2_ (Popup & Konfirmasi Modern)
  - _Google Fonts_ (Inter & Poppins)

## 🚀 Cara Instalasi

### 1. Persiapan Lingkungan

Pastikan Anda telah menginstal web server lokal seperti **Laragon** (Rekomendasi) atau **XAMPP**.

### 2. Setup Database

1.  Buka database manager (HeidiSQL / phpMyAdmin).
2.  Buat database baru dengan nama `sifaster_gudang`.
3.  Import file `sifaster_gudang.sql` (atau `database.sql`) yang ada di folder root project.
    - _Pastikan struktur tabel users memiliki kolom role._

### 3. Konfigurasi

Cek file `koneksi.php` dan sesuaikan kredensial database Anda:

```php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sifaster_gudang";
```

### 4. Akses Aplikasi

Buka browser dan kunjungi: `http://localhost/SIFASTER-Gudang`

## 🔑 Akun Demo (Role)

| Role              | Username     | Password     | Deskripsi                    |
| :---------------- | :----------- | :----------- | :--------------------------- |
| **Administrator** | `admin`      | `admin`      | Full Akses & Approval        |
| **Produksi**      | `produksi`   | `produksi`   | Request Bahan & Setor Barang |
| **Purchasing**    | `purchasing` | `purchasing` | Request PO (Masuk)           |
| **Sales**         | `sales`      | `sales`      | Request SO (Keluar)          |

_(Buat user baru via halaman Kelola User jika akun di atas belum ada di database)_

## 📂 Struktur File Penting

- `adminTransactionsMasuk.php` & `...Keluar.php`: Logika Transaksi & Approval.
- `adminUsers.php`: Manajemen Pengguna & Hak Akses.
- `adminRetur.php`: Modul Retur Barang.
- `header.php`: Logika Notifikasi & Navigasi Dinamis.
- `style.css`: Styling utama (Glassmorphism & Layout).

---

&copy; 2025 SIFASTER - Real-Time, Real Solution
