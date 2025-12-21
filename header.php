<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'SIFASTER Gudang'; ?></title>
    <?php $prefix = isset($pathPrefix) ? $pathPrefix : ''; ?>
    <link rel="stylesheet" href="<?php echo $prefix; ?>style.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <?php if (isset($extraHead)) echo $extraHead; ?>
</head>
<body<?php echo isset($bodyClass) ? ' class="'.$bodyClass.'"' : ''; ?>>
    <div class="container">
        <header class="header">
            <div class="logo">
                <img src="<?php echo $prefix; ?>logo_clear.png" alt="SIFASTER" class="header-logo-img">
            </div>
            <div class="header-text">
                <h1>Sistem Informasi Gudang</h1>
                <p>Manufaktur Alat Tulis Kantor (ATK)</p>
                <p class="location">Lokasi: Tangerang Selatan | User: <?php echo htmlspecialchars($_SESSION['username']); ?> (<?php echo htmlspecialchars($_SESSION['role']); ?>)</p>
            </div>

            <!-- NOTIFICATION SECTION -->
            <?php
            // --- NOTIFICATION LOGIC ---
            $id_user_login = $_SESSION['id_user'] ?? 0;
            $role_login    = $_SESSION['role'] ?? '';

            // 1. Low Stock (All Roles)
            $batasAman = 10;
            $q_notif = mysqli_query($koneksi, "SELECT * FROM barang WHERE stok <= $batasAman ORDER BY stok ASC");
            $count_notif = mysqli_num_rows($q_notif);

            // 2. Pending Approval (Admin Only)
            $count_pending = 0;
            $q_pending = false;
            if ($role_login == 'Admin') {
                $q_pending = mysqli_query($koneksi, "SELECT t.*, u.username FROM transaksi t JOIN users u ON t.request_by = u.id_user WHERE t.status = 'Pending' AND t.tipe != 'Retur' ORDER BY t.tanggal DESC");
                $count_pending = mysqli_num_rows($q_pending);
            }

            // 3. Pending Retur (Admin Only)
            $count_retur = 0;
            $q_retur = false;
            if ($role_login == 'Admin') {
                // Ensure we catch all Pending Retur
                $q_retur = mysqli_query($koneksi, "SELECT t.*, u.username FROM transaksi t JOIN users u ON t.request_by = u.id_user WHERE t.status = 'Pending' AND t.tipe = 'Retur' ORDER BY t.tanggal DESC");
                if ($q_retur) {
                    $count_retur = mysqli_num_rows($q_retur);
                }
            }

            // 4. My Requests Status (Non-Admin) - Approved, Rejected, OR Pending - Last 5
            $count_my_req = 0;
            $q_my_req = false;
            if ($role_login != 'Admin') {
                $q_my_req = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE request_by = '$id_user_login' AND status IN ('Approved', 'Rejected', 'Pending') ORDER BY tanggal DESC LIMIT 5");
                if ($q_my_req) {
                    $count_my_req = mysqli_num_rows($q_my_req);
                }
            }

            $total_notif = $count_notif + $count_pending + $count_retur + $count_my_req;
            ?>
            <div class="header-right" style="margin-left: 20px; position: relative;">
                <div class="notif-btn" onclick="toggleNotif()">
                    <i class="fas fa-bell"></i>
                    <?php if ($total_notif > 0): ?>
                        <span class="notif-badge"><?php echo $total_notif; ?></span>
                    <?php endif; ?>
                </div>

                <!-- DROPDOWN NOTIFIKASI -->
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <h4><i class="fas fa-bell"></i> Notifikasi</h4>
                    </div>
                    <div class="notif-body">
                        
                        <?php if ($total_notif == 0): ?>
                            <div class="notif-safe">
                                <i class="fas fa-check-circle"></i>
                                <p>Tidak ada notifikasi baru.</p>
                            </div>
                        <?php endif; ?>

                        <!-- 1. LOW STOCK -->
                        <?php if ($count_notif > 0): ?>
                            <div class="notif-section-title">Low Stock Alert</div>
                            <?php 
                            mysqli_data_seek($q_notif, 0);
                            while($rn = mysqli_fetch_array($q_notif)): 
                                $uniqId = 'stock-' . $rn['kode_barang'];
                            ?>
                                <div class="notif-wrapper" id="notif-<?php echo $uniqId; ?>">
                                    <a href="<?php echo $prefix; ?>adminBarang.php?op=edit&id=<?php echo $rn['kode_barang']; ?>" class="notif-item">
                                        <div class="notif-icon" style="background: #fee2e2; color: #ef4444;"><i class="fas fa-box"></i></div>
                                        <div class="notif-details">
                                            <div class="notif-title"><?php echo htmlspecialchars($rn['nama_barang']); ?></div>
                                            <div class="notif-stock">Sisa: <b><?php echo $rn['stok']; ?> <?php echo $rn['satuan']; ?></b></div>
                                        </div>
                                    </a>
                                    <button class="notif-close" onclick="dismissNotif('<?php echo $uniqId; ?>')">&times;</button>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <!-- 2. PENDING APPROVAL (ADMIN) -->
                        <?php if ($count_pending > 0): ?>
                            <div class="notif-section-title">Butuh Approval</div>
                            <?php 
                            while($rp = mysqli_fetch_array($q_pending)): 
                                $link = ($rp['tipe'] == 'Masuk') ? 'adminTransaksiMasuk.php' : 'adminTransaksiKeluar.php';
                                $uniqId = 'pending-' . $rp['no_transaksi'];
                            ?>
                                <div class="notif-wrapper" id="notif-<?php echo $uniqId; ?>">
                                    <a href="<?php echo $prefix . $link; ?>?op=approve&id=<?php echo $rp['no_transaksi']; ?>" class="notif-item">
                                        <div class="notif-icon" style="background: #fff7ed; color: #f97316;"><i class="fas fa-clock"></i></div>
                                        <div class="notif-details">
                                            <div class="notif-title"><?php echo $rp['tipe']; ?>: <?php echo $rp['no_transaksi']; ?></div>
                                            <div class="notif-stock">Req by: <?php echo htmlspecialchars($rp['username']); ?></div>
                                        </div>
                                    </a>
                                    <button class="notif-close" onclick="dismissNotif('<?php echo $uniqId; ?>')">&times;</button>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <!-- 3. PENDING RETUR (ADMIN) -->
                        <?php if ($count_retur > 0): ?>
                            <div class="notif-section-title">Permintaan Retur</div>
                            <?php 
                            while($rr = mysqli_fetch_array($q_retur)): 
                                $uniqId = 'retur-' . $rr['no_transaksi'];
                            ?>
                                <div class="notif-wrapper" id="notif-<?php echo $uniqId; ?>">
                                    <a href="<?php echo $prefix; ?>adminRetur.php" class="notif-item">
                                        <div class="notif-icon" style="background: #fef2f2; color: #dc2626;"><i class="fas fa-undo"></i></div>
                                        <div class="notif-details">
                                            <div class="notif-title">Retur: <?php echo $rr['no_transaksi']; ?></div>
                                            <div class="notif-stock">Req by: <?php echo htmlspecialchars($rr['username']); ?></div>
                                        </div>
                                    </a>
                                    <button class="notif-close" onclick="dismissNotif('<?php echo $uniqId; ?>')">&times;</button>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>

                        <!-- 4. MY REQUEST STATUS (USER) -->
                        <?php if ($count_my_req > 0): ?>
                            <div class="notif-section-title">Status Pengajuan</div>
                            <?php 
                            while($mr = mysqli_fetch_array($q_my_req)): 
                                $iconColor = '#64748b'; // Default Pending (Gray)
                                $iconBg = '#f1f5f9';
                                $iconClass = 'fa-clock';

                                if ($mr['status'] == 'Approved') {
                                    $iconColor = '#16a34a'; // Green
                                    $iconBg = '#dcfce7';
                                    $iconClass = 'fa-check';
                                } elseif ($mr['status'] == 'Rejected') {
                                    $iconColor = '#dc2626'; // Red
                                    $iconBg = '#fee2e2';
                                    $iconClass = 'fa-times';
                                } elseif ($mr['status'] == 'Pending') {
                                    $iconColor = '#d97706'; // Orange
                                    $iconBg = '#ffedd5';
                                    $iconClass = 'fa-hourglass-half';
                                }
                                $uniqId = 'myreq-' . $mr['no_transaksi'];
                            ?>
                                <div class="notif-wrapper" id="notif-<?php echo $uniqId; ?>">
                                    <div class="notif-item" style="cursor: default;">
                                        <div class="notif-icon" style="background: <?php echo $iconBg; ?>; color: <?php echo $iconColor; ?>;"><i class="fas <?php echo $iconClass; ?>"></i></div>
                                        <div class="notif-details">
                                            <div class="notif-title"><?php echo $mr['tipe']; ?>: <?php echo $mr['no_transaksi']; ?></div>
                                            <div class="notif-stock">Status: <b><?php echo $mr['status']; ?></b></div>
                                        </div>
                                    </div>
                                    <button class="notif-close" onclick="dismissNotif('<?php echo $uniqId; ?>')">&times;</button>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </header>

        <script>
        function toggleNotif() {
            const dropdown = document.getElementById('notifDropdown');
            dropdown.classList.toggle('active');
        }

        function dismissNotif(id) {
            // Hide the element
            const el = document.getElementById('notif-' + id);
            if (el) {
                el.style.display = 'none';
            }
            
            // Save to localStorage
            let dismissed = JSON.parse(localStorage.getItem('dismissedNotifs') || '[]');
            if (!dismissed.includes(id)) {
                dismissed.push(id);
                localStorage.setItem('dismissedNotifs', JSON.stringify(dismissed));
            }
            
            updateBadgeCount();
        }

        function updateBadgeCount() {
            // Count all .notif-wrapper that do NOT have display: none
            const allNotifs = document.querySelectorAll('.notif-wrapper');
            let visibleCount = 0;
            
            allNotifs.forEach(el => {
                if (el.style.display !== 'none') {
                    visibleCount++;
                }
            });

            const badge = document.querySelector('.notif-badge');
            if (badge) {
                badge.innerText = visibleCount;
                if (visibleCount === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = ''; // Revert to default CSS
                }
            }
        }

        // Apply dismissed state on load
        document.addEventListener('DOMContentLoaded', function() {
            const dismissed = JSON.parse(localStorage.getItem('dismissedNotifs') || '[]');
            dismissed.forEach(id => {
                const el = document.getElementById('notif-' + id);
                if (el) {
                    el.style.display = 'none';
                }
            });
            updateBadgeCount();
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notifDropdown');
            const btn = document.querySelector('.notif-btn');
            
            if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });
        </script>

        <div class="content-wrapper">
            <nav class="nav">
                <h2>Menu Utama</h2>
                <ul>
                    <?php
                    // CUSTOM MENU LOGIC (For specific pages like adminWeb.php)
                    if (isset($customMenu) && is_array($customMenu)) {
                        foreach($customMenu as $cm) {
                            $activeClass = (isset($cm['active']) && $cm['active']) ? 'active' : '';
                            $extraStyle = (isset($cm['text_color'])) ? 'style="color:'.$cm['text_color'].';"' : '';
                            echo '<li><a href="'.$cm['url'].'" class="'.$activeClass.'" '.$extraStyle.'>'.$cm['label'].'</a></li>';
                        }
                    } 
                    // STANDARD DYNAMIC MENU FROM DATABASE
                    else {
                        $userRole = $_SESSION['role'];
                        $qMenu = mysqli_query($koneksi, "SELECT * FROM cms_menus WHERE is_active=1 ORDER BY sort_order ASC");
                        
                        while($m = mysqli_fetch_assoc($qMenu)) {
                            // Prevent Duplicate Logout (Skip if exists in DB)
                            if (strtolower($m['label']) == 'logout') continue;

                            // Check Role Access
                            $allowedRoles = explode(',', $m['role_access']);
                            if(in_array($userRole, $allowedRoles)) {
                                // Determine Active State
                                $isActive = '';
                                if(isset($activePage)) {
                                    if(strpos($m['url'], $activePage) !== false) {
                                        $isActive = 'active';
                                    }
                                    if($activePage == 'dashboard' && $m['url'] == 'index.php') $isActive = 'active';
                                    if($activePage == 'adminWeb' && $m['url'] == 'adminWeb.php') $isActive = 'active';
                                }
                                
                                echo '<li><a href="'.$prefix.$m['url'].'" class="'.$isActive.'">'.$m['label'].'</a></li>';
                            }
                        }
                        // LOGOUT BUTTON (STANDARD MENU ONLY)
                        echo '<li><a href="'.$prefix.'logout.php" style="color: #ef4444; margin-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);"><i class="fas fa-sign-out-alt" style="margin-right: 10px;"></i> Logout</a></li>';
                    }
                    ?>
                </ul>
            </nav>
