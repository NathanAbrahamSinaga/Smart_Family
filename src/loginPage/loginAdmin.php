<?php
session_start();
if (isset($_SESSION["user_id"])) {
    header("location: ../adminPage/adminPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Smart Family</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <!-- Container Utama -->
    <div class="container text-center mt-5 content">
        <!-- Judul -->
        <h2 class="mb-4">Login Admin</h2>
        <!-- Form Login -->
        <form action="../../server/validasi/validasiAdmin.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>
        <?php
        if(isset($_GET['login_gagal'])) {
            if($_GET['login_gagal'] == 'username') {
                echo '<div class="alert alert-danger mt-3">Username tidak ditemukan</div>';
            } elseif($_GET['login_gagal'] == 'password') {
                echo '<div class="alert alert-danger mt-3">Password salah</div>';
            }
        }
        ?>
        <!-- Link Kembali ke Halaman Utama -->
        <div class="mt-4">
            <a href="../../index.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>
    <!-- Footer -->
    <footer>
        <p class="mb-0">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>
    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
