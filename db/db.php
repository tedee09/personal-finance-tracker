<?php
//tedy
$host = 'localhost';
$dbname = 'personal_finance_tracker_db';
$user = 'root'; 
$password = '';

$koneksi = mysqli_connect($host, $user, $password, $dbname);

if (!$koneksi) {
    die("Gagal terhubung ke database: " . mysqli_connect_error());
}
?>
