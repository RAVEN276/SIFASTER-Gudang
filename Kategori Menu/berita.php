<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pageTitle = 'Berita Gudang';
$headerTitle = 'Berita & Informasi';
$headerDesc = 'Kabar terbaru seputar aktivitas gudang';
$activePage = 'berita';

$pathPrefix = '../';
include '../header.php';
?>

<main class="article">
    <h2>Berita Gudang Terbaru</h2>
    
    <div class="crud-wrapper">
        <div class="headline">
            <h3>Peningkatan Efisiensi Stok Opname</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 10px;"><i class="far fa-calendar-alt"></i> 20 Desember 2025 | Oleh: Admin</p>
            <p>Bulan ini tim gudang berhasil meningkatkan efisiensi stok opname sebesar 15% berkat penggunaan sistem barcode baru. Hal ini mengurangi waktu downtime operasional secara signifikan.</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">
            
            <h3>Jadwal Maintenance Gudang</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 10px;"><i class="far fa-calendar-alt"></i> 18 Desember 2025 | Oleh: Kepala Gudang</p>
            <p>Diberitahukan kepada seluruh staff bahwa akan dilakukan perbaikan struktur pada Rak B3-B5 pada hari Sabtu mendatang. Mohon area tersebut dikosongkan sementara.</p>
            <hr style="border: 0; border-top: 1px solid #eee; margin: 15px 0;">

            <h3>Penerimaan Barang Bulk Besar</h3>
            <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 10px;"><i class="far fa-calendar-alt"></i> 15 Desember 2025 | Oleh: Purchasing</p>
            <p>Minggu depan kita akan menerima kiriman bahan baku kertas dalam jumlah besar. Mohon tim inbound mempersiapkan area loading dock 1 dan 2.</p>
        </div>
    </div>
</main>

<?php include '../aside.php'; ?>
</div>
<?php include '../footer.php'; ?>
