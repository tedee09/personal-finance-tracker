<?php
require 'db.php';
session_start();

$login_error = ""; // Untuk menyimpan pesan error

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($email) || empty($password)) {
        $login_error = "All fields are required.";
    } else {
        // Cari pengguna berdasarkan email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Simpan data ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $login_error = "Invalid email or password.";
        }
    }
}
?>
