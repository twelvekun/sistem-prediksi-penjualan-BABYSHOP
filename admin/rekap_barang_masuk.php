<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
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

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Rekap Barang Masuk');

// Atur lebar kolom agar sesuai dengan konten
$sheet->getColumnDimension('A')->setWidth(8);  // No.
$sheet->getColumnDimension('B')->setWidth(40); // Nama Produk
$sheet->getColumnDimension('C')->setWidth(15); // Jumlah Masuk
$sheet->getColumnDimension('D')->setWidth(25); // Pemasok
$sheet->getColumnDimension('E')->setWidth(20); // Tanggal Masuk

// Judul dan header
$sheet->setCellValue('A1', 'Mama Asix BABYSHOP');
$sheet->setCellValue('A2', 'REKAP DATA BARANG MASUK');
$sheet->setCellValue('A3', 'Tanggal: ' . date('d-m-Y')); // Format tanggal rekapan
$sheet->mergeCells('A1:E1');
$sheet->mergeCells('A2:E2');
$sheet->mergeCells('A3:E3');

// Styling judul
$sheet->getStyle('A1:E1')->getFont()->setBold(true)->setSize(16);
$sheet->getStyle('A2:E2')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A3:E3')->getFont()->setSize(12);
$sheet->getStyle('A1:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Header tabel
$sheet->setCellValue('A5', 'NO.');
$sheet->setCellValue('B5', 'NAMA PRODUK');
$sheet->setCellValue('C5', 'JUMLAH MASUK');
$sheet->setCellValue('D5', 'PEMASOK');
$sheet->setCellValue('E5', 'TANGGAL MASUK');

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
$sheet->getStyle('A5:E5')->applyFromArray($headerStyle);
$sheet->getRowDimension(5)->setRowHeight(20); // Tinggi baris header

// Ambil data barang masuk
$query = "SELECT bm.IDMASUK, p.NAMAPRODUK, bm.JUMLAHMASUK, ps.NAMAPEMASOK, bm.TANGGALMASUK
          FROM barangmasuk bm
          JOIN produk p ON bm.IDPRODUK = p.IDPRODUK
          JOIN pemasok ps ON bm.IDPEMASOK = ps.IDPEMASOK
          ORDER BY bm.TANGGALMASUK DESC";
$result = mysqli_query($conn, $query);

$rowNum = 6; // Mulai dari baris ke-6 untuk data
$nomor = 1; // Nomor urut
$totalBarangMasuk = 0; // Untuk menghitung total barang masuk

while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNum, $nomor); // Nomor urut
    $sheet->setCellValue('B' . $rowNum, $row['NAMAPRODUK']);
    $sheet->setCellValue('C' . $rowNum, $row['JUMLAHMASUK']);
    $sheet->setCellValue('D' . $rowNum, $row['NAMAPEMASOK']);
    
    // Format tanggal agar lebih mudah dibaca
    $tanggalMasuk = date('d-m-Y', strtotime($row['TANGGALMASUK']));
    $sheet->setCellValue('E' . $rowNum, $tanggalMasuk);
    
    // Alignment untuk data
    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    $totalBarangMasuk += (int)$row['JUMLAHMASUK'];
    $rowNum++;
    $nomor++;
}

// Styling untuk seluruh data
$dataBorderStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];
$sheet->getStyle('A6:E' . ($rowNum - 1))->applyFromArray($dataBorderStyle);

// Set warna zebra-striping untuk baris data (selang-seling)
for ($i = 6; $i < $rowNum; $i += 2) {
    $sheet->getStyle('A' . $i . ':E' . $i)->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E9EFF7');
}

// Tambahkan footer dengan total
$footerRow = $rowNum + 1;
$sheet->setCellValue('A' . $footerRow, 'TOTAL BARANG MASUK:');
$sheet->setCellValue('C' . $footerRow, $totalBarangMasuk);
$sheet->mergeCells('A' . $footerRow . ':B' . $footerRow);
$sheet->getStyle('A' . $footerRow . ':E' . $footerRow)->getFont()->setBold(true);
$sheet->getStyle('A' . $footerRow . ':E' . $footerRow)->applyFromArray($dataBorderStyle);
$sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('C' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Dapatkan username pengguna yang sedang login
$username = $_SESSION['username'] ?? 'Administrator'; // Default jika username tidak tersedia

// Tambahkan tanda tangan dan informasi penanggung jawab
$signatureRow = $footerRow + 3;
$sheet->setCellValue('E' . $signatureRow, 'Lamongan, ' . date('d-m-Y'));
$sheet->setCellValue('E' . ($signatureRow + 4), '(________________)');
$sheet->getStyle('E' . $signatureRow . ':E' . ($signatureRow + 5))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Atur area print
$sheet->getPageSetup()->setPrintArea('A1:E' . ($signatureRow + 5));
$sheet->getPageSetup()->setFitToPage(true);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);
$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

// Tambahkan header dan footer untuk cetakan
$sheet->getHeaderFooter()
    ->setOddHeader('&C&B&16UD. Mama Asix BABYSHOP - REKAP BARANG MASUK')
    ->setOddFooter('&L&B' . $sheet->getTitle() . '&R&P dari &N');

// Set nama file
$filename = 'Rekap_Barang_Masuk_' . date('Ymd_His') . '.xlsx';

// Output ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>