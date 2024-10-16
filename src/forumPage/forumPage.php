<?php
session_start();
require_once '../../server/config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil semua forum
$sql = "SELECT tf.id, tf.judul, tf.isi, tf.tanggal_dibuat, uf.username 
        FROM topik_forum tf 
        JOIN users_forum uf ON tf.id_pembuat = uf.id 
        ORDER BY tf.tanggal_dibuat DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-semibold">Smart Family Forum</h1>
            <div>
                <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Logout</a>
            </div>
        </div>
    </header>

    <!-- Container Utama -->
    <div class="container mx-auto mt-8 px-4">
        <!-- Tombol Tambah Forum dan Daftar Forum Saya -->
        <div class="flex justify-between mb-6">
            <h2 class="text-2xl font-semibold">Semua Forum</h2>
            <div class="space-x-4">
                <a href="tambahForumPage.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">Create Forum</a>
                <a href="daftarForumPage.php" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-2 px-4 rounded">Daftar Forum Saya</a>
            </div>
        </div>

        <!-- Daftar Forum -->
        <?php if ($result->num_rows > 0): ?>
            <div class="space-y-6">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded shadow">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold">
                                <a href="topikForum.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline">
                                    <?php echo htmlspecialchars($row['judul']); ?>
                                </a>
                            </h3>
                            <span class="text-sm text-gray-500">
                                by <?php echo htmlspecialchars($row['username']); ?> on <?php echo date("d M Y, H:i", strtotime($row['tanggal_dibuat'])); ?>
                            </span>
                        </div>
                        <p class="mt-4 text-gray-700">
                            <?php echo nl2br(htmlspecialchars($row['isi'])); ?>
                        </p>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-700">Belum ada forum. <a href="tambahForumPage.php" class="text-blue-500 hover:underline">Buat forum pertama Anda</a>.</p>
        <?php endif; ?>
    </div>

    <!-- Footer Static (Ditampilkan saat ada scroll) -->
    <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-20">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>

    <!-- Footer Fixed (Ditampilkan saat tidak ada scroll) -->
    <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center hidden">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <!-- JavaScript untuk Menentukan Footer yang Ditampilkan -->
    <script>
        function toggleFooter() {
            const footerStatic = document.getElementById('footer-static');
            const footerFixed = document.getElementById('footer-fixed');
            const isScrollable = document.body.scrollHeight > window.innerHeight;
            
            if (isScrollable) {
                footerStatic.classList.remove('hidden');
                footerFixed.classList.add('hidden');
            } else {
                footerStatic.classList.add('hidden');
                footerFixed.classList.remove('hidden');
            }
        }

        // Jalankan fungsi saat halaman dimuat
        window.addEventListener('load', toggleFooter);

        // Jalankan fungsi saat jendela di-resize
        window.addEventListener('resize', toggleFooter);
    </script>
</body>
</html>
<?php
$conn->close();
?>
