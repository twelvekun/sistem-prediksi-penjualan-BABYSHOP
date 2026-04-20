<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$selected_month = isset($_POST['bulan']) ? $_POST['bulan'] : date('Y-m');

$query = "SELECT pj.IDJUAL, pr.NAMAPRODUK, pj.JUMLAHJUAL, pj.TANGGALJUAL 
          FROM penjualan pj 
          LEFT JOIN produk pr ON pj.IDPRODUK = pr.IDPRODUK
          WHERE DATE_FORMAT(pj.TANGGALJUAL, '%Y-%m') = '$selected_month'
          ORDER BY pj.TANGGALJUAL DESC";
$result = mysqli_query($conn, $query);

if (isset($_POST['export'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'UD.Roda Alam');
    $sheet->setCellValue('A2', 'Rekap Data Penjualan - Bulan ' . date('F Y', strtotime($selected_month)));
    $sheet->setCellValue('A3', 'Dibuat tanggal: ' . date('d-m-Y'));
    $sheet->mergeCells('A1:E1');
    $sheet->mergeCells('A2:E2');
    $sheet->mergeCells('A3:E3');
    $sheet->setCellValue('A5', 'No');
    $sheet->setCellValue('B5', 'Nama Produk');
    $sheet->setCellValue('C5', 'Jumlah Jual');
    $sheet->setCellValue('D5', 'Tanggal Jual');

    $rowNum = 6;
    $no = 1;
    mysqli_data_seek($result, 0);
    while ($row = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue("A$rowNum", $no++);
        $sheet->setCellValue("B$rowNum", $row['NAMAPRODUK']);
        $sheet->setCellValue("C$rowNum", $row['JUMLAHJUAL']);
        $sheet->setCellValue("D$rowNum", date('d-m-Y', strtotime($row['TANGGALJUAL'])));
        $rowNum++;
    }

    $filename = 'Rekap_Penjualan_' . $selected_month . '_' . date('His') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment; filename=\"$filename\"");
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Penjualan</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <style>
        body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Rekap Data Penjualan</h5>

                        <!-- Tombol kembali -->
                        <div class="mb-3">
                            <a href="penjualan.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>

                        <!-- Form Filter Bulan -->
                        <form method="POST" class="row g-3 align-items-center mb-3">
                            <div class="col-auto">
                                <label for="bulan" class="form-label mb-0">Pilih Bulan:</label>
                            </div>
                            <div class="col-auto">
                                <input type="month" id="bulan" name="bulan" class="form-control" value="<?= $selected_month ?>">
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Tampilkan
                                </button>
                            </div>
                        </form>

                        <!-- Tabel Rekap -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead class="table-secondary">
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Jumlah Jual</th>
                                        <th>Tanggal Jual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>$no</td>";
                                            echo "<td>{$row['NAMAPRODUK']}</td>";
                                            echo "<td>{$row['JUMLAHJUAL']}</td>";
                                            echo "<td>" . date('d-m-Y', strtotime($row['TANGGALJUAL'])) . "</td>";
                                            echo "</tr>";
                                            $no++;
                                        }
                                    } else {
                                        echo "<tr><td colspan='4' class='text-center'>Tidak ada data penjualan untuk bulan ini.</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Tombol Unduh Rekap di Tengah -->
                        <div class="d-flex justify-content-center mt-3">
                            <form method="POST">
                                <input type="hidden" name="bulan" value="<?= $selected_month ?>">
                                <button type="submit" name="export" class="btn btn-success">
                                    <i class="fas fa-file-excel"></i> Unduh Rekap
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
