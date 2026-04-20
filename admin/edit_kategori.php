<?php
session_start();

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Ambil ID kategori yang akan diedit
if (isset($_GET['id'])) {
    $idKategori = $_GET['id'];

    // Query untuk mengambil data kategori berdasarkan ID
    $query = "SELECT * FROM kategori WHERE IDKATEGORI = $idKategori";
    $result = mysqli_query($conn, $query);

    // Jika data kategori ditemukan
    if (mysqli_num_rows($result) > 0) {
        $kategori = mysqli_fetch_assoc($result);
    } else {
        echo "Kategori tidak ditemukan.";
        exit();
    }
}

// Proses update kategori
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $jenisProduk = mysqli_real_escape_string($conn, $_POST['jenisProduk']);

    // Query untuk mengupdate data kategori
    $updateQuery = "UPDATE kategori SET JENISPRODUK = '$jenisProduk' WHERE IDKATEGORI = $idKategori";
    if (mysqli_query($conn, $updateQuery)) {
        $message = "Kategori berhasil diperbarui!";
        $status = "success";
        header("Location: kategori.php"); // Redirect ke halaman kategori
        exit();
    } else {
        $message = "Gagal memperbarui kategori!";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kategori - Admin</title>
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
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h1 class="mt-4 ml-4">Edit Kategori</h1>
                        <form action="" method="POST" class="mt-4 mx-4">
                            <div class="form-group">
                                <label for="jenisProduk">Nama Kategori</label>
                                <input type="text" class="form-control" id="jenisProduk" name="jenisProduk" value="<?php echo $kategori['JENISPRODUK']; ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Update Kategori</button>
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
