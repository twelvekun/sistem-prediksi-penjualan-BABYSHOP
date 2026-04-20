<?php
session_start();
require '../config.php';

if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID tidak ditemukan.";
    exit();
}

$id = $_GET['id'];

// Ambil data barang masuk
$query = "SELECT bm.IDMASUK, bm.TANGGALMASUK, bm.JUMLAHMASUK, 
                 p.IDPRODUK, p.NAMAPRODUK, 
                 s.IDPEMASOK, s.NAMAPEMASOK 
          FROM barangmasuk bm
          JOIN produk p ON bm.IDPRODUK = p.IDPRODUK
          JOIN pemasok s ON bm.IDPEMASOK = s.IDPEMASOK
          WHERE bm.IDMASUK = '$id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// Ambil data produk dan pemasok untuk dropdown
$produkList = mysqli_query($conn, "SELECT * FROM produk");
$pemasokList = mysqli_query($conn, "SELECT * FROM pemasok");

$editMode = isset($_GET['edit']) && $_GET['edit'] == 'true';

// Update data barang masuk dan stok
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id_masuk = $_POST['id_masuk'];
    $id_produk_baru = $_POST['id_produk'];
    $id_pemasok_baru = $_POST['id_pemasok'];
    $jumlah_masuk_baru = $_POST['jumlah_masuk'];
    $jumlah_masuk_awal = $_POST['jumlah_masuk_awal'];
    $id_produk_lama = $_POST['id_produk_lama'];

    if ($id_produk_baru == $id_produk_lama) {
        $selisih = $jumlah_masuk_baru - $jumlah_masuk_awal;
        $update_stok = "UPDATE produk SET JUMLAHPRODUK = JUMLAHPRODUK + $selisih WHERE IDPRODUK = '$id_produk_baru'";
        mysqli_query($conn, $update_stok);
    } else {
        $kurangi_lama = "UPDATE produk SET JUMLAHPRODUK = JUMLAHPRODUK - $jumlah_masuk_awal WHERE IDPRODUK = '$id_produk_lama'";
        $tambah_baru  = "UPDATE produk SET JUMLAHPRODUK = JUMLAHPRODUK + $jumlah_masuk_baru WHERE IDPRODUK = '$id_produk_baru'";
        mysqli_query($conn, $kurangi_lama);
        mysqli_query($conn, $tambah_baru);
    }

    $update_query = "UPDATE barangmasuk SET IDPRODUK='$id_produk_baru', IDPEMASOK='$id_pemasok_baru', JUMLAHMASUK='$jumlah_masuk_baru' WHERE IDMASUK='$id_masuk'";
    if (mysqli_query($conn, $update_query)) {
        header("Location: view_barang_masuk.php?id=" . $id_masuk);
        exit();
    } else {
        echo "<div class='alert alert-danger'>Gagal memperbarui data.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Detail Barang Masuk</title>
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
    <h3 class="mb-2">Detail Barang Masuk</h3>
    </div>
            <div class="card-body">
                <?php if ($editMode): ?>
                    <form method="POST">
                        <input type="hidden" name="id_masuk" value="<?= $data['IDMASUK']; ?>">
                        <input type="hidden" name="jumlah_masuk_awal" value="<?= $data['JUMLAHMASUK']; ?>">
                        <input type="hidden" name="id_produk_lama" value="<?= $data['IDPRODUK']; ?>">

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
                            <label for="id_pemasok" class="form-label">Nama Pemasok</label>
                            <select name="id_pemasok" id="id_pemasok" class="form-control" required>
                                <?php while ($s = mysqli_fetch_assoc($pemasokList)) : ?>
                                    <option value="<?= $s['IDPEMASOK']; ?>" <?= $s['IDPEMASOK'] == $data['IDPEMASOK'] ? 'selected' : '' ?>>
                                        <?= $s['NAMAPEMASOK']; ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah_masuk" class="form-label">Jumlah Masuk</label>
                            <input type="number" name="jumlah_masuk" id="jumlah_masuk" class="form-control" value="<?= $data['JUMLAHMASUK']; ?>" required>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="view_barang_masuk.php?id=<?= $data['IDMASUK']; ?>" class="btn btn-secondary">Batal</a>
                            <button type="submit" name="update" class="btn btn-success">Simpan Update</button>
                        </div>
                    </form>
                <?php else: ?>
                    <table class="table table-bordered">
                        <tr>
                            <th>ID Barang Masuk</th>
                            <td><?= $data['IDMASUK']; ?></td>
                        </tr>
                        <tr>
                            <th>Tanggal Masuk</th>
                            <td><?= $data['TANGGALMASUK']; ?></td>
                        </tr>
                        <tr>
                            <th>Nama Produk</th>
                            <td><?= $data['NAMAPRODUK']; ?></td>
                        </tr>
                        <tr>
                            <th>Nama Pemasok</th>
                            <td><?= $data['NAMAPEMASOK']; ?></td>
                        </tr>
                        <tr>
                            <th>Jumlah Masuk</th>
                            <td><?= $data['JUMLAHMASUK']; ?></td>
                        </tr>
                    </table>

                    <div class="d-flex justify-content-center mt-4" style="gap: 15px;">
    <a href="barang_masuk.php" class="btn btn-secondary">Kembali</a>
    <a href="view_barang_masuk.php?id=<?= $data['IDMASUK']; ?>&edit=true" class="btn btn-warning">Edit</a>
    <a href="cetak_barang_masuk.php?id=<?= $data['IDMASUK']; ?>" target="_blank" class="btn btn-primary">Cetak</a>
</div>

                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
