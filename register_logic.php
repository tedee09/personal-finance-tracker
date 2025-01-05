<?php
//tedy
require 'db/db.php';

$register_error = ""; // Untuk menyimpan pesan error atau sukses

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Validasi input
    if (empty($username) || empty($email) || empty($password)) {
        $register_error = "Semua kolom harus diisi.";
    } else {
        // Cek apakah email sudah digunakan
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $register_error = "Email sudah terdaftar.";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user ke database
            $query = "INSERT INTO users (username, email, password_hash) VALUES ('$username', '$email', '$password_hash')";
            if (mysqli_query($koneksi, $query)) {
                header("Location: login.php?register=success");
                exit();
            } else {
                $register_error = "Pendaftaran gagal.";
            }
        }
    }
}
?>
