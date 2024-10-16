<?php
session_start();
require_once '../../server/config.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Forum - Smart Family</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <!-- Container Utama -->
    <div class="container text-center mt-5 content">
        <!-- Judul -->
        <h2 class="mb-4">Register Forum</h2>

        <!-- Form Register -->
        <form action="<?php echo BASE_URL; ?>server/registerUser.php" method="POST">
            <div class="mb-3">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-success">Register</button>
        </form>

        <?php
            if(isset($_GET['register_gagal'])) {
                if($_GET['register_gagal'] == 'password') {
                    echo '<div class="alert alert-danger mt-3">Password tidak sama</div>';
                } elseif($_GET['register_gagal'] == 'username') {
                    echo '<div class="alert alert-danger mt-3">Username sudah digunakan</div>';
                } elseif($_GET['register_gagal'] == 'database') {
                    echo '<div class="alert alert-danger mt-3">Gagal menyimpan data. Silakan coba lagi.</div>';
                }
            } elseif(isset($_GET['register_sukses'])) {
                echo '<div class="alert alert-success mt-3">Registrasi berhasil. Silakan login.</div>';
            }
        ?>

        <!-- Link Kembali ke Halaman Forum -->
        <div class="mt-4">
            <a href="loginForumPage.php" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p class="mb-0">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
