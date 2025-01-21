<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: forumPage.php");
    exit();
}

$forum_id = intval($_GET['id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("SELECT tf.judul, tf.isi, tf.tanggal_dibuat, uf.username, tf.id_pembuat 
                        FROM topik_forum tf 
                        JOIN users_forum uf ON tf.id_pembuat = uf.id 
                        WHERE tf.id = ?");
$stmt->bind_param("i", $forum_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: forumPage.php");
    exit();
}

$forum = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT cf.id, cf.isi, cf.tanggal_dibuat, uf.username, cf.id_penulis 
                        FROM komentar_forum cf 
                        JOIN users_forum uf ON cf.id_penulis = uf.id 
                        WHERE cf.id_topik = ? 
                        ORDER BY cf.tanggal_dibuat ASC");
$stmt->bind_param("i", $forum_id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($forum['judul']); ?> - Smart Family Forum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="bg-gray-100 dark:bg-gray-900">
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

    <div class="container mx-auto mt-8 px-4">
        <div class="bg-white dark:bg-gray-800 p-6 rounded shadow">
            <div class="w-full max-w-full break-words break-all">
                <h2 class="text-2xl font-bold mb-2 text-gray-900 dark:text-white"><?php echo htmlspecialchars($forum['judul']); ?></h2>
                <span class="text-sm text-gray-500 dark:text-gray-400">by <?php echo htmlspecialchars($forum['username']); ?> on <?php echo date("d M Y, H:i", strtotime($forum['tanggal_dibuat'])); ?></span>
                <p class="mt-4 text-gray-700 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($forum['isi'])); ?></p>
            </div>
        </div>

        <div class="mt-8">
            <h3 class="text-xl font-semibold mb-4 text-gray-900 dark:text-white">Komentar</h3>
            <?php if ($comments->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($comment = $comments->fetch_assoc()): ?>
                        <div class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                            <div class="w-full max-w-full break-words break-all">
                                <div class="flex justify-between items-center">
                                    <span class="font-semibold text-gray-900 dark:text-white"><?php echo htmlspecialchars($comment['username']); ?></span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400"><?php echo date("d M Y, H:i", strtotime($comment['tanggal_dibuat'])); ?></span>
                                </div>
                                <p class="mt-2 text-gray-700 dark:text-gray-300"><?php echo nl2br(htmlspecialchars($comment['isi'])); ?></p>
                                
                                <?php if ($comment['id_penulis'] == $_SESSION["user_id"]): ?>
                                    <form action="../../server/validasi/deleteComment.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                        <button type="submit" class="mt-2 bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Hapus</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-700 dark:text-gray-300">Belum ada komentar.</p>
            <?php endif; ?>

            <div class="mt-6">
                <h4 class="text-lg font-semibold mb-2 text-gray-900 dark:text-white">Tambahkan Komentar</h4>
                <form action="../../server/validasi/addComment.php" method="POST" class="bg-white dark:bg-gray-800 p-4 rounded shadow">
                    <input type="hidden" name="id_topik" value="<?php echo $forum_id; ?>">
                    <div class="mb-4">
                        <textarea name="isi" id="isi" rows="4" class="w-full border rounded p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" placeholder="Tulis komentar Anda di sini..." required></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Submit</button>
                </form>
            </div>
        </div>
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