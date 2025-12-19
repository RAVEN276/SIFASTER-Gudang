<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SIFASTER Gudang'; ?></title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body<?php echo isset($bodyClass) ? ' class="'.$bodyClass.'"' : ''; ?>>
    <div class="container">
        <header class="header">
            <div class="logo">
                <img src="logo_clear.png" alt="SIFASTER" class="header-logo-img">
            </div>
            <div class="header-text">
                <h1>Sistem Informasi Gudang</h1>
                <p>Manufaktur Alat Tulis Kantor (ATK)</p>
                <p class="location">Lokasi: Gudang Utama | User: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
            </div>

            <!-- NOTIFICATION SECTION -->
            <?php
            // Logic Low Stock
            $batasAman = 10;
            $q_notif = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= $batasAman ORDER BY stok ASC");
            $count_notif = mysqli_num_rows($q_notif);
            ?>
            <div class="header-right" style="margin-left: 20px;">
                <div class="notif-btn" onclick="toggleNotif()">
                    <i class="fas fa-bell"></i>
                    <?php if ($count_notif > 0): ?>
                        <span class="notif-badge"><?php echo $count_notif; ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <!-- OVERLAY NOTIFIKASI (MODAL) -->
        <div class="notif-overlay" id="notifOverlay">
            <div class="notif-modal">
                <div class="notif-modal-header">
                    <h4><i class="fas fa-exclamation-triangle"></i> Low Stock Alert</h4>
                    <button class="close-notif" onclick="toggleNotif()"><i class="fas fa-times"></i></button>
                </div>
                <div class="notif-modal-body">
                    <?php if ($count_notif > 0): ?>
                        <?php 
                        // Reset pointer data
                        mysqli_data_seek($q_notif, 0);
                        while($rn = mysqli_fetch_array($q_notif)): 
                        ?>
                            <a href="adminBarang.php?op=edit&id=<?php echo $rn['kode_barang']; ?>" class="notif-card">
                                <div class="notif-card-icon"><i class="fas fa-box"></i></div>
                                <div class="notif-card-info">
                                    <div class="notif-card-name"><?php echo htmlspecialchars($rn['nama_barang']); ?></div>
                                    <div class="notif-card-stock">Sisa: <b><?php echo $rn['stok']; ?> <?php echo $rn['satuan']; ?></b></div>
                                </div>
                            </a>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="notif-empty-state">
                            <i class="fas fa-check-circle"></i>
                            <p>Semua stok aman!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <script>
        function toggleNotif() {
            const overlay = document.getElementById('notifOverlay');
            overlay.classList.toggle('active');
        }

        // Close when clicking outside modal
        document.getElementById('notifOverlay').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
        </script>

        <div class="content-wrapper">
            <nav class="nav">
                <h2>Menu Utama</h2>
                <ul>
                    <li><a href="index.php" class="<?php echo (isset($activePage) && $activePage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a></li>
                    
                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <li><a href="adminBarang.php" class="<?php echo (isset($activePage) && $activePage == 'barang') ? 'active' : ''; ?>">Master Data & Stok</a></li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Purchasing'): ?>
                    <li><a href="adminTransaksiMasuk.php" class="<?php echo (isset($activePage) && $activePage == 'masuk') ? 'active' : ''; ?>">Transaksi Masuk (PO)</a></li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] == 'Admin' || $_SESSION['role'] == 'Produksi' || $_SESSION['role'] == 'Sales'): ?>
                    <li><a href="adminTransaksiKeluar.php" class="<?php echo (isset($activePage) && $activePage == 'keluar') ? 'active' : ''; ?>">Transaksi Keluar (SPK/SO)</a></li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                    <li><a href="laporan.php" class="<?php echo (isset($activePage) && $activePage == 'laporan') ? 'active' : ''; ?>">Laporan & Monitoring</a></li>
                    <?php endif; ?>

                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
