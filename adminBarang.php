<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

$kode_barang = ""; $nama_barang = ""; $kategori = ""; $satuan = ""; $stok = 0; $lokasi_rak = "";
$sukses = ""; $error = ""; $op = ""; 

if (isset($_GET['op'])) { $op = $_GET['op']; }

if ($op == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $sql1 = "DELETE FROM barang WHERE kode_barang = '$id'";
    $q1   = mysqli_query($koneksi, $sql1);
    if ($q1) { $sukses = "Berhasil hapus data"; } else { $error = "Gagal hapus data."; }
}

if ($op == 'edit') {
    $id   = mysqli_real_escape_string($koneksi, $_GET['id']);
    $sql1 = "SELECT * FROM barang WHERE kode_barang = '$id'";
    $q1   = mysqli_query($koneksi, $sql1);
    $r1   = mysqli_fetch_array($q1);
    if ($r1) {
        $kode_barang = $r1['kode_barang'];
        $nama_barang = $r1['nama_barang'];
        $kategori    = $r1['kategori'];
        $satuan      = $r1['satuan'];
        $stok        = $r1['stok'];
        $lokasi_rak  = $r1['lokasi_rak'];
    } else { $error = "Data tidak ditemukan"; }
}

if (isset($_POST['simpan'])) {
    $kode_barang = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $nama_barang = mysqli_real_escape_string($koneksi, $_POST['nama_barang']);
    $kategori    = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $satuan      = mysqli_real_escape_string($koneksi, $_POST['satuan']);
    $stok        = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $lokasi_rak  = mysqli_real_escape_string($koneksi, $_POST['lokasi_rak']);

    if ($kode_barang && $nama_barang && $kategori && $satuan && $lokasi_rak) {
        if ($op == 'edit') {
            $sql1 = "UPDATE barang SET nama_barang='$nama_barang', kategori='$kategori', satuan='$satuan', stok='$stok', lokasi_rak='$lokasi_rak' WHERE kode_barang='$kode_barang'";
            $q1   = mysqli_query($koneksi, $sql1);
            if ($q1) { $sukses = "Data berhasil diupdate"; } else { $error = "Data gagal diupdate"; }
        } else {
            $cek = mysqli_query($koneksi, "SELECT * FROM barang WHERE kode_barang='$kode_barang'");
            if (mysqli_num_rows($cek) > 0) {
                $error = "Kode Barang sudah ada!";
            } else {
                $sql1 = "INSERT INTO barang(kode_barang, nama_barang, kategori, satuan, stok, lokasi_rak) VALUES ('$kode_barang','$nama_barang','$kategori','$satuan','$stok','$lokasi_rak')";
                $q1   = mysqli_query($koneksi, $sql1);
                if ($q1) {
                    $sukses = "Berhasil memasukkan data baru";
                    $kode_barang = ""; $nama_barang = ""; $kategori = ""; $satuan = ""; $stok = 0; $lokasi_rak = "";
                } else { $error = "Gagal memasukkan data"; }
            }
        }
    } else { $error = "Silakan masukkan semua data"; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Barang - SIFASTER</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="crud-page">
    <div class="container">
        <header class="header">
            <div class="logo">
                <img src="logo_clear.png" alt="SIFASTER" class="header-logo-img">
            </div>
            <div class="header-text">
                <h1>Master Data Barang</h1>
                <p>Kelola SKU, Kategori, dan Stok Awal</p>
                <p class="location">User: <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</p>
            </div>
        </header>

        <div class="content-wrapper">
            <nav class="nav">
                <h2>Menu Utama</h2>
                <ul>
                    <li><a href="index.php">Dashboard</a></li>
                    <li><a href="adminBarang.php" class="active">Master Data & Stok</a></li>
                    <li><a href="adminTransaksiMasuk.php">Transaksi Masuk (Inbound)</a></li>
                    <li><a href="adminTransaksiKeluar.php">Transaksi Keluar (Outbound)</a></li>
                    <li><a href="laporan.php">Laporan & Monitoring</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>

            <main class="article">
                <?php if ($error) { ?> <div class="alert alert-danger"><?php echo $error ?></div> <?php } ?>
                <?php if ($sukses) { ?> <div class="alert alert-success"><?php echo $sukses ?></div> <?php } ?>

                <div class="crud-wrapper">
                    <div class="form-section">
                        <h3><?php echo ($op == 'edit') ? 'Edit Barang' : 'Tambah Barang Baru'; ?></h3>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Kode Barang (SKU)</label>
                                <input type="text" name="kode_barang" value="<?php echo htmlspecialchars($kode_barang) ?>" <?php echo ($op == 'edit') ? 'readonly style="background:#eee;"' : ''; ?> required placeholder="Contoh: B001">
                            </div>
                            <div class="form-group">
                                <label>Nama Barang</label>
                                <input type="text" name="nama_barang" value="<?php echo htmlspecialchars($nama_barang) ?>" required placeholder="Contoh: Kertas HVS">
                            </div>
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori" required>
                                    <option value="">- Pilih Kategori -</option>
                                    <option value="Bahan Baku" <?php if ($kategori == 'Bahan Baku') echo 'selected' ?>>Bahan Baku</option>
                                    <option value="Barang Jadi" <?php if ($kategori == 'Barang Jadi') echo 'selected' ?>>Barang Jadi</option>
                                    <option value="ATK" <?php if ($kategori == 'ATK') echo 'selected' ?>>ATK</option>
                                    <option value="Lainnya" <?php if ($kategori == 'Lainnya') echo 'selected' ?>>Lainnya</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Satuan</label>
                                <input type="text" name="satuan" value="<?php echo htmlspecialchars($satuan) ?>" required placeholder="Pcs, Rim, Kg, Liter">
                            </div>
                            <div class="form-group">
                                <label>Stok Awal / Saat Ini</label>
                                <input type="number" name="stok" value="<?php echo htmlspecialchars($stok) ?>" required>
                                <small style="color: #666; font-size: 0.8em;">*Gunakan Transaksi untuk update rutin.</small>
                            </div>
                            <div class="form-group">
                                <label>Lokasi Rak</label>
                                <input type="text" name="lokasi_rak" value="<?php echo htmlspecialchars($lokasi_rak) ?>" required placeholder="Contoh: Rak A-1">
                            </div>
                            
                            <button type="submit" name="simpan" class="btn-submit"><?php echo ($op == 'edit') ? 'Update Data' : 'Simpan Data'; ?></button>
                            <?php if ($op == 'edit') { ?>
                                <a href="adminBarang.php" class="btn-reset" style="display:block; text-align:center; margin-top:10px; text-decoration:none; color:#666;">Batal Edit</a>
                            <?php } ?>
                        </form>
                    </div>

                    <div class="table-section">
                        <h3>Daftar Barang</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Barang</th>
                                        <th>Kategori</th>
                                        <th>Stok</th>
                                        <th>Lokasi</th>
                                        <th style="min-width: 140px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql2 = "SELECT * FROM barang ORDER BY kode_barang ASC";
                                    $q2   = mysqli_query($koneksi, $sql2);
                                    $urut = 1;
                                    while ($r2 = mysqli_fetch_array($q2)) {
                                        $id = $r2['kode_barang'];
                                    ?>
                                        <tr>
                                            <td><?php echo $urut++ ?></td>
                                            <td style="font-weight:bold;"><?php echo htmlspecialchars($id) ?></td>
                                            <td><?php echo htmlspecialchars($r2['nama_barang']) ?></td>
                                            <td><?php echo htmlspecialchars($r2['kategori']) ?></td>
                                            <td>
                                                <?php 
                                                if($r2['stok'] <= 10) { echo "<span style='color:red; font-weight:bold;'>{$r2['stok']} {$r2['satuan']}</span>"; } 
                                                else { echo "{$r2['stok']} {$r2['satuan']}"; }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($r2['lokasi_rak']) ?></td>
                                            <td>
                                                <a href="adminBarang.php?op=edit&id=<?php echo $id ?>" class="btn-action btn-edit">Edit</a>
                                                <a href="adminBarang.php?op=delete&id=<?php echo $id ?>" onclick="return confirm('Yakin mau delete data?')" class="btn-action btn-delete">Delete</a>
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
        <footer class="footer"><div class="footer-text"><span>&copy; 2025 SIFASTER</span></div></footer>
    </div>
</body>
</html>