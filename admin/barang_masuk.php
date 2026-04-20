<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Query untuk mengambil data barang masuk beserta informasi produk dan pemasok
$query = "SELECT bm.IDMASUK, p.NAMAPRODUK, bm.JUMLAHMASUK, ps.NAMAPEMASOK, bm.TANGGALMASUK
          FROM barangmasuk bm
          JOIN produk p ON bm.IDPRODUK = p.IDPRODUK
          JOIN pemasok ps ON bm.IDPEMASOK = ps.IDPEMASOK";
$result = mysqli_query($conn, $query);

// Proses penghapusan barang masuk
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Query untuk menghapus barang masuk berdasarkan ID
    $delete_query = "DELETE FROM barangmasuk WHERE IDMASUK = $delete_id";
    if (mysqli_query($conn, $delete_query)) {
        // Mengurangi stok produk setelah penghapusan
        $get_barang_query = "SELECT IDPRODUK, JUMLAHPRODUK FROM produk WHERE IDPRODUK = (SELECT IDPRODUK FROM barangmasuk WHERE IDMASUK = $delete_id)";
        $barang_result = mysqli_query($conn, $get_barang_query);
        $barang = mysqli_fetch_assoc($barang_result);
        $new_stock = $barang['JUMLAHPRODUK'] - mysqli_fetch_assoc(mysqli_query($conn, "SELECT JUMLAHMASUK FROM barangmasuk WHERE IDMASUK = $delete_id"))['JUMLAHMASUK'];

        // Update stok produk
        mysqli_query($conn, "UPDATE produk SET JUMLAHPRODUK = $new_stock WHERE IDPRODUK = {$barang['IDPRODUK']}");

        $message = "Barang berhasil dihapus!";
        $status = "success";
    } else {
        $message = "Gagal menghapus barang!";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Masuk - Admin</title>
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
            min-height: 100vh;}
</style>
</head>

<body class="right-body">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row">
            <!-- Data Barang Masuk -->
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Data Barang Masuk</h5>
                        
                        <?php if (isset($message)) { ?>
                            <div class="alert alert-<?php echo $status; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>

                        <a href="tambah_barang_masuk.php" class="btn btn-primary mb-3">+ Barang Masuk</a>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Jumlah Masuk</th>
                                    <th>Pemasok</th>
                                    <th>Tanggal Masuk</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $row['NAMAPRODUK'] . "</td>";
                                        echo "<td>" . $row['JUMLAHMASUK'] . "</td>";
                                        echo "<td>" . $row['NAMAPEMASOK'] . "</td>";
                                        echo "<td>" . $row['TANGGALMASUK'] . "</td>";
                                        echo "<td>
                                                <a href='view_barang_masuk.php?id=" . $row['IDMASUK'] . "' class='btn btn-info btn-sm'>View</a>
                                                <a href='?delete_id=" . $row['IDMASUK'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Anda yakin ingin menghapus barang ini?\")'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>Tidak ada data barang masuk.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Tombol Rekap Barang Masuk -->
                        <a href="rekap_barang_masuk.php" class="btn btn-success fas fa-file-excel"> Rekap</a>
                    </div>
                </div>
            </div>

            <!-- Data Pemasok -->
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Data Pemasok</h5>
                        <a href="tambah_pemasok.php" class="btn btn-primary mb-3">+ Pemasok</a>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Pemasok</th>
                                    <th>Kontak</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Query untuk menampilkan pemasok
                                $query_pemasok = "SELECT * FROM pemasok";
                                $result_pemasok = mysqli_query($conn, $query_pemasok);
                                if (mysqli_num_rows($result_pemasok) > 0) {
                                    while ($row_pemasok = mysqli_fetch_assoc($result_pemasok)) {
                                        echo "<tr>";
                                        echo "<td>" . $row_pemasok['NAMAPEMASOK'] . "</td>";
                                        echo "<td>" . $row_pemasok['KONTAK'] . "</td>";
                                        echo "<td>
                                                <a href='edit_pemasok.php?id=" . $row_pemasok['IDPEMASOK'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                                <a href='hapus_pemasok.php?id=" . $row_pemasok['IDPEMASOK'] . "' class='btn btn-danger btn-sm'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='3' class='text-center'>Tidak ada data pemasok.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
