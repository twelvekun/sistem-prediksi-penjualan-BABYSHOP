<?php
session_start();

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Cek apakah ada IDUSER yang dikirimkan
if (isset($_GET['id'])) {
    $id_user = $_GET['id'];

    // Ambil data pengguna yang akan diedit
    $query = "SELECT * FROM user WHERE IDUSER = $id_user";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $row = mysqli_fetch_assoc($result);
        $username = $row['USERNAME'];
        $role = $row['ROLE'];
        $password = $row['PASSWORD']; // Mengambil password
    } else {
        echo "User tidak ditemukan!";
        exit();
    }
} else {
    echo "ID User tidak ditemukan!";
    exit();
}

// Proses update data pengguna
if (isset($_POST['update'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash password baru jika ada perubahan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Query untuk update data pengguna
    $update_query = "UPDATE user SET USERNAME = '$username', PASSWORD = '$hashed_password', ROLE = '$role' WHERE IDUSER = $id_user";

    if (mysqli_query($conn, $update_query)) {
        echo "<script>alert('User berhasil diperbarui!'); window.location='user.php';</script>";
    } else {
        echo "<script>alert('Gagal memperbarui user!');</script>";
    }
}

// Proses batal update
if (isset($_POST['cancel'])) {
    // Kembali ke halaman user.php jika batal
    header("Location: user.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengguna - Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
       body {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%);
        background-attachment: fixed;
        min-height: 100vh;
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
        <div class="row">
            <div class="col-lg-6 offset-lg-3">
                <div class="card shadow">
                    <h1 class="mt-2 ml-4">Edit Pengguna</h1>
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo $username; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <input type="password" class="form-control" id="password" name="password" value="<?php echo $password; ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                    <option value="user" <?php echo ($role == 'user') ? 'selected' : ''; ?>>User</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary" name="update">Update Data User</button>
                            <button type="submit" class="btn btn-secondary" name="cancel">Batal Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
