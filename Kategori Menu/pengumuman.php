<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pageTitle = 'Pengumuman';
$headerTitle = 'Papan Pengumuman';
$headerDesc = 'Informasi penting untuk seluruh staff gudang';
$activePage = 'pengumuman';

$pathPrefix = '../';
include '../header.php';
?>

<main class="article">
    <h2>Pengumuman Internal</h2>
    
    <div class="crud-wrapper">
        <div class="headline" style="border-left: 5px solid #eab308;">
            <h3 style="color: #ca8a04;"><i class="fas fa-bullhorn"></i> Libur Operasional Akhir Tahun</h3>
            <p>Sehubungan dengan cuti bersama akhir tahun, operasional gudang akan diliburkan pada tanggal <strong>30-31 Desember 2025</strong>. Mohon selesaikan semua pending request sebelum tanggal 29.</p>
        </div>

        <div class="headline" style="border-left: 5px solid #3b82f6; margin-top: 20px;">
            <h3 style="color: #2563eb;"><i class="fas fa-info-circle"></i> Update SOP Penerimaan Barang</h3>
            <p>Mulai 1 Januari 2026, setiap penerimaan barang wajib melampirkan foto kondisi barang saat tiba di loading dock. Fitur upload foto akan segera tersedia di sistem.</p>
        </div>

        <div class="headline" style="border-left: 5px solid #ef4444; margin-top: 20px;">
            <h3 style="color: #dc2626;"><i class="fas fa-exclamation-circle"></i> Wajib APD Lengkap</h3>
            <p>Diingatkan kembali untuk seluruh staff yang memasuki area penyimpanan (Zone Red) wajib menggunakan Helm Safety dan Sepatu Safety. Pelanggaran akan dikenakan sanksi SP1.</p>
        </div>
    </div>
</main>

<?php include '../aside.php'; ?>
</div>
<?php include '../footer.php'; ?>
