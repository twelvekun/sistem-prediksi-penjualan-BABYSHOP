<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

$idjual = $_GET['id'] ?? null;
if (!$idjual) {
    echo "ID penjualan tidak ditemukan.";
    exit();
}

// Ambil data penjualan
$query = "SELECT * FROM penjualan WHERE IDJUAL = '$idjual'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "Data tidak ditemukan.";
    exit();
}

// Ambil data produk untuk dropdown
$produk = mysqli_query($conn, "SELECT IDPRODUK, NAMAPRODUK FROM produk");

// Proses update
if (isset($_POST['update'])) {
    $idproduk = $_POST['idproduk'];
    $jumlah = $_POST['jumlah'];
    $tanggal = $_POST['tanggal'];
    $bulan = $_POST['bulan'];
    $tahun = $_POST['tahun'];

    // Validasi tanggal
    if (!checkdate($bulan, $tanggal, $tahun)) {
        $error = "Tanggal tidak valid!";
    } else {
        $tanggal_jual = sprintf('%04d-%02d-%02d', $tahun, $bulan, $tanggal);

        $update = "UPDATE penjualan SET 
            IDPRODUK='$idproduk', 
            JUMLAHJUAL='$jumlah', 
            TANGGALJUAL='$tanggal_jual',
            HARI='$tanggal',
            BULAN='$bulan',
            TAHUN='$tahun'
            WHERE IDJUAL='$idjual'";

        if (mysqli_query($conn, $update)) {
            header("Location: penjualan.php");
            exit();
        } else {
            $error = "Gagal mengupdate data!";
        }
    }
}

// Ambil data tanggal untuk prefill dropdown
$tanggalLama = (int)$data['HARI'];
$bulanLama = (int)$data['BULAN'];
$tahunLama = (int)$data['TAHUN'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Penjualan</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }
    </style>
</head>
<body class="bg-light">
<?php include 'header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-body">
                    <h5 class="card-title">Edit Data Penjualan</h5>

                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="idproduk" class="form-label">Produk</label>
                            <select name="idproduk" id="idproduk" class="form-select" required>
                                <?php while ($row = mysqli_fetch_assoc($produk)) : ?>
                                    <option value="<?= $row['IDPRODUK'] ?>" <?= ($row['IDPRODUK'] == $data['IDPRODUK']) ? 'selected' : '' ?>>
                                        <?= $row['NAMAPRODUK'] ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jumlah" class="form-label">Jumlah Jual</label>
                            <input type="number" class="form-control" name="jumlah" id="jumlah" value="<?= $data['JUMLAHJUAL'] ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tanggal Jual</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <select name="tanggal" class="form-control" required>
                                        <option value="">Tanggal</option>
                                        <?php for ($i = 1; $i <= 31; $i++) {
                                            $selected = ($i == $tanggalLama) ? 'selected' : '';
                                            echo "<option value='$i' $selected>$i</option>";
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
                                            $selected = ($num == $bulanLama) ? 'selected' : '';
                                            echo "<option value='$num' $selected>$nama</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <select name="tahun" class="form-control" required>
                                        <option value="">Tahun</option>
                                        <?php for ($t = 2024; $t <= 2026; $t++) {
                                            $selected = ($t == $tahunLama) ? 'selected' : '';
                                            echo "<option value='$t' $selected>$t</option>";
                                        } ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="penjualan.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
