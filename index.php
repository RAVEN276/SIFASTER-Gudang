<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// --- Logika Dashboard & Notifikasi ---

// 1. Hitung Total Stok (Semua Barang)
$queryTotal = mysqli_query($koneksi, "SELECT SUM(stok) as total_stok FROM barang");
$dataTotal = mysqli_fetch_assoc($queryTotal);
$totalStok = $dataTotal['total_stok'] ?? 0;

// 2. Ambil Barang Low Stock (Misal batas aman <= 10)
$batasAman = 10;
$queryLow = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= $batasAman ORDER BY stok ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>SIFASTER Gudang</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="logo">SIFASTER</div>
      <div class="header-text">
        <h1>Sistem Informasi Gudang</h1>
        <p>Manufaktur Alat Tulis Kantor (ATK)</p>
        <p class="location">Lokasi: Gudang Utama | User: <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</p>
      </div>
    </header>

    <div class="content-wrapper">
      <nav class="nav">
        <h2>Menu Utama</h2>
        <ul>
          <li><a href="index.php" class="active">Dashboard</a></li>
          <li><a href="adminBarang.php">Master Data & Stok</a></li>
          <li><a href="adminTransaksiMasuk.php">Transaksi Masuk (Inbound)</a></li>
          <li><a href="adminTransaksiKeluar.php">Transaksi Keluar (Outbound)</a></li>
          <li><a href="#">Laporan & Monitoring</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </nav>

      <article class="article">
        <h2>Dashboard Ringkasan</h2>
        <section class="headline">
          <h3>Total Stok Barang</h3>
          <p style="font-size: 2em; font-weight: bold; color: var(--primary-color); margin: 10px 0;">
            <?php echo $totalStok; ?> Unit
          </p>
          <p>Akumulasi seluruh SKU di gudang.</p>
        </section>
        <section class="headline">
          <h3>Barang Masuk Hari Ini</h3>
          <p>Ringkasan transaksi penerimaan dari Supplier & Produksi.</p>
        </section>
        <section class="headline">
          <h3>Barang Keluar Hari Ini</h3>
          <p>Ringkasan transaksi pengeluaran ke Produksi & Sales.</p>
        </section>
      </article>

      <aside class="aside">
        <h2>Notifikasi (Low Stock)</h2>
        <p style="font-size: 0.9em; margin-bottom: 10px;">Barang dengan stok &le; <?php echo $batasAman; ?>:</p>
        <ul>
          <?php 
          if (mysqli_num_rows($queryLow) > 0) {
              while($row = mysqli_fetch_assoc($queryLow)) {
                  echo '<li>';
                  echo '<a href="adminBarang.php" style="color: #e74c3c; font-weight: bold;">';
                  echo '[!] ' . htmlspecialchars($row['nama_barang']);
                  echo ' <br><span style="font-size:0.8em; color: #555;">(Sisa: ' . $row['stok'] . ' ' . $row['satuan'] . ')</span>';
                  echo '</a>';
                  echo '</li>';
              }
          } else {
              echo '<li><a href="#" style="color: green;">Semua stok aman.</a></li>';
          }
          ?>
        </ul>
      </aside>
    </div>

    <footer class="footer">
      <div class="social">
        <span>Support IT</span> | <span>Panduan Pengguna</span>
      </div>
      <div class="footer-text">
        <span>&copy; 2025 SIFASTER - Sistem Informasi Cepat & Akurat</span>
      </div>
    </footer>
  </div>
</body>
</html>