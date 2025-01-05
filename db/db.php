<?php
//tedy
// Koneksi ke database
$host = 'localhost';
$dbname = 'personal_finance_tracker_db';
$user = 'root'; // Ubah jika username MySQL berbeda
$password = ''; // Ubah jika ada password untuk MySQL

// Membuat koneksi ke database
$koneksi = mysqli_connect($host, $user, $password, $dbname);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}
?>
