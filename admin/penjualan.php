<?php
session_start();

// Pastikan user adalah admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Proses penghapusan
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM penjualan WHERE IDJUAL = '$delete_id'";
    if (mysqli_query($conn, $delete_query)) {
        $message = "Penjualan berhasil dihapus!";
        $status = "success";
    } else {
        $message = "Gagal menghapus penjualan!";
        $status = "danger";
    }
}

// Inisialisasi filter
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query gabung penjualan dan produk dengan filter
$query = "SELECT pj.IDJUAL, pr.NAMAPRODUK, pj.TANGGALJUAL, pj.JUMLAHJUAL
          FROM penjualan pj
          LEFT JOIN produk pr ON pj.IDPRODUK = pr.IDPRODUK
          WHERE pj.BULAN = '$bulan' AND pj.TAHUN = '$tahun'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penjualan - Admin</title>
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
        .action-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .filter-form {
            display: flex;
            align-items: center;
        }
        
        .filter-form select, .filter-form button {
            margin-right: 10px;
        }
        
        .form-group {
            margin-right: 10px;
        }
    </style>
</head>

<body class="right-body">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <!-- Data Penjualan -->
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Data Penjualan</h5>

                        <?php if (isset($message)) { ?>
                            <div class="alert alert-<?php echo $status; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>

                        <!-- Container untuk filter dan tombol tambah -->
                        <div class="action-container">
                            <!-- Filter Bulan/Tahun di sisi kiri -->
                            <form action="" method="GET" class="filter-form">
                                <div class="form-group">
                                    <label for="bulan">Bulan:</label>
                                    <select name="bulan" id="bulan" class="form-control">
                                        <option value="1" <?php if($bulan == '1') echo 'selected'; ?>>Januari</option>
                                        <option value="2" <?php if($bulan == '2') echo 'selected'; ?>>Februari</option>
                                        <option value="3" <?php if($bulan == '3') echo 'selected'; ?>>Maret</option>
                                        <option value="4" <?php if($bulan == '4') echo 'selected'; ?>>April</option>
                                        <option value="5" <?php if($bulan == '5') echo 'selected'; ?>>Mei</option>
                                        <option value="6" <?php if($bulan == '6') echo 'selected'; ?>>Juni</option>
                                        <option value="7" <?php if($bulan == '7') echo 'selected'; ?>>Juli</option>
                                        <option value="8" <?php if($bulan == '8') echo 'selected'; ?>>Agustus</option>
                                        <option value="9" <?php if($bulan == '9') echo 'selected'; ?>>September</option>
                                        <option value="10" <?php if($bulan == '10') echo 'selected'; ?>>Oktober</option>
                                        <option value="11" <?php if($bulan == '11') echo 'selected'; ?>>November</option>
                                        <option value="12" <?php if($bulan == '12') echo 'selected'; ?>>Desember</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="tahun">Tahun:</label>
                                    <select name="tahun" id="tahun" class="form-control">
                                        <?php 
                                        $tahun_sekarang = date('Y');
                                        for($i = $tahun_sekarang - 2; $i <= $tahun_sekarang + 0; $i++) {
                                            echo "<option value='$i'";
                                            if($tahun == $i) echo " selected";
                                            echo ">$i</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                                <a href="?bulan=<?php echo date('m'); ?>&tahun=<?php echo date('Y'); ?>" class="btn btn-secondary">Reset</a>
                            </form>
                            
                            <!-- Tombol Tambah Penjualan di sisi kanan -->
                            <a href="tambah_penjualan.php" class="btn btn-primary">+ Tambah Penjualan</a>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>ID Penjualan</th>
                                    <th>Nama Produk</th>
                                    <th>Tanggal Jual</th>
                                    <th>Jumlah Jual</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . $row['IDJUAL'] . "</td>";
                                        echo "<td>" . $row['NAMAPRODUK'] . "</td>";
                                        echo "<td>" . $row['TANGGALJUAL'] . "</td>";
                                        echo "<td>" . $row['JUMLAHJUAL'] . "</td>";
                                        echo "<td>
                                                <a href='edit_penjualan.php?id=" . $row['IDJUAL'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                                <a href='?delete_id=" . $row['IDJUAL'] . "&bulan=" . $bulan . "&tahun=" . $tahun . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus penjualan ini?\")'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Tidak ada data penjualan untuk periode yang dipilih.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Setelah tabel -->
                        <div class="d-flex justify-content-end mt-3">
                            <a href="rekap_penjualan.php" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Rekap Penjualan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>