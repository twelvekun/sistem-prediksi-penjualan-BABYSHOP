<?php
session_start();
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

$selected_month = isset($_POST['bulan']) ? $_POST['bulan'] : date('Y-m');

// Ubah query untuk mengambil juga informasi kategori
$query = "SELECT pj.IDJUAL, pr.NAMAPRODUK, k.JENISPRODUK, pj.JUMLAHJUAL, pj.TANGGALJUAL 
          FROM penjualan pj 
          LEFT JOIN produk pr ON pj.IDPRODUK = pr.IDPRODUK
          LEFT JOIN kategori k ON pr.IDKATEGORI = k.IDKATEGORI
          WHERE DATE_FORMAT(pj.TANGGALJUAL, '%Y-%m') = '$selected_month'
          ORDER BY pj.TANGGALJUAL DESC";
$result = mysqli_query($conn, $query);

if (isset($_POST['export'])) {
    // Buat spreadsheet baru
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Rekap Penjualan');
    
    // Atur lebar kolom agar sesuai dengan konten
    $sheet->getColumnDimension('A')->setWidth(8);  // No.
    $sheet->getColumnDimension('B')->setWidth(40); // Nama Produk
    $sheet->getColumnDimension('C')->setWidth(15); // Jumlah Jual
    $sheet->getColumnDimension('D')->setWidth(20); // Tanggal Jual
    
    // Judul dan header
    $bulan_tahun = date('F Y', strtotime($selected_month));
    $sheet->setCellValue('A1', 'Mama Asix BABYSHOP');
    $sheet->setCellValue('A2', 'REKAP DATA PENJUALAN - BULAN ' . strtoupper($bulan_tahun));
    $sheet->setCellValue('A3', 'Tanggal: ' . date('d-m-Y')); // Format tanggal rekapan
    $sheet->mergeCells('A1:D1');
    $sheet->mergeCells('A2:D2');
    $sheet->mergeCells('A3:D3');
    
    // Styling judul
    $sheet->getStyle('A1:D1')->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle('A2:D2')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A3:D3')->getFont()->setSize(12);
    $sheet->getStyle('A1:D3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Header tabel
    $sheet->setCellValue('A5', 'NO.');
    $sheet->setCellValue('B5', 'NAMA PRODUK');
    $sheet->setCellValue('C5', 'JUMLAH JUAL');
    $sheet->setCellValue('D5', 'TANGGAL JUAL');
    
    // Styling header tabel
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4472C4'],
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
    ];
    $sheet->getStyle('A5:D5')->applyFromArray($headerStyle);
    $sheet->getRowDimension(5)->setRowHeight(20); // Tinggi baris header
    
    // Isi data
    $rowNum = 6;
    $no = 1;
    $totalPenjualan = 0;
    
    mysqli_data_seek($result, 0); // Reset pointer hasil query
    while ($row = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue('A' . $rowNum, $no++);
        
        // Gabungkan kategori dan nama produk
        $namaProduk = !empty($row['JENISPRODUK']) ? 
                      $row['JENISPRODUK'] . " " . $row['NAMAPRODUK'] : 
                      $row['NAMAPRODUK'];
        
        $sheet->setCellValue('B' . $rowNum, $namaProduk);
        $sheet->setCellValue('C' . $rowNum, $row['JUMLAHJUAL']);
        
        // Format tanggal agar lebih mudah dibaca
        $tanggalJual = date('d-m-Y', strtotime($row['TANGGALJUAL']));
        $sheet->setCellValue('D' . $rowNum, $tanggalJual);
        
        // Alignment untuk data
        $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        
        $totalPenjualan += (int)$row['JUMLAHJUAL'];
        $rowNum++;
    }
    
    // Styling untuk seluruh data
    $dataBorderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle('A6:D' . ($rowNum - 1))->applyFromArray($dataBorderStyle);
    
    // Set warna zebra-striping untuk baris data (selang-seling)
    for ($i = 6; $i < $rowNum; $i += 2) {
        $sheet->getStyle('A' . $i . ':D' . $i)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('E9EFF7');
    }
    
    // Tambahkan footer dengan total
    $footerRow = $rowNum + 1;
    $sheet->setCellValue('A' . $footerRow, 'TOTAL PENJUALAN:');
    $sheet->setCellValue('C' . $footerRow, $totalPenjualan);
    $sheet->mergeCells('A' . $footerRow . ':B' . $footerRow);
    $sheet->getStyle('A' . $footerRow . ':D' . $footerRow)->getFont()->setBold(true);
    $sheet->getStyle('A' . $footerRow . ':D' . $footerRow)->applyFromArray($dataBorderStyle);
    $sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle('C' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Dapatkan username pengguna yang sedang login
    $username = $_SESSION['username'] ?? 'Administrator'; // Default jika username tidak tersedia
    
    // Tambahkan tanda tangan dan informasi penanggung jawab
    $signatureRow = $footerRow + 3;
    $sheet->setCellValue('D' . $signatureRow, 'Lamongan, ' . date('d-m-Y'));
    $sheet->setCellValue('D' . ($signatureRow + 4), '(________________)');
    $sheet->getStyle('D' . $signatureRow . ':D' . ($signatureRow + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Atur area print
    $sheet->getPageSetup()->setPrintArea('A1:D' . ($signatureRow + 5));
    $sheet->getPageSetup()->setFitToPage(true);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(0);
    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
    
    // Tambahkan header dan footer untuk cetakan
    $sheet->getHeaderFooter()
        ->setOddHeader('&C&B&16UD. Mama Asix BABYSHOP - REKAP PENJUALAN')
        ->setOddFooter('&L&B' . $sheet->getTitle() . '&R&P dari &N');
    
    // Set nama file
    $bulan_filename = str_replace(' ', '_', $bulan_tahun);
    $filename = 'Rekap_Penjualan_' . $bulan_filename . '_' . date('Ymd_His') . '.xlsx';
    
    // Output ke browser
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
            background: url('../assets/img/.jpg') no-repeat center center fixed;
            background-size: cover;
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
                                            // Gabungkan kategori dan nama produk
                                            $namaProduk = !empty($row['JENISPRODUK']) ? 
                                                          $row['JENISPRODUK'] . " " . $row['NAMAPRODUK'] : 
                                                          $row['NAMAPRODUK'];
                                            
                                            echo "<tr>";
                                            echo "<td>$no</td>";
                                            echo "<td>{$namaProduk}</td>";
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