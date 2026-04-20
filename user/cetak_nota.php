<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php'; // Menghubungkan dengan file config.php

// Ambil ID Barang Masuk dari URL
if (isset($_GET['id'])) {
    $id_barang_masuk = $_GET['id'];

    // Query untuk mengambil data barang masuk berdasarkan ID
    $query = "SELECT bm.IDMASUK, p.NAMAPRODUK, pe.NAMAPEMASOK, bm.JUMLAHMASUK, bm.TANGGALMASUK, bm.IDPRODUK, bm.IDPEMASOK
              FROM barangmasuk bm
              LEFT JOIN produk p ON bm.IDPRODUK = p.IDPRODUK
              LEFT JOIN pemasok pe ON bm.IDPEMASOK = pe.IDPEMASOK
              WHERE bm.IDMASUK = $id_barang_masuk";
    $result = mysqli_query($conn, $query);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        echo "Data tidak ditemukan!";
        exit();
    }
} else {
    echo "ID Barang Masuk tidak ditemukan!";
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nota Barang Masuk</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        @media print {
            body {
                font-family: Arial, sans-serif;
                font-size: 14px;
            }
            .btn-print {
                display: none;
            }
        }
        .container {
            margin-top: 30px;
        }
        .nota-header {
            text-align: center;
        }
        .nota-header h3 {
            margin-bottom: 0;
        }
        .nota-header p {
            font-size: 14px;
        }
        .nota-footer {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="nota-header">
            <h3>Nota Barang Masuk</h3>
            <p>Tanggal Masuk: <?php echo $data['TANGGALMASUK']; ?></p>
            <p>Nama Produk: <?php echo $data['NAMAPRODUK']; ?></p>
            <p>Nama Pemasok: <?php echo $data['NAMAPEMASOK']; ?></p>
        </div>

        <table class="table table-bordered mt-4">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Pemasok</th>
                    <th>Jumlah Masuk</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $data['NAMAPRODUK']; ?></td>
                    <td><?php echo $data['NAMAPEMASOK']; ?></td>
                    <td><?php echo $data['JUMLAHMASUK']; ?></td>
                </tr>
            </tbody>
        </table>

        <div class="nota-footer">
            <p>Terima kasih telah melakukan transaksi dengan kami!</p>
            <button class="btn btn-primary btn-print" onclick="window.print()">Cetak Nota</button>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
