<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_logged_in'])) {
    die("Akses Ditolak");
}

$id = $_GET['id'];
$sql = "SELECT t.*, dt.qty, b.nama_barang, b.satuan, u.username as requestor 
        FROM transaksi t
        JOIN detail_transaksi dt ON t.no_transaksi = dt.no_transaksi
        JOIN barang b ON dt.kode_barang = b.kode_barang
        JOIN users u ON t.request_by = u.id_user
        WHERE t.no_transaksi = '$id'";

$q = mysqli_query($koneksi, $sql);
$r = mysqli_fetch_array($q);

if (!$r) {
    die("Data tidak ditemukan");
}

// Tentukan Judul Dokumen
$judulDokumen = "DOKUMEN TRANSAKSI";
$tipeDokumen = "";
if ($r['tipe'] == 'Keluar') {
    $judulDokumen = "SURAT JALAN (DELIVERY NOTE)";
    $tipeDokumen = "OUTBOUND";
} elseif ($r['tipe'] == 'Masuk') {
    $judulDokumen = "TANDA TERIMA BARANG (RECEIPT)";
    $tipeDokumen = "INBOUND";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak <?php echo $id; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header p { margin: 5px 0; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .data-table th { background-color: #f0f0f0; }
        .signature { width: 100%; margin-top: 50px; }
        .signature td { text-align: center; width: 33%; }
        .signature .line { margin-top: 60px; border-bottom: 1px solid #000; width: 80%; margin-left: auto; margin-right: auto; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Cetak Dokumen</button>
    </div>

    <div class="container">
        <div class="header">
            <h1><?php echo $judulDokumen; ?></h1>
            <p>SIFASTER GUDANG - MANUFAKTUR ATK</p>
        </div>

        <table class="info-table">
            <tr>
                <td width="15%">No. Transaksi</td>
                <td width="35%">: <b><?php echo $r['no_transaksi']; ?></b></td>
                <td width="15%">Tanggal</td>
                <td width="35%">: <?php echo date('d F Y H:i', strtotime($r['tanggal'])); ?></td>
            </tr>
            <tr>
                <td>Tipe</td>
                <td>: <?php echo $tipeDokumen; ?></td>
                <td>No. Referensi</td>
                <td>: <?php echo $r['no_referensi']; ?></td>
            </tr>
            <tr>
                <td>Request By</td>
                <td>: <?php echo $r['requestor']; ?></td>
                <td>Status</td>
                <td>: <?php echo strtoupper($r['status']); ?></td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th width="5%">No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th width="15%">Qty</th>
                    <th width="15%">Satuan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td><?php echo $r['kode_barang']; ?></td>
                    <td><?php echo $r['nama_barang']; ?></td>
                    <td><?php echo $r['qty']; ?></td>
                    <td><?php echo $r['satuan']; ?></td>
                </tr>
            </tbody>
        </table>

        <table class="signature">
            <tr>
                <td>
                    Diserahkan Oleh,<br>
                    (Bagian <?php echo ($r['tipe'] == 'Keluar') ? 'Gudang' : 'Pengirim'; ?>)
                    <div class="line"></div>
                </td>
                <td>
                    Mengetahui,<br>
                    (Kepala Gudang)
                    <div class="line"></div>
                </td>
                <td>
                    Diterima Oleh,<br>
                    (<?php echo $r['requestor']; ?>)
                    <div class="line"></div>
                </td>
            </tr>
        </table>
        
        <div style="margin-top: 20px; font-size: 10px; color: #666;">
            Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?> oleh <?php echo $_SESSION['username']; ?>
        </div>
    </div>

</body>
</html>