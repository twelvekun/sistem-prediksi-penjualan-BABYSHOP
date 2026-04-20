<?php
session_start();

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Proses tambah kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenisProduk = mysqli_real_escape_string($conn, $_POST['jenisProduk']);

    $query = "INSERT INTO kategori (JENISPRODUK) VALUES ('$jenisProduk')";
    if (mysqli_query($conn, $query)) {
        $message = "Kategori berhasil ditambahkan!";
        $status = "success";
        header("Location: kategori.php"); // Redirect ke halaman kategori
        exit();
    } else {
        $message = "Gagal menambahkan kategori!";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kategori - Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="mt-4 ml-4">Tambah Kategori</h1>
                        <form action="" method="POST" class="mt-4 mx-4">
                            <div class="form-group">
                                <label for="jenisProduk">Nama Kategori</label>
                                <input type="text" class="form-control" id="jenisProduk" name="jenisProduk" required>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Tambah Kategori</button>
                            <a href="kategori.php" class="btn btn-secondary mt-3 ml-2">Batal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
