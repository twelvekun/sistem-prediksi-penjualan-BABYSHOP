<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php'; // Menghubungkan dengan file config.php

// Proses penambahan pemasok
if (isset($_POST['submit'])) {
    $namapemasok = mysqli_real_escape_string($conn, $_POST['namapemasok']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);

    // Query untuk menambah pemasok baru
    $query = "INSERT INTO pemasok (NAMAPEMASOK, KONTAK) VALUES ('$namapemasok', '$kontak')";

    if (mysqli_query($conn, $query)) {
        // Menampilkan notifikasi sukses dan redirect ke halaman barang_masuk.php
        header("Location: barang_masuk.php?status=success&message=Pemasok berhasil ditambahkan!");
        exit();
    } else {
        // Menampilkan notifikasi error
        $message = "Gagal menambahkan pemasok!";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pemasok - user</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
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

<body class="right-body">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Tambah Pemasok</h5>

                        <!-- Notifikasi jika ada -->
                        <?php if (isset($message)) { ?>
                            <div class="alert alert-<?php echo $status; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>

                        <!-- Form untuk menambah pemasok -->
                        <form action="tambah_pemasok.php" method="POST">
                            <div class="form-group">
                                <label for="namapemasok">Nama Pemasok</label>
                                <input type="text" id="namapemasok" name="namapemasok" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="kontak">Kontak</label>
                                <input type="text" id="kontak" name="kontak" class="form-control" required>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Tambah Pemasok</button>
                            <a href="barang_masuk.php" class="btn btn-secondary">Kembali</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
