<?php
session_start();
include 'koneksi.php';

// 1. Cek Login
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

// 2. Cek Role (Admin, Sales, Produksi)
if ($_SESSION['role'] !== 'Admin' && $_SESSION['role'] !== 'Sales' && $_SESSION['role'] !== 'Produksi') {
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
        $qty_retur = $r_cek['qty'];

        // 1. Update Status Transaksi -> Approved
        $q_update = mysqli_query($koneksi, "UPDATE transaksi SET status = 'Approved', approved_by = '$id_user_login' WHERE no_transaksi = '$id'");
        
        // 2. Tambah Stok Barang (Retur Masuk)
        $q_stok = mysqli_query($koneksi, "UPDATE barang SET stok = stok + $qty_retur WHERE kode_barang = '$kode_brg'");

        if ($q_update && $q_stok) {
            $sukses = "Retur $id BERHASIL DISETUJUI. Stok bertambah.";
        } else {
            $error = "Gagal melakukan approval.";
        }
    }
}

// --- LOGIKA REJECT (ADMIN ONLY) ---
if ($op == 'reject' && $role_login == 'Admin') {
    $id = mysqli_real_escape_string($koneksi, $_GET['id']);
    $q_update = mysqli_query($koneksi, "UPDATE transaksi SET status = 'Rejected', approved_by = '$id_user_login' WHERE no_transaksi = '$id'");
    if ($q_update) {
        $sukses = "Retur $id DITOLAK.";
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
        $sukses = "Draft Retur berhasil dihapus.";
    } else {
        $error = "Transaksi yang sudah diproses tidak bisa dihapus!";
    }
}

// --- LOGIKA SIMPAN (REQUEST BARU) - NON ADMIN ---
if (isset($_POST['simpan']) && $role_login !== 'Admin') {
    $kode_barang  = mysqli_real_escape_string($koneksi, $_POST['kode_barang']);
    $qty          = mysqli_real_escape_string($koneksi, $_POST['qty']);
    $tanggal      = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $no_referensi = mysqli_real_escape_string($koneksi, $_POST['no_referensi']);
    
    // Generate No Transaksi: TR-YYYYMMDD-HIS
    $no_transaksi = "TR-" . date('YmdHis');

    if ($kode_barang && $qty && $no_referensi) {
        // Explicitly set status to Pending and approved_by to NULL
        $sql1 = "INSERT INTO transaksi (no_transaksi, tanggal, tipe, no_referensi, status, request_by, approved_by) 
                 VALUES ('$no_transaksi', '$tanggal', 'Retur', '$no_referensi', 'Pending', '$id_user_login', NULL)";
        
        $q1 = mysqli_query($koneksi, $sql1);
        
        if ($q1) {
            $sql2 = "INSERT INTO detail_transaksi (no_transaksi, kode_barang, qty) 
                     VALUES ('$no_transaksi', '$kode_barang', '$qty')";
            $q2 = mysqli_query($koneksi, $sql2);
            
            if ($q2) {
                $sukses = "Request Retur Berhasil diajukan. Menunggu Approval Admin.";
                // Optional: Clear POST data to prevent resubmission on refresh
                // header("Location: adminRetur.php?status=success"); 
            } else {
                $error = "Gagal simpan detail transaksi.";
            }
        } else {
            $error = "Gagal simpan transaksi utama.";
        }
    } else {
        $error = "Semua data wajib diisi!";
    }
}

$pageTitle = 'Retur Barang - SIFASTER';
$headerTitle = 'Retur Barang';
$headerDesc = 'Pengembalian Barang (Inbound)';
$activePage = 'retur';

include 'header.php';
?>

<main class="article">
    <div class="crud-wrapper">
        
        <!-- FORM INPUT (HANYA NON-ADMIN) -->
        <?php if ($role_login !== 'Admin'): ?>
        <div class="form-section">
            <h3><i class="fas fa-undo"></i> Input Retur Barang</h3>
            
            <?php if ($error) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
            <?php if ($sukses) { echo "<div class='alert alert-success'>$sukses</div>"; } ?>

            <form action="" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Tanggal Retur</label>
                        <input type="datetime-local" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Barang</label>
                        <select name="kode_barang" class="form-control" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php
                            $q_brg = mysqli_query($koneksi, "SELECT * FROM barang ORDER BY nama_barang ASC");
                            while ($rb = mysqli_fetch_array($q_brg)) {
                                echo "<option value='".$rb['kode_barang']."'>".$rb['kode_barang']." - ".$rb['nama_barang']." (Stok: ".$rb['stok'].")</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jumlah (Qty)</label>
                        <input type="number" name="qty" class="form-control" placeholder="0" min="1" required>
                    </div>
                    <div class="form-group">
                        <label>No. Referensi / Alasan</label>
                        <input type="text" name="no_referensi" class="form-control" placeholder="Contoh: Barang Rusak / Salah Kirim" required>
                    </div>
                </div>
                <button type="submit" name="simpan" class="btn-submit"><i class="fas fa-save"></i> Ajukan Retur</button>
            </form>
        </div>
        <?php else: ?>
            <?php 
            if ($sukses) { 
                echo "<script>
                    Swal.fire({
                        title: 'Berhasil!',
                        text: '$sukses',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href='adminRetur.php';
                        }
                    });
                </script>"; 
            } 
            if ($error) { 
                echo "<script>
                    Swal.fire({
                        title: 'Gagal!',
                        text: '$error',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                </script>"; 
            } 
            ?>
        <?php endif; ?>

        <!-- TABEL DATA -->
        <div class="table-section">
            <h3><i class="fas fa-history"></i> Riwayat Retur</h3>
            <div class="table-responsive">
                <table class="table-retur">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th>Barang</th>
                            <th>Qty</th>
                            <th>Ref / Alasan</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_tampil = mysqli_query($koneksi, "SELECT t.*, dt.qty, b.nama_barang, b.satuan, u.username 
                                                            FROM transaksi t 
                                                            JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi
                                                            JOIN barang b ON dt.kode_barang = b.kode_barang
                                                            JOIN users u ON t.request_by = u.id_user
                                                            WHERE t.tipe = 'Retur'
                                                            ORDER BY t.tanggal DESC");
                        $no = 1;
                        while ($r = mysqli_fetch_array($q_tampil)) {
                            $statusClass = '';
                            if ($r['status'] == 'Pending') $statusClass = 'badge-pending';
                            if ($r['status'] == 'Approved') $statusClass = 'badge-approved';
                            if ($r['status'] == 'Rejected') $statusClass = 'badge-rejected';
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $r['no_transaksi']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($r['tanggal'])); ?></td>
                            <td><?php echo $r['nama_barang']; ?></td>
                            <td><?php echo $r['qty']; ?> <?php echo $r['satuan']; ?></td>
                            <td><?php echo $r['no_referensi']; ?></td>
                            <td><span class="badge <?php echo $statusClass; ?>"><?php echo $r['status']; ?></span></td>
                            <td><?php echo $r['username']; ?></td>
                            <td>
                                <?php if ($r['status'] == 'Pending'): ?>
                                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                                        <button type="button" class="btn-edit" style="background:#10b981; border:none; cursor:pointer;" onclick="confirmAction('approve', '<?php echo $r['no_transaksi']; ?>')" title="Approve"><i class="fas fa-check"></i></button>
                                        <button type="button" class="btn-delete" style="border:none; cursor:pointer;" onclick="confirmAction('reject', '<?php echo $r['no_transaksi']; ?>')" title="Reject"><i class="fas fa-times"></i></button>
                                    <?php endif; ?>
                                    <button type="button" class="btn-delete" style="background:#64748b; border:none; cursor:pointer;" onclick="confirmAction('delete', '<?php echo $r['no_transaksi']; ?>')" title="Hapus"><i class="fas fa-trash"></i></button>
                                <?php else: ?>
                                    <span style="color:#94a3b8;"><i class="fas fa-lock"></i> Locked</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

<script>
function confirmAction(action, id) {
    let title, text, icon, confirmBtnColor, confirmBtnText, url;

    if (action === 'approve') {
        title = 'Setujui Retur?';
        text = "Stok barang akan bertambah otomatis!";
        icon = 'warning';
        confirmBtnColor = '#10b981';
        confirmBtnText = 'Ya, Setujui!';
        url = 'adminRetur.php?op=approve&id=' + id;
    } else if (action === 'reject') {
        title = 'Tolak Retur?';
        text = "Permintaan ini akan ditandai sebagai Rejected.";
        icon = 'warning';
        confirmBtnColor = '#ef4444';
        confirmBtnText = 'Ya, Tolak!';
        url = 'adminRetur.php?op=reject&id=' + id;
    } else if (action === 'delete') {
        title = 'Hapus Draft?';
        text = "Data yang dihapus tidak dapat dikembalikan!";
        icon = 'warning';
        confirmBtnColor = '#64748b';
        confirmBtnText = 'Ya, Hapus!';
        url = 'adminRetur.php?op=delete&id=' + id;
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

<?php include 'aside.php'; ?>
</div>
<?php include 'footer.php'; ?>
