<?php
//tedy
require 'db/db.php';

$register_error = ""; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi input (jangan sampai kosong)
    if (empty($username) || empty($email) || empty($password)) {
        $register_error = "Semua kolom harus diisi.";
    } else {
        // Cek email sudah digunakan orang lain ato blom
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $register_error = "Email sudah terdaftar.";
        } else {
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // masukkan user ke database
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
