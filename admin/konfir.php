<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}
?>
<h1>Selamat datang Admin, <?php echo $_SESSION['username']; ?>!</h1>
<p>Ini adalah halaman konfirmasi untuk admin.</p>
