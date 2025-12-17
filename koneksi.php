<?php
$host = "localhost";
$user = "root";
$pass = ""; // Password default Laragon biasanya kosong
$db   = "sifaster_gudang";

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>