<?php
session_start();
include 'koneksi.php';

// Cek Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// Ambil ID Admin dari session username
$username = $_SESSION['username'];
$q_admin = mysqli_query($koneksi, "SELECT id_admin FROM admin WHERE username = '$username'");
$d_admin = mysqli_fetch_array($q_admin);
$id_admin_login = $d_admin['id_admin'];

// Inisialisasi Variabel
$no_transaksi = "";
$tanggal      = date('Y-m-d H:i:s');
$kode_barang  = "";
$qty          = "";
$sukses       = "";
$error        = "";
$op           = "";

if (isset($_GET['op'])) {
    $op = $_GET['op'];
}

// --- LOGIKA DELETE (VOID TRANSAKSI) ---
if ($op == 'delete') {
    $id = $_GET['id'];
    
    // 1. Ambil data lama untuk kembalikan stok
    $q_cek = mysqli_query($koneksi, "SELECT * FROM detail_transaksi WHERE no_transaksi = '$id'");
    $r_cek = mysqli_fetch_array($q_cek);
    
    if ($r_cek) {
        $kode_brg_old = $r_cek['kode_barang'];
        $qty_old      = $r_cek['qty'];
        
        // 2. Kurangi Stok Barang (Karena ini transaksi masuk yang dihapus)
        mysqli_query($koneksi, "UPDATE barang SET stok = stok - $qty_old WHERE kode_barang = '$kode_brg_old'");
        
        // 3. Hapus Data
        mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE no_transaksi = '$id'");
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE no_transaksi = '$id'");
        
        $sukses = "Transaksi berhasil dihapus dan stok dikembalikan.";
    } else {
        $error = "Data transaksi tidak ditemukan.";
    }
}

// --- LOGIKA SIMPAN (INSERT) ---
// Catatan: Untuk kesederhanaan prototype, fitur Edit ditiadakan di transaksi agar integritas stok terjaga. 
// Disarankan Delete lalu Input ulang jika salah.
if (isset($_POST['simpan'])) {
    $kode_barang = $_POST['kode_barang'];
    $qty         = $_POST['qty'];
    $tanggal     = $_POST['tanggal']; // Format YYYY-MM-DDTHH:MM
    
    // Generate No Transaksi Otomatis: TM-YYYYMMDD-HIS
    $no_transaksi = "TM-" . date('YmdHis');

    if ($kode_barang && $qty) {
        // 1. Insert Header Transaksi
        $sql1 = "INSERT INTO transaksi (no_transaksi, tanggal, tipe, id_admin) VALUES ('$no_transaksi', '$tanggal', 'Masuk', '$id_admin_login')";
        $q1   = mysqli_query($koneksi, $sql1);

        if ($q1) {
            // 2. Insert Detail Transaksi
            $sql2 = "INSERT INTO detail_transaksi (no_transaksi, kode_barang, qty) VALUES ('$no_transaksi', '$kode_barang', '$qty')";
            $q2   = mysqli_query($koneksi, $sql2);

            // 3. Update Stok Barang (+)
            $sql3 = "UPDATE barang SET stok = stok + $qty WHERE kode_barang = '$kode_barang'";
            $q3   = mysqli_query($koneksi, $sql3);

            if ($q2 && $q3) {
                $sukses = "Transaksi Masuk berhasil disimpan. Stok bertambah.";
                $kode_barang = ""; $qty = ""; // Reset form
            } else {
                $error = "Gagal menyimpan detail transaksi.";
            }
        } else {
            $error = "Gagal menyimpan transaksi utama.";
        }
    } else {
        $error = "Silakan lengkapi data.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Masuk - SIFASTER</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Layout Khusus CRUD (Konsisten dengan adminBarang.php) */
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

        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background-color: var(--primary-color); color: white; }
        tr:hover { background-color: #f1f1f1; }
        
        /* Alerts */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .btn-delete { background-color: #dc3545; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 0.8rem;}
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">SIFASTER</div>
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
                    <li><a href="adminTransaksiMasuk.php" class="active">Transaksi Masuk (Inbound)</a></li>
                    <li><a href="adminTransaksiKeluar.php">Transaksi Keluar (Outbound)</a></li>
                    <li><a href="laporan.php">Laporan & Monitoring</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>

            <div style="width: 100%;">
                <?php if ($error) { ?> <div class="alert alert-danger"><?php echo $error ?></div> <?php } ?>
                <?php if ($sukses) { ?> <div class="alert alert-success"><?php echo $sukses ?></div> <?php } ?>

                <div class="crud-wrapper">
                    <!-- FORM INPUT -->
                    <div class="form-section">
                        <h3 style="margin-bottom: 15px; border-bottom: 2px solid var(--accent-color); padding-bottom: 5px;">Input Barang Masuk</h3>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Tanggal</label>
                                <input type="datetime-local" name="tanggal" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
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

                    <!-- TABEL DATA -->
                    <div class="table-section">
                        <h3 style="margin-bottom: 15px; border-bottom: 2px solid var(--accent-color); padding-bottom: 5px;">Riwayat Barang Masuk</h3>
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
                                        <td><?php echo $row['nama_barang'] ?></td>
                                        <td style="color: green; font-weight: bold;">+<?php echo $row['qty'] ?></td>
                                        <td><?php echo $row['username'] ?></td>
                                        <td>
                                            <a href="adminTransaksiMasuk.php?op=delete&id=<?php echo $row['no_transaksi'] ?>" onclick="return confirm('Hapus transaksi ini? Stok akan dikurangi kembali.')" class="btn-delete">Batal/Hapus</a>
                                        </td>
                                    </tr>
                                <?php } ?>
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
