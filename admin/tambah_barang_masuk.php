<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

$message = "";
$status = "";

// Proses simpan data barang masuk
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_produk = $_POST['id_produk'];
    $id_pemasok = $_POST['id_pemasok'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $jumlah_masuk = $_POST['jumlah_masuk'];

    // Simpan ke tabel barangmasuk
    $query_masuk = "INSERT INTO barangmasuk (IDPRODUK, IDPEMASOK, TANGGALMASUK, JUMLAHMASUK)
                    VALUES ('$id_produk', '$id_pemasok', '$tanggal_masuk', '$jumlah_masuk')";

    if (mysqli_query($conn, $query_masuk)) {
        // Update stok produk
        $update_stok = "UPDATE produk SET JUMLAHPRODUK = JUMLAHPRODUK + $jumlah_masuk WHERE IDPRODUK = '$id_produk'";
        mysqli_query($conn, $update_stok);

        header("Location: barang_masuk.php?status=success&message=Barang berhasil ditambahkan");
        exit();
    } else {
        $message = "Gagal menambahkan data barang masuk!";
        $status = "error";
    }
}

// Ambil data produk dan pemasok
$produk = mysqli_query($conn, "SELECT * FROM produk");
$pemasok = mysqli_query($conn, "SELECT * FROM pemasok");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Barang Masuk</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }
        .card {
            max-width: 600px;
            margin: auto;
        }
        .right-body{
            background: #FBF7E6;
            background-attachment: fixed;
            min-height: 100vh;}
    </style>
</head>
<body class="right-body">
    <?php include 'header.php'; ?>

    <div class="container mt-5">
        <div class="card shadow p-4">
            <h3 class="text-center mb-4">Tambah Barang Masuk</h3>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $status == 'success' ? 'success' : 'danger'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label for="id_produk" class="form-label">Nama Produk</label>
                    <select name="id_produk" id="id_produk" class="form-control" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php while ($row = mysqli_fetch_assoc($produk)): ?>
                            <option value="<?= $row['IDPRODUK']; ?>"><?= $row['NAMAPRODUK']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="id_pemasok" class="form-label">Nama Pemasok</label>
                    <select name="id_pemasok" id="id_pemasok" class="form-control" required>
                        <option value="">-- Pilih Pemasok --</option>
                        <?php while ($row = mysqli_fetch_assoc($pemasok)): ?>
                            <option value="<?= $row['IDPEMASOK']; ?>"><?= $row['NAMAPEMASOK']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="tanggal_masuk" class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tanggal_masuk" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="jumlah_masuk" class="form-label">Jumlah Masuk</label>
                    <input type="number" name="jumlah_masuk" class="form-control" min="1" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="barang_masuk.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Barang Masuk</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
