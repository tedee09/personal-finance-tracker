<?php
//tedy
require 'db/db.php';
session_start();

$login_error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Validasi input
    if (empty($email) || empty($password)) {
        $login_error = "Semua kolom harus diisi.";
    } else {
        // Cari pengguna berdasarkan email
        $query = "SELECT * FROM users WHERE email = '$email'";
        $result = mysqli_query($koneksi, $query);

        if ($result && mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password_hash'])) {
                // Simpan data ke session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                header("Location: dashboard.php");
                exit();
            } else {
                $login_error = "Password salah.";
            }
        } else {
            $login_error = "Email tidak ditemukan.";
        }
    }
}
?>
