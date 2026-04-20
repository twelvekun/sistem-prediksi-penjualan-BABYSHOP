<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}
?>
<h1>Selamat datang user, <?php echo $_SESSION['username']; ?>!</h1>
<p>Ini adalah halaman konfirmasi untuk user.</p>
