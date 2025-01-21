<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
    exit();
}

$user_id = $_SESSION["user_id"];
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="../../assets/css/style.css">
    </head>
    <body class="bg-gray-100 dark:bg-gray-900 flex flex-col min-h-screen">
        <header class="bg-blue-500 text-white py-4">
            <div class="container mx-auto flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="forumPage.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded"><</a>
                    <h1 class="text-xl font-semibold ml-5">Forum</h1>
                </div>
                <div>
                    <span class="mr-4"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
                </div>
            </div>
        </header>

        <div class="container mx-auto mt-8 px-4 flex-grow">
            <h2 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white">Daftar Forum Saya</h2>

            <div class="flex justify-end mb-4">
                <a href="tambahForumPage.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">Tambah Forum</a>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($row = $result->fetch_assoc()): ?>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow flex justify-between items-center">
                            <div>
                                <a href="topikForum.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline font-semibold break-words break-all max-w-full dark:text-blue-400">
                                    <?php echo htmlspecialchars($row['judul']); ?>
                                </a>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Dibuat pada <?php echo date("d M Y, H:i", strtotime($row['tanggal_dibuat'])); ?></p>
                            </div>
                            <form action="../../server/validasi/deleteForum.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus forum ini?');">
                                <input type="hidden" name="forum_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Hapus</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-700 dark:text-gray-300">Anda belum membuat forum apapun. <a href="tambahForumPage.php" class="text-blue-500 hover:underline dark:text-blue-400">Buat forum sekarang</a>.</p>
            <?php endif; ?>

            <?php
                if(isset($_GET['delete_gagal'])) {
                    $message = '';
                    $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4 dark:bg-red-800 dark:border-red-600 dark:text-red-200';
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
                    echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4 dark:bg-green-800 dark:border-green-600 dark:text-green-200" role="alert">Forum berhasil dihapus.</div>';
                }
            ?>
        </div>

        <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-20">
            <div class="container mx-auto text-center">
                <p>&copy; 2024 Smart Family. All rights reserved.</p>
            </div>
        </footer>

        <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
            <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
        </footer>

        <button onclick="toggleDarkMode()" class="fixed bottom-4 right-4 p-3 bg-gray-200 dark:bg-gray-700 rounded-full hover:scale-110 transition-transform duration-200">
            <span class="dark:hidden">🌙</span>
            <span class="hidden dark:inline">☀️</span>
        </button>

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

            tailwind.config = {
                darkMode: 'class',
                theme: {
                    extend: {
                    }
                }
            }

            function initializeTheme() {
                const savedTheme = localStorage.getItem('theme');
                
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                
                if (savedTheme === 'dark' || (!savedTheme && systemPrefersDark)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            function toggleDarkMode() {
                const html = document.documentElement;
                
                if (html.classList.contains('dark')) {
                    html.classList.remove('dark');
                    localStorage.setItem('theme', 'light');
                } else {
                    html.classList.add('dark');
                    localStorage.setItem('theme', 'dark');
                }
            }

            document.addEventListener('DOMContentLoaded', initializeTheme);
            window.addEventListener('load', toggleFooter);
            window.addEventListener('resize', toggleFooter);
        </script>
    </body>
</html>
<?php
$conn->close();
?>