<?php
session_start();
include 'koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Cek Role (Admin, Produksi, Sales)
$allowed_roles = ['Admin', 'Produksi', 'Sales'];
if (!in_array($_SESSION['role'], $allowed_roles)) {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$id_user_login = $_SESSION['id_user'];
$role_login    = $_SESSION['role'];

$no_transaksi = ""; $tanggal = date('Y-m-d\TH:i'); $kode_barang = ""; $qty = ""; $no_referensi = "";
$sukses = ""; $error = ""; $op = "";

if (isset($_GET['op'])) { $op = $_GET['op']; }

// --- LOGIKA APPROVAL (ADMIN ONLY) ---
if ($op == 'approve' && $role_login == 'Admin') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Ambil data transaksi pending
    $q_cek = mysqli_query($koneksi, "SELECT t.*, dt.kode_barang, dt.qty FROM transaksi t 
                                     JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi 
                                     WHERE t.no_transaksi = '$id' AND t.status = 'Pending'");
    $r_cek = mysqli_fetch_array($q_cek);

    if ($r_cek) {
        $kode_brg = $r_cek['kode_barang'];
        $qty_keluar = $r_cek['qty'];

        // Cek Stok Dulu!
        $q_stok = mysqli_query($koneksi, "SELECT stok FROM barang WHERE kode_barang = '$kode_brg'");
        $d_stok = mysqli_fetch_array($q_stok);
        $stok_saat_ini = $d_stok['stok'];

        if ($stok_saat_ini >= $qty_keluar) {
            // 1. Update Status -> Approved
            $q_update = mysqli_query($koneksi, "UPDATE transaksi SET status = 'Approved', approved_by = '$id_user_login' WHERE no_transaksi = '$id'");
            
            // 2. Kurangi Stok
            $q_kurang = mysqli_query($koneksi, "UPDATE barang SET stok = stok - $qty_keluar WHERE kode_barang = '$kode_brg'");

            if ($q_update && $q_kurang) {
                $sukses = "Request $id DISETUJUI. Stok berkurang.";
            } else {
                $error = "Gagal update database.";
            }
        } else {
            $error = "GAGAL APPROVE! Stok tidak cukup. (Sisa: $stok_saat_ini, Minta: $qty_keluar)";
        }
    }
}

// --- LOGIKA REJECT (ADMIN ONLY) ---
if ($op == 'reject' && $role_login == 'Admin') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $q_update = mysqli_query($koneksi, "UPDATE transaksi SET status = 'Rejected', approved_by = '$id_user_login' WHERE no_transaksi = '$id'");
    if ($q_update) {
        $sukses = "Request $id DITOLAK.";
    }
}

// --- LOGIKA DELETE (Hanya jika Pending) ---
if ($op == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $q_cek = mysqli_query($koneksi, "SELECT status FROM transaksi WHERE no_transaksi = '$id'");
    $r_cek = mysqli_fetch_array($q_cek);

    if ($r_cek['status'] == 'Pending') {
        mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE no_transaksi = '$id'");
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE no_transaksi = '$id'");
        $sukses = "Draft Request berhasil dihapus.";
    } else {
        $error = "Transaksi yang sudah diproses tidak bisa dihapus.";
    }
}

// --- LOGIKA SIMPAN (REQUEST BARU) ---
if (isset($_POST['simpan'])) {
    $kode_barang  = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $qty          = mysqli_real_escape_string($koneksi, $_POST['qty']);
    $tanggal      = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $no_referensi = mysqli_real_escape_string($koneksi, $_POST['no_referensi']);
    
    $no_transaksi = "TK-" . date('YmdHis');

    if ($kode_barang && $qty && $no_referensi) {
        // Insert Transaksi (Status: Pending)
        $sql1 = "INSERT INTO transaksi (no_transaksi, tanggal, tipe, no_referensi, status, request_by) 
                 VALUES ('$no_transaksi', '$tanggal', 'Keluar', '$no_referensi', 'Pending', '$id_user_login')";
        
        $q1 = mysqli_query($koneksi, $sql1);
        
        if ($q1) {
            $sql2 = "INSERT INTO detail_transaksi (no_transaksi, kode_barang, qty) VALUES ('$no_transaksi', '$kode_barang', '$qty')";
            $q2 = mysqli_query($koneksi, $sql2);
            
            if ($q2) {
                $sukses = "Request Barang Keluar berhasil dibuat! Menunggu Approval Admin.";
                $kode_barang = ""; $qty = ""; $no_referensi = "";
            } else {
                $error = "Gagal menyimpan detail.";
            }
        } else {
            $error = "Gagal menyimpan transaksi utama.";
        }
    } else {
        $error = "Lengkapi semua data.";
    }
}

// Label Dinamis berdasarkan Role
$label_ref = "No. Referensi";
$placeholder_ref = "Contoh: REF-001";
if ($role_login == 'Produksi') {
    $label_ref = "No. SPK (Surat Perintah Kerja)";
    $placeholder_ref = "Contoh: SPK-2023-001";
} elseif ($role_login == 'Sales') {
    $label_ref = "No. SO (Sales Order)";
    $placeholder_ref = "Contoh: SO-2023-001";
}

$pageTitle = 'Transaksi Keluar - SIFASTER';
$headerTitle = 'Transaksi Barang Keluar (Outbound)';
$headerDesc = 'Request Produksi (SPK) / Sales (SO)';
$activePage = 'keluar';
$bodyClass = 'crud-page';

include 'header.php';
?>
            <main class="article">
                <?php if ($error) { ?> <div class="alert alert-danger"><?php echo $error ?></div> <?php } ?>
                <?php if ($sukses) { ?> <div class="alert alert-success"><?php echo $sukses ?></div> <?php } ?>

                <div class="crud-wrapper">
                    <!-- FORM INPUT -->
                    <div class="form-section">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:1px solid #eee; padding-bottom:15px;">
                            <h3 style="border:none; margin:0; padding:0;">Form Request Barang Keluar</h3>
                            <span class="badge badge-keluar" style="font-size:0.9rem; padding:8px 15px;"><i class="fas fa-truck-loading"></i> Outbound</span>
                        </div>

                        <form action="" method="POST">
                            <div class="form-grid">
                                
                                <!-- Group 1: Info Dokumen -->
                                <div class="form-box-keluar">
                                    <div class="form-box-title"><i class="fas fa-file-invoice"></i> Informasi Dokumen</div>
                                    <div class="form-group">
                                        <label>Tanggal Request</label>
                                        <input type="datetime-local" name="tanggal" value="<?php echo $tanggal; ?>" required style="background:white; width: 100%;">
                                    </div>
                                    <div class="form-group">
                                        <label><?php echo $label_ref; ?></label>
                                        <input type="text" name="no_referensi" placeholder="<?php echo $placeholder_ref; ?>" value="<?php echo $no_referensi; ?>" required style="background:white; width: 100%;">
                                    </div>
                                </div>

                                <!-- Group 2: Detail Barang -->
                                <div class="form-box-keluar">
                                    <div class="form-box-title"><i class="fas fa-box-open"></i> Detail Barang</div>
                                    <div class="form-group">
                                        <label>Pilih Barang</label>
                                        <select name="kode_barang" required style="background:white; width: 100%;">
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
                                        <label>Jumlah (Qty)</label>
                                        <input type="number" name="qty" min="1" placeholder="Contoh: 10" required style="background:white; width: 100%;">
                                    </div>
                                </div>
                            </div>
                            
                            <div style="margin-top:25px; text-align:right; border-top:1px solid #eee; padding-top:20px;">
                                <button type="submit" name="simpan" class="btn-submit btn-danger-gradient">
                                    <i class="fas fa-paper-plane"></i> Kirim Request Barang Keluar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TABEL DATA -->
                    <div class="table-section">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                            <h3 style="border:none; margin:0; padding:0;">Daftar Request Keluar (SPK/SO)</h3>
                            <div style="font-size:0.85rem; color:#64748b;">
                                <i class="fas fa-info-circle"></i> Menampilkan data terbaru
                            </div>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table-keluar">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="15%">No Ref</th>
                                        <th width="20%">Barang</th>
                                        <th width="10%">Qty</th>
                                        <th width="10%">Status</th>
                                        <th width="10%">Request By</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT t.*, dt.qty, b.nama_barang, b.kode_barang, b.satuan, u.username as requestor 
                                            FROM transaksi t
                                            JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi
                                            JOIN barang b ON dt.kode_barang = b.kode_barang
                                            JOIN users u ON t.request_by = u.id_user
                                            WHERE t.tipe = 'Keluar'
                                            ORDER BY t.tanggal DESC";
                                    
                                    $q_tampil = mysqli_query($koneksi, $sql);
                                    $no = 1;
                                    while ($r = mysqli_fetch_array($q_tampil)) {
                                        $statusClass = strtolower($r['status']);
                                        ?>
                                        <tr>
                                            <td style="text-align:center;"><?php echo $no++; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($r['tanggal'])); ?></td>
                                            <td style="font-weight:600; color:#b91c1c;"><?php echo htmlspecialchars($r['no_referensi']); ?></td>
                                            <td>
                                                <div style="font-weight:600;"><?php echo htmlspecialchars($r['nama_barang']); ?></div>
                                                <div style="font-size:0.8rem; color:#64748b;"><?php echo $r['kode_barang']; ?></div>
                                            </td>
                                            <td style="font-weight:bold;"><?php echo $r['qty'] . ' ' . $r['satuan']; ?></td>
                                            <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo $r['status']; ?></span></td>
                                            <td>
                                                <div style="display:flex; align-items:center; gap:5px;">
                                                    <i class="fas fa-user-circle" style="color:#cbd5e1;"></i> 
                                                    <?php echo htmlspecialchars($r['requestor']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($r['status'] == 'Pending'): ?>
                                                    <div style="display:flex; gap:5px;">
                                                    <?php if ($role_login == 'Admin'): ?>
                                                        <a href="adminTransaksiKeluar.php?op=approve&id=<?php echo $r['no_transaksi']; ?>" class="btn-edit" style="background:#10b981;" onclick="return confirm('Setujui Request ini? Stok akan berkurang.')" title="Approve"><i class="fas fa-check"></i></a>
                                                        <a href="adminTransaksiKeluar.php?op=reject&id=<?php echo $r['no_transaksi']; ?>" class="btn-delete" onclick="return confirm('Tolak Request ini?')" title="Reject"><i class="fas fa-times"></i></a>
                                                    <?php endif; ?>

                                                    <?php if ($role_login == 'Admin' || $id_user_login == $r['request_by']): ?>
                                                        <a href="adminTransaksiKeluar.php?op=delete&id=<?php echo $r['no_transaksi']; ?>" class="btn-delete" style="background:#64748b;" onclick="return confirm('Hapus draft ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                                                    <?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <?php if ($r['status'] == 'Approved'): ?>
                                                        <a href="cetak_transaksi.php?id=<?php echo $r['no_transaksi']; ?>" target="_blank" class="btn-edit" style="background:#3b82f6;" title="Cetak Surat Jalan"><i class="fas fa-print"></i></a>
                                                    <?php endif; ?>
                                                    <span style="color:#94a3b8; font-size:0.8rem; font-style:italic;"><i class="fas fa-lock"></i> Locked</span>
                                                <?php endif; ?>
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
            </main>
<?php include 'footer.php'; ?>
