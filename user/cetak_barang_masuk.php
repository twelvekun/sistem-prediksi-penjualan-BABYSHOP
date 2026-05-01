<?php
session_start();
require '../config.php';

// Cek akses pengguna
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

if (!isset($_GET['id'])) {
    echo "ID tidak ditemukan.";
    exit();
}

$id = $_GET['id'];

// Ambil data barang masuk
$query = "SELECT bm.IDMASUK, bm.TANGGALMASUK, bm.JUMLAHMASUK, 
                 p.IDPRODUK, p.NAMAPRODUK, 
                 s.IDPEMASOK, s.NAMAPEMASOK 
          FROM barangmasuk bm
          JOIN produk p ON bm.IDPRODUK = p.IDPRODUK
          JOIN pemasok s ON bm.IDPEMASOK = s.IDPEMASOK
          WHERE bm.IDMASUK = '$id'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Detail Barang Masuk</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 30px;
            margin: 20px auto;
            width: 80%;
            background-color: #fff;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        .header h3 {
            margin: 5px 0;
            font-size: 20px;
        }
        .header p {
            font-size: 16px;
            margin: 5px 0;
        }
        .address {
            text-align: center;
            font-size: 16px;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
        }
        .footer p {
            font-size: 14px;
            margin: 0;
        }
        @media print {
            body {
                background-color: #fff;
            }
            .container {
                width: 100%;
                padding: 20px;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>UD.Roda Alam</h2>
            <p>Desa Babat, Kec. Babat,</p>
            <p>Kabupaten Lamongan, Jawa Timur</p>
            <h3>Rekap Data Barang Masuk</h3>
            <p><?= date('d-m-Y'); ?></p>
        </div>

        <table>
            <tr>
                <th>ID Barang Masuk</th>
                <td><?= $data['IDMASUK']; ?></td>
            </tr>
            <tr>
                <th>Tanggal Masuk</th>
                <td><?= $data['TANGGALMASUK']; ?></td>
            </tr>
            <tr>
                <th>Nama Produk</th>
                <td><?= $data['NAMAPRODUK']; ?></td>
            </tr>
            <tr>
                <th>Nama Pemasok</th>
                <td><?= $data['NAMAPEMASOK']; ?></td>
            </tr>
            <tr>
                <th>Jumlah Masuk</th>
                <td><?= $data['JUMLAHMASUK']; ?></td>
            </tr>
        </table>

        <div class="footer no-print">
            <button onclick="window.print()">Print this page</button>
        </div>
    </div>
</body>
</html>
