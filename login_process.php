<?php
session_start();
require 'config.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Query untuk memeriksa kecocokan username dan password
    $query = "SELECT * FROM user WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);
    $hitung = mysqli_num_rows($result);

    if ($hitung > 0) {
        $row = mysqli_fetch_assoc($result);
        $role = $row['ROLE']; // Pastikan menggunakan nama kolom yang benar untuk role
        $_SESSION['log'] = 'logged';
        $_SESSION['role'] = $role;
        $_SESSION['username'] = $row['username']; // Menyimpan username ke session

        // Cek role dan arahkan ke halaman yang sesuai
        if ($role === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: user/index.php');
        }
    } else {
        echo "<script>alert('Username atau password salah'); window.location='index.php';</script>";
    }
}
?>
