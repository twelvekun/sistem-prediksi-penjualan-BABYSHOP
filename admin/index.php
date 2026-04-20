<?php
session_start();
require '../config.php';

// Cek apakah user sudah login dan memiliki peran admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
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
$username = $_SESSION['username']; 

// Ambil jam saat ini
$hour = date("H");
if ($hour >= 5 && $hour < 12) {
    $greeting = "Selamat Pagi";
} elseif ($hour >= 12 && $hour < 15) {
    $greeting = "Selamat Siang";
} elseif ($hour >= 15 && $hour < 18) {
    $greeting = "Selamat Sore";
} else {
    $greeting = "Selamat Malam";
}

// PANGGIL HEADER
include "header.php"; 
?>

<style>
    /* Styling untuk tautan agar tidak terlihat seperti link biasa */
    .card-link {
        text-decoration: none !important;
        display: block;
        color: inherit;
    }
    .right-body{
            background: #FBF7E6;
            background-attachment: fixed;
            min-height: 100vh;
        }

    /* Desain Card Utama */
    .modern-card {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.05);
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        overflow: hidden;
        position: relative;
    }

    /* Efek Hover saat mouse diarahkan */
    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(0,0,0,0.1);
    }

    /* Layout Isi Card */
    .modern-card-body {
        padding: 24px;
        display: flex;
        align-items: center;
    }

    /* Kotak Ikon di Kiri */
    .icon-box {
        width: 65px;
        height: 65px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        color: #ffffff;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    /* Area Teks di Kanan */
    .info-box {
        margin-left: 20px;
    }

    .info-title {
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6c757d;
        margin-bottom: 5px;
    }

    .info-number {
        font-size: 1.8rem;
        font-weight: 800;
        color: #2b3445;
        margin: 0;
        line-height: 1.2;
    }

    /* Warna Gradasi untuk masing-masing Ikon */
    .bg-grad-primary { background: linear-gradient(135deg, #3A7BD5 0%, #3A6073 100%); }
    .bg-grad-success { background: linear-gradient(135deg, #11998E 0%, #38EF7D 100%); }
    .bg-grad-info { background: linear-gradient(135deg, #00B4DB 0%, #0083B0 100%); }
    .bg-grad-danger { background: linear-gradient(135deg, #ED213A 0%, #93291E 100%); }
    .bg-grad-warning { background: linear-gradient(135deg, #F2994A 0%, #F2C94C 100%); }
    .bg-grad-purple { background: linear-gradient(135deg, #8E2DE2 0%, #4A00E0 100%); }

    /* Hiasan background abstrak di sudut card */
    .modern-card::after {
        content: '';
        position: absolute;
        top: -20px;
        right: -20px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: rgba(0,0,0,0.02);
        z-index: 0;
        pointer-events: none;
    }
</style>
<body class="right-body">
<div class="card shadow-lg p-4 bg-white bg-opacity-90 rounded-4 mb-4 mt-2 border-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-dark m-0 fw-bold">
            <?= $greeting; ?>, <span class="text-primary"><?= $_SESSION['username']; ?></span> Semoga Harimu Selalu Menjadi Harimu Saja!
        </h4>
        <span class="text-muted"><i class="fas fa-calendar-alt mr-2"></i><?= date('d M Y'); ?></span>
    </div>
    
    <div class="row g-4">
        
        <div class="col-xl-4 col-md-6 mb-4">
            <a href="data_produk.php" class="card-link">
                <div class="modern-card">
                    <div class="modern-card-body z-1">
                        <div class="icon-box bg-grad-primary">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <div class="info-box">
                            <div class="info-title">Total Produk</div>
                            <h3 class="info-number"><?= number_format($jumlah_produk, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <a href="data_produk.php" class="card-link">
                <div class="modern-card">
                    <div class="modern-card-body z-1">
                        <div class="icon-box bg-grad-success">
                            <i class="fas fa-cubes"></i>
                        </div>
                        <div class="info-box">
                            <div class="info-title">Jumlah Stok Keseluruhan</div>
                            <h3 class="info-number"><?= number_format($stok_produk, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <a href="barang_masuk.php" class="card-link">
                <div class="modern-card">
                    <div class="modern-card-body z-1">
                        <div class="icon-box bg-grad-info">
                            <i class="fas fa-truck-loading"></i>
                        </div>
                        <div class="info-box">
                            <div class="info-title">Transaksi Barang Masuk</div>
                            <h3 class="info-number"><?= number_format($barang_masuk, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <a href="barang_keluar.php" class="card-link">
                <div class="modern-card">
                    <div class="modern-card-body z-1">
                        <div class="icon-box bg-grad-danger">
                            <i class="fas fa-dolly-flatbed"></i>
                        </div>
                        <div class="info-box">
                            <div class="info-title">Transaksi Barang Keluar</div>
                            <h3 class="info-number"><?= number_format($barang_keluar, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <a href="kategori.php" class="card-link">
                <div class="modern-card">
                    <div class="modern-card-body z-1">
                        <div class="icon-box bg-grad-warning">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="info-box">
                            <div class="info-title">Jenis Kategori</div>
                            <h3 class="info-number"><?= number_format($kategori, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <a href="penjualan.php" class="card-link">
                <div class="modern-card">
                    <div class="modern-card-body z-1">
                        <div class="icon-box bg-grad-purple">
                            <i class="fas fa-cash-register"></i>
                        </div>
                        <div class="info-box">
                            <div class="info-title">Data Penjualan</div>
                            <h3 class="info-number"><?= number_format($penjualan, 0, ',', '.'); ?></h3>
                        </div>
                    </div>
                </div>
            </a>
        </div>

    </div>
</div>

</div> 
        </div> 
    </div> 

    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="../assets/vendor/datatables/responsive.bootstrap4.min.js"></script>
</body>
</html>