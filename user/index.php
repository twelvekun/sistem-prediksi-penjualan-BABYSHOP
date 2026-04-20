<?php
session_start();
require '../config.php';

// Cek apakah user sudah login dan memiliki peran user
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

// Ambil data dari database untuk statistik
$jumlah_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM produk"))['total'];
$stok_produk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(JUMLAHPRODUK) AS total FROM produk"))['total'];
$barang_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM barangmasuk"))['total'];
$barang_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM barangkeluar"))['total'];
$kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori"))['total'];
$penjualan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM penjualan"))['total'];

// Ambil username yang sudah login
$username = $_SESSION['username']; // Pastikan username sudah diset di session saat login

// Ambil jam saat ini
$hour = date("H");

// Tentukan pesan sapaan berdasarkan jam
if ($hour >= 5 && $hour < 12) {
    $greeting = "Selamat Pagi";
} elseif ($hour >= 12 && $hour < 15) {
    $greeting = "Selamat Siang";
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = "Selamat Sore";
} else {
    $greeting = "Selamat Malam";
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        bbody {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }
        .dashboard-card {
            border-left: 5px solid #0d6efd;
            border-radius: 0.75rem;
            transition: all 0.2s ease-in-out;
        }
        .dashboard-card:hover {
            transform: scale(1.02);
        }
        .dashboard-icon {
            font-size: 2.5rem;
            opacity: 0.7;
        }
        .card-title {
            font-size: 1rem;
            font-weight: bold;
            text-transform: uppercase;
        }
        .card-number {
            font-size: 1.5rem;
            font-weight: bold;
        }
        /* Tambahan style untuk card yang bisa diklik */
        .card-link {
            color: inherit;
            text-decoration: none;
            display: block;
        }
        .card-link:hover {
            text-decoration: none;
            color: inherit;
        }
    </style>
</head>
<body class="bg-light">
    <?php 
    include "header.php"; 
    ?>

    <div class="container mt-4">
        <div class="card shadow-lg p-4 bg-white bg-opacity-75 rounded-4">
            <h4 class="mb-4 text-dark"><?= $greeting; ?>, <strong><?= $_SESSION['username']; ?></strong></h4>
            <div class="row g-4">
                <!-- Jumlah Produk -->
                <div class="col-md-4 mb-4">
                    <a href="data_produk.php" class="card-link">
                        <div class="card shadow-sm text-center border-primary">
                            <div class="card-body">
                                <i class="fas fa-box fa-2x text-primary mb-2"></i>
                                <h5 class="card-title">Jumlah Produk</h5>
                                <p class="card-text fs-4 fw-bold text-primary"><?= $jumlah_produk; ?></p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Total Stok Produk -->
                <div class="col-md-4 mb-4">
                    <a href="data_produk.php" class="card-link">
                        <div class="card shadow-sm text-center border-success">
                            <div class="card-body">
                                <i class="fas fa-cubes fa-2x text-success mb-2"></i>
                                <h5 class="card-title">Total Stok</h5>
                                <p class="card-text fs-4 fw-bold text-success"><?= $stok_produk; ?></p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Barang Masuk -->
                <div class="col-md-4 mb-4">
                    <a href="barang_masuk.php" class="card-link">
                        <div class="card shadow-sm text-center border-info">
                            <div class="card-body">
                                <i class="fas fa-truck-loading fa-2x text-info mb-2"></i>
                                <h5 class="card-title">Barang Masuk</h5>
                                <p class="card-text fs-4 fw-bold text-info"><?= $barang_masuk; ?></p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Barang Keluar -->
                <div class="col-md-4 mb-4">
                    <a href="barang_keluar.php" class="card-link">
                        <div class="card shadow-sm text-center border-danger">
                            <div class="card-body">
                                <i class="fas fa-dolly fa-2x text-danger mb-2"></i>
                                <h5 class="card-title">Barang Keluar</h5>
                                <p class="card-text fs-4 fw-bold text-danger"><?= $barang_keluar; ?></p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Kategori -->
                <div class="col-md-4 mb-4">
                    <a href="kategori.php" class="card-link">
                        <div class="card shadow-sm text-center border-warning">
                            <div class="card-body">
                                <i class="fas fa-tags fa-2x text-warning mb-2"></i>
                                <h5 class="card-title">Kategori</h5>
                                <p class="card-text fs-4 fw-bold text-warning"><?= $kategori; ?></p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Penjualan -->
                <div class="col-md-4 mb-4">
                    <a href="penjualan.php" class="card-link">
                        <div class="card shadow-sm text-center border-secondary">
                            <div class="card-body">
                                <i class="fas fa-cash-register fa-2x text-secondary mb-2"></i>
                                <h5 class="card-title">Penjualan</h5>
                                <p class="card-text fs-4 fw-bold text-secondary"><?= $penjualan; ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>