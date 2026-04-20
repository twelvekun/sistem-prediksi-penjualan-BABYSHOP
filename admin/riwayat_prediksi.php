<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Ubah query untuk mengambil informasi kategori, ALPHA, BETA, MAPE, dan AKURASI
$query = "SELECT p.IDPRED, p.WAKTUPRED, p.HASILPRED, p.PREDIKSIBULANTAHUN, p.NILAI_PREDIKSI, 
         p.MAPE, p.AKURASI, p.ALPHA, p.BETA, pr.NAMAPRODUK, k.JENISPRODUK 
         FROM pred p
         JOIN produk pr ON p.HASILPRED = pr.IDPRODUK
         LEFT JOIN kategori k ON pr.IDKATEGORI = k.IDKATEGORI
         ORDER BY p.WAKTUPRED DESC";
$result = mysqli_query($conn, $query);

// Handle delete if requested
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $delete_query = "DELETE FROM pred WHERE IDPRED = $id";
    $delete_result = mysqli_query($conn, $delete_query);
    
    if ($delete_result) {
        header("Location: riwayat_prediksi.php?msg=delete-success");
        exit();
    } else {
        header("Location: riwayat_prediksi.php?msg=delete-failed");
        exit();
    }
}

// Message handling
$message = '';
$status = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'delete-success') {
        $message = "Data prediksi berhasil dihapus!";
        $status = "success";
    } elseif ($_GET['msg'] == 'delete-failed') {
        $message = "Gagal menghapus data prediksi.";
        $status = "danger";
    }
}

// Definisi array nama bulan
$bulan_nama = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Prediksi Penjualan</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        .right-body {
            background: #FBF7E6;
            min-height: 100vh;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body class="right-body">

<?php include "header.php"; ?>

<div class="container-fluid mt-4 mb-5">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title text-primary fw-bold"><i class="fas fa-history mr-2"></i> Riwayat Prediksi Penjualan</h5>
                        <a href="prediksi.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali ke Prediksi
                        </a>
                    </div>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $status ?>"><?= $message ?></div>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <th>Waktu Prediksi</th>
                                <th>Produk</th>
                                <th>Periode Prediksi</th>
                                <th>Nilai Prediksi</th>
                                <th>Alpha (α)</th>
                                <th>Beta (β)</th>
                                <th>MAPE</th>
                                <th>Akurasi</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $no = 1;
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)): 
                                    // Parse the periode (format: bulan-tahun menjadi nama bulan tahun)
                                    $periode_parts = explode('-', $row['PREDIKSIBULANTAHUN']);
                                    if (count($periode_parts) == 2) {
                                        $bulan_num = (int)$periode_parts[0];
                                        $tahun = $periode_parts[1];
                                        $periode_display = $bulan_nama[$bulan_num] . ' ' . $tahun;
                                    } else {
                                        $periode_display = $row['PREDIKSIBULANTAHUN'];
                                    }
                                    
                                    // Gabungkan kategori dan nama produk
                                    $displayProduk = !empty($row['JENISPRODUK']) ? 
                                                     $row['JENISPRODUK'] . " " . $row['NAMAPRODUK'] : 
                                                     $row['NAMAPRODUK'];
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($row['WAKTUPRED'])) ?></td>
                                <td class="text-start"><?= $displayProduk ?></td>
                                <td class="fw-bold"><?= $periode_display ?></td>
                                <td class="text-success fw-bold"><?= number_format($row['NILAI_PREDIKSI'], 2) ?> unit</td>
                                <td><?= number_format($row['ALPHA'], 2) ?></td>
                                <td><?= number_format($row['BETA'], 2) ?></td>
                                <td><?= number_format($row['MAPE'], 2) ?>%</td>
                                <td class="text-info fw-bold"><?= isset($row['AKURASI']) ? number_format($row['AKURASI'], 2) . '%' : '-' ?></td>
                                <td>
                                    <a href="?delete=<?= $row['IDPRED'] ?>" class="btn btn-danger btn-sm" 
                                    onclick="return confirm('Apakah Anda yakin ingin menghapus riwayat prediksi ini?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php 
                                endwhile; 
                            } else {
                            ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">Belum ada data riwayat prediksi.</td>
                            </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div> </div> </div> <script src="../assets/js/jquery.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>