<?php
require 'config.php';

$query = "SELECT * FROM user";
$result = mysqli_query($conn, $query);

if (!$result) {
    die("Query gagal: " . mysqli_error($conn));
}

echo "<h2>Data User (debug):</h2>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<pre>";
    print_r($row); // tampilkan isi lengkap dari setiap baris
    echo "</pre>";
}
?>
