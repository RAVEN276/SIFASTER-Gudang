<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// --- 1. KARTU RINGKASAN ---
// Total Jenis Barang
$qTotalBarang = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang");
$dTotalBarang = mysqli_fetch_assoc($qTotalBarang);
$totalBarang = $dTotalBarang['total'];

// Stok Menipis (< 10)
$qStokMin = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM barang WHERE stok < 10");
$dStokMin = mysqli_fetch_assoc($qStokMin);
$stokMin = $dStokMin['total'];

// Transaksi Bulan Ini
$qMasukBulan = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Masuk' AND t.status = 'Approved' AND MONTH(t.tanggal) = MONTH(CURRENT_DATE()) AND YEAR(t.tanggal) = YEAR(CURRENT_DATE())");
$dMasukBulan = mysqli_fetch_assoc($qMasukBulan);
$masukBulan = $dMasukBulan['total'] ?? 0;

$qKeluarBulan = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Keluar' AND t.status = 'Approved' AND MONTH(t.tanggal) = MONTH(CURRENT_DATE()) AND YEAR(t.tanggal) = YEAR(CURRENT_DATE())");
$dKeluarBulan = mysqli_fetch_assoc($qKeluarBulan);
$keluarBulan = $dKeluarBulan['total'] ?? 0;

$qReturBulan = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Retur' AND t.status = 'Approved' AND MONTH(t.tanggal) = MONTH(CURRENT_DATE()) AND YEAR(t.tanggal) = YEAR(CURRENT_DATE())");
$dReturBulan = mysqli_fetch_assoc($qReturBulan);
$returBulan = $dReturBulan['total'] ?? 0;


// --- 2. DATA GRAFIK (7 HARI TERAKHIR) ---
$chartLabels = [];
$chartMasuk = [];
$chartKeluar = [];
$chartRetur = [];

for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('d M', strtotime($date));

    // Masuk
    $qIn = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Masuk' AND t.status = 'Approved' AND DATE(t.tanggal) = '$date'");
    $dIn = mysqli_fetch_assoc($qIn);
    $chartMasuk[] = $dIn['total'] ?? 0;

    // Keluar
    $qOut = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Keluar' AND t.status = 'Approved' AND DATE(t.tanggal) = '$date'");
    $dOut = mysqli_fetch_assoc($qOut);
    $chartKeluar[] = $dOut['total'] ?? 0;

    // Retur
    $qRet = mysqli_query($koneksi, "SELECT SUM(dt.qty) as total FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi WHERE t.tipe = 'Retur' AND t.status = 'Approved' AND DATE(t.tanggal) = '$date'");
    $dRet = mysqli_fetch_assoc($qRet);
    $chartRetur[] = $dRet['total'] ?? 0;
}

// --- 3. TOP 5 BARANG KELUAR (TERLARIS) ---
$topItems = [];
$qTop = mysqli_query($koneksi, "SELECT b.nama_barang, SUM(dt.qty) as total_qty FROM detail_transaksi dt JOIN transaksi t ON dt.no_transaksi = t.no_transaksi JOIN barang b ON dt.kode_barang = b.kode_barang WHERE t.tipe = 'Keluar' AND t.status = 'Approved' GROUP BY b.kode_barang ORDER BY total_qty DESC LIMIT 5");
while($rTop = mysqli_fetch_assoc($qTop)) {
    $topItems[] = $rTop;
}

// --- 4. TRANSAKSI TERBARU ---
$recentTrx = [];
$qRecent = mysqli_query($koneksi, "SELECT t.tanggal, t.tipe, b.nama_barang, dt.qty, u.username FROM transaksi t JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi JOIN barang b ON dt.kode_barang = b.kode_barang JOIN users u ON t.request_by = u.id_user ORDER BY t.tanggal DESC LIMIT 5");
while($rRecent = mysqli_fetch_assoc($qRecent)) {
    $recentTrx[] = $rRecent;
}

$pageTitle = 'Dashboard Ringkasan';
$headerTitle = 'Dashboard Utama';
$headerDesc = 'Ringkasan aktivitas gudang dan statistik';
$activePage = 'dashboard';
$extraHead = '<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>';

include 'header.php';
?>

<style>
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: #fff;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        transition: transform 0.2s;
    }
    .stat-card:hover { transform: translateY(-5px); }
    .stat-icon {
        width: 50px; height: 50px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-right: 15px;
    }
    .stat-info h4 { margin: 0; font-size: 0.9rem; color: #64748b; font-weight: 500; }
    .stat-info p { margin: 5px 0 0; font-size: 1.5rem; font-weight: 700; color: #1e293b; }
    
    .chart-section {
        background: #fff;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .bottom-grid {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 20px;
    }
    @media (max-width: 900px) { .bottom-grid { grid-template-columns: 1fr; } }

    .list-group-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .list-group-item:last-child { border-bottom: none; }
    .badge-tipe { padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 600; }
    .bg-masuk { background: #dcfce7; color: #166534; }
    .bg-keluar { background: #fee2e2; color: #991b1b; }
    .bg-retur { background: #ede9fe; color: #5b21b6; }
</style>

<main class="article">
    
    <!-- 1. SUMMARY CARDS -->
    <div class="dashboard-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: #e0f2fe; color: #0284c7;">
                <i class="fas fa-boxes"></i>
            </div>
            <div class="stat-info">
                <h4>Total Jenis Barang</h4>
                <p><?php echo $totalBarang; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #fee2e2; color: #dc2626;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-info">
                <h4>Stok Menipis</h4>
                <p><?php echo $stokMin; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #dcfce7; color: #166534;">
                <i class="fas fa-arrow-down"></i>
            </div>
            <div class="stat-info">
                <h4>Masuk (Bulan Ini)</h4>
                <p><?php echo $masukBulan; ?> <span style="font-size:0.8rem; font-weight:400;">Unit</span></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #ffedd5; color: #c2410c;">
                <i class="fas fa-arrow-up"></i>
            </div>
            <div class="stat-info">
                <h4>Keluar (Bulan Ini)</h4>
                <p><?php echo $keluarBulan; ?> <span style="font-size:0.8rem; font-weight:400;">Unit</span></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background: #ede9fe; color: #7c3aed;">
                <i class="fas fa-undo"></i>
            </div>
            <div class="stat-info">
                <h4>Retur (Bulan Ini)</h4>
                <p><?php echo $returBulan; ?> <span style="font-size:0.8rem; font-weight:400;">Unit</span></p>
            </div>
        </div>
    </div>

    <!-- 2. MAIN CHART -->
    <div class="chart-section">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0;">Aktivitas Keluar Masuk (7 Hari Terakhir)</h3>
            <select style="padding:5px; border-radius:5px; border:1px solid #ddd;">
                <option>Last 7 Days</option>
            </select>
        </div>
        <div style="position: relative; height: 350px; width: 100%;">
            <canvas id="mainChart"></canvas>
        </div>
    </div>

    <!-- 3. BOTTOM SECTION -->
    <div class="bottom-grid">
        <!-- Top Items -->
        <div class="chart-section">
            <h3 style="margin-top:0; margin-bottom:20px;">Top 5 Barang Keluar</h3>
            <?php if(empty($topItems)): ?>
                <p style="color:#999; text-align:center;">Belum ada data transaksi keluar.</p>
            <?php else: ?>
                <div style="display:flex; flex-direction:column;">
                    <?php foreach($topItems as $item): ?>
                    <div class="list-group-item">
                        <div style="display:flex; align-items:center;">
                            <div style="width:35px; height:35px; background:#f1f5f9; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-right:10px; color:#64748b;">
                                <i class="fas fa-box"></i>
                            </div>
                            <span style="font-weight:500;"><?php echo $item['nama_barang']; ?></span>
                        </div>
                        <span style="font-weight:bold; color:#0f172a;"><?php echo $item['total_qty']; ?> Unit</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Transactions -->
        <div class="chart-section">
            <h3 style="margin-top:0; margin-bottom:20px;">Transaksi Terbaru</h3>
            <div class="table-responsive">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:2px solid #f1f5f9; text-align:left;">
                            <th style="padding:10px;">Tanggal</th>
                            <th style="padding:10px;">Barang</th>
                            <th style="padding:10px;">Tipe</th>
                            <th style="padding:10px;">User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recentTrx as $trx): 
                            $badgeClass = '';
                            if($trx['tipe'] == 'Masuk') $badgeClass = 'bg-masuk';
                            elseif($trx['tipe'] == 'Keluar') $badgeClass = 'bg-keluar';
                            elseif($trx['tipe'] == 'Retur') $badgeClass = 'bg-retur';
                        ?>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px; color:#64748b; font-size:0.9rem;">
                                <?php echo date('d/m H:i', strtotime($trx['tanggal'])); ?>
                            </td>
                            <td style="padding:10px;">
                                <div style="font-weight:500;"><?php echo $trx['nama_barang']; ?></div>
                                <div style="font-size:0.8rem; color:#64748b;"><?php echo $trx['qty']; ?> Unit</div>
                            </td>
                            <td style="padding:10px;">
                                <span class="badge-tipe <?php echo $badgeClass; ?>"><?php echo $trx['tipe']; ?></span>
                            </td>
                            <td style="padding:10px; font-size:0.9rem;"><?php echo $trx['username']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</main>

<?php include 'aside.php'; ?>
</div>
<?php include 'footer.php'; ?>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const ctx = document.getElementById('mainChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($chartLabels); ?>,
            datasets: [
                {
                    label: 'Barang Masuk',
                    data: <?php echo json_encode($chartMasuk); ?>,
                    backgroundColor: '#22c55e',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
                {
                    label: 'Barang Keluar',
                    data: <?php echo json_encode($chartKeluar); ?>,
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                    barPercentage: 0.6,
                },
                {
                    label: 'Retur Barang',
                    data: <?php echo json_encode($chartRetur); ?>,
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4,
                    barPercentage: 0.6,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 4], color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
});
</script>
</body>
</html>