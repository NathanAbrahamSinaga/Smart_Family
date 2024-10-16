<?php
session_start();
require_once '../../server/config.php';

// Memeriksa apakah pengguna sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Smart Family</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h1>Forum Sederhana</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</p>
        <div class="mb-4">
            <a href="../loginPage/logout.php" class="btn btn-danger">Logout</a>
        </div>
        <div class="">
            <h3>Judul Postingan Pertama</h3>
            <div class="">Oleh: Pengguna123 | Tanggal: 3 Oktober 2024</div>
                <div class="">
                    <p>Ini adalah contoh isi dari postingan pertama di forum kita. Semoga forum ini bisa menjadi tempat diskusi yang menyenangkan!</p>
                </div>
            </div>
            
            <div class="">
                <h3>Pertanyaan tentang HTML</h3>
                <div class="">Oleh: WebDev456 | Tanggal: 2 Oktober 2024</div>
                <div class="">
                    <p>Halo semua, ada yang bisa menjelaskan perbedaan antara div dan span dalam HTML? Terima kasih sebelumnya!</p>
                </div>
            </div>
            
            <div class="">
                <h3>Buat Postingan Baru</h3>
                <form action="<?php echo BASE_URL; ?>server/addPost.php" method="POST">
                    <div class="mb-3">
                        <textarea class="form-control" name="isi_postingan" rows="4" placeholder="Tulis postingan Anda di sini..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Postingan</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="mt-5">
        <p class="mb-0">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script src="../../assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>
