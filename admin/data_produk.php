<?php
session_start();

if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Proses penghapusan
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $delete_query = "DELETE FROM produk WHERE IDPRODUK = '$delete_id'";
    if (mysqli_query($conn, $delete_query)) {
        $message = "Produk berhasil dihapus!";
        $status = "success";
    } else {
        $message = "Gagal menghapus produk!";
        $status = "danger";
    }
}

// TAMBAH PRODUK
if (isset($_POST['tambah_produk'])) {
    $idkategori = $_POST['idkategori'];
    $namaproduk = $_POST['namaproduk'];
    $jumlahproduk = $_POST['jumlahproduk'];

    // Ambil kode prefix kategori
    $kategori_query = "SELECT JENISPRODUK FROM kategori WHERE IDKATEGORI = '$idkategori'";
    $kategori_result = mysqli_query($conn, $kategori_query);
    $kategori_data = mysqli_fetch_assoc($kategori_result);
    $kategori_prefix = substr(strtolower($kategori_data['JENISPRODUK']), 0, 2); // Awalan dua huruf kategori

    // Ambil nomor urut produk berdasarkan kategori
    $urutan_query = "SELECT MAX(CAST(SUBSTRING(IDPRODUK, 3) AS UNSIGNED)) AS max_urutan 
                     FROM produk WHERE IDKATEGORI = '$idkategori'";
    $urutan_result = mysqli_query($conn, $urutan_query);
    $urutan_data = mysqli_fetch_assoc($urutan_result);
    $urutan = ($urutan_data['max_urutan']) ? $urutan_data['max_urutan'] + 1 : 1; // Tambah 1 pada nomor urut terakhir

    // Format kode produk (contoh: se001, se002, ...)
    $kodeproduk = $kategori_prefix . str_pad($urutan, 3, '0', STR_PAD_LEFT);

    // Query untuk menambah produk
    $query = "INSERT INTO produk (IDKATEGORI, NAMAPRODUK, JUMLAHPRODUK, IDPRODUK) 
              VALUES ('$idkategori', '$namaproduk', '$jumlahproduk', '$kodeproduk')";

    if (mysqli_query($conn, $query)) {
        $message = "Produk berhasil ditambahkan!";
        $status = "success";
    } else {
        $message = "Gagal menambahkan produk: " . mysqli_error($conn);
        $status = "danger";
    }
}

// EDIT PRODUK
if (isset($_POST['edit_produk'])) {
    $idproduk = $_POST['idproduk'];
    $namaproduk = $_POST['edit_namaproduk'];
    $idkategori = $_POST['edit_idkategori'];
    $jumlahproduk = $_POST['edit_jumlahproduk'];

    // Query untuk update produk
    $update_query = "UPDATE produk 
                    SET NAMAPRODUK = '$namaproduk', IDKATEGORI = '$idkategori', JUMLAHPRODUK = '$jumlahproduk'
                    WHERE IDPRODUK = '$idproduk'";

    if (mysqli_query($conn, $update_query)) {
        $message = "Produk berhasil diperbarui!";
        $status = "success";
    } else {
        $message = "Gagal memperbarui produk: " . mysqli_error($conn);
        $status = "danger";
    }
}

// Filter kategori berdasarkan pilihan
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Filter pencarian berdasarkan nama produk
$search_filter = isset($_GET['search']) ? $_GET['search'] : '';

// Query data produk dengan kategori dan pencarian
$query = "SELECT p.IDPRODUK, p.NAMAPRODUK, p.JUMLAHPRODUK, p.IDKATEGORI, k.JENISPRODUK
          FROM produk p
          LEFT JOIN kategori k ON p.IDKATEGORI = k.IDKATEGORI
          WHERE 1=1";

// Tambahkan filter kategori jika dipilih
if ($kategori_filter) {
    $query .= " AND p.IDKATEGORI = '$kategori_filter'";
}

// Tambahkan filter pencarian jika ada
if ($search_filter) {
    $query .= " AND p.NAMAPRODUK LIKE '%$search_filter%'";
}

$result = mysqli_query($conn, $query);

// Ambil daftar kategori untuk dropdown
$kategori_query = "SELECT * FROM kategori";
$kategori_result = mysqli_query($conn, $kategori_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
        background:#FBF7E6;
        background-attachment: fixed;
        min-height: 100vh;
        }
         .right-body{
            background: #FBF7E6;
            background-attachment: fixed;
            min-height: 100vh;
        }
        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        .search-form {
            flex-grow: 1;
            max-width: 300px;
        }
        #filterForm {
            min-width: 200px;
            margin-right: 30px;
        }
        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
                align-items: stretch;
            }
            .search-form {
                max-width: 100%;
                margin-top: 15px;
                margin-bottom: 15px;
            }
            #filterForm {
                margin-right: 0;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body class="right-body">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <!-- Data Produk -->
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Data Produk</h5>

                        <?php if (isset($message)) { ?>
                            <div class="alert alert-<?php echo $status; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php } ?>

                        <?php 
                        // Tampilkan informasi filter yang aktif
                        if ($kategori_filter || $search_filter) { 
                            echo '<div class="mb-2">';
                            echo '<div class="d-flex gap-2 align-items-center">';
                            echo '<strong>Filter aktif:</strong>';
                            
                            if ($kategori_filter) {
                                // Dapatkan nama kategori
                                $kat_query = "SELECT JENISPRODUK FROM kategori WHERE IDKATEGORI = '$kategori_filter'";
                                $kat_result = mysqli_query($conn, $kat_query);
                                $kat_data = mysqli_fetch_assoc($kat_result);
                                
                                echo '<span class="badge bg-info me-1">Kategori: ' . $kat_data['JENISPRODUK'] . '</span>';
                            }
                            
                            if ($search_filter) {
                                echo '<span class="badge bg-info me-1">Pencarian: ' . htmlspecialchars($search_filter) . '</span>';
                            }
                            
                            echo '<a href="data_produk.php" class="btn btn-sm btn-outline-secondary">Reset Filter</a>';
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>

                        <!-- Filter Kategori dan Tombol Tambah Produk Sejajar -->
                        <div class="filter-container mb-3">
                            <div class="d-flex flex-column flex-md-row gap-3 w-75">
                                <!-- Filter Kategori -->
                                <form method="GET" id="filterForm" class="mb-2 mb-md-0">
                                    <h6 class="mb-2">Pilih Kategori :</h6>
                                    <div class="form-group">
                                        <select name="kategori" class="form-control" onchange="this.form.submit()">
                                            <option value="">Semua Kategori</option>
                                            <?php 
                                            // Reset pointer hasil query
                                            mysqli_data_seek($kategori_result, 0); 
                                            while ($kategori = mysqli_fetch_assoc($kategori_result)) { ?>
                                                <option value="<?php echo $kategori['IDKATEGORI']; ?>" <?php echo ($kategori_filter == $kategori['IDKATEGORI']) ? 'selected' : ''; ?>>
                                                    <?php echo $kategori['JENISPRODUK']; ?>
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </form>
                                
                                <!-- Kotak Pencarian -->
                                <form method="GET" class="search-form">
                                    <h6 class="mb-2">Cari Produk :</h6>
                                    <div class="input-group">
                                        <input type="text" name="search" class="form-control" placeholder="Nama produk..." 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        <button class="btn btn-outline-secondary" type="submit">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <?php if (isset($_GET['kategori'])) { ?>
                                        <input type="hidden" name="kategori" value="<?php echo htmlspecialchars($_GET['kategori']); ?>">
                                    <?php } ?>
                                </form>
                            </div>
                            
                            <!-- Tombol Tambah Produk menggunakan link dengan onclick untuk membuka modal -->
                            <a href="#" class="btn btn-primary" onclick="openTambahModal(); return false;">
                                + Tambah Produk
                            </a>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Kode Barang</th>
                                    <th>Nama Produk</th>
                                    <th>Kategori Barang</th>
                                    <th>Jumlah Stok</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (mysqli_num_rows($result) > 0) {
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>" . $no++ . "</td>";
                                        echo "<td>" . $row['IDPRODUK'] . "</td>";
                                        echo "<td>" . $row['NAMAPRODUK'] . "</td>";
                                        echo "<td>" . $row['JENISPRODUK'] . "</td>";
                                        echo "<td>" . $row['JUMLAHPRODUK'] . "</td>";
                                        echo "<td>
                                                <a href='#' class='btn btn-warning btn-sm' 
                                                    onclick='openEditModal(\"" . $row['IDPRODUK'] . "\", \"" . $row['NAMAPRODUK'] . "\", \"" . $row['IDKATEGORI'] . "\", \"" . $row['JUMLAHPRODUK'] . "\"); return false;'>
                                                    Edit
                                                </a>
                                                <a href='?delete_id=" . $row['IDPRODUK'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus produk ini?\")'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>Tidak ada data produk.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                        <!-- Setelah tabel -->
                        <div class="d-flex justify-content-end mt-3">
                            <a href="rekap_produk.php" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Rekap
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Produk -->
    <div class="modal" id="tambahProdukModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                    <button type="button" class="btn-close" onclick="closeTambahModal()"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <div class="form-group mb-3">
                            <label for="idkategori" class="form-label">Kategori</label>
                            <select name="idkategori" id="idkategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <?php 
                                // Reset pointer hasil query
                                mysqli_data_seek($kategori_result, 0); 
                                while ($row = mysqli_fetch_assoc($kategori_result)) { ?>
                                    <option value="<?php echo $row['IDKATEGORI']; ?>">
                                        <?php echo $row['JENISPRODUK']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="namaproduk" class="form-label">Nama Produk</label>
                            <input type="text" name="namaproduk" id="namaproduk" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="jumlahproduk" class="form-label">Jumlah Produk</label>
                            <input type="number" name="jumlahproduk" id="jumlahproduk" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeTambahModal()">Batal</button>
                        <button type="submit" name="tambah_produk" class="btn btn-primary">Tambah Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div class="modal" id="editProdukModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Produk</h5>
                    <button type="button" class="btn-close" onclick="closeEditModal()"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="idproduk" id="edit_idproduk">
                        <div class="form-group mb-3">
                            <label for="edit_idkategori" class="form-label">Kategori</label>
                            <select name="edit_idkategori" id="edit_idkategori" class="form-control" required>
                                <option value="">Pilih Kategori</option>
                                <?php 
                                // Reset pointer hasil query
                                mysqli_data_seek($kategori_result, 0); 
                                while ($row = mysqli_fetch_assoc($kategori_result)) { ?>
                                    <option value="<?php echo $row['IDKATEGORI']; ?>">
                                        <?php echo $row['JENISPRODUK']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_namaproduk" class="form-label">Nama Produk</label>
                            <input type="text" name="edit_namaproduk" id="edit_namaproduk" class="form-control" required>
                        </div>
                        <div class="form-group mb-3">
                            <label for="edit_jumlahproduk" class="form-label">Jumlah Produk</label>
                            <input type="number" name="edit_jumlahproduk" id="edit_jumlahproduk" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Batal</button>
                        <button type="submit" name="edit_produk" class="btn btn-primary">Update Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk modal tambah produk
        function openTambahModal() {
            document.getElementById('tambahProdukModal').style.display = 'block';
            document.getElementById('tambahProdukModal').classList.add('show');
        }
        
        function closeTambahModal() {
            document.getElementById('tambahProdukModal').style.display = 'none';
            document.getElementById('tambahProdukModal').classList.remove('show');
        }
        
        // Fungsi untuk modal edit produk
        function openEditModal(id, nama, kategori, jumlah) {
            document.getElementById('edit_idproduk').value = id;
            document.getElementById('edit_namaproduk').value = nama;
            document.getElementById('edit_idkategori').value = kategori;
            document.getElementById('edit_jumlahproduk').value = jumlah;
            
            document.getElementById('editProdukModal').style.display = 'block';
            document.getElementById('editProdukModal').classList.add('show');
        }
        
        function closeEditModal() {
            document.getElementById('editProdukModal').style.display = 'none';
            document.getElementById('editProdukModal').classList.remove('show');
        }
        
        // Menutup modal ketika mengklik di luar modal
        window.onclick = function(event) {
            if (event.target == document.getElementById('tambahProdukModal')) {
                closeTambahModal();
            }
            if (event.target == document.getElementById('editProdukModal')) {
                closeEditModal();
            }
        }
    </script>
</body>
</html>