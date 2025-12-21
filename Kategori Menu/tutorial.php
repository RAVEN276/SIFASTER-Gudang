<?php
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$pageTitle = 'Tutorial Sistem';
$headerTitle = 'Panduan Penggunaan';
$headerDesc = 'Cara menggunakan fitur-fitur SIFASTER Gudang';
$activePage = 'tutorial';

$pathPrefix = '../';
include '../header.php';
?>

<main class="article">
    <h2>Tutorial Penggunaan Sistem</h2>
    
    <div class="crud-wrapper">
        <div class="headline">
            <h3><i class="fas fa-book"></i> Cara Input Barang Masuk (Purchasing)</h3>
            <ol style="margin-left: 20px; line-height: 1.6;">
                <li>Login sebagai <strong>Purchasing</strong> atau <strong>Admin</strong>.</li>
                <li>Buka menu <strong>Transaksi Masuk (PO)</strong>.</li>
                <li>Isi form di sebelah kiri:
                    <ul>
                        <li>Pilih Tanggal.</li>
                        <li>Masukkan No. PO (Purchase Order).</li>
                        <li>Pilih Barang dari dropdown.</li>
                        <li>Masukkan Jumlah (Qty).</li>
                    </ul>
                </li>
                <li>Klik tombol <strong>Simpan Request Masuk</strong>.</li>
                <li>Status awal adalah <em>Pending</em>. Tunggu Admin melakukan Approval.</li>
            </ol>

            <h3 style="margin-top: 25px;"><i class="fas fa-print"></i> Cara Mencetak Surat Jalan</h3>
            <ol style="margin-left: 20px; line-height: 1.6;">
                <li>Pastikan transaksi sudah berstatus <strong>Approved</strong>.</li>
                <li>Buka menu <strong>Transaksi Keluar</strong> (untuk Surat Jalan) atau <strong>Masuk</strong> (untuk Tanda Terima).</li>
                <li>Cari transaksi di tabel sebelah kanan.</li>
                <li>Klik tombol ikon <strong>Printer (Biru)</strong> di kolom Aksi.</li>
                <li>Jendela baru akan terbuka menampilkan dokumen siap cetak.</li>
            </ol>

            <h3 style="margin-top: 25px;"><i class="fas fa-bell"></i> Memahami Notifikasi Stok</h3>
            <p>Jika stok barang mencapai batas minimum (10 unit), ikon lonceng di pojok kanan atas akan menampilkan badge merah. Klik ikon tersebut untuk melihat daftar barang yang perlu segera dipesan ulang (Re-stock).</p>
        </div>
    </div>
</main>

<?php include '../aside.php'; ?>
</div>
<?php include '../footer.php'; ?>
