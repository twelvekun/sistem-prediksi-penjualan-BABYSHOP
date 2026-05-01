<?php
session_start();
require '../config.php';

// Cek apakah pengguna sudah login dan memiliki hak akses
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

// Ambil daftar produk untuk dropdown
$produkList = mysqli_query($conn, "SELECT * FROM produk");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_produk = $_POST['id_produk'];
    $jumlah_keluar = (int) $_POST['jumlah_keluar'];
    $tujuan = $_POST['tujuan'];
    $tanggal_keluar = $_POST['tanggal_keluar'];

    // Ambil stok saat ini dari database
    $stok_result = mysqli_query($conn, "SELECT JUMLAHPRODUK FROM produk WHERE IDPRODUK = '$id_produk'");
    $stok_data = mysqli_fetch_assoc($stok_result);
    $stok_sekarang = (int) $stok_data['JUMLAHPRODUK'];

    if (mysqli_query($conn, $query)) {
        // Update stok produk
        $update_stok = "UPDATE produk SET JUMLAHPRODUK = JUMLAHPRODUK - '$jumlah_keluar' WHERE IDPRODUK = '$id_produk'";
        mysqli_query($conn, $update_stok);

        echo "<div class='alert alert-success'>Barang keluar berhasil ditambahkan.</div>";
    } else {
        echo "<div class='alert alert-danger'>Gagal menambahkan data barang keluar.</div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang Keluar</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        body {
        background: #FBF7E6;
        background-attachment: fixed;
        min-height: 100vh;
        }
        .right-body{
            background: #FBF7E6;
            background-attachment: fixed;
            min-height: 100vh;
        }
    </style>
</head>
<body class="right-body">
    <?php include "header.php"; ?>
    <div class="container mt-4 d-flex justify-content-center">
        <div class="card shadow w-100" style="max-width: 600px;">
            <div class="card-header">
                <h3 class="mb-2">Tambah Barang Keluar</h3>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="id_produk" class="form-label">Nama Produk</label>
                        <select name="id_produk" id="id_produk" class="form-control" required>
                            <option value="">Pilih Produk</option>
                            <?php while ($p = mysqli_fetch_assoc($produkList)) : ?>
                                <option value="<?= $p['IDPRODUK']; ?>"><?= $p['NAMAPRODUK']; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jumlah_keluar" class="form-label">Jumlah Keluar</label>
                        <input type="number" name="jumlah_keluar" id="jumlah_keluar" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="tujuan" class="form-label">Tujuan</label>
                        <input type="text" name="tujuan" id="tujuan" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="tanggal_keluar" class="form-label">Tanggal Keluar</label>
                        <input type="date" name="tanggal_keluar" id="tanggal_keluar" class="form-control" required>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="barang_keluar.php" class="btn btn-secondary">Kembali</a>
                        <button type="submit" class="btn btn-success">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
