<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php'; // Menghubungkan dengan file config.php

// Ambil data kategori produk
$query_kategori = "SELECT IDKATEGORI, JENISPRODUK FROM kategori";
$result_kategori = mysqli_query($conn, $query_kategori);

// Proses edit produk
if (isset($_GET['id'])) {
    $idproduk = $_GET['id'];

    // Query untuk mengambil data produk berdasarkan ID
    $query_produk = "SELECT p.IDPRODUK, p.NAMAPRODUK, p.IDKATEGORI, p.JUMLAHPRODUK 
                     FROM produk p 
                     WHERE p.IDPRODUK = '$idproduk'";
    $result_produk = mysqli_query($conn, $query_produk);
    $produk = mysqli_fetch_assoc($result_produk);
}

// Update data produk setelah form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $namaproduk = $_POST['namaproduk'];
    $idkategori = $_POST['idkategori'];
    $jumlahproduk = $_POST['jumlahproduk'];

    // Query untuk update produk
    $update_query = "UPDATE produk 
                     SET NAMAPRODUK = '$namaproduk', IDKATEGORI = '$idkategori', JUMLAHPRODUK = '$jumlahproduk'
                     WHERE IDPRODUK = '$idproduk'";

    if (mysqli_query($conn, $update_query)) {
        $message = "Produk berhasil diperbarui!";
        $status = "success";
        header("Location: data_produk.php"); // Kembali ke halaman data_produk.php setelah sukses
        exit();
    } else {
        $message = "Gagal memperbarui produk!";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }
    </style>
</head>

<body class="bg-light">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="mt-2">Edit Produk</h1>
                        <?php
                        if (isset($message)) {
                            echo "<div class='alert alert-$status' role='alert'>$message</div>";
                        }
                        ?>
                        <form action="edit_produk.php?id=<?php echo $idproduk; ?>" method="POST">
                            <div class="form-group">
                                <label for="idkategori">Kategori</label>
                                <select class="form-control" id="idkategori" name="idkategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($result_kategori)) {
                                        $selected = ($row['IDKATEGORI'] == $produk['IDKATEGORI']) ? 'selected' : '';
                                        echo "<option value='" . $row['IDKATEGORI'] . "' $selected>" . $row['JENISPRODUK'] . "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="namaproduk">Nama Produk</label>
                                <input type="text" class="form-control" id="namaproduk" name="namaproduk" 
                                       value="<?php echo $produk['NAMAPRODUK']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="jumlahproduk">Jumlah Produk</label>
                                <input type="number" class="form-control" id="jumlahproduk" name="jumlahproduk" 
                                       value="<?php echo $produk['JUMLAHPRODUK']; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Update Produk</button>
                            <a href="data_produk.php" class="btn btn-secondary mt-3 ml-2">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
