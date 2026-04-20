<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'skrip2';


// Membuat koneksi
$conn = mysqli_connect($host, $user, $pass, $db);

// Memeriksa koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
try {
    $conn = new mysqli($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    die('Koneksi ke database gagal: ' . $e->getMessage());
}
?>
