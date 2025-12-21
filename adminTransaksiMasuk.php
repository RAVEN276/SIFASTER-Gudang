<?php
session_start();
include 'koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Cek Role (Hanya Admin, Purchasing, & Produksi yang boleh akses)
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Purchasing' && $_SESSION['role'] !== 'Produksi') {
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
        $qty_masuk = $r_cek['qty'];

        // 1. Update Status Transaksi -> Approved
        $q_update = mysqli_query($koneksi, "UPDATE transaksi SET status = 'Approved', approved_by = '$id_user_login' WHERE no_transaksi = '$id'");
        
        // 2. Tambah Stok Barang
        $q_stok = mysqli_query($koneksi, "UPDATE barang SET stok = stok + $qty_masuk WHERE kode_barang = '$kode_brg'");

        if ($q_update && $q_stok) {
            $sukses = "Request PO $id BERHASIL DISETUJUI. Stok bertambah.";
        } else {
            $error = "Gagal melakukan approval.";
        }
    }
}

// --- LOGIKA REJECT (ADMIN ONLY) ---
if ($op == 'reject' && $role_login == 'Admin') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    // Cukup update status jadi Rejected, stok tidak berubah
    $q_update = mysqli_query($koneksi, "UPDATE transaksi SET status = 'Rejected', approved_by = '$id_user_login' WHERE no_transaksi = '$id'");
    if ($q_update) {
        $sukses = "Request PO $id DITOLAK.";
    }
}

// --- LOGIKA DELETE (Hanya jika Pending) ---
if ($op == 'delete') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    
    // Cek status dulu
    $q_cek = mysqli_query($koneksi, "SELECT status FROM transaksi WHERE no_transaksi = '$id'");
    $r_cek = mysqli_fetch_array($q_cek);

    if ($r_cek['status'] == 'Pending') {
        // Hapus aman karena belum approved (stok belum nambah)
        mysqli_query($koneksi, "DELETE FROM detail_transaksi WHERE no_transaksi = '$id'");
        mysqli_query($koneksi, "DELETE FROM transaksi WHERE no_transaksi = '$id'");
        $sukses = "Draft Transaksi berhasil dihapus.";
    } else {
        $error = "Transaksi yang sudah diproses (Approved/Rejected) tidak bisa dihapus! Hubungi IT jika mendesak.";
    }
}

// --- LOGIKA SIMPAN (REQUEST BARU) ---
if (isset($_POST['simpan'])) {
    $kode_barang  = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $qty          = mysqli_real_escape_string($koneksi, $_POST['qty']);
    $tanggal      = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $no_referensi = mysqli_real_escape_string($koneksi, $_POST['no_referensi']);
    
    // Generate No Transaksi: TM-YYYYMMDD-HIS
    $no_transaksi = "TM-" . date('YmdHis');

    if ($kode_barang && $qty && $no_referensi) {
        // Insert Transaksi (Status Default: Pending)
        // Jika Admin yang input, apakah langsung Approved? 
        // Sesuai request workflow: "Request -> Approve". Jadi Admin pun tetap bikin Request dulu biar tercatat, lalu Approve sendiri (atau auto-approve).
        // Untuk konsistensi, kita buat semua jadi 'Pending' dulu. Admin tinggal klik Approve di tabel.
        
        $sql1 = "INSERT INTO transaksi (no_transaksi, tanggal, tipe, no_referensi, status, request_by) 
                 VALUES ('$no_transaksi', '$tanggal', 'Masuk', '$no_referensi', 'Pending', '$id_user_login')";
        
        $q1 = mysqli_query($koneksi, $sql1);
        
        if ($q1) {
            $sql2 = "INSERT INTO detail_transaksi (no_transaksi, kode_barang, qty) VALUES ('$no_transaksi', '$kode_barang', '$qty')";
            $q2 = mysqli_query($koneksi, $sql2);
            
            if ($q2) {
                $sukses = "Request PO berhasil dibuat! Menunggu Approval Admin.";
                $kode_barang = ""; $qty = ""; $no_referensi = "";
            } else {
                $error = "Gagal menyimpan detail transaksi.";
            }
        } else {
            $error = "Gagal menyimpan transaksi utama. " . mysqli_error($koneksi);
        }
    } else {
        $error = "Silakan lengkapi data (Barang, Qty, No PO).";
    }
}

$pageTitle = 'Transaksi Masuk - SIFASTER';
$headerTitle = 'Purchasing Order (Barang Masuk)';
$headerDesc = 'Request Pembelian Bahan Baku';
$label_ref = "No. PO (Purchase Order)";
$placeholder_ref = "Contoh: PO-2023-001";
$label_barang = "Pilih Bahan Baku";

if ($role_login == 'Produksi') {
    $headerTitle = 'Penerimaan Barang Jadi';
    $headerDesc = 'Setoran Barang Jadi dari Produksi';
    $label_ref = "No. Bukti Setoran";
    $placeholder_ref = "Contoh: BUKTI-2023-001";
    $label_barang = "Pilih Barang Jadi";
}

$activePage = 'masuk';
$bodyClass = 'crud-page';

include 'header.php';
?>
            <main class="article">
                <?php if ($error) { ?> <div class="alert alert-danger"><?php echo $error ?></div> <?php } ?>
                <?php if ($sukses) { ?> <div class="alert alert-success"><?php echo $sukses ?></div> <?php } ?>

                <div class="crud-wrapper">
                    <!-- FORM INPUT (Bisa Admin / Purchasing / Produksi) -->
                    <div class="form-section">
                        <h3>Form Input Transaksi Masuk</h3>
                        <form action="" method="POST">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Tanggal Transaksi</label>
                                    <input type="datetime-local" name="tanggal" value="<?php echo $tanggal; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $label_ref; ?></label>
                                    <input type="text" name="no_referensi" placeholder="<?php echo $placeholder_ref; ?>" value="<?php echo $no_referensi; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label><?php echo $label_barang; ?></label>
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
                                    <label>Jumlah (Qty)</label>
                                    <input type="number" name="qty" min="1" placeholder="Contoh: 100" required>
                                </div>
                            </div>
                            <button type="submit" name="simpan" class="btn-submit">Kirim Request</button>
                        </form>
                    </div>

                    <!-- TABEL DATA -->
                    <div class="table-section">
                        <h3>Daftar Request PO (Masuk)</h3>
                        <div class="table-responsive">
                            <table>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tanggal</th>
                                        <th>No PO</th>
                                        <th>Barang</th>
                                        <th>Qty</th>
                                        <th>Status</th>
                                        <th>Request By</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Query Join Lengkap
                                    $sql = "SELECT t.*, dt.qty, b.nama_barang, b.satuan, u.username as requestor 
                                            FROM transaksi t
                                            JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi
                                            JOIN barang b ON dt.kode_barang = b.kode_barang
                                            JOIN users u ON t.request_by = u.id_user
                                            WHERE t.tipe = 'Masuk'
                                            ORDER BY t.tanggal DESC";
                                    
                                    $q_tampil = mysqli_query($koneksi, $sql);
                                    $no = 1;
                                    while ($r = mysqli_fetch_array($q_tampil)) {
                                        $statusClass = strtolower($r['status']); // pending, approved, rejected
                                        ?>
                                        <tr>
                                            <td><?php echo $no++; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($r['tanggal'])); ?></td>
                                            <td><?php echo htmlspecialchars($r['no_referensi']); ?></td>
                                            <td><?php echo htmlspecialchars($r['nama_barang']); ?></td>
                                            <td><?php echo $r['qty'] . ' ' . $r['satuan']; ?></td>
                                            <td><span class="badge badge-<?php echo $statusClass; ?>"><?php echo $r['status']; ?></span></td>
                                            <td><?php echo htmlspecialchars($r['requestor']); ?></td>
                                            <td>
                                                <?php if ($r['status'] == 'Pending'): ?>
                                                    
                                                    <!-- Tombol Approval (Hanya Admin) -->
                                                    <?php if ($role_login == 'Admin'): ?>
                                                        <button type="button" class="btn-edit" style="background:#10b981; border:none; cursor:pointer;" onclick="confirmAction('approve', '<?php echo $r['no_transaksi']; ?>')" title="Approve">Approve</button>
                                                        <button type="button" class="btn-delete" style="border:none; cursor:pointer;" onclick="confirmAction('reject', '<?php echo $r['no_transaksi']; ?>')" title="Reject">Reject</button>
                                                    <?php endif; ?>

                                                    <!-- Tombol Hapus (Admin atau Pembuat Request) -->
                                                    <?php if ($role_login == 'Admin' || $id_user_login == $r['request_by']): ?>
                                                        <button type="button" class="btn-delete" style="background:#64748b; border:none; cursor:pointer;" onclick="confirmAction('delete', '<?php echo $r['no_transaksi']; ?>')" title="Hapus">Hapus</button>
                                                    <?php endif; ?>

                                                <?php else: ?>
                                                    <!-- Jika sudah Approved, Tampilkan Tombol Cetak -->
                                                    <?php if ($r['status'] == 'Approved'): ?>
                                                        <a href="cetak_transaksi.php?id=<?php echo $r['no_transaksi']; ?>" target="_blank" class="btn-edit" style="background:#3b82f6;" title="Cetak Bukti"><i class="fas fa-print"></i></a>
                                                    <?php endif; ?>
                                                    
                                                    <span style="color:#aaa; font-size:0.8rem; margin-left:5px;">Locked</span>
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

<script>
function confirmAction(action, id) {
    let title, text, icon, confirmBtnColor, confirmBtnText, url;

    if (action === 'approve') {
        title = 'Setujui PO?';
        text = 'Stok barang akan bertambah otomatis!';
        icon = 'warning';
        confirmBtnColor = '#10b981';
        confirmBtnText = 'Ya, Setujui!';
        url = 'adminTransaksiMasuk.php?op=approve&id=' + id;
    } else if (action === 'reject') {
        title = 'Tolak PO?';
        text = 'Permintaan ini akan ditandai sebagai Rejected.';
        icon = 'warning';
        confirmBtnColor = '#ef4444';
        confirmBtnText = 'Ya, Tolak!';
        url = 'adminTransaksiMasuk.php?op=reject&id=' + id;
    } else if (action === 'delete') {
        title = 'Hapus Draft?';
        text = 'Data yang dihapus tidak dapat dikembalikan!';
        icon = 'warning';
        confirmBtnColor = '#64748b';
        confirmBtnText = 'Ya, Hapus!';
        url = 'adminTransaksiMasuk.php?op=delete&id=' + id;
    }

    Swal.fire({
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonColor: confirmBtnColor,
        cancelButtonColor: '#d33',
        confirmButtonText: confirmBtnText,
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = url;
        }
    });
}
</script>

