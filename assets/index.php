<?php
include "../config.php"; // Sisipkan file config.php untuk koneksi ke database

// Memeriksa sesi login admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Query untuk mengambil data produk dari tabel produk
$query_produk = "SELECT idproduk, namaproduk, hargaproduk, stokproduk FROM produk";
$result_produk = mysqli_query($conn, $query_produk);

if (!$result_produk) {
    die("Query error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Penjualan - Admin</title>
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
    <?php include "header.php"; // Memasukkan header untuk admin ?>

    <div class="container mt-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="mb-3">Form Penjualan</h2>
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="idpelanggan" class="mb-1">Nama Pelanggan</label>
                                <select class="form-control form-control-sm" id="idpelanggan" name="idpelanggan"
                                    required>
                                    <option value="">Pilih Nama Pelanggan</option>
                                    <!-- Pilihan pelanggan dari database -->
                                    <?php
                                    $query_pelanggan = "SELECT idpelanggan, namapelanggan FROM pelanggan";
                                    $result_pelanggan = mysqli_query($conn, $query_pelanggan);
                                    while ($row_pelanggan = mysqli_fetch_assoc($result_pelanggan)) {
                                        echo '<option value="' . $row_pelanggan['idpelanggan'] . '">' . $row_pelanggan['namapelanggan'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label for="idkaryawan" class="mb-1">Nama Karyawan</label>
                                <select class="form-control form-control-sm" id="idkaryawan" name="idkaryawan" required>
                                    <option value="">Pilih Nama Karyawan</option>
                                    <!-- Pilihan karyawan dari database -->
                                    <?php
                                    $query_karyawan = "SELECT idkaryawan, namakaryawan FROM karyawan";
                                    $result_karyawan = mysqli_query($conn, $query_karyawan);
                                    while ($row_karyawan = mysqli_fetch_assoc($result_karyawan)) {
                                        echo '<option value="' . $row_karyawan['idkaryawan'] . '">' . $row_karyawan['namakaryawan'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-4">
                                <label for="idproduk" class="mb-1">ID Produk</label>
                                <select class="form-control form-control-sm" id="idproduk" name="idproduk" onchange="getProductData()" required>
                                <option value="">Pilih ID Produk</option>
                                <?php
                                    $query_produk = "SELECT idproduk, namaproduk FROM produk";
                                    $result_produk = mysqli_query($conn, $query_produk);
                                    while ($row_produk = mysqli_fetch_assoc($result_produk)) {
                                        echo '<option value="' . $row_produk['idproduk'] . '">' . $row_produk['idproduk'] . '</option>';
                                    }
                                    ?>
                                </select>

                            </div>
                            <div class="col-lg-8">
                                <label for="namaproduk" class="mb-1">Nama Produk</label>
                                <input type="text" class="form-control form-control-sm bg-white" id="namaproduk"
                                    readonly>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-4">
                                <label for="hargaproduk" class="mb-1">Harga</label>
                                <input type="text" class="form-control form-control-sm bg-white" id="hargaproduk"
                                    readonly>
                            </div>
                            <div class="col-lg-4">
                                <label for="stokproduk" class="mb-1">Stok</label>
                                <input type="text" class="form-control form-control-sm bg-white" id="stokproduk"
                                    readonly>
                            </div>
                            <div class="col-lg-2">
                                <label for="quantity" class="mb-1">Qty</label>
                                <input type="number" class="form-control form-control-sm" id="quantity" name="quantity"
                                    onchange="calculateSubtotal()" placeholder="0" required>
                            </div>
                            <div class="col-lg-2">
                                <label for="subtotal" class="mb-1">Subtotal</label>
                                <input type="text" class="form-control form-control-sm bg-white" id="subtotal" readonly>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-lg-12 text-center">
                                <button class="btn btn-primary btn-sm" id="addProductBtn" onclick="addProduct()">Tambah Produk Beli</button>
                                <button class="btn btn-danger btn-sm" id="resetFormBtn" onclick="resetForm()">Reset</button>
                                <button class="btn btn-success btn-sm" id="saveTransactionBtn" onclick="saveTransaction()">Simpan</button>
                                <button class="btn btn-secondary btn-sm" onclick="printNota()">Cetak</button>
                            </div>
                        </div>
                        
                        <div class="row mt-3">
                            <div class="col-lg-12">
                                <h2 class="row mt-3 col-lg-10">List Pesanan</h2>
                                <table class="table table-striped table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID Produk</th>
                                            <th>Nama Produk</th>
                                            <th>Harga</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="productTableBody">
                                        <!-- Baris produk akan ditambahkan di sini melalui JavaScript -->
                                    </tbody>
                                </table>
                                <div class="bg-total p-2 text-right print-none">
                                    <strong>Total: </strong><span id="totalSubtotal">Rp.0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="mb-3 text-center">Daftar Produk</h2>
                        <div class="product-list">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID Produk</th>
                                        <th>Nama Produk</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_produk_list = "SELECT idproduk, namaproduk FROM produk";
                                    $result_produk_list = mysqli_query($conn, $query_produk_list);
                                    while ($row_produk_list = mysqli_fetch_assoc($result_produk_list)) {
                                        echo '<tr>';
                                        echo '<td>' . $row_produk_list['idproduk'] . '</td>';
                                        echo '<td>' . $row_produk_list['namaproduk'] . '</td>';
                                        echo '</tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function getProductData() {
        }

        function calculateSubtotal() {
        }

        function addProduct() {
        }

        function removeProduct(button) {
        }

        function resetForm() {
        }

        function updateTotal() {
        }

        function saveTransaction() {
        }

        function formatRupiah(angka) {
            var number_string = angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            return 'Rp.' + number_string;
        }

        function printNota() {
            var title = 'BABYSHOP';
            var alamat = 'Desa Babat , Kec. Babat';
            var kasir = '<?php echo $_SESSION["username"]; ?>'; // Ubah untuk memuat nama kasir
                var tanggal = '<?php echo date("d-m-Y"); ?>'; // Tanggal saat ini

                var namaPelanggan = document.getElementById('idpelanggan').selectedOptions[0].innerText.trim();

                var table = document.getElementById('productTableBody');
                var notaContent = '------------------------------------------------------\n';
                notaContent += '                    ' + title + '\n';
                notaContent += '    ' + alamat + '\n';
                notaContent += '\n';
                notaContent += ' ';
                notaContent += '  KASIR: ' + kasir + '     Tanggal: ' + tanggal + '\n';
                notaContent += '   Pelanggan: ' + namaPelanggan + '\n';
                notaContent += '-------------------------------------------------------\n';
                notaContent += ' QTY   PRODUK                HARGA   SUBTOTAL\n';
                notaContent += '-------------------------------------------------------\n';

                for (var i = 0; i < table.rows.length; i++) {
                    var cells = table.rows[i].cells;
                    var qty = cells[3].innerText.trim();
                    var produk = cells[1].innerText.trim();
                    var harga = cells[2].innerText.trim();
                    var subtotal = cells[4].innerText.trim();
                    notaContent += '  ' + qty + '   ' + produk + '   ' + harga + '   ' + subtotal + '\n';
                }

                var totalBelanja = '     Total Belanja: ' + document.getElementById('totalSubtotal').innerText.trim() + '\n';
                notaContent += '------------------------------------------------------\n';
                notaContent += totalBelanja;
                notaContent += '------------------------------------------------------\n';
                notaContent += '     * Terima Kasih Telah Berbelanja Di Toko Kami *\n';

                var printWindow = window.open('', '_blank');
                printWindow.document.open();
                printWindow.document.write('<pre>' + notaContent + '</pre>');
                printWindow.document.close();

                printWindow.print();
            }

    </script>
</body>
</html>