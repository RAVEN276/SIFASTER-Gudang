<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$tipe      = isset($_GET['tipe']) ? $_GET['tipe'] : 'Semua';

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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan & Monitoring - SIFASTER</title>
  <link rel="stylesheet" href="style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
          <li><a href="index.php">Dashboard</a></li>
          <li><a href="adminBarang.php">Master Data & Stok</a></li>
          <li><a href="adminTransaksiMasuk.php">Transaksi Masuk</a></li>
          <li><a href="adminTransaksiKeluar.php">Transaksi Keluar</a></li>
          <li><a href="laporan.php" class="active">Laporan & Monitoring</a></li>
          <li><a href="logout.php">Logout</a></li>
        </ul>
      </nav>

      <main class="article">
        <div class="crud-wrapper">
            <div class="form-section">
                <h3>Filter Laporan Mutasi</h3>
                <form method="GET" action="">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" value="<?php echo $tgl_mulai; ?>">
                        </div>
                        <div class="form-group">
                            <label>Sampai Tanggal</label>
                            <input type="date" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>">
                        </div>
                        <div class="form-group">
                            <label>Tipe Transaksi</label>
                            <select name="tipe">
                                <option value="Semua" <?php if($tipe == 'Semua') echo 'selected'; ?>>Semua</option>
                                <option value="Masuk" <?php if($tipe == 'Masuk') echo 'selected'; ?>>Masuk (Inbound)</option>
                                <option value="Keluar" <?php if($tipe == 'Keluar') echo 'selected'; ?>>Keluar (Outbound)</option>
                            </select>
                        </div>
                        
                        <div class="form-group button-stack"> 
                            <button type="submit" class="btn-submit">Tampilkan</button>
                            <button type="button" onclick="window.print()" class="btn-print">Cetak PDF</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-section">
                <h3>Hasil Laporan</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No Transaksi</th>
                                <th>Tanggal & Waktu</th>
                                <th>Tipe</th>
                                <th>Kode Barang</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Admin (PIC)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $badgeClass = ($row['tipe'] == 'Masuk') ? 'badge-masuk' : 'badge-keluar';
                                    $tanda = ($row['tipe'] == 'Masuk') ? '+' : '-';
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td style='font-weight:bold;'>" . $row['no_transaksi'] . "</td>";
                                    echo "<td>" . $row['tanggal'] . "</td>";
                                    echo "<td><span class='badge $badgeClass'>" . $row['tipe'] . "</span></td>";
                                    echo "<td>" . $row['kode_barang'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_barang']) . "</td>";
                                    echo "<td style='font-weight:bold;'>" . $tanda . $row['qty'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8' style='text-align:center; padding: 20px; color: #666;'>Tidak ada data transaksi pada periode yang dipilih.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </main>
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
</body>
</html>