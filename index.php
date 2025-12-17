<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// --- Logika Dashboard ---
$queryTotal = mysqli_query($koneksi, "SELECT SUM(stok) as total_stok FROM barang");
$dataTotal = mysqli_fetch_assoc($queryTotal);
$totalStok = $dataTotal['total_stok'] ?? 0;

$batasAman = 10;
$queryLow = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= $batasAman ORDER BY stok ASC");

$queryMasuk = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Masuk' AND DATE(t.tanggal) = CURDATE()");
$dataMasuk = mysqli_fetch_assoc($queryMasuk);
$masukHariIni = $dataMasuk['total'] ?? 0;

$queryKeluar = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Keluar' AND DATE(t.tanggal) = CURDATE()");
$dataKeluar = mysqli_fetch_assoc($queryKeluar);
$keluarHariIni = $dataKeluar['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
  <title>SIFASTER Gudang</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>
  <div class="container">
    <header class="header">
      <div class="logo">
          <img src="logo_clear.png" alt="SIFASTER" class="header-logo-img">
      </div>
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
          <li><a href="laporan.php">Laporan & Monitoring</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </nav>

      <main class="article">
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
          <p style="font-size: 2em; font-weight: bold; color: #27ae60; margin: 10px 0;">
            <?php echo $masukHariIni; ?> Unit
          </p>
          <p>Ringkasan transaksi penerimaan dari Supplier & Produksi.</p>
        </section>
        <section class="headline">
          <h3>Barang Keluar Hari Ini</h3>
          <p style="font-size: 2em; font-weight: bold; color: #e67e22; margin: 10px 0;">
            <?php echo $keluarHariIni; ?> Unit
          </p>
          <p>Ringkasan transaksi pengeluaran ke Produksi & Sales.</p>
        </section>
      </main>

      <aside class="aside">
        <h2>Notifikasi (Low Stock)</h2>
        <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 15px;">
            Stok Barang &le; <?php echo $batasAman; ?> Unit. Segera lakukan restock!
        </p>
        
        <div class="notif-container">
          <?php 
          if (mysqli_num_rows($queryLow) > 0) {
              while($row = mysqli_fetch_assoc($queryLow)) {
                  // Link menuju halaman edit barang tersebut
                  echo '<a href="adminBarang.php?op=edit&id='.$row['kode_barang'].'" class="notif-item">';
                  
                  // Ikon Warning
                  echo '<div class="notif-icon">⚠️</div>';
                  
                  // Detail Barang
                  echo '<div class="notif-details">';
                  echo '<span class="notif-title">' . htmlspecialchars($row['nama_barang']) . '</span>';
                  echo '<span class="notif-stock">Sisa: ' . $row['stok'] . ' ' . $row['satuan'] . '</span>';
                  echo '</div>';
                  
                  echo '</a>';
              }
          } else {
              // Tampilan jika semua aman
              echo '<div class="notif-safe">';
              echo '<span>✅</span> Semua stok barang aman.';
              echo '</div>';
          }
          ?>
        </div>
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