<?php
session_start();
require '../config.php';

// Cek akses pengguna
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID tidak ditemukan.";
    exit();
}

$id = $_GET['id'];

// Ambil data barang keluar
$query = "SELECT bk.IDKELUAR, bk.TANGGALKELUAR, bk.JUMLAHKELUAR, bk.TUJUAN,
                 p.IDPRODUK, p.NAMAPRODUK
          FROM barangkeluar bk
          JOIN produk p ON bk.IDPRODUK = p.IDPRODUK
          WHERE bk.IDKELUAR = '$id'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Detail Barang Keluar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            padding: 30px;
            margin: 20px auto;
            width: 80%;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
        }
        th {
            background: #eee;
        }
        .no-print {
            margin-top: 20px;
            text-align: center;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h2>Mama Asix BABYSHOP</h2>
        <p>Desa Babat, Kec. Babat</p>
        <p>Kabupaten Lamongan, Jawa Timur</p>
        <h3>Detail Barang Keluar</h3>
        <p><?= date('d-m-Y'); ?></p>
    </div>

    <table>
        <tr>
            <th>ID Barang Keluar</th>
            <td><?= $data['IDKELUAR']; ?></td>
        </tr>
        <tr>
            <th>Tanggal Keluar</th>
            <td><?= $data['TANGGALKELUAR']; ?></td>
        </tr>
        <tr>
            <th>Nama Produk</th>
            <td><?= $data['NAMAPRODUK']; ?></td>
        </tr>
        <tr>
            <th>Tujuan</th>
            <td><?= $data['TUJUAN']; ?></td>
        </tr>
        <tr>
            <th>Jumlah Keluar</th>
            <td><?= $data['JUMLAHKELUAR']; ?></td>
        </tr>
    </table>

    <div class="no-print">
        <button onclick="window.print()">Print</button>
    </div>
</div>

</body>
</html>