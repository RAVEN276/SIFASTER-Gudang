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

$labels = [];
$dataStok = [];
$queryChart = mysqli_query($koneksi, "SELECT nama_barang, stok FROM barang ORDER BY stok DESC LIMIT 10");

while ($rc = mysqli_fetch_assoc($queryChart)) {
    $labels[] = $rc['nama_barang']; 
    $dataStok[] = $rc['stok'];
}

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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
          <li><a href="adminTransaksiMasuk.php">Transaksi Masuk</a></li>
          <li><a href="adminTransaksiKeluar.php">Transaksi Keluar</a></li>
          <li><a href="laporan.php">Laporan & Monitoring</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </nav>

      <main class="article">
        <h2>Dashboard Ringkasan</h2>
        
        <section class="headline">
          <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <h3>Grafik Level Stok Barang</h3>
              <span style="font-weight: bold; color: var(--primary-color); background: #eff6ff; padding: 5px 12px; border-radius: 20px; font-size: 0.9rem; border: 1px solid #dbeafe;">
                  Total Aset: <?php echo $totalStok; ?> Unit
              </span>
          </div>
          <div style="position: relative; height: 300px; width: 100%;">
            <canvas id="stokChart"></canvas>
          </div>
        </section>

        <div style="display: flex; gap: 20px; flex-wrap: wrap;">
            <section class="headline" style="flex: 1; min-width: 250px;">
              <h3>Barang Masuk Hari Ini</h3>
              <p style="font-size: 2em; font-weight: bold; color: #27ae60; margin: 10px 0;">
                <?php echo $masukHariIni; ?> Unit
              </p>
              <p>Transaksi penerimaan dari Supplier.</p>
            </section>

            <section class="headline" style="flex: 1; min-width: 250px;">
              <h3>Barang Keluar Hari Ini</h3>
              <p style="font-size: 2em; font-weight: bold; color: #e67e22; margin: 10px 0;">
                <?php echo $keluarHariIni; ?> Unit
              </p>
              <p>Transaksi pengeluaran ke Produksi.</p>
            </section>
        </div>
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
                  echo '<a href="adminBarang.php?op=edit&id='.$row['kode_barang'].'" class="notif-item">';
                  echo '<div class="notif-icon">⚠️</div>';
                  echo '<div class="notif-details">';
                  echo '<span class="notif-title">' . htmlspecialchars($row['nama_barang']) . '</span>';
                  echo '<span class="notif-stock">Sisa: ' . $row['stok'] . ' ' . $row['satuan'] . '</span>';
                  echo '</div>';
                  echo '</a>';
              }
          } else {
              echo '<div class="notif-safe">';
              echo '<span>✅</span> Semua stok barang aman.';
              echo '</div>';
          }
          ?>
        </div>
      </aside>
    </div>

    <footer class="footer">
        <div class="footer-left">
            <a href="#"><i class="fab fa-instagram"></i></a>
            <a href="#"><i class="fab fa-whatsapp"></i></a>
            <a href="#"><i class="fab fa-youtube"></i></a>
            <a href="#"><i class="fab fa-linkedin"></i></a>
        </div>
        <div class="footer-center">
            Copyright &copy; 2025. All Rights Reserved
        </div>
        <div class="footer-right">
            <div class="footer-brand">FIZARS WEB</div>
            <div class="footer-slogan">Try to be strong</div>
        </div>
    </footer>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('stokChart').getContext('2d');
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.5)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        new Chart(ctx, {
          type: 'line',
          data: {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
              label: 'Jumlah Stok',
              data: <?php echo json_encode($dataStok); ?>,
              borderColor: '#003366',
              backgroundColor: gradient,
              borderWidth: 3,
              pointBackgroundColor: '#fff',
              pointBorderColor: '#f39c12',
              pointRadius: 6,
              pointHoverRadius: 8,
              fill: true,
              tension: 0.4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: 'rgba(0, 51, 102, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 12,
                cornerRadius: 8,
                displayColors: false,
                callbacks: { label: function(context) { return context.parsed.y + ' Unit'; } }
              }
            },
            scales: {
              y: { beginAtZero: true, grid: { color: '#f1f5f9', borderDash: [5, 5] }, ticks: { font: { family: "'Segoe UI', sans-serif" }, color: '#64748b' } },
              x: { grid: { display: false }, ticks: { font: { family: "'Segoe UI', sans-serif", size: 11 }, color: '#64748b' } }
            },
            interaction: { intersect: false, mode: 'index' },
          }
        });
    });
  </script>
</body>
</html>