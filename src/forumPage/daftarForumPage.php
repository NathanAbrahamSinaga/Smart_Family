<?php
session_start();
require_once '../../server/config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
    exit();
}

$user_id = $_SESSION["user_id"];
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil forum pengguna
$stmt = $conn->prepare("SELECT id, judul, tanggal_dibuat FROM topik_forum WHERE id_pembuat = ? ORDER BY tanggal_dibuat DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Daftar Forum Saya - Smart Family</title>
        <link rel="stylesheet" href="../../assets/css/output.css">
    </head>
    <body class="bg-gray-100 flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-blue-500 text-white py-4">
            <div class="container mx-auto flex justify-between items-center">
                <h1 class="text-xl font-semibold ml-5">Smart Family Forum</h1>
                <div>
                    <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
                </div>
            </div>
        </header>

        <!-- Container Utama -->
        <div class="container mx-auto mt-8 px-4 flex-grow">
            <h2 class="text-2xl font-semibold mb-6">Daftar Forum Saya</h2>

            <!-- Tombol Tambah Forum -->
            <div class="flex justify-end mb-4">
                <a href="tambahForumPage.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">Tambah Forum</a>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="bg-white p-4 rounded shadow flex justify-between items-center">
                            <div>
                                <a href="topikForum.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline font-semibold"><?php echo htmlspecialchars($row['judul']); ?></a>
                                <p class="text-sm text-gray-500">Dibuat pada <?php echo date("d M Y, H:i", strtotime($row['tanggal_dibuat'])); ?></p>
                            </div>
                            <form action="../../server/validasi/deleteForum.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus forum ini?');">
                                <input type="hidden" name="forum_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Hapus</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-700">Anda belum membuat forum apapun. <a href="tambahForumPage.php" class="text-blue-500 hover:underline">Buat forum sekarang</a>.</p>
            <?php endif; ?>

            <!-- Pesan Feedback -->
            <?php
                if(isset($_GET['delete_gagal'])) {
                    $message = '';
                    $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
                    if($_GET['delete_gagal'] == 'stmt_prepare') {
                        $message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
                    } elseif($_GET['delete_gagal'] == 'unauthorized') {
                        $message = 'Anda tidak memiliki izin untuk menghapus forum ini.';
                    } elseif($_GET['delete_gagal'] == 'not_found') {
                        $message = 'Forum tidak ditemukan.';
                    } elseif($_GET['delete_gagal'] == 'database') {
                        $message = 'Gagal menghapus forum. Silakan coba lagi.';
                    }
                    echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
                }

                if(isset($_GET['delete_sukses'])) {
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">Forum berhasil dihapus.</div>';
                }
            ?>
        </div>

        <!-- Footer Static (Ditampilkan saat ada scroll) -->
        <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-20">
            <div class="container mx-auto text-center">
                <p>&copy; 2024 Smart Family. All rights reserved.</p>
            </div>
        </footer>

        <!-- Footer Fixed (Ditampilkan saat tidak ada scroll) -->
        <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
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
