<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$q_admin = mysqli_query($koneksi, "SELECT id_admin FROM admin WHERE username = '$username'");
$d_admin = mysqli_fetch_array($q_admin);
$id_admin_login = $d_admin['id_admin'];

$no_transaksi = ""; $tanggal = date('Y-m-d\TH:i'); $kode_barang = ""; $qty = "";
$sukses = ""; $error = ""; $op = "";
if (isset($_GET['op'])) { $op = $_GET['op']; }

if ($op == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $q_cek = mysqli_query($koneksi, "SELECT * FROM detail_transaksi WHERE no_transaksi = '$id'");
    $r_cek = mysqli_fetch_array($q_cek);
    
    if ($r_cek) {
        $kode_brg_old = $r_cek['kode_barang'];
        $qty_old      = $r_cek['qty'];
        mysqli_query($koneksi, "UPDATE barang SET stok = stok - $qty_old WHERE kode_barang = '$kode_brg_old'");
        mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE no_transaksi = '$id'");
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE no_transaksi = '$id'");
        $sukses = "Transaksi berhasil dihapus dan stok dikembalikan.";
    } else { $error = "Data transaksi tidak ditemukan."; }
}

if (isset($_POST['simpan'])) {
    $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $qty         = mysqli_real_escape_string($koneksi, $_POST['qty']);
    $tanggal     = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $no_transaksi = "TM-" . date('YmdHis');

    if ($kode_barang && $qty) {
        $sql1 = "INSERT INTO transaksi (no_transaksi, tanggal, tipe, id_admin) VALUES ('$no_transaksi', '$tanggal', 'Masuk', '$id_admin_login')";
        $q1   = mysqli_query($koneksi, $sql1);
        if ($q1) {
            $sql2 = "INSERT INTO detail_transaksi (no_transaksi, kode_barang, qty) VALUES ('$no_transaksi', '$kode_barang', '$qty')";
            $q2   = mysqli_query($koneksi, $sql2);
            $sql3 = "UPDATE barang SET stok = stok + $qty WHERE kode_barang = '$kode_barang'";
            $q3   = mysqli_query($koneksi, $sql3);
            if ($q2 && $q3) {
                $sukses = "Transaksi Masuk berhasil disimpan. Stok bertambah.";
                $kode_barang = ""; $qty = "";
            } else { $error = "Gagal menyimpan detail transaksi."; }
        } else { $error = "Gagal menyimpan transaksi utama."; }
    } else { $error = "Silakan lengkapi data."; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <title>Transaksi Masuk - SIFASTER</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="crud-page">
    <div class="container">
        <header class="header">
            <div class="logo">
                <img src="logo_clear.png" alt="SIFASTER" class="header-logo-img">
            </div>
            <div class="header-text">
                <h1>Transaksi Barang Masuk (Inbound)</h1>
                <p>Penerimaan dari Supplier / Produksi</p>
                <p class="location">User: <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</p>
            </div>
        </header>

        <div class="content-wrapper">
            <nav class="nav">
                <h2>Menu Utama</h2>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="adminBarang.php">Master Data & Stok</a></li>
                    <li><a href="adminTransaksiMasuk.php" class="active">Transaksi Masuk</a></li>
                    <li><a href="adminTransaksiKeluar.php">Transaksi Keluar</a></li>
                    <li><a href="laporan.php">Laporan & Monitoring</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>

            <main class="article">
                <?php if ($error) { ?> <div class="alert alert-danger"><?php echo $error ?></div> <?php } ?>
                <?php if ($sukses) { ?> <div class="alert alert-success"><?php echo $sukses ?></div> <?php } ?>

                <div class="crud-wrapper">
                    <div class="form-section">
                        <h3>Input Barang Masuk</h3>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="datetime-local" name="tanggal" value="<?php echo $tanggal; ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Pilih Barang</label>
                                <select name="kode_barang" required>
                                    <option value="">- Pilih Barang -</option>
                                    <?php
                                    $q_brg = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                                    while ($r_brg = mysqli_fetch_array($q_brg)) {
                                        echo "<option value='{$r_brg['kode_barang']}'>{$r_brg['kode_barang']} - {$r_brg['nama_barang']} (Stok: {$r_brg['stok']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Jumlah Masuk (Qty)</label>
                                <input type="number" name="qty" min="1" placeholder="Contoh: 100" required>
                            </div>
                            <button type="submit" name="simpan" class="btn-submit">Simpan Transaksi</button>
                        </form>
                    </div>

                    <div class="table-section">
                        <h3>Riwayat Barang Masuk</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No Transaksi</th>
                                        <th>Tanggal</th>
                                        <th>Barang</th>
                                        <th>Qty Masuk</th>
                                        <th>Admin</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_view = "SELECT t.no_transaksi, t.tanggal, b.nama_barang, dt.qty, a.username 
                                                 FROM transaksi t
                                                 JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi
                                                 JOIN barang b ON dt.kode_barang = b.kode_barang
                                                 JOIN admin a ON t.id_admin = a.id_admin
                                                 WHERE t.tipe = 'Masuk'
                                                 ORDER BY t.tanggal DESC LIMIT 20";
                                    $q_view = mysqli_query($koneksi, $sql_view);
                                    while ($row = mysqli_fetch_array($q_view)) {
                                    ?>
                                        <tr>
                                            <td><?php echo $row['no_transaksi'] ?></td>
                                            <td><?php echo $row['tanggal'] ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_barang']) ?></td>
                                            <td style="color: green; font-weight: bold;">+<?php echo $row['qty'] ?></td>
                                            <td><?php echo htmlspecialchars($row['username']) ?></td>
                                            <td>
                                                <a href="adminTransaksiMasuk.php?op=delete&id=<?php echo $row['no_transaksi'] ?>" onclick="return confirm('Hapus transaksi ini? Stok akan dikurangi kembali.')" class="btn-delete" style="font-size:0.8rem;">Batal</a>
                                            </td>
                                        </tr>
                                    <?php } ?>
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