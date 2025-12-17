<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Inisialisasi Variabel untuk Form
$kode_barang = "";
$nama_barang = "";
$kategori    = "";
$satuan      = "";
$stok        = 0;
$lokasi_rak  = "";
$sukses      = "";
$error       = "";
$op          = ""; // Operasi (edit/delete)

// Tangkap Parameter GET (untuk Edit/Delete)
if (isset($_GET['op'])) {
    $op = $_GET['op'];
}

// --- LOGIKA DELETE ---
if ($op == 'delete') {
    $id = $_GET['id'];
    $sql1 = "DELETE FROM barang WHERE kode_barang = '$id'";
    $q1   = mysqli_query($koneksi, $sql1);
    if ($q1) {
        $sukses = "Berhasil hapus data";
    } else {
        $error  = "Gagal hapus data (Mungkin barang sudah digunakan di transaksi). Error: " . mysqli_error($koneksi);
    }
}

// --- LOGIKA EDIT (AMBIL DATA) ---
if ($op == 'edit') {
    $id          = $_GET['id'];
    $sql1        = "SELECT * FROM barang WHERE kode_barang = '$id'";
    $q1          = mysqli_query($koneksi, $sql1);
    $r1          = mysqli_fetch_array($q1);
    if ($r1) {
        $kode_barang = $r1['kode_barang'];
        $nama_barang = $r1['nama_barang'];
        $kategori    = $r1['kategori'];
        $satuan      = $r1['satuan'];
        $stok        = $r1['stok'];
        $lokasi_rak  = $r1['lokasi_rak'];
    } else {
        $error = "Data tidak ditemukan";
    }
}

// --- LOGIKA SIMPAN (CREATE / UPDATE) ---
if (isset($_POST['simpan'])) {
    $kode_barang = $_POST['kode_barang'];
    $nama_barang = $_POST['nama_barang'];
    $kategori    = $_POST['kategori'];
    $satuan      = $_POST['satuan'];
    $stok        = $_POST['stok'];
    $lokasi_rak  = $_POST['lokasi_rak'];

    if ($kode_barang && $nama_barang && $kategori && $satuan && $lokasi_rak) {
        if ($op == 'edit') { // Update
            $sql1 = "UPDATE barang SET nama_barang='$nama_barang', kategori='$kategori', satuan='$satuan', stok='$stok', lokasi_rak='$lokasi_rak' WHERE kode_barang='$kode_barang'";
            $q1   = mysqli_query($koneksi, $sql1);
            if ($q1) {
                $sukses = "Data berhasil diupdate";
            } else {
                $error  = "Data gagal diupdate";
            }
        } else { // Insert
            // Cek duplikat
            $cek = mysqli_query($koneksi, "SELECT * FROM barang WHERE kode_barang='$kode_barang'");
            if (mysqli_num_rows($cek) > 0) {
                $error = "Kode Barang sudah ada!";
            } else {
                $sql1 = "INSERT INTO barang(kode_barang, nama_barang, kategori, satuan, stok, lokasi_rak) VALUES ('$kode_barang','$nama_barang','$kategori','$satuan','$stok','$lokasi_rak')";
                $q1   = mysqli_query($koneksi, $sql1);
                if ($q1) {
                    $sukses = "Berhasil memasukkan data baru";
                    // Reset form
                    $kode_barang = ""; $nama_barang = ""; $kategori = ""; $satuan = ""; $stok = 0; $lokasi_rak = "";
                } else {
                    $error  = "Gagal memasukkan data";
                }
            }
        }
    } else {
        $error = "Silakan masukkan semua data";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Barang - SIFASTER</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Layout Khusus CRUD */
        .crud-wrapper {
            display: flex;
            gap: 20px;
            width: 100%;
        }
        .form-section {
            flex: 1;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            height: fit-content;
        }
        .table-section {
            flex: 2;
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        
        /* Form Styles */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: var(--primary-color); }
        .form-group input, .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-weight: bold;
        }
        .btn-submit:hover { background-color: var(--secondary-color); }
        .btn-reset {
            background-color: #95a5a6;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: var(--primary-color); color: white; }
        tr:hover { background-color: #f1f1f1; }
        
        /* Action Buttons */
        .btn-action { padding: 5px 10px; border-radius: 3px; text-decoration: none; color: white; font-size: 0.85em; margin-right: 5px; }
        .btn-edit { background-color: var(--accent-color); }
        .btn-delete { background-color: var(--danger-color); }
        
        /* Alerts */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">SIFASTER</div>
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

            <div style="width: 100%;">
                <?php if ($error) { ?>
                    <div class="alert alert-danger"><?php echo $error ?></div>
                <?php } ?>
                <?php if ($sukses) { ?>
                    <div class="alert alert-success"><?php echo $sukses ?></div>
                <?php } ?>

                <div class="crud-wrapper">
                    <!-- FORM SECTION -->
                    <div class="form-section">
                        <h3 style="margin-bottom: 15px; border-bottom: 2px solid var(--accent-color); padding-bottom: 5px;">
                            <?php echo ($op == 'edit') ? 'Edit Barang' : 'Tambah Barang Baru'; ?>
                        </h3>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Kode Barang (SKU)</label>
                                <input type="text" name="kode_barang" value="<?php echo $kode_barang ?>" <?php echo ($op == 'edit') ? 'readonly style="background:#eee;"' : ''; ?> required placeholder="Contoh: B001">
                            </div>
                            <div class="form-group">
                                <label>Nama Barang</label>
                                <input type="text" name="nama_barang" value="<?php echo $nama_barang ?>" required placeholder="Contoh: Kertas HVS">
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
                                <input type="text" name="satuan" value="<?php echo $satuan ?>" required placeholder="Pcs, Rim, Kg, Liter">
                            </div>
                            <div class="form-group">
                                <label>Stok Awal / Saat Ini</label>
                                <input type="number" name="stok" value="<?php echo $stok ?>" required>
                                <small style="color: #666;">*Gunakan Transaksi Masuk/Keluar untuk update rutin.</small>
                            </div>
                            <div class="form-group">
                                <label>Lokasi Rak</label>
                                <input type="text" name="lokasi_rak" value="<?php echo $lokasi_rak ?>" required placeholder="Contoh: Rak A-1">
                            </div>
                            
                            <button type="submit" name="simpan" class="btn-submit">
                                <?php echo ($op == 'edit') ? 'Update Data' : 'Simpan Data'; ?>
                            </button>
                            <?php if ($op == 'edit') { ?>
                                <a href="adminBarang.php" class="btn-reset">Batal Edit</a>
                            <?php } ?>
                        </form>
                    </div>

                    <!-- TABLE SECTION -->
                    <div class="table-section">
                        <h3 style="margin-bottom: 15px; border-bottom: 2px solid var(--accent-color); padding-bottom: 5px;">Daftar Barang</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Lokasi</th>
                                    <th style="width: 150px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql2   = "SELECT * FROM barang ORDER BY kode_barang ASC";
                                $q2     = mysqli_query($koneksi, $sql2);
                                $urut   = 1;
                                while ($r2 = mysqli_fetch_array($q2)) {
                                    $id         = $r2['kode_barang'];
                                    $nama       = $r2['nama_barang'];
                                    $kat        = $r2['kategori'];
                                    $sat        = $r2['satuan'];
                                    $stk        = $r2['stok'];
                                    $lok        = $r2['lokasi_rak'];
                                ?>
                                    <tr>
                                        <td><?php echo $urut++ ?></td>
                                        <td style="font-weight:bold;"><?php echo $id ?></td>
                                        <td><?php echo $nama ?></td>
                                        <td><?php echo $kat ?></td>
                                        <td>
                                            <?php 
                                            if($stk <= 10) {
                                                echo "<span style='color:red; font-weight:bold;'>$stk $sat</span>";
                                            } else {
                                                echo "$stk $sat";
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $lok ?></td>
                                        <td>
                                            <a href="adminBarang.php?op=edit&id=<?php echo $id ?>" class="btn-action btn-edit">Edit</a>
                                            <a href="adminBarang.php?op=delete&id=<?php echo $id ?>" onclick="return confirm('Yakin mau delete data?')" class="btn-action btn-delete">Delete</a>
                                        </td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
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
