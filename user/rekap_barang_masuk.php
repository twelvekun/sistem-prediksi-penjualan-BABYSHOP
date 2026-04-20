<?php
session_start();

// Cek apakah pengguna sudah login dan memiliki akses yang benar
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'user') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';
require '../vendor/autoload.php'; // Pastikan sudah install PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Buat spreadsheet baru
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul dan header
$sheet->setCellValue('A1', 'UD.Roda Alam');
$sheet->setCellValue('A2', 'Rekap Data Barang Masuk');
$sheet->setCellValue('A3', date('d-m-Y')); // Format tanggal rekapan
$sheet->mergeCells('A1:E1');
$sheet->mergeCells('A2:E2');
$sheet->mergeCells('A3:E3');
$sheet->setCellValue('A5', 'No.');
$sheet->setCellValue('B5', 'Nama Produk');
$sheet->setCellValue('C5', 'Jumlah Masuk');
$sheet->setCellValue('D5', 'Pemasok');
$sheet->setCellValue('E5', 'Tanggal Masuk');

// Ambil data barang masuk
$query = "SELECT bm.IDMASUK, p.NAMAPRODUK, bm.JUMLAHMASUK, ps.NAMAPEMASOK, bm.TANGGALMASUK
          FROM barangmasuk bm
          JOIN produk p ON bm.IDPRODUK = p.IDPRODUK
          JOIN pemasok ps ON bm.IDPEMASOK = ps.IDPEMASOK
          ORDER BY bm.TANGGALMASUK DESC";
$result = mysqli_query($conn, $query);

$rowNum = 6; // Mulai dari baris ke-6 untuk data
$nomor = 1; // Nomor urut
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNum, $nomor); // Nomor urut
    $sheet->setCellValue('B' . $rowNum, $row['NAMAPRODUK']);
    $sheet->setCellValue('C' . $rowNum, $row['JUMLAHMASUK']);
    $sheet->setCellValue('D' . $rowNum, $row['NAMAPEMASOK']);
    $sheet->setCellValue('E' . $rowNum, $row['TANGGALMASUK']);
    $rowNum++;
    $nomor++; // Menambah nomor urut
}

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
