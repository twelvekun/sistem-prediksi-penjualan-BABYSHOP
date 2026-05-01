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
$sheet->setCellValue('A1', 'Mama Asix BABYSHOP');
$sheet->setCellValue('A2', 'Rekap Data Barang Keluar');
$sheet->setCellValue('A3', date('d-m-Y')); // Format tanggal rekapan
$sheet->mergeCells('A1:E1');
$sheet->mergeCells('A2:E2');
$sheet->mergeCells('A3:E3');
$sheet->setCellValue('A5', 'No.');
$sheet->setCellValue('B5', 'Nama Produk');
$sheet->setCellValue('C5', 'Jumlah Keluar');
$sheet->setCellValue('D5', 'Tujuan');
$sheet->setCellValue('E5', 'Tanggal Keluar');

// Ambil data barang keluar
$query = "SELECT bk.IDKELUAR, p.NAMAPRODUK, bk.JUMLAHKELUAR, bk.TUJUAN, bk.TANGGALKELUAR
          FROM barangkeluar bk
          JOIN produk p ON bk.IDPRODUK = p.IDPRODUK
          ORDER BY bk.TANGGALKELUAR DESC";
$result = mysqli_query($conn, $query);

$rowNum = 6; // Mulai dari baris ke-6 untuk data
$nomor = 1; // Nomor urut
while ($row = mysqli_fetch_assoc($result)) {
    $sheet->setCellValue('A' . $rowNum, $nomor); // Nomor urut
    $sheet->setCellValue('B' . $rowNum, $row['NAMAPRODUK']);
    $sheet->setCellValue('C' . $rowNum, $row['JUMLAHKELUAR']);
    $sheet->setCellValue('D' . $rowNum, $row['TUJUAN']);
    $sheet->setCellValue('E' . $rowNum, $row['TANGGALKELUAR']);
    $rowNum++;
    $nomor++; // Menambah nomor urut
}

// Set nama file
$filename = 'Rekap_Barang_Keluar_' . date('Ymd_His') . '.xlsx';

// Output ke browser
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
?>
