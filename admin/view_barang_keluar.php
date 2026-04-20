<?php
session_start();
require '../config.php'; // Pastikan config.php berada di jalur yang benar

if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID tidak ditemukan.";
    exit();
}

$id = $_GET['id'];

// Ambil data barang keluar menggunakan prepared statement
$query = "SELECT bk.IDKELUAR, bk.TANGGALKELUAR, bk.JUMLAHKELUAR, 
                 p.IDPRODUK, p.NAMAPRODUK, bk.TUJUAN
          FROM barangkeluar bk
          JOIN produk p ON bk.IDPRODUK = p.IDPRODUK
          WHERE bk.IDKELUAR = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);

$produkList = mysqli_query($conn, "SELECT * FROM produk");
$editMode = isset($_GET['edit']) && $_GET['edit'] == 'true';

// Update data barang keluar
// Update data barang keluar
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id_keluar = $_POST['id_keluar'];
    $id_produk_baru = $_POST['id_produk'];
    $jumlah_keluar_baru = $_POST['jumlah_keluar'];
    $tujuan_baru = $_POST['tujuan'];

    // Periksa apakah produk yang dipilih ada di tabel produk
    // Assuming $id_produk_baru contains the new product ID to update
$product_check_query = "SELECT * FROM produk WHERE IDPRODUK = ?";
$stmt = mysqli_prepare($conn, $product_check_query);
mysqli_stmt_bind_param($stmt, "s", $id_produk_baru);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    // Product exists, proceed with the update
    $update_query = "UPDATE barangkeluar SET IDPRODUK = ?, JUMLAHKELUAR = ?, TUJUAN = ? WHERE IDKELUAR = ?";
    $stmt_update = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt_update, "siis", $id_produk_baru, $jumlah_keluar_baru, $tujuan_baru, $id_keluar);
    mysqli_stmt_execute($stmt_update);
    echo "Data updated successfully!";
} else {
    // Product does not exist in produk table
    echo "Error: Produk tidak ditemukan!";
}

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Barang Keluar</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }
        .right-body{
            background: #FBF7E6;
            background-attachment: fixed;
            min-height: 100vh;}
    </style>
</head>
<body class="right-body">
    <?php include "header.php"; ?>
    <div class="container mt-4 d-flex justify-content-center">
        <div class="card shadow w-100" style="max-width: 600px;">
            <div class="card-header">
                <h3 class="mb-2">Detail Barang Keluar</h3>
            </div>
            <div class="card-body">
                <?php if ($editMode): ?>
                    <form method="POST">
                        <input type="hidden" name="id_keluar" value="<?= $data['IDKELUAR']; ?>">

                        <div class="mb-3">
                            <label for="id_produk" class="form-label">Nama Produk</label>
                            <select name="id_produk" id="id_produk" class="form-control" required>
                                <?php while ($p = mysqli_fetch_assoc($produkList)) : ?>
                                    <option value="<?= $p['IDPRODUK']; ?>" <?= $p['IDPRODUK'] == $data['IDPRODUK'] ? 'selected' : '' ?>>
                                        <?= $p['NAMAPRODUK']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah_keluar" class="form-label">Jumlah Keluar</label>
                            <input type="number" name="jumlah_keluar" id="jumlah_keluar" class="form-control" value="<?= $data['JUMLAHKELUAR']; ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="tujuan" class="form-label">Tujuan</label>
                            <input type="text" name="tujuan" id="tujuan" class="form-control" value="<?= $data['TUJUAN']; ?>" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="view_barang_keluar.php?id=<?= $data['IDKELUAR']; ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" name="update" class="btn btn-success">Simpan Update</button>
                        </div>
                    </form>
                <?php else: ?>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID Barang Keluar</th>
                            <td><?= $data['IDKELUAR']; ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Keluar</th>
                            <td><?= $data['TANGGALKELUAR']; ?></td>
                        </tr>
                        <tr>
                            <th>Nama Produk</th>
                            <td><?= $data['NAMAPRODUK']; ?></td>
                        </tr>
                        <tr>
                            <th>Jumlah Keluar</th>
                            <td><?= $data['JUMLAHKELUAR']; ?></td>
                        </tr>
                        <tr>
                            <th>Tujuan</th>
                            <td><?= $data['TUJUAN']; ?></td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-center mt-4" style="gap: 15px;">
                        <a href="barang_keluar.php" class="btn btn-secondary">Kembali</a>
                        <a href="view_barang_keluar.php?id=<?= $data['IDKELUAR']; ?>&edit=true" class="btn btn-warning">Edit</a>
                        <a href="cetak_barang_keluar.php?id=<?= $data['IDKELUAR']; ?>" target="_blank" class="btn btn-primary">Cetak</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
