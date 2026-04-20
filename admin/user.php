<?php
session_start();

if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Proses hapus user
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    $stmt = $conn->prepare("DELETE FROM user WHERE IDUSER = ?");
    $stmt->bind_param("i", $delete_id);

    if ($stmt->execute()) {
        $message = "User berhasil dihapus!";
        $status = "success";
    } else {
        $message = "Gagal menghapus user!";
        $status = "danger";
    }
    $stmt->close();
}

// Ambil data user
$query = "SELECT IDUSER, USERNAME, ROLE FROM user";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pengguna - Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
        }

        .filter-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .right-body{
            background: #FBF7E6;
            background-attachment: fixed;
            min-height: 100vh;}
    </style>
</head>

<body class="right-body">
    <?php include "header.php"; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow">
                    <div class="card-body">
                        <h5 class="card-title">Data Pengguna</h5>

                        <?php if (isset($message)) { ?>
                            <div class="alert alert-<?= $status; ?>"><?= $message; ?></div>
                        <?php } ?>

                        <div class="filter-container mb-3">
                            <a href="tambah_user.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Tambah User
                            </a>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Username</th>
                                    <th>Role</th>
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
                                        echo "<td>" . htmlspecialchars($row['USERNAME']) . "</td>";
                                        $role = $row['ROLE'];
$badgeColor = ($role === 'admin') ? 'primary' : 'warning';
echo "<td><span class='badge bg-$badgeColor text-dark'>" . ucfirst($role) . "</span></td>";
                                        echo "<td>
                                                <a href='edit_user.php?id=" . $row['IDUSER'] . "' class='btn btn-warning btn-sm'>Edit</a>
                                                <a href='?delete_id=" . $row['IDUSER'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Anda yakin ingin menghapus user ini?\")'>Hapus</a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>Tidak ada data pengguna.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
