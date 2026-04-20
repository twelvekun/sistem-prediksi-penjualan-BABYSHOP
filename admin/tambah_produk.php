<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Ambil data kategori untuk dropdown
$query_kategori = "SELECT * FROM kategori";
$result_kategori = mysqli_query($conn, $query_kategori);

// Ambil prefix kategori
$kategori_prefix = '';
if (isset($_POST['idkategori']) && $_POST['idkategori'] != '') {
    $idkategori = $_POST['idkategori'];

    // Ambil kode prefix kategori
    $kategori_query = "SELECT JENISPRODUK FROM kategori WHERE IDKATEGORI = '$idkategori'";
    $kategori_result = mysqli_query($conn, $kategori_query);
    $kategori_data = mysqli_fetch_assoc($kategori_result);
    $kategori_prefix = substr(strtolower($kategori_data['JENISPRODUK']), 0, 2); // Awalan dua huruf kategori
}

// Proses saat form disubmit
if (isset($_POST['submit'])) {
    $idkategori = $_POST['idkategori'];
    $namaproduk = $_POST['namaproduk'];
    $jumlahproduk = $_POST['jumlahproduk'];

    // Ambil nomor urut produk berdasarkan kategori
    $urutan_query = "SELECT MAX(CAST(SUBSTRING(IDPRODUK, 3) AS UNSIGNED)) AS max_urutan 
                     FROM produk WHERE IDKATEGORI = '$idkategori'";
    $urutan_result = mysqli_query($conn, $urutan_query);
    $urutan_data = mysqli_fetch_assoc($urutan_result);
    $urutan = $urutan_data['max_urutan'] + 1; // Tambah 1 pada nomor urut terakhir

    // Format kode produk (contoh: se001, se002, ...)
    $kodeproduk = $kategori_prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT);

    // Query untuk menambah produk
    $query = "INSERT INTO produk (IDKATEGORI, NAMAPRODUK, JUMLAHPRODUK, IDPRODUK) 
              VALUES ('$idkategori', '$namaproduk', '$jumlahproduk', '$kodeproduk')";

    if (mysqli_query($conn, $query)) {
        header("Location: data_produk.php");
        exit();
    } else {
        echo "Error: " . $query . "<br>" . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk</title>
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
            width: 50%;
            margin: auto;
        }
    </style>
</head>

<body class="bg-light">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="card shadow">
            <h1 class="mt-4 ml-4 text-center">Tambah Produk</h1>
            <form method="POST" class="p-4">
                <div class="form-group">
                    <label for="kategori">Kategori</label>
                    <select name="idkategori" id="kategori" class="form-control" onchange="this.form.submit()">
                        <option value="">Pilih Kategori</option>
                        <?php while ($row = mysqli_fetch_assoc($result_kategori)) { ?>
                            <option value="<?php echo $row['IDKATEGORI']; ?>" <?php echo (isset($_POST['idkategori']) && $_POST['idkategori'] == $row['IDKATEGORI']) ? 'selected' : ''; ?>>
                                <?php echo $row['JENISPRODUK']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <!-- Tidak menampilkan kode produk lagi -->

                <div class="form-group">
                    <label for="namaproduk">Nama Produk</label>
                    <input type="text" name="namaproduk" id="namaproduk" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="jumlahproduk">Jumlah Produk</label>
                    <input type="number" name="jumlahproduk" id="jumlahproduk" class="form-control" required>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="data_produk.php" class="btn btn-secondary">Kembali</a>
                    <button type="submit" name="submit" class="btn btn-primary">Tambah Produk</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
