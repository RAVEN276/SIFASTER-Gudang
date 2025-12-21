<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pageTitle = 'Tips Manajemen Stok';
$headerTitle = 'Tips & Trik';
$headerDesc = 'Panduan mengelola stok agar lebih efisien';
$activePage = 'tips';

$pathPrefix = '../';
include '../header.php';
?>

<main class="article">
    <h2>Tips Manajemen Stok Efektif</h2>
    
    <div class="crud-wrapper">
        <div class="headline">
            <h3>1. Metode FIFO (First In First Out)</h3>
            <p>Pastikan barang yang pertama kali masuk adalah yang pertama kali keluar. Ini sangat penting untuk bahan baku yang memiliki masa simpan atau rentan rusak jika disimpan terlalu lama.</p>
            
            <h3 style="margin-top: 20px;">2. Klasifikasi ABC</h3>
            <p>Kelompokkan barang berdasarkan nilai dan perputarannya:</p>
            <ul style="margin-left: 20px; margin-bottom: 10px;">
                <li><strong>Kelas A:</strong> Barang bernilai tinggi, perputaran cepat (Kontrol ketat).</li>
                <li><strong>Kelas B:</strong> Barang nilai sedang, perputaran sedang.</li>
                <li><strong>Kelas C:</strong> Barang nilai rendah, perputaran lambat.</li>
            </ul>

            <h3 style="margin-top: 20px;">3. Rutin Stok Opname</h3>
            <p>Lakukan pengecekan fisik secara berkala (mingguan/bulanan) untuk mencocokkan data di sistem dengan fisik di gudang. Jangan menunggu akhir tahun!</p>

            <h3 style="margin-top: 20px;">4. Optimalkan Tata Letak</h3>
            <p>Simpan barang fast-moving (sering keluar) di area yang mudah dijangkau dekat pintu keluar. Barang slow-moving bisa diletakkan di bagian belakang atau rak atas.</p>
        </div>
    </div>
</main>

<?php include '../aside.php'; ?>
</div>
<?php include '../footer.php'; ?>
