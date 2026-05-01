<?php
session_start();

// Pastikan user adalah user
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
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

// Query gabung penjualan dan produk
$query = "SELECT pj.IDJUAL, pr.NAMAPRODUK, pj.TANGGALJUAL, pj.JUMLAHJUAL
          FROM penjualan pj
          LEFT JOIN produk pr ON pj.IDPRODUK = pr.IDPRODUK";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penjualan - user</title>
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


        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

                        <!-- Tombol Tambah Penjualan -->
                        <div class="filter-container mb-3">
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
                                                <a href='?delete_id=" . $row['IDJUAL'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus penjualan ini?\")'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6' class='text-center'>Tidak ada data penjualan.</td></tr>";
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
