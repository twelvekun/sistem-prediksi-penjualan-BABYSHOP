<?php
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
$sheet->setTitle('Rekap Produk');

// Atur lebar kolom agar sesuai dengan konten
$sheet->getColumnDimension('A')->setWidth(15); // Kode Barang
$sheet->getColumnDimension('B')->setWidth(40); // Nama Produk
$sheet->getColumnDimension('C')->setWidth(20); // Kategori
$sheet->getColumnDimension('D')->setWidth(15); // Jumlah Stok

// Judul dan header
$sheet->setCellValue('A1', 'Mama Asix BABYSHOP');
$sheet->setCellValue('A2', 'REKAP DATA PRODUK');
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
$sheet->setCellValue('A5', 'KODE BARANG');
$sheet->setCellValue('B5', 'NAMA PRODUK');
$sheet->setCellValue('C5', 'KATEGORI');
$sheet->setCellValue('D5', 'JUMLAH STOK');

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

// Ambil data produk
$query = "SELECT p.IDPRODUK, p.NAMAPRODUK, k.JENISPRODUK, p.JUMLAHPRODUK
          FROM produk p
          LEFT JOIN kategori k ON p.IDKATEGORI = k.IDKATEGORI
          ORDER BY k.JENISPRODUK, p.NAMAPRODUK"; // Urutkan berdasarkan kategori dan nama
$result = mysqli_query($conn, $query);

$rowNum = 6; // Mulai dari baris ke-6 untuk data
$totalProduk = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNum, $row['IDPRODUK']);
    $sheet->setCellValue('B' . $rowNum, $row['NAMAPRODUK']);
    $sheet->setCellValue('C' . $rowNum, $row['JENISPRODUK']);
    $sheet->setCellValue('D' . $rowNum, $row['JUMLAHPRODUK']);
    
    // Highlight merah untuk stok menipis
    $jumlahStok = (int)$row['JUMLAHPRODUK'];
    if ($jumlahStok <= 10) {
        $sheet->getStyle('D' . $rowNum)->getFont()->getColor()->setRGB('FF0000');
    }
    
    $totalProduk += $jumlahStok;
    
    // Alignment untuk data
    $sheet->getStyle('A' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D' . $rowNum)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
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
$sheet->setCellValue('A' . $footerRow, 'TOTAL PRODUK:');
$sheet->setCellValue('D' . $footerRow, $totalProduk);
$sheet->mergeCells('A' . $footerRow . ':C' . $footerRow);
$sheet->getStyle('A' . $footerRow . ':D' . $footerRow)->getFont()->setBold(true);
$sheet->getStyle('A' . $footerRow . ':D' . $footerRow)->applyFromArray($dataBorderStyle);
$sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle('D' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Dapatkan username pengguna yang sedang login
$username = $_SESSION['username'] ?? 'Administrator'; // Default jika session tidak tersedia

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
    ->setOddHeader('&C&B&16UD. Mama Asix BABYSHOP- REKAP DATA PRODUK')
    ->setOddFooter('&L&B' . $sheet->getTitle() . '&R&P dari &N');

// Set nama file
$filename = 'Rekap_Produk_' . date('Ymd_His') . '.xlsx';

// Output ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>