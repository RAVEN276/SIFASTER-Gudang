<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

$kode_barang = isset($_GET['kode_barang']) ? $_GET['kode_barang'] : '';
$tgl_mulai = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');

$pageTitle = 'Kartu Stok Barang';
$headerTitle = 'Laporan Kartu Stok';
$headerDesc = 'Riwayat mutasi stok per item';
$activePage = 'laporan';

include 'header.php';
?>

<main class="article">
    <div class="crud-wrapper">
        <div class="form-section">
            <h3><i class="fas fa-file-alt"></i> Filter Kartu Stok</h3>
            <form method="GET" action="" class="form-grid">
                <div class="form-group">
                    <label>Pilih Barang:</label>
                    <select name="kode_barang" class="form-control" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php
                        $qBarang = mysqli_query($koneksi, "SELECT kode_barang, nama_barang, satuan FROM barang ORDER BY nama_barang ASC");
                        while($rb = mysqli_fetch_array($qBarang)){
                            $selected = ($kode_barang == $rb['kode_barang']) ? 'selected' : '';
                            echo "<option value='".$rb['kode_barang']."' $selected>".$rb['kode_barang']." - ".$rb['nama_barang']." (".$rb['satuan'].")</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dari Tanggal:</label>
                    <input type="date" name="tgl_mulai" class="form-control" value="<?php echo $tgl_mulai; ?>" required>
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal:</label>
                    <input type="date" name="tgl_selesai" class="form-control" value="<?php echo $tgl_selesai; ?>" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-submit" style="margin-bottom:0;"><i class="fas fa-search"></i> Tampilkan</button>
                </div>
            </form>
        </div>

        <?php if($kode_barang): 
            // 1. Ambil Info Barang
            $qInfo = mysqli_query($koneksi, "SELECT * FROM barang WHERE kode_barang = '$kode_barang'");
            $info = mysqli_fetch_array($qInfo);

            // 2. Hitung Saldo Awal (Sebelum Tgl Mulai)
            // Masuk (Termasuk Retur)
            $qMasukAwal = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE dt.kode_barang = '$kode_barang' AND (t.tipe = 'Masuk' OR t.tipe = 'Retur') AND t.status = 'Approved' AND DATE(t.tanggal) < '$tgl_mulai'");
            $dMasukAwal = mysqli_fetch_assoc($qMasukAwal);
            $masukAwal = $dMasukAwal['total'] ?? 0;

            // Keluar
            $qKeluarAwal = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE dt.kode_barang = '$kode_barang' AND t.tipe = 'Keluar' AND t.status = 'Approved' AND DATE(t.tanggal) < '$tgl_mulai'");
            $dKeluarAwal = mysqli_fetch_assoc($qKeluarAwal);
            $keluarAwal = $dKeluarAwal['total'] ?? 0;

            $saldoAwal = $masukAwal - $keluarAwal;

            // 3. Ambil Mutasi (Antara Tgl Mulai - Selesai)
            $sqlMutasi = "SELECT t.tanggal, t.no_transaksi, t.tipe, t.no_referensi, dt.qty, u.username 
                          FROM transaksi t 
                          JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi 
                          JOIN users u ON t.request_by = u.id_user
                          WHERE dt.kode_barang = '$kode_barang' 
                          AND t.status = 'Approved'
                          AND DATE(t.tanggal) BETWEEN '$tgl_mulai' AND '$tgl_selesai'
                          ORDER BY t.tanggal ASC";
            $qMutasi = mysqli_query($koneksi, $sqlMutasi);
        ?>
        
        <div class="table-section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px;">
                <div>
                    <h3 style="margin:0; border:none; padding:0;">Kartu Stok: <?php echo $info['nama_barang']; ?></h3>
                    <p style="margin:5px 0 0; color:#64748b;">Periode: <?php echo date('d M Y', strtotime($tgl_mulai)); ?> s/d <?php echo date('d M Y', strtotime($tgl_selesai)); ?></p>
                </div>
                <button onclick="window.print()" class="btn-print" style="width:auto;"><i class="fas fa-print"></i> Cetak / PDF</button>
            </div>

            <div class="table-responsive">
                <table class="table-stok">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Transaksi</th>
                            <th>Keterangan / Ref</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Stok</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Saldo Awal Row -->
                        <tr style="background:#f8fafc; font-weight:bold;">
                            <td colspan="3" style="text-align:right;">Stok Awal (Akumulasi s/d <?php echo date('d/m/Y', strtotime($tgl_mulai . ' -1 day')); ?>)</td>
                            <td style="color:green;"><?php echo $masukAwal; ?></td>
                            <td style="color:red;"><?php echo $keluarAwal; ?></td>
                            <td><?php echo $saldoAwal; ?></td>
                        </tr>

                        <?php
                        $saldoBerjalan = $saldoAwal;
                        $totalMasuk = 0;
                        $totalKeluar = 0;

                        if(mysqli_num_rows($qMutasi) > 0) {
                            while($r = mysqli_fetch_array($qMutasi)) {
                                $masuk = ($r['tipe'] == 'Masuk' || $r['tipe'] == 'Retur') ? $r['qty'] : 0;
                                $keluar = ($r['tipe'] == 'Keluar') ? $r['qty'] : 0;
                                
                                $saldoBerjalan = $saldoBerjalan + $masuk - $keluar;
                                $totalMasuk += $masuk;
                                $totalKeluar += $keluar;
                                ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($r['tanggal'])); ?></td>
                                    <td><?php echo $r['no_transaksi']; ?></td>
                                    <td>
                                        <?php echo $r['tipe']; ?> - <?php echo $r['no_referensi']; ?><br>
                                        <small style="color:#64748b;">By: <?php echo $r['username']; ?></small>
                                    </td>
                                    <td style="color:green;"><?php echo ($masuk > 0) ? "+".$masuk : "-"; ?></td>
                                    <td style="color:red;"><?php echo ($keluar > 0) ? "-".$keluar : "-"; ?></td>
                                    <td style="font-weight:bold;"><?php echo $saldoBerjalan; ?></td>
                                </tr>
                                <?php
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding:20px;'>Tidak ada mutasi pada periode ini.</td></tr>";
                        }
                        ?>
                        
                        <!-- Footer Totals -->
                        <tr style="background:#f1f5f9; font-weight:bold;">
                            <td colspan="3" style="text-align:right;">Total Mutasi</td>
                            <td style="color:green;"><?php echo $totalMasuk; ?></td>
                            <td style="color:red;"><?php echo $totalKeluar; ?></td>
                            <td><?php echo $saldoBerjalan; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</main>

<?php include 'aside.php'; ?>
</div>
<?php include 'footer.php'; ?>
