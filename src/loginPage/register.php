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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="bg-gray-100 dark:bg-gray-900 flex items-center justify-center min-h-screen px-4 md:px-0">
    <button onclick="toggleDarkMode()" class="fixed top-4 right-4 p-3 bg-gray-200 dark:bg-gray-700 rounded-full hover:scale-110 transition-transform duration-200">
        <span class="dark:hidden">🌙</span>
        <span class="hidden dark:inline">☀️</span>
    </button>

    <div class="bg-white dark:bg-gray-800 p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-3xl font-semibold mb-6 text-center dark:text-white">Register</h2>

        <form action="<?php echo BASE_URL; ?>server/registerUser.php" method="POST">
            <div class="mb-4">
                <label for="nama_lengkap" class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Nama Lengkap</label>
                <input type="text" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:border-gray-600" 
                       id="nama_lengkap" 
                       name="nama_lengkap" 
                       required>
            </div>
            <div class="mb-4">
                <label for="username" class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Username (nama panggilan)</label>
                <input type="text" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:border-gray-600" 
                       id="username" 
                       name="username" 
                       required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Password</label>
                <input type="password" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:border-gray-600" 
                       id="password" 
                       name="password" 
                       required>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Konfirmasi Password</label>
                <input type="password" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:border-gray-600" 
                       id="confirm_password" 
                       name="confirm_password" 
                       required>
            </div>
            <div class="mb-6">
                <label for="kode" class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Kode Registrasi</label>
                <input type="text" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:border-gray-600" 
                       id="kode" 
                       name="kode" 
                       required>
            </div>
            <div class="mb-4 flex justify-center">
                <div class="cf-turnstile" data-sitekey="" data-theme="auto"></div>
            </div>
            <button type="submit" 
                    class="w-full bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline transform transition-transform duration-300 hover:scale-105">
                Register
            </button>
        </form>

        <?php
            if (isset($_GET['register_gagal'])) {
                $message = '';
                $alertClass = 'bg-red-100 dark:bg-red-200 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-900 px-4 py-3 rounded relative mt-4';
                switch ($_GET['register_gagal']) {
                    case 'password':
                        $message = 'Password tidak sama';
                        break;
                    case 'password_format':
                        $message = 'Password harus terdiri dari minimal 8 karakter, minimal 1 angka, 1 huruf kapital, dan 1 simbol';
                        break;
                    case 'username':
                        $message = 'Username sudah digunakan';
                        break;
                    case 'kode':
                        $message = 'Kode registrasi tidak valid';
                        break;
                    case 'captcha':
                        $message = 'Verifikasi captcha gagal';
                        break;
                }
                if ($message) {
                    echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
                }
            } elseif (isset($_GET['register_sukses'])) {
                echo '<div class="bg-green-100 dark:bg-green-200 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-900 px-4 py-3 rounded relative mt-4" role="alert">Registrasi berhasil. Silakan login.</div>';
            }
        ?>

        <div class="mt-6 text-center">
            <a href="../../index.php" 
               class="w-full bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white font-semibold py-2 px-4 rounded focus:outline-none focus:shadow-outline transform transition-transform duration-300 hover:scale-105">
                Kembali
            </a>
        </div>
    </div>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
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