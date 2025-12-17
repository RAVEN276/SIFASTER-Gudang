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
        $error  = "Gagal melakukan delete data";
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
        /* Tambahan CSS khusus halaman CRUD */
        .crud-container {
            display: flex;
            gap: 20px;
        }
        .form-card {
            flex: 1;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
        }
        .table-card {
            flex: 2;
            background: #fff;
            padding: 20px;
            border: 1px solid #ccc;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        .alert-success { background-color: #d4edda; color: #155724; }
        .alert-danger { background-color: #f8d7da; color: #721c24; }
        .btn-action {
            text-decoration: none;
            padding: 5px 10px;
            color: white;
            border-radius: 3px;
            font-size: 0.8rem;
        }
        .btn-edit { background-color: #ffc107; color: #000; }
        .btn-delete { background-color: #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">SIFASTER</div>
            <div class="header-text">
                <h1>Kelola Master Data Barang</h1>
                <p>Admin: <?php echo $_SESSION['username']; ?></p>
            </div>
        </header>

        <div class="content-wrapper">
            <!-- Navigasi Sederhana -->
            <nav class="nav" style="width: 200px;">
                <ul>
                    <li><a href="index.php">&laquo; Kembali ke Dashboard</a></li>
                    <li><a href="adminBarang.php" class="active">Data Barang</a></li>
                    <!-- Nanti tambah adminTransaksi.php dll -->
                </ul>
            </nav>

            <div style="flex: 1;">
                <?php if ($error) { ?>
                    <div class="alert alert-danger"><?php echo $error ?></div>
                <?php } ?>
                <?php if ($sukses) { ?>
                    <div class="alert alert-success"><?php echo $sukses ?></div>
                <?php } ?>

                <div class="crud-container">
                    <!-- FORM INPUT / EDIT -->
                    <div class="form-card">
                        <h3>Form Data Barang</h3>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Kode Barang</label>
                                <input type="text" name="kode_barang" value="<?php echo $kode_barang ?>" <?php echo ($op == 'edit') ? 'readonly' : ''; ?> required>
                            </div>
                            <div class="form-group">
                                <label>Nama Barang</label>
                                <input type="text" name="nama_barang" value="<?php echo $nama_barang ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Kategori</label>
                                <select name="kategori">
                                    <option value="">- Pilih -</option>
                                    <option value="Bahan Baku" <?php if ($kategori == 'Bahan Baku') echo 'selected' ?>>Bahan Baku</option>
                                    <option value="Barang Jadi" <?php if ($kategori == 'Barang Jadi') echo 'selected' ?>>Barang Jadi</option>
                                    <option value="ATK" <?php if ($kategori == 'ATK') echo 'selected' ?>>ATK</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Satuan</label>
                                <input type="text" name="satuan" value="<?php echo $satuan ?>" placeholder="Pcs/Kg/Rim" required>
                            </div>
                            <div class="form-group">
                                <label>Stok Awal</label>
                                <input type="number" name="stok" value="<?php echo $stok ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Lokasi Rak</label>
                                <input type="text" name="lokasi_rak" value="<?php echo $lokasi_rak ?>" placeholder="Contoh: A-01" required>
                            </div>
                            <button type="submit" name="simpan" class="btn-login">Simpan Data</button>
                            <?php if($op == 'edit'): ?>
                                <a href="adminBarang.php" style="display:block; text-align:center; margin-top:10px;">Batal Edit</a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <!-- TABEL DATA -->
                    <div class="table-card">
                        <h3>Daftar Barang</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode</th>
                                    <th>Nama</th>
                                    <th>Kategori</th>
                                    <th>Stok</th>
                                    <th>Rak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql2   = "SELECT * FROM barang ORDER BY kode_barang ASC";
                                $q2     = mysqli_query($koneksi, $sql2);
                                $urut   = 1;
                                while ($r2 = mysqli_fetch_array($q2)) {
                                    $kode   = $r2['kode_barang'];
                                    $nama   = $r2['nama_barang'];
                                    $kat    = $r2['kategori'];
                                    $stok   = $r2['stok'];
                                    $sat    = $r2['satuan'];
                                    $rak    = $r2['lokasi_rak'];
                                ?>
                                    <tr>
                                        <td><?php echo $urut++ ?></td>
                                        <td><?php echo $kode ?></td>
                                        <td><?php echo $nama ?></td>
                                        <td><?php echo $kat ?></td>
                                        <td><?php echo $stok . ' ' . $sat ?></td>
                                        <td><?php echo $rak ?></td>
                                        <td>
                                            <a href="adminBarang.php?op=edit&id=<?php echo $kode ?>" class="btn-action btn-edit">Edit</a>
                                            <a href="adminBarang.php?op=delete&id=<?php echo $kode ?>" onclick="return confirm('Yakin mau delete data?')" class="btn-action btn-delete">Delete</a>
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
            <div class="footer-text">
                <span>&copy; 2025 SIFASTER</span>
            </div>
        </footer>
    </div>
</body>
</html>
