<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// --- LOGIKA FILTER ---
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$tipe      = isset($_GET['tipe']) ? $_GET['tipe'] : 'Semua';

// Build Query
$sql = "SELECT t.no_transaksi, t.tanggal, t.tipe, b.kode_barang, b.nama_barang, dt.qty, a.username 
        FROM transaksi t
        JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi
        JOIN barang b ON dt.kode_barang = b.kode_barang
        LEFT JOIN admin a ON t.id_admin = a.id_admin
        WHERE DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_akhir'";

if ($tipe != 'Semua') {
    $sql .= " AND t.tipe = '$tipe'";
}

$sql .= " ORDER BY t.tanggal DESC";

$query = mysqli_query($koneksi, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <title>Laporan & Monitoring - SIFASTER</title>
  <link rel="stylesheet" href="style.css" />
  <style>
      /* Tambahan CSS Khusus Halaman Laporan */
      .filter-box {
          background: #f8f9fa;
          padding: 15px;
          border-radius: 5px;
          border: 1px solid #ddd;
          margin-bottom: 20px;
          display: flex;
          gap: 10px;
          align-items: flex-end;
      }
      .filter-group {
          display: flex;
          flex-direction: column;
      }
      .filter-group label {
          font-size: 0.9em;
          margin-bottom: 5px;
          font-weight: bold;
      }
      .filter-group input, .filter-group select {
          padding: 8px;
          border: 1px solid #ccc;
          border-radius: 4px;
      }
      .btn-filter {
          padding: 9px 20px;
          background-color: var(--primary-color);
          color: white;
          border: none;
          border-radius: 4px;
          cursor: pointer;
      }
      .btn-filter:hover {
          background-color: #34495e;
      }
      .btn-print {
          padding: 9px 20px;
          background-color: #27ae60;
          color: white;
          border: none;
          border-radius: 4px;
          cursor: pointer;
          text-decoration: none;
          display: inline-block;
      }
      table {
          width: 100%;
          border-collapse: collapse;
          margin-top: 10px;
      }
      th, td {
          padding: 10px;
          border: 1px solid #ddd;
          text-align: left;
      }
      th {
          background-color: var(--primary-color);
          color: white;
      }
      tr:nth-child(even) {
          background-color: #f2f2f2;
      }
      .badge {
          padding: 5px 10px;
          border-radius: 4px;
          color: white;
          font-size: 0.8em;
      }
      .badge-masuk { background-color: #27ae60; }
      .badge-keluar { background-color: #e67e22; }

      @media print {
          .header, .nav, .footer, .filter-box {
              display: none !important;
          }
          .container, .content-wrapper, .article {
              width: 100% !important;
              margin: 0 !important;
              padding: 0 !important;
              box-shadow: none !important;
              display: block !important;
          }
          body {
              background-color: white !important;
              -webkit-print-color-adjust: exact;
          }
          table {
              width: 100%;
              border-collapse: collapse;
          }
          th, td {
              border: 1px solid #000 !important;
              color: #000 !important;
          }
          th {
              background-color: #ccc !important; /* Light gray for header in print */
          }
      }
  </style>
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
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="adminBarang.php">Master Data & Stok</a></li>
          <li><a href="adminTransaksiMasuk.php">Transaksi Masuk (Inbound)</a></li>
          <li><a href="adminTransaksiKeluar.php">Transaksi Keluar (Outbound)</a></li>
          <li><a href="laporan.php" class="active">Laporan & Monitoring</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </nav>

      <article class="article" style="width: 100%;"> <!-- Full width for report -->
        <h2>Laporan Mutasi Barang</h2>
        
        <form method="GET" action="" class="filter-box">
            <div class="filter-group">
                <label>Dari Tanggal:</label>
                <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>">
            </div>
            <div class="filter-group">
                <label>Sampai Tanggal:</label>
                <input type="date" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>">
            </div>
            <div class="filter-group">
                <label>Tipe Transaksi:</label>
                <select name="tipe">
                    <option value="Semua" <?php if($tipe == 'Semua') echo 'selected'; ?>>Semua</option>
                    <option value="Masuk" <?php if($tipe == 'Masuk') echo 'selected'; ?>>Masuk (Inbound)</option>
                    <option value="Keluar" <?php if($tipe == 'Keluar') echo 'selected'; ?>>Keluar (Outbound)</option>
                </select>
            </div>
            <button type="submit" class="btn-filter">Tampilkan</button>
            <button type="button" onclick="window.print()" class="btn-print">Cetak Laporan</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>No Transaksi</th>
                    <th>Tanggal</th>
                    <th>Tipe</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Qty</th>
                    <th>Admin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if (mysqli_num_rows($query) > 0) {
                    while ($row = mysqli_fetch_assoc($query)) {
                        $badgeClass = ($row['tipe'] == 'Masuk') ? 'badge-masuk' : 'badge-keluar';
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . $row['no_transaksi'] . "</td>";
                        echo "<td>" . $row['tanggal'] . "</td>";
                        echo "<td><span class='badge $badgeClass'>" . $row['tipe'] . "</span></td>";
                        echo "<td>" . $row['kode_barang'] . "</td>";
                        echo "<td>" . $row['nama_barang'] . "</td>";
                        echo "<td>" . $row['qty'] . "</td>";
                        echo "<td>" . $row['username'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' style='text-align:center;'>Tidak ada data transaksi pada periode ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
      </article>
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
