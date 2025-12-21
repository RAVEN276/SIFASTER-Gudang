<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$slug = isset($_GET['slug']) ? mysqli_real_escape_string($koneksi, $_GET['slug']) : '';
$catInfo = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM cms_categories WHERE slug='$slug'"));

if (!$catInfo) {
    echo "Kategori tidak ditemukan.";
    exit;
}

$pageTitle = $catInfo['name'];
$headerTitle = $catInfo['name'];
$headerDesc = 'Informasi seputar ' . $catInfo['name'];
$activePage = 'category'; // Generic active state

$pathPrefix = '../';
include '../header.php';
?>

<main class="article">
    <h2><?php echo $catInfo['name']; ?></h2>
    
    <div class="crud-wrapper">
        <?php
        $qPosts = mysqli_query($koneksi, "SELECT * FROM cms_posts WHERE category_id='{$catInfo['id']}' ORDER BY created_at DESC");
        if (mysqli_num_rows($qPosts) > 0) {
            while($p = mysqli_fetch_assoc($qPosts)) {
        ?>
            <div class="headline" style="margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                <h3><?php echo $p['title']; ?></h3>
                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 10px;">
                    <i class="far fa-calendar-alt"></i> <?php echo date('d F Y', strtotime($p['created_at'])); ?> | Oleh: Admin
                </p>
                <div class="content-body">
                    <?php echo $p['content']; // Allow HTML ?>
                </div>
            </div>
        <?php 
            }
        } else {
            echo "<p>Belum ada konten di kategori ini.</p>";
        }
        ?>
    </div>
</main>

<?php include '../aside.php'; ?>
</div>
<?php include '../footer.php'; ?>
