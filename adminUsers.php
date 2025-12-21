<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'Admin') {
    echo "<script>alert('Akses Ditolak!'); window.location.href='index.php';</script>";
    exit;
}

$sukses = ""; $error = ""; $op = "";
if (isset($_GET['op'])) { $op = $_GET['op']; }

// --- TAMBAH / EDIT USER ---
if (isset($_POST['simpan'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $role     = mysqli_real_escape_string($koneksi, $_POST['role']);
    $status   = mysqli_real_escape_string($koneksi, $_POST['status']);
    $password = $_POST['password'];
    
    $id = isset($_POST['id_user']) ? $_POST['id_user'] : '';

    if ($id) {
        // Edit Mode
        $sql = "UPDATE users SET username='$username', role='$role', status='$status'";
        if (!empty($password)) {
            $md5pass = md5($password);
            $sql .= ", password='$md5pass'";
        }
        $sql .= " WHERE id_user='$id'";
        $q = mysqli_query($koneksi, $sql);
        if ($q) $sukses = "Data User berhasil diupdate.";
        else $error = "Gagal update user.";
    } else {
        // Add Mode
        if (empty($password)) {
            $error = "Password wajib diisi untuk user baru.";
        } else {
            $md5pass = md5($password);
            // Cek username exist
            $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
            if (mysqli_num_rows($cek) > 0) {
                $error = "Username sudah ada!";
            } else {
                $sql = "INSERT INTO users (username, password, role, status) VALUES ('$username', '$md5pass', '$role', '$status')";
                $q = mysqli_query($koneksi, $sql);
                if ($q) $sukses = "User baru berhasil ditambahkan.";
                else $error = "Gagal tambah user.";
            }
        }
    }
}

// --- DELETE USER ---
if ($op == 'delete') {
    $id = $_GET['id'];
    if ($id == $_SESSION['id_user']) {
        $error = "Tidak bisa menghapus diri sendiri!";
    } else {
        $q = mysqli_query($koneksi, "DELETE FROM users WHERE id_user='$id'");
        if ($q) $sukses = "User berhasil dihapus.";
        else $error = "Gagal hapus user (Mungkin terikat data transaksi).";
    }
}

// --- EDIT PREPARE ---
$edit_id = ""; $edit_username = ""; $edit_role = ""; $edit_status = "";
if ($op == 'edit') {
    $id = $_GET['id'];
    $q = mysqli_query($koneksi, "SELECT * FROM users WHERE id_user='$id'");
    $r = mysqli_fetch_array($q);
    $edit_id = $r['id_user'];
    $edit_username = $r['username'];
    $edit_role = $r['role'];
    $edit_status = $r['status'];
}

$pageTitle = 'Kelola User - SIFASTER';
$headerTitle = 'Manajemen User';
$headerDesc = 'Kelola Akun Pengguna & Hak Akses';
$activePage = 'users';

include 'header.php';
?>

<main class="article">
    <div class="crud-wrapper">
        
        <!-- FORM -->
        <div class="form-section">
            <h3><i class="fas fa-user-plus"></i> <?php echo ($op == 'edit') ? 'Edit User' : 'Tambah User Baru'; ?></h3>
            
            <?php if ($error) { echo "<div class='alert alert-danger'>$error</div>"; } ?>
            <?php if ($sukses) { echo "<div class='alert alert-success'>$sukses</div>"; } ?>

            <form action="adminUsers.php" method="POST">
                <input type="hidden" name="id_user" value="<?php echo $edit_id; ?>">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" class="form-control" value="<?php echo $edit_username; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Password <?php echo ($op == 'edit') ? '(Kosongkan jika tidak ubah)' : ''; ?></label>
                        <input type="password" name="password" class="form-control" placeholder="***">
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" class="form-control">
                            <option value="Admin" <?php if($edit_role=='Admin') echo 'selected'; ?>>Admin</option>
                            <option value="Produksi" <?php if($edit_role=='Produksi') echo 'selected'; ?>>Produksi</option>
                            <option value="Purchasing" <?php if($edit_role=='Purchasing') echo 'selected'; ?>>Purchasing</option>
                            <option value="Sales" <?php if($edit_role=='Sales') echo 'selected'; ?>>Sales</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status Akun</label>
                        <select name="status" class="form-control">
                            <option value="Aktif" <?php if($edit_status=='Aktif') echo 'selected'; ?>>Aktif</option>
                            <option value="Suspend" <?php if($edit_status=='Suspend') echo 'selected'; ?>>Suspend (Blokir)</option>
                        </select>
                    </div>
                </div>
                <div class="button-stack">
                    <button type="submit" name="simpan" class="btn-submit"><i class="fas fa-save"></i> Simpan User</button>
                    <?php if($op == 'edit'): ?>
                        <a href="adminUsers.php" class="btn-delete" style="background:#64748b; text-decoration:none; text-align:center;">Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABLE -->
        <div class="table-section">
            <h3><i class="fas fa-users"></i> Daftar User</h3>
            <div class="table-responsive">
                <table class="table-users">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q = mysqli_query($koneksi, "SELECT * FROM users ORDER BY role ASC, username ASC");
                        $no = 1;
                        while ($r = mysqli_fetch_array($q)) {
                            $statusBadge = ($r['status'] == 'Aktif') ? 'badge-success' : 'badge-danger';
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $r['username']; ?></td>
                            <td><?php echo $r['role']; ?></td>
                            <td><span class="badge <?php echo $statusBadge; ?>"><?php echo $r['status']; ?></span></td>
                            <td>
                                <a href="adminUsers.php?op=edit&id=<?php echo $r['id_user']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                <?php if($r['username'] !== 'admin' && $r['id_user'] !== $_SESSION['id_user']): ?>
                                    <a href="adminUsers.php?op=delete&id=<?php echo $r['id_user']; ?>" class="btn-delete" onclick="return confirm('Hapus user ini?')"><i class="fas fa-trash"></i></a>
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

<?php include 'aside.php'; ?>
</div>
<?php include 'footer.php'; ?>
