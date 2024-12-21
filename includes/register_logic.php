<?php
require 'db.php';

$register_error = ""; // Untuk menyimpan pesan error atau sukses

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($username) || empty($email) || empty($password)) {
        $register_error = "All fields are required.";
    } else {
        // Cek apakah email sudah digunakan
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $register_error = "Email already registered.";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            // Insert user ke database
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $password_hash])) {
                header("Location: login.php?register=success");
                exit();
            } else {
                $register_error = "Registration failed.";
            }
        }
    }
}
?>
