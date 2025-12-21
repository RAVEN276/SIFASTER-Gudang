<?php
session_start();
include 'koneksi.php';

// Cek Login & Role Admin
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['role'] !== 'Admin') {
    header("Location: index.php");
    exit;
}

$pageTitle = 'Kelola Web';
$headerTitle = 'Manajemen Website';
$headerDesc = 'Kelola Menu, Kategori, dan Konten Website';
$activePage = 'adminWeb';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'menu';
$op = isset($_GET['op']) ? $_GET['op'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';
$msg = "";

// --- ACTION HANDLERS ---

// 1. MENU ACTIONS
if ($tab == 'menu') {
    if (isset($_POST['save_menu'])) {
        $label = mysqli_real_escape_string($koneksi, $_POST['label']);
        $url = mysqli_real_escape_string($koneksi, $_POST['url']);
        $roles = isset($_POST['roles']) ? implode(',', $_POST['roles']) : '';
        $order = intval($_POST['sort_order']);
        
        if ($op == 'edit' && $id) {
            $sql = "UPDATE cms_menus SET label='$label', url='$url', role_access='$roles', sort_order='$order' WHERE id='$id'";
        } else {
            $sql = "INSERT INTO cms_menus (label, url, role_access, sort_order) VALUES ('$label', '$url', '$roles', '$order')";
        }
        
        if (mysqli_query($koneksi, $sql)) $msg = "Menu berhasil disimpan!";
        else $msg = "Gagal menyimpan menu: " . mysqli_error($koneksi);
        $op = ''; $id = ''; // Reset
    }
    
    if ($op == 'delete' && $id) {
        mysqli_query($koneksi, "DELETE FROM cms_menus WHERE id='$id'");
        $msg = "Menu berhasil dihapus!";
        $op = ''; $id = '';
    }
}

// 2. CATEGORY ACTIONS
if ($tab == 'category') {
    if (isset($_POST['save_category'])) {
        $name = mysqli_real_escape_string($koneksi, $_POST['name']);
        $slug = strtolower(str_replace(' ', '-', $name)); // Simple slugify
        
        if ($op == 'edit' && $id) {
            $sql = "UPDATE cms_categories SET name='$name', slug='$slug' WHERE id='$id'";
        } else {
            $sql = "INSERT INTO cms_categories (name, slug) VALUES ('$name', '$slug')";
        }
        
        if (mysqli_query($koneksi, $sql)) $msg = "Kategori berhasil disimpan!";
        else $msg = "Gagal menyimpan kategori: " . mysqli_error($koneksi);
        $op = ''; $id = '';
    }
    
    if ($op == 'delete' && $id) {
        mysqli_query($koneksi, "DELETE FROM cms_categories WHERE id='$id'");
        $msg = "Kategori berhasil dihapus!";
        $op = ''; $id = '';
    }
}

// 3. CONTENT ACTIONS
if ($tab == 'content') {
    if (isset($_POST['save_content'])) {
        $title = mysqli_real_escape_string($koneksi, $_POST['title']);
        $cat_id = intval($_POST['category_id']);
        $content = mysqli_real_escape_string($koneksi, $_POST['content']);
        $author = $_SESSION['id_user'];
        
        if ($op == 'edit' && $id) {
            $sql = "UPDATE cms_posts SET title='$title', category_id='$cat_id', content='$content' WHERE id='$id'";
        } else {
            $sql = "INSERT INTO cms_posts (title, category_id, content, author_id) VALUES ('$title', '$cat_id', '$content', '$author')";
        }
        
        if (mysqli_query($koneksi, $sql)) $msg = "Konten berhasil disimpan!";
        else $msg = "Gagal menyimpan konten: " . mysqli_error($koneksi);
        $op = ''; $id = '';
    }
    
    if ($op == 'delete' && $id) {
        mysqli_query($koneksi, "DELETE FROM cms_posts WHERE id='$id'");
        $msg = "Konten berhasil dihapus!";
        $op = ''; $id = '';
    }
}

// --- CUSTOM NAVIGATION FOR THIS PAGE ---
$customMenu = [
    [
        'label' => '<i class="fas fa-arrow-left" style="margin-right: 10px;"></i> Kembali ke Dashboard',
        'url' => 'index.php',
        'active' => false,
        'text_color' => '#ef4444'
    ],
    [
        'label' => 'Kelola Menu Utama',
        'url' => '?tab=menu',
        'active' => ($tab == 'menu')
    ],
    [
        'label' => 'Kelola Kategori',
        'url' => '?tab=category',
        'active' => ($tab == 'category')
    ],
    [
        'label' => 'Kelola Isi Konten',
        'url' => '?tab=content',
        'active' => ($tab == 'content')
    ]
];

include 'header.php';
?>

<style>
    /* .tabs { display: flex; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; } */
    /* .tab-item { padding: 10px 20px; cursor: pointer; font-weight: 600; color: #64748b; text-decoration: none; border-bottom: 2px solid transparent; } */
    /* .tab-item:hover { color: #2563eb; } */
    /* .tab-item.active { color: #2563eb; border-bottom-color: #2563eb; } */
    .form-box { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #e2e8f0; }
    .role-checkboxes label { display: inline-block; margin-right: 15px; cursor: pointer; }
    /* CKEditor Height & List Fix */
    .ck-editor__editable_inline { min-height: 300px; padding: 0 20px !important; }
    .ck-content ul, .ck-content ol { margin-left: 20px; padding-left: 20px; }
    .ck-content ul { list-style-type: disc; }
    .ck-content ol { list-style-type: decimal; }
</style>

<main class="article">
    <?php if ($msg): ?>
        <div class="alert alert-success" style="background:#dcfce7; color:#166534; padding:10px; border-radius:6px; margin-bottom:20px;">
            <?php echo $msg; ?>
        </div>
    <?php endif; ?>

    <!-- TABS MOVED TO SIDEBAR NAV -->
    <!-- 
    <div class="tabs">
        <a href="?tab=menu" class="tab-item <?php echo $tab=='menu'?'active':''; ?>">Kelola Menu Utama</a>
        <a href="?tab=category" class="tab-item <?php echo $tab=='category'?'active':''; ?>">Kelola Kategori</a>
        <a href="?tab=content" class="tab-item <?php echo $tab=='content'?'active':''; ?>">Kelola Isi Konten</a>
    </div> 
    -->

    <!-- TAB 1: MENU MANAGEMENT -->
    <?php if ($tab == 'menu'): ?>
        <div class="crud-wrapper">
            <?php
            $editData = [];
            if ($op == 'edit' && $id) {
                $qEdit = mysqli_query($koneksi, "SELECT * FROM cms_menus WHERE id='$id'");
                $editData = mysqli_fetch_assoc($qEdit);
            }
            ?>
            <div class="form-box">
                <h4><?php echo $op=='edit' ? 'Edit Menu' : 'Tambah Menu Baru'; ?></h4>
                <form method="POST" action="?tab=menu&op=<?php echo $op; ?>&id=<?php echo $id; ?>">
                    <div class="form-group">
                        <label>Label Menu</label>
                        <input type="text" name="label" class="form-control" value="<?php echo $editData['label'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Target URL (File PHP)</label>
                        <input type="text" name="url" class="form-control" value="<?php echo $editData['url'] ?? ''; ?>" required placeholder="contoh: adminBarang.php">
                    </div>
                    <div class="form-group">
                        <label>Urutan</label>
                        <input type="number" name="sort_order" class="form-control" value="<?php echo $editData['sort_order'] ?? '0'; ?>" style="width:100px;">
                    </div>
                    <div class="form-group">
                        <label>Akses Role</label>
                        <div class="role-checkboxes">
                            <?php 
                            $currentRoles = explode(',', $editData['role_access'] ?? '');
                            $roles = ['Admin', 'Purchasing', 'Produksi', 'Sales'];
                            foreach($roles as $r) {
                                $checked = in_array($r, $currentRoles) ? 'checked' : '';
                                echo "<label><input type='checkbox' name='roles[]' value='$r' $checked> $r</label>";
                            }
                            ?>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="save_menu" class="btn-submit" style="flex: 1;">Simpan Menu</button>
                        <?php if($op=='edit'): ?>
                            <a href="?tab=menu" class="btn-delete" style="flex: 1; text-align:center; background: #64748b; color:white; padding:12px; border-radius:8px; text-decoration:none; font-weight:600; font-size: 0.9rem;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table-users">
                    <thead>
                        <tr>
                            <th>Urutan</th>
                            <th>Label</th>
                            <th>URL</th>
                            <th>Akses Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qMenu = mysqli_query($koneksi, "SELECT * FROM cms_menus ORDER BY sort_order ASC");
                        while($rm = mysqli_fetch_assoc($qMenu)):
                        ?>
                        <tr>
                            <td><?php echo $rm['sort_order']; ?></td>
                            <td><?php echo $rm['label']; ?></td>
                            <td><?php echo $rm['url']; ?></td>
                            <td><?php echo $rm['role_access']; ?></td>
                            <td>
                                <a href="?tab=menu&op=edit&id=<?php echo $rm['id']; ?>" class="btn-edit">Edit</a>
                                <a href="?tab=menu&op=delete&id=<?php echo $rm['id']; ?>" onclick="return confirm('Hapus menu ini?')" class="btn-delete">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 2: CATEGORY MANAGEMENT -->
    <?php if ($tab == 'category'): ?>
        <div class="crud-wrapper">
            <?php
            $editCat = [];
            if ($op == 'edit' && $id) {
                $qEdit = mysqli_query($koneksi, "SELECT * FROM cms_categories WHERE id='$id'");
                $editCat = mysqli_fetch_assoc($qEdit);
            }
            ?>
            <div class="form-box">
                <h4><?php echo $op=='edit' ? 'Edit Kategori' : 'Tambah Kategori Baru'; ?></h4>
                <form method="POST" action="?tab=category&op=<?php echo $op; ?>&id=<?php echo $id; ?>">
                    <div class="form-group">
                        <label>Nama Kategori</label>
                        <input type="text" name="name" class="form-control" value="<?php echo $editCat['name'] ?? ''; ?>" required>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="save_category" class="btn-submit" style="flex: 1;">Simpan Kategori</button>
                        <?php if($op=='edit'): ?>
                            <a href="?tab=category" class="btn-delete" style="flex: 1; text-align:center; background: #64748b; color:white; padding:12px; border-radius:8px; text-decoration:none; font-weight:600; font-size: 0.9rem;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table-users">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Kategori</th>
                            <th>Slug (URL Friendly)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qCat = mysqli_query($koneksi, "SELECT * FROM cms_categories ORDER BY name ASC");
                        while($rc = mysqli_fetch_assoc($qCat)):
                        ?>
                        <tr>
                            <td><?php echo $rc['id']; ?></td>
                            <td><?php echo $rc['name']; ?></td>
                            <td><?php echo $rc['slug']; ?></td>
                            <td>
                                <a href="?tab=category&op=edit&id=<?php echo $rc['id']; ?>" class="btn-edit">Edit</a>
                                <a href="?tab=category&op=delete&id=<?php echo $rc['id']; ?>" onclick="return confirm('Hapus kategori ini?')" class="btn-delete">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- TAB 3: CONTENT MANAGEMENT -->
    <?php if ($tab == 'content'): ?>
        <div class="crud-wrapper">
            <?php
            $editPost = [];
            if ($op == 'edit' && $id) {
                $qEdit = mysqli_query($koneksi, "SELECT * FROM cms_posts WHERE id='$id'");
                $editPost = mysqli_fetch_assoc($qEdit);
            }
            ?>
            <div class="form-box">
                <h4><?php echo $op=='edit' ? 'Edit Konten' : 'Tambah Konten Baru'; ?></h4>
                <form method="POST" action="?tab=content&op=<?php echo $op; ?>&id=<?php echo $id; ?>">
                    <div class="form-group">
                        <label>Judul</label>
                        <input type="text" name="title" class="form-control" value="<?php echo $editPost['title'] ?? ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category_id" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            <?php
                            $qC = mysqli_query($koneksi, "SELECT * FROM cms_categories ORDER BY name ASC");
                            while($rc = mysqli_fetch_assoc($qC)) {
                                $sel = (isset($editPost['category_id']) && $editPost['category_id'] == $rc['id']) ? 'selected' : '';
                                echo "<option value='{$rc['id']}' $sel>{$rc['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Isi Konten</label>
                        <textarea name="content" id="editor" class="form-control" rows="6"><?php echo $editPost['content'] ?? ''; ?></textarea>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="save_content" class="btn-submit" style="flex: 1;">Simpan Konten</button>
                        <?php if($op=='edit'): ?>
                            <a href="?tab=content" class="btn-delete" style="flex: 1; text-align:center; background: #64748b; color:white; padding:12px; border-radius:8px; text-decoration:none; font-weight:600; font-size: 0.9rem;">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- CKEditor 5 CDN -->
            <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
            <script>
                ClassicEditor
                    .create( document.querySelector( '#editor' ), {
                        toolbar: {
                            items: [ 'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo' ],
                            shouldNotGroupWhenFull: true
                        },
                        link: {
                            // Automatically add target="_blank" to external links
                            addTargetToExternalLinks: true,
                            // Let the user control the "Open in new tab" behavior
                            decorators: {
                                openInNewTab: {
                                    mode: 'manual',
                                    label: 'Open in a new tab',
                                    attributes: {
                                        target: '_blank',
                                        rel: 'noopener noreferrer'
                                    }
                                }
                            }
                        }
                    } )
                    .catch( error => {
                        console.error( error );
                    } );
            </script>
            </div>

            <div class="table-responsive">
                <table class="table-users">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $qPost = mysqli_query($koneksi, "SELECT p.*, c.name as cat_name FROM cms_posts p LEFT JOIN cms_categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
                        while($rp = mysqli_fetch_assoc($qPost)):
                        ?>
                        <tr>
                            <td><?php echo $rp['title']; ?></td>
                            <td><?php echo $rp['cat_name']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($rp['created_at'])); ?></td>
                            <td>
                                <a href="?tab=content&op=edit&id=<?php echo $rp['id']; ?>" class="btn-edit">Edit</a>
                                <a href="?tab=content&op=delete&id=<?php echo $rp['id']; ?>" onclick="return confirm('Hapus konten ini?')" class="btn-delete">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

</main>

</div>
<?php include 'footer.php'; ?>
