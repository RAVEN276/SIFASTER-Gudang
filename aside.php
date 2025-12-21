<aside class="aside">
    <?php $prefix = isset($pathPrefix) ? $pathPrefix : ''; ?>
    <div class="aside-card">
        <h3 class="aside-title">Kategori</h3>
        <ul class="aside-menu">
            <?php
            $qCat = mysqli_query($koneksi, "SELECT * FROM cms_categories ORDER BY name ASC");
            if ($qCat && mysqli_num_rows($qCat) > 0) {
                while($c = mysqli_fetch_assoc($qCat)) {
                    // Link to generic page handler
                    echo '<li><a href="'.$prefix.'Kategori Menu/page.php?slug='.$c['slug'].'">'.$c['name'].'</a></li>';
                }
            } else {
                echo '<li style="color:#999; font-style:italic; padding:5px 0;">Belum ada kategori</li>';
            }
            ?>
        </ul>
    </div>
</aside>
