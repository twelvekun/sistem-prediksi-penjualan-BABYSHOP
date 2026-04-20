<?php
include "../config.php"; // Sisipkan file config.php untuk koneksi ke database

// Memeriksa sesi login admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Pengecekan apakah parameter id telah diterima dari URL
if (isset($_GET['id'])) {
    $idpelanggan = $_GET['id'];

    // Query untuk menghapus data pelanggan dari tabel pelanggan
    $query_delete = "DELETE FROM pelanggan WHERE idpelanggan = '$idpelanggan'";

    if (mysqli_query($conn, $query_delete)) {
        header('Location: data_pelanggan.php');
        exit();
    } else {
        echo "Error deleting record: " . mysqli_error($conn);
    }
} else {
    echo "Invalid request."; // Pesan ini akan muncul jika parameter id tidak ada
}
?>