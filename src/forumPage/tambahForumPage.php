<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Forum - Smart Family</title>
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
        <h2 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white">Tambah Forum Baru</h2>

        <form action="../../server/validasi/addForum.php" method="POST" class="bg-white dark:bg-gray-800 p-6 rounded shadow max-w-lg mx-auto">
            <div class="mb-4">
                <label for="judul" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Judul</label>
                <input type="text" id="judul" name="judul" class="w-full border rounded p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" required>
            </div>
            <div class="mb-4">
                <label for="isi" class="block text-gray-700 dark:text-gray-300 font-bold mb-2">Isi</label>
                <textarea id="isi" name="isi" rows="6" class="w-full border rounded p-2 dark:bg-gray-700 dark:text-white dark:border-gray-600" required></textarea>
            </div>
            <div class="flex justify-between">
                <a href="forumPage.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded"><</a>
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">Tambah</button>
            </div>
        </form>

        <?php
            if(isset($_GET['add_gagal'])) {
                $message = '';
                $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4 dark:bg-red-800 dark:border-red-600 dark:text-red-200';
                if($_GET['add_gagal'] == 'lengkapi') {
                    $message = 'Silakan lengkapi semua field.';
                } elseif($_GET['add_gagal'] == 'stmt_prepare') {
                    $message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
                } elseif($_GET['add_gagal'] == 'database') {
                    $message = 'Gagal menambahkan forum. Silakan coba lagi.';
                }
                echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
            }

            if(isset($_GET['add_sukses'])) {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4 dark:bg-green-800 dark:border-green-600 dark:text-green-200" role="alert">Forum berhasil ditambahkan.</div>';
            }
        ?>
    </div>

    <footer class="bg-blue-500 text-white py-4 mt-20 flex justify-center items-center fixed bottom-0 left-0 right-0">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <button onclick="toggleDarkMode()" class="fixed bottom-4 right-4 p-3 bg-gray-200 dark:bg-gray-700 rounded-full hover:scale-110 transition-transform duration-200">
        <span class="dark:hidden">🌙</span>
        <span class="hidden dark:inline">☀️</span>
    </button>

    <script>
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
    </script>
</body>
</html>