<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Query untuk mengambil data barang keluar beserta informasi produk dan tujuan
$query = "SELECT bk.IDKELUAR, p.NAMAPRODUK, bk.JUMLAHKELUAR, bk.TUJUAN, bk.TANGGALKELUAR
          FROM barangkeluar bk
          JOIN produk p ON bk.IDPRODUK = p.IDPRODUK";
$result = mysqli_query($conn, $query);

// Proses penghapusan barang keluar
if (isset($_GET['delete_id']) && is_numeric($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Ambil data IDPRODUK dan jumlah yang dikeluarkan sebelum dihapus
    $get_data_query = "SELECT IDPRODUK, JUMLAHKELUAR FROM barangkeluar WHERE IDKELUAR = ?";
    $stmt = mysqli_prepare($conn, $get_data_query);
    mysqli_stmt_bind_param($stmt, "i", $delete_id);
    mysqli_stmt_execute($stmt);
    $result_data = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result_data);

    if ($data) {
        $id_produk = $data['IDPRODUK'];
        $jumlah_keluar = $data['JUMLAHKELUAR'];

        // Update stok produk (tambah kembali karena dibatalkan keluar)
        $update_query = "UPDATE produk SET JUMLAHPRODUK = JUMLAHPRODUK + ? WHERE IDPRODUK = ?";
        $stmt_update = mysqli_prepare($conn, $update_query);
        if ($stmt_update) {
            mysqli_stmt_bind_param($stmt_update, "is", $jumlah_keluar, $id_produk);
            mysqli_stmt_execute($stmt_update);
        } else {
            $message = "Gagal menyiapkan query update!";
            $status = "error";
        }

        // Hapus data dari tabel barang keluar
        $delete_query = "DELETE FROM barangkeluar WHERE IDKELUAR = ?";
        $stmt_delete = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt_delete, "i", $delete_id);
        if (mysqli_stmt_execute($stmt_delete)) {
            $message = "Barang berhasil dihapus!";
            $status = "success";
        } else {
            $message = "Gagal menghapus barang!";
            $status = "error";
        }
    } else {
        $message = "Data tidak ditemukan!";
        $status = "error";
    }

    // Close all prepared statements
    mysqli_stmt_close($stmt);
    if (isset($stmt_update)) {
        mysqli_stmt_close($stmt_update);
    }
    mysqli_stmt_close($stmt_delete);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barang Keluar - user</title>
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
            <!-- Data Barang Keluar -->
            <div class="col-lg-12">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Data Barang Keluar</h5>

                        <?php if (isset($message)) { ?>
                            <div class="alert alert-<?php echo $status; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>

                        <a href="tambah_barang_keluar.php" class="btn btn-primary mb-3">+ Barang Keluar</a>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Nama Produk</th>
                                    <th>Jumlah Keluar</th>
                                    <th>Tujuan</th>
                                    <th>Tanggal Keluar</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['NAMAPRODUK']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['JUMLAHKELUAR']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['TUJUAN']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['TANGGALKELUAR']) . "</td>";
                                        echo "<td>
                                                <a href='view_barang_keluar.php?id=" . $row['IDKELUAR'] . "' class='btn btn-info btn-sm'>View</a>
                                                <a href='?delete_id=" . $row['IDKELUAR'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Anda yakin ingin menghapus barang ini?\")'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>Tidak ada data barang keluar.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>

                        <!-- Tombol Rekap Barang Keluar -->
                        <a href="rekap_barang_keluar.php" class="btn btn-success fas fa-file-excel"> Rekap</a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
