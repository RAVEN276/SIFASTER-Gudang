<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Cek Role (Hanya Admin)
if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_akhir = isset($_GET['tgl_akhir']) ? $_GET['tgl_akhir'] : date('Y-m-d');
$tipe      = isset($_GET['tipe']) ? $_GET['tipe'] : 'Semua';

// Query Laporan Updated (Join ke tabel users)
$sql = "SELECT t.no_transaksi, t.tanggal, t.tipe, t.status, t.no_referensi, 
               b.kode_barang, b.nama_barang, dt.qty, 
               u_req.username as requestor, u_app.username as approver
        FROM transaksi t
        JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi
        JOIN barang b ON dt.kode_barang = b.kode_barang
        LEFT JOIN users u_req ON t.request_by = u_req.id_user
        LEFT JOIN users u_app ON t.approved_by = u_app.id_user
        WHERE DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_akhir'";


if ($tipe != 'Semua') {
    $sql .= " AND t.tipe = '$tipe'";
}

$sql .= " ORDER BY t.tanggal DESC";
$query = mysqli_query($koneksi, $sql);

$pageTitle = 'Laporan & Monitoring - SIFASTER';
$headerTitle = 'Sistem Informasi Gudang';
$headerDesc = 'Manufaktur Alat Tulis Kantor (ATK)';
$headerLocation = 'Lokasi: Gudang Utama | User: ' . htmlspecialchars($_SESSION['username']) . ' (Admin)';
$activePage = 'laporan';

include 'header.php';
?>


      <main class="article">
        <div class="crud-wrapper">
            <div class="form-section">
                <h3>Filter Laporan Mutasi</h3>
                <form method="GET" action="">
                    <div class="form-grid">
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
                        
                        <div class="button-stack"> 
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
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Ref (PO/SPK)</th>
                                <th>Status</th>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>User</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            if (mysqli_num_rows($query) > 0) {
                                while ($row = mysqli_fetch_assoc($query)) {
                                    $badgeClass = ($row['tipe'] == 'Masuk') ? 'badge-masuk' : 'badge-keluar';
                                    $statusClass = strtolower($row['status']); // pending, approved, rejected
                                    $tanda = ($row['tipe'] == 'Masuk') ? '+' : '-';
                                    
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>";
                                    echo "<td style='font-weight:bold;'>" . htmlspecialchars($row['no_transaksi']) . "</td>";
                                    echo "<td>" . $row['tanggal'] . "</td>";
                                    echo "<td><span class='badge $badgeClass'>" . $row['tipe'] . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['no_referensi']) . "</td>";
                                    echo "<td><span class='badge badge-$statusClass'>" . $row['status'] . "</span></td>";
                                    echo "<td>" . htmlspecialchars($row['nama_barang']) . " (" . $row['kode_barang'] . ")</td>";
                                    echo "<td style='font-weight:bold;'>" . $tanda . $row['qty'] . "</td>";
                                    echo "<td>Req: " . htmlspecialchars($row['requestor']) . "<br><small>App: " . htmlspecialchars($row['approver']) . "</small></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' style='text-align:center; padding: 20px; color: #666;'>Tidak ada data transaksi pada periode yang dipilih.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
      </main>
      <?php include 'aside.php'; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>