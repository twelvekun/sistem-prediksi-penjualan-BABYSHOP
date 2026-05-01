<?php
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BABYSHOP- Admin Dashboard</title>
    <link rel="icon" href="../favicon.ico">
    <link rel="icon" href="../icon.ico" type="image/ico">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="../assets/fontawesome/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="../assets/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="../assets/vendor/datatables/responsive.bootstrap4.min.css" rel="stylesheet">
    <style>

        body {
            overflow-x: hidden;
            background-color: #f8f9fa;
        }
        #wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
            min-height: 100vh;
        }
        /* CSS untuk Sidebar */
        #sidebar-wrapper {
            min-width: 250px;
            max-width: 250px;
            background-color: #FFF0DB;
            color: #fff;
            transition: all 0.3s;
        }
        #sidebar-wrapper .sidebar-heading {
            padding: 1rem;
            text-align: center;
        }
        .sidebar-logo {
            max-width: 200%;
            max-height: 150px;
            object-fit: contain;
            transition: 0.3s;
        }
        #sidebar-wrapper .list-group-item {
            border: none;
            padding: 15px 20px;
            background-color: transparent;
            color: black;
            transition: 0.2s;
        }
        #sidebar-wrapper .list-group-item:hover,
        #sidebar-wrapper .list-group-item.active {
            background-color: #495057;
            color: #fff;
            text-decoration: none;
        }
        #page-content-wrapper {
            flex-grow: 1; /* Penting: Membuat area ini memenuhi sisa ruang */
            min-width: 0;
            padding: 20px;
            background: #FFF0DB;
        }

        /* Responsif Mobile */
        @media (max-width: 768px) {
            #sidebar-wrapper {
                min-width: 70px;
                max-width: 70px;
            }
            #sidebar-wrapper .sidebar-heading {
                padding: 15px 5px;
            }
            .sidebar-logo {
                max-height: 35px; 
            }
            #sidebar-wrapper .menu-text {
                display: none;
            }
            #sidebar-wrapper .list-group-item {
                text-align: center;
                padding: 15px 0;
            }
            #sidebar-wrapper .list-group-item i {
                margin-right: 0 !important;
                font-size: 1.2rem;
            }
            
        }
    </style>
</head>
<body>
    <div id="wrapper">
        
        <div id="sidebar-wrapper" class="shadow-sm">
            <div class="sidebar-heading bg-blue text-dark font-weight-bold">
                <img src="../assets/img/mama asix.png" alt="Logo MAMA ASIX BABYSHOP" class="sidebar-logo">
            </div>
            <div class="list-group list-group-flush mt-3">
                
            <div class="list-group list-group-flush mt-3">
                <a href="index.php" class="list-group-item list-group-item-action">
                    <i class="fa fa-home"></i> <span class="menu-text">Dashboard</span>
                </a>
                <a href="data_produk.php" class="list-group-item list-group-item-action">
                    <i class="fa fa-cubes fa-fw mr-2"></i> <span class="menu-text">Data Produk</span>
                </a>
                <a href="barang_masuk.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-truck-loading fa-fw mr-2"></i> <span class="menu-text">Barang Masuk</span>
                </a>
                <a href="barang_keluar.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-dolly fa-fw mr-2"></i> <span class="menu-text">Barang Keluar</span>
                </a>
                <a href="kategori.php" class="list-group-item list-group-item-action">
                    <i class="fa fa-folder-open fa-fw mr-2"></i> <span class="menu-text">Kategori</span>
                </a>
                <a href="penjualan.php" class="list-group-item list-group-item-action">
                    <i class="fa fa-shopping-cart fa-fw mr-2"></i> <span class="menu-text">Penjualan</span>
                </a>
                
                <hr class="border-secondary my-2">
                
                <a href="../logout.php" class="list-group-item list-group-item-action text-danger" onclick="return confirm('Anda yakin ingin keluar?');">
                    <i class="fa fa-sign-out-alt fa-fw mr-2"></i> <span class="menu-text">Keluar</span>
                </a>
            </div>
        </div>
        <div id="page-content-wrapper">
            <div class="container-fluid">
                
            </div>
        </div>
        </div> <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/vendor/datatables/jquery.dataTables.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.bootstrap4.min.js"></script>
    <script src="../assets/vendor/datatables/dataTables.responsive.min.js"></script>
    <script src="../assets/vendor/datatables/responsive.bootstrap4.min.js"></script>
</body>
    <div class="container">
