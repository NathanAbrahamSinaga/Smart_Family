<?php
session_start();
require_once '../../server/config.php';

if (isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/forumPage/forumPage.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Forum - Smart Family</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <!-- Container Utama -->
    <div class="container text-center mt-5 content">
        <!-- Judul -->
        <h2 class="mb-4">Login Forum</h2>

        <!-- Form Login -->
        <form action="<?php echo BASE_URL; ?>server/validasi/validasiUser.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required 
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <?php
            // Menampilkan pesan gagal login
            if(isset($_GET['login_gagal'])) {
                if($_GET['login_gagal'] == 'username') {
                    echo '<div class="alert alert-danger mt-3">Username tidak ditemukan</div>';
                } elseif($_GET['login_gagal'] == 'password') {
                    echo '<div class="alert alert-danger mt-3">Password salah</div>';
                } elseif($_GET['login_gagal'] == 'lengkapi') {
                    echo '<div class="alert alert-danger mt-3">Silakan lengkapi semua field</div>';
                } elseif($_GET['login_gagal'] == 'stmt_prepare') {
                    echo '<div class="alert alert-danger mt-3">Terjadi kesalahan pada sistem. Silakan coba lagi.</div>';
                }
            }

            // Menampilkan pesan sukses registrasi
            if(isset($_GET['register_sukses']) && $_GET['register_sukses'] == '1') {
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
