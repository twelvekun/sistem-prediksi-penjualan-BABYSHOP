<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Ambil data kategori
$query = "SELECT k.IDKATEGORI, k.JENISPRODUK, COUNT(p.IDPRODUK) AS jumlah_produk
          FROM kategori k
          LEFT JOIN produk p ON k.IDKATEGORI = p.IDKATEGORI
          GROUP BY k.IDKATEGORI";
$result = mysqli_query($conn, $query);

// Proses penghapusan kategori
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Query untuk menghapus kategori berdasarkan ID
    $delete_query = "DELETE FROM kategori WHERE IDKATEGORI = $delete_id";
    if (mysqli_query($conn, $delete_query)) {
        $message = "Kategori berhasil dihapus!";
        $status = "success";
    } else {
        $message = "Gagal menghapus kategori!";
        $status = "error";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kategori - user</title>
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
            <div class="col-lg">
                <div class="card shadow">
                    <div class="d-flex justify-content-between align-items-center mt-3 mx-4">
                        <h5>Data Kategori</h5>
                        <a href="tambah_kategori.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Tambah Kategori
                        </a>
                    </div>

                    <!-- Menampilkan pesan notifikasi jika ada -->
                    <?php if (isset($message)) { ?>
                        <div class="alert alert-<?php echo $status; ?> alert-dismissible fade show mt-3 mx-4" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php } ?>

                    <!-- Tabel Kategori -->
                    <table class="table table-bordered mt-3 mx-4">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Kategori</th>
                                <th>Jumlah Produk</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                            $no = 1; // Menambahkan nomor urut
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $no++ . "</td>"; // Menampilkan nomor urut
                                    echo "<td>" . $row['JENISPRODUK'] . "</td>";
                                    echo "<td>" . $row['jumlah_produk'] . "</td>"; // Menampilkan jumlah produk
                                    echo "<td>
                                            <a href='edit_kategori.php?id=" . $row['IDKATEGORI'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                            <a href='?delete_id=" . $row['IDKATEGORI'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Anda yakin ingin menghapus kategori ini?\")'>Hapus</a>
                                          </td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='text-center'>Tidak ada data kategori.</td></tr>";
                            }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
