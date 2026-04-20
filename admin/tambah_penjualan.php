<?php
session_start();

// Pastikan user adalah admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Proses penyimpanan data penjualan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_produk = $_POST['id_produk'];
    $tanggal = $_POST['tanggal'];
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];
    $jumlah_jual = $_POST['jumlah_jual'];

    // Validasi tanggal
    if (!checkdate($bulan, $tanggal, $tahun)) {
        $message = "Tanggal tidak valid!";
        $status = "danger";
    } else {
        // Format tanggal lengkap
        $tanggal_jual = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggal);

        // Query untuk menyimpan data penjualan
        $query = "INSERT INTO penjualan (IDPRODUK, TANGGALJUAL, JUMLAHJUAL, HARI, BULAN, TAHUN) 
                  VALUES ('$id_produk', '$tanggal_jual', '$jumlah_jual', '$tanggal', '$bulan', '$tahun')";
        if (mysqli_query($conn, $query)) {
            $message = "Penjualan berhasil ditambahkan!";
            $status = "success";
        } else {
            $message = "Gagal menambahkan penjualan!";
            $status = "danger";
        }
    }
}

// Query untuk mendapatkan daftar produk
$query_produk = "SELECT * FROM produk";
$result_produk = mysqli_query($conn, $query_produk);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penjualan - Admin</title>
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Tambah Penjualan</h5>

                    <?php if (isset($message)) { ?>
                        <div class="alert alert-<?php echo $status; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php } ?>

                    <form method="POST" action="tambah_penjualan.php">
                        <div class="form-group mb-3">
                            <label for="id_produk">Pilih Produk</label>
                            <select name="id_produk" id="id_produk" class="form-control" required>
                                <option value="">Pilih Produk</option>
                                <?php while ($row = mysqli_fetch_assoc($result_produk)) { ?>
                                    <option value="<?php echo $row['IDPRODUK']; ?>"><?php echo $row['NAMAPRODUK']; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group mb-3">
                            <label>Tanggal Jual</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="tanggal" class="form-control" required>
                                        <option value="">Tanggal</option>
                                        <?php for ($i = 1; $i <= 31; $i++) {
                                            echo "<option value='$i'>$i</option>";
                                        } ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="bulan" class="form-control" required>
                                        <option value="">Bulan</option>
                                        <?php
                                        $nama_bulan = [
                                            1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April",
                                            5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
                                            9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
                                        ];
                                        foreach ($nama_bulan as $num => $nama) {
                                            echo "<option value='$num'>$nama</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="tahun" class="form-control" required>
                                        <option value="">Tahun</option>
                                        <?php for ($t = 2024; $t <= 2026; $t++) {
                                            echo "<option value='$t'>$t</option>";
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="jumlah_jual">Jumlah Jual</label>
                            <input type="number" name="jumlah_jual" id="jumlah_jual" class="form-control" required min="1">
                        </div>

                        <a href="penjualan.php" class="btn btn-secondary ml-2">Kembali</a>
                        <button type="submit" class="btn btn-primary">Simpan Penjualan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
