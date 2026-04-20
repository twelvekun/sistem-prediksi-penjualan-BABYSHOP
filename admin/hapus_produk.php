<?php
// Include file konfigurasi untuk koneksi ke database
include "../config.php";

// Memeriksa sesi login admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Memeriksa apakah parameter id produk telah diberikan
if (!isset($_GET['id'])) {
    header('Location: data_produk.php');
    exit();
}

$id_produk = $_GET['id'];

// Query untuk menghapus data produk berdasarkan id
$query_delete = "DELETE FROM produk WHERE idproduk = $id_produk";

if (mysqli_query($conn, $query_delete)) {
    header('Location: data_produk.php');
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
