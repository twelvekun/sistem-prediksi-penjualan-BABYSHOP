<?php 
session_start();

// Cek apakah pengguna adalah admin
if (!isset($_SESSION['log']) || $_SESSION['role'] !== 'admin') {
    echo "Akses ditolak.";
    exit();
}

require '../config.php';

// Proses untuk menyimpan data user baru
if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $role = $_POST['role'];

    // Simpan password langsung tanpa enkripsi
    $query = "INSERT INTO user (USERNAME, PASSWORD, ROLE) VALUES ('$username', '$password', '$role')";

    if (mysqli_query($conn, $query)) {
        $message = "User berhasil ditambahkan!";
        $status = "success";
    } else {
        $message = "Gagal menambahkan user!";
        $status = "error";
    }
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Admin</title>
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
            <div class="col-lg-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h3>Tambah User</h3>
                    </div>
                    <div class="card-body">
                        <form action="tambah_user.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-control" id="role" name="role" required>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                </select>
                            </div>
                            <button type="submit" name="submit" class="btn btn-primary">Tambah User</button>
                            <a href="user.php" class="btn btn-secondary ml-2">Kembali</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <script>
            alert("<?php echo $message; ?>");
            <?php if ($status == "success"): ?>
                window.location = "user.php";
            <?php endif; ?>
        </script>
    <?php endif; ?>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>
