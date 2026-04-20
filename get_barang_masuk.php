<?php
session_start();
require "../config.php";

if (!isset($_SESSION["log"]) || $_SESSION["role"] !== "user") {
    echo "Akses ditolak.";
    exit();
}

if (!isset($_GET["id"])) {
    echo "<div class='alert alert-danger'>ID tidak ditemukan.</div>";
    exit();
}

$id = $_GET["id"];

// Ambil data barang masuk
$query = "SELECT bm.IDMASUK, bm.TANGGALMASUK, bm.JUMLAHMASUK, 
                p.IDPRODUK, p.NAMAPRODUK, 
                s.IDPEMASOK, s.NAMAPEMASOK 
         FROM barangmasuk bm
         JOIN produk p ON bm.IDPRODUK = p.IDPRODUK
         JOIN pemasok s ON bm.IDPEMASOK = s.IDPEMASOK
         WHERE bm.IDMASUK = '" . $id . "'";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    echo "<div class='alert alert-danger'>Data tidak ditemukan.</div>";
    exit();
}

// Ambil data produk dan pemasok untuk dropdown
$produk_query = "SELECT * FROM produk";
$produk_result = mysqli_query($conn, $produk_query);

$pemasok_query = "SELECT * FROM pemasok";
$pemasok_result = mysqli_query($conn, $pemasok_query);
?>

<!-- View Mode -->
<div id="viewMode">
    <table class="table table-bordered detail-table">
        <tr>
            <th>ID Barang Masuk</th>
            <td><?= $data["IDMASUK"]; ?></td>
        </tr>
        <tr>
            <th>Tanggal Masuk</th>
            <td><?= $data["TANGGALMASUK"]; ?></td>
        </tr>
        <tr>
            <th>Nama Produk</th>
            <td><?= $data["NAMAPRODUK"]; ?></td>
        </tr>
        <tr>
            <th>Nama Pemasok</th>
            <td><?= $data["NAMAPEMASOK"]; ?></td>
        </tr>
        <tr>
            <th>Jumlah Masuk</th>
            <td><?= $data["JUMLAHMASUK"]; ?></td>
        </tr>
    </table>

    <div class="d-flex justify-content-center mt-3 gap-2">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-warning" onclick="toggleEditMode()">Edit</button>
        <a href="cetak_barang_masuk.php?id=<?= $data["IDMASUK"]; ?>" target="_blank" class="btn btn-primary">
            Cetak
        </a>
    </div>
</div>

<!-- Edit Mode -->
<div id="editMode" style="display: none;">
    <form method="POST" action="">
        <input type="hidden" name="id_masuk" value="<?= $data["IDMASUK"]; ?>">
        <input type="hidden" name="jumlah_masuk_awal" value="<?= $data["JUMLAHMASUK"]; ?>">
        <input type="hidden" name="id_produk_lama" value="<?= $data["IDPRODUK"]; ?>">

        <div class="mb-3">
            <label for="id_produk" class="form-label">Nama Produk</label>
            <select name="id_produk" id="id_produk" class="form-control" required>
                <?php while ($p = mysqli_fetch_assoc($produk_result)) : ?>
                    <option value="<?= $p["IDPRODUK"]; ?>" <?= $p["IDPRODUK"] == $data["IDPRODUK"] ? "selected" : "" ?>>
                        <?= $p["NAMAPRODUK"]; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="id_pemasok" class="form-label">Nama Pemasok</label>
            <select name="id_pemasok" id="id_pemasok" class="form-control" required>
                <?php while ($s = mysqli_fetch_assoc($pemasok_result)) : ?>
                    <option value="<?= $s["IDPEMASOK"]; ?>" <?= $s["IDPEMASOK"] == $data["IDPEMASOK"] ? "selected" : "" ?>>
                        <?= $s["NAMAPEMASOK"]; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="jumlah_masuk" class="form-label">Jumlah Masuk</label>
            <input type="number" name="jumlah_masuk" id="jumlah_masuk" class="form-control" 
                   value="<?= $data["JUMLAHMASUK"]; ?>" required>
        </div>

        <div class="d-flex justify-content-between">
            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">Batal</button>
            <button type="submit" name="update_barang_masuk" class="btn btn-success">Simpan Update</button>
        </div>
    </form>
</div>