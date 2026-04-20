<?php
include 'config.php';
$query = "SELECT idproduk, namaproduk FROM produk";
$result = mysqli_query($conn, $query);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $produk_id = $row['idproduk'];
        $produk_nama = $row['namaproduk'];
        echo "<option value='$produk_id'>$produk_nama</option>";
    }
} else {
    echo "<option>Error: " . mysqli_error($conn) . "</option>";
}
mysqli_close($conn);
?>