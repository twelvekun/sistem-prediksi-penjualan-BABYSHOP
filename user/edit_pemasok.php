<?php
session_start();

// Cek apakah pengguna adalah user
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Ambil ID pemasok dari URL
if (isset($_GET['id'])) {
    $id_pemasok = $_GET['id'];

    // Query untuk mengambil data pemasok berdasarkan ID
    $query = "SELECT * FROM pemasok WHERE IDPEMASOK = $id_pemasok";
    $result = mysqli_query($conn, $query);
    $pemasok = mysqli_fetch_assoc($result);

    // Jika data tidak ditemukan
    if (!$pemasok) {
        echo "Data pemasok tidak ditemukan.";
        exit();
    }
}

// Proses update data pemasok
if (isset($_POST['update'])) {
    $nama_pemasok = mysqli_real_escape_string($conn, $_POST['nama_pemasok']);
    $kontak = mysqli_real_escape_string($conn, $_POST['kontak']);

    // Query untuk memperbarui data pemasok
    $update_query = "UPDATE pemasok SET NAMAPEMASOK = '$nama_pemasok', KONTAK = '$kontak' WHERE IDPEMASOK = $id_pemasok";
    
    if (mysqli_query($conn, $update_query)) {
        // Redirect ke halaman barang_masuk.php setelah berhasil update
        header("Location: barang_masuk.php?status=success");
        exit();
    } else {
        $message = "Gagal memperbarui pemasok!";
        $status = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pemasok</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
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

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3>Edit Pemasok</h3>
                        <form action="edit_pemasok.php?id=<?php echo $id_pemasok; ?>" method="post">
                            <div class="form-group">
                                <label for="nama_pemasok">Nama Pemasok</label>
                                <input type="text" class="form-control" id="nama_pemasok" name="nama_pemasok" value="<?php echo $pemasok['NAMAPEMASOK']; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="kontak">Kontak</label>
                                <input type="text" class="form-control" id="kontak" name="kontak" value="<?php echo $pemasok['KONTAK']; ?>" required>
                            </div>
                            <button type="submit" name="update" class="btn btn-primary mt-3">Update Data Pemasok</button>
                            <a href="barang_masuk.php" class="btn btn-secondary mt-3 ml-2">Kembali</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
