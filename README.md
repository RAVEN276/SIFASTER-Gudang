# SIFASTER - Sistem Informasi Gudang

SIFASTER adalah aplikasi Sistem Informasi Manajemen Gudang berbasis web yang dirancang untuk mengelola stok barang, transaksi masuk (inbound), dan transaksi keluar (outbound) secara efisien. Aplikasi ini dibangun menggunakan PHP Native dan MySQL dengan antarmuka "Corporate" yang bersih dan responsif.

## 🎓 Identitas Proyek

Proyek ini dikembangkan untuk memenuhi **Ujian Akhir Semester (UAS)** pada mata kuliah:
*   Rekayasa Perangkat Lunak
*   Pengelolaan Informasi Berbasis Script
*   Konsep dan Perancangan Berbasis Data

**Universitas Pembangunan Jaya - Prodi Sistem Informasi**

### 👥 Anggota Kelompok
1.  **Panji Kurnia Akbar** (NIM: 2024081024)
2.  **Arae Mahesa Armera** (NIM: 2024081015)
3.  **Mochammad Lintar Arya Dwiputra** (NIM: 2024081032)
4.  **Fizar Erlansyah** (NIM: 2024081041)

## 📋 Fitur Utama

*   **Dashboard Dinamis**: Menampilkan ringkasan stok total, transaksi hari ini, dan peringatan stok menipis (Low Stock Alert).
*   **Manajemen Stok (Master Data)**: CRUD (Create, Read, Update, Delete) data barang dengan informasi lokasi rak dan kategori.
*   **Transaksi Masuk (Inbound)**: Mencatat barang masuk dan otomatis menambah stok.
*   **Transaksi Keluar (Outbound)**: Mencatat barang keluar dan otomatis mengurangi stok.
*   **Laporan & Monitoring**:
    *   Filter riwayat transaksi berdasarkan tanggal dan tipe (Masuk/Keluar).
    *   Fitur Cetak Laporan (Print) yang dioptimalkan untuk kertas (hanya mencetak tabel data).
*   **Otentikasi**: Sistem Login/Logout untuk keamanan akses admin.

## 🛠️ Teknologi yang Digunakan

*   **Bahasa Pemrograman**: PHP (Native)
*   **Database**: MySQL
*   **Frontend**: HTML5, CSS3 (Custom Corporate Theme)
*   **Server Environment**: Laragon / XAMPP (Apache)

## 🚀 Cara Instalasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal Anda:

### 1. Persiapan Lingkungan
Pastikan Anda telah menginstal web server lokal seperti **Laragon** atau **XAMPP**.

### 2. Clone / Download Project
Simpan folder project `SIFASTER Gudang` ke dalam direktori root web server Anda:
*   **Laragon**: `D:\laragon\www\SIFASTER Gudang`
*   **XAMPP**: `C:\xampp\htdocs\SIFASTER Gudang`

### 3. Setup Database
1.  Buka **phpMyAdmin** atau Database Manager (HeidiSQL/DBeaver).
2.  Buat database baru (opsional, script akan membuatnya jika belum ada).
3.  Import file `database.sql` yang terdapat di dalam folder project.
    *   File ini akan membuat database `sifaster_gudang` beserta tabel dan data dummy awal.

### 4. Konfigurasi Koneksi
Buka file `koneksi.php` dan sesuaikan pengaturan database jika berbeda dengan default:
```php
$host = "localhost";
$user = "root";
$pass = ""; // Sesuaikan password database Anda
$db   = "sifaster_gudang";
```

### 5. Jalankan Aplikasi
Buka browser dan akses alamat berikut:
```
http://localhost/SIFASTER Gudang
```
atau jika menggunakan Laragon dengan fitur auto-virtual host:
```
http://sifaster-gudang.test
```

## 🔑 Akun Default
Gunakan akun berikut untuk login pertama kali:
*   **Username**: `admin`
*   **Password**: `admin`

## 📂 Struktur File

*   `index.php`: Halaman Dashboard utama.
*   `adminBarang.php`: Halaman pengelolaan Master Data Barang.
*   `adminTransaksiMasuk.php`: Halaman input barang masuk.
*   `adminTransaksiKeluar.php`: Halaman input barang keluar.
*   `laporan.php`: Halaman laporan dan monitoring transaksi.
*   `login.php` & `logout.php`: Fitur otentikasi.
*   `koneksi.php`: Konfigurasi koneksi database.
*   `style.css`: File styling utama aplikasi.
*   `database.sql`: Script SQL untuk struktur database dan data awal.

## 📝 Catatan Pengembangan
*   Aplikasi ini menggunakan layout *fullscreen* untuk memaksimalkan area kerja.
*   Fitur cetak pada halaman laporan secara otomatis menyembunyikan navigasi dan filter untuk hasil cetak yang rapi.

---
&copy; 2025 SIFASTER - Sistem Informasi Cepat & Akurat
