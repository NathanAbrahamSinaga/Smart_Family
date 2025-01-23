<?php
session_start();
require_once '../../server/adminBackend.php';

$successMessage = $_SESSION['successMessage'] ?? null;
$errorMessage = $_SESSION['errorMessage'] ?? null;
unset($_SESSION['successMessage']);
unset($_SESSION['errorMessage']);
?>

<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [data-collapse] {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }
        .rotate-180 {
            transform: rotate(180deg);
        }
        .mobile-menu {
            display: none;
        }
        .mobile-menu.active {
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">
    <header class="bg-blue-500 dark:bg-blue-800 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="../../index.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded"><</a>
                <h1 class="text-xl font-semibold ml-5">Admin</h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative lg:hidden mr-5">
                    <button id="mobile-menu-button" class="text-white p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div id="mobile-menu" class="mobile-menu absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-lg shadow-lg z-10">
                        <a href="adminTaromboPage.php" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">Tarombo</a>
                        <a href="../loginPage/logout.php" class="block px-4 py-2 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700">Logout</a>
                    </div>
                </div>

                <div class="hidden lg:flex items-center">
                    <span class="mr-4 dark:text-white">Admin <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                    <a href="adminTaromboPage.php" class="bg-green-500 hover:bg-green-600 dark:bg-green-700 dark:hover:bg-green-800 text-white font-semibold py-1 px-3 rounded mr-4">Tarombo</a>
                    <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto mt-8 px-4">
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-100 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-100 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4 dark:text-white">Atur Forum dan Komentar</h2>
            <div class="space-y-4">
                <?php foreach ($forums as $forum): ?>
                    <div class="forum-item bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
                        <div class="forum-header p-4 flex justify-between items-center cursor-pointer">
                            <div class="flex-1">
                                <h3 class="font-semibold dark:text-white">
                                    <span class="md:hidden">
                                        <?php echo htmlspecialchars(strlen($forum['judul']) > 23 ? substr($forum['judul'], 0, 23) . '...' : $forum['judul']); ?>
                                    </span>
                                    <span class="hidden md:inline">
                                        <?php echo htmlspecialchars(strlen($forum['judul']) > 50 ? substr($forum['judul'], 0, 50) . '...' : $forum['judul']); ?>
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600 dark:text-gray-300">by <?php echo htmlspecialchars($forum['pembuat']); ?> on <?php echo date("d M Y, H:i", strtotime($forum['tanggal_dibuat'])); ?></p>
                            </div>
                            <div class="flex items-center ml-4">
                                <span class="mr-2 dark:text-white"><?php echo count($forum['comments']); ?> komentar</span>
                                <svg class="arrow-icon w-4 h-4 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div data-collapse class="border-t dark:border-gray-700">
                            <div class="p-4 bg-gray-50 dark:bg-gray-700">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this forum and all its comments?');" class="mb-4">
                                    <input type="hidden" name="forum_id" value="<?php echo $forum['id']; ?>">
                                    <button type="submit" name="delete_forum" class="bg-red-500 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800 text-white font-bold py-2 px-4 rounded">Hapus Forum</button>
                                </form>
                                <?php if (empty($forum['comments'])): ?>
                                    <p class="text-gray-600 dark:text-gray-300">Tidak ada komentar</p>
                                <?php else: ?>
                                    <?php foreach ($forum['comments'] as $comment): ?>
                                        <div class="bg-white dark:bg-gray-800 p-3 rounded shadow mb-2">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1 pr-4">
                                                    <p class="text-sm dark:text-white">
                                                        <span class="md:hidden">
                                                            <?php echo htmlspecialchars(strlen($comment['isi']) > 23 ? substr($comment['isi'], 0, 23) . '...' : $comment['isi']); ?>
                                                        </span>
                                                        <span class="hidden md:inline">
                                                            <?php echo htmlspecialchars(strlen($comment['isi']) > 100 ? substr($comment['isi'], 0, 100) . '...' : $comment['isi']); ?>
                                                        </span>
                                                    </p>
                                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1">by <?php echo htmlspecialchars($comment['penulis']); ?> on <?php echo date("d M Y, H:i", strtotime($comment['tanggal_dibuat'])); ?></p>
                                                </div>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');" class="flex-shrink-0">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" name="delete_comment" class="bg-red-500 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800 text-white text-xs font-bold py-1 px-2 rounded">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4 dark:text-white">Atur Pengguna</h2>
            <div class="overflow-x-auto bg-white dark:bg-gray-800 shadow rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="py-3 px-4 text-left dark:text-white">Username</th>
                            <th class="py-3 px-4 text-left dark:text-white">Nama Lengkap</th>
                            <th class="py-3 px-4 text-left dark:text-white">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                            <tr class="border-t dark:border-gray-700">
                                <td class="py-3 px-4 dark:text-white"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="py-3 px-4 dark:text-white"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td class="py-3 px-4">
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their forums and comments.');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-600 dark:bg-red-700 dark:hover:bg-red-800 text-white font-bold py-1 px-2 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <footer id="footer-fixed" class="bg-blue-500 dark:bg-blue-800 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
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
                extend: {}
            }
        };

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

        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('active');
        });

        document.addEventListener('click', (e) => {
            if (!mobileMenu.contains(e.target) && !mobileMenuButton.contains(e.target)) {
                mobileMenu.classList.remove('active');
            }
        });

        document.querySelectorAll('.forum-header').forEach(header => {
            header.addEventListener('click', () => {
                const forumItem = header.closest('.forum-item');
                const content = forumItem.querySelector('[data-collapse]');
                const arrow = forumItem.querySelector('.arrow-icon');
                
                content.style.maxHeight = content.style.maxHeight 
                    ? null 
                    : content.scrollHeight + 'px';
                
                arrow.classList.toggle('rotate-180');
            });
        });

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

        window.addEventListener('load', toggleFooter);
        window.addEventListener('resize', toggleFooter);
    </script>
</body>
</html>