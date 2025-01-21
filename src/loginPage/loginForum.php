<?php
session_start();
require_once '../../server/config.php';

if (isset($_SESSION["admin_id"])) {
    session_unset();
    session_destroy();
}

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
        <h2 class="text-3xl font-semibold mb-6 text-center dark:text-white">Login Forum</h2>

        <form action="<?php echo BASE_URL; ?>server/validasi/validasiUser.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Username</label>
                <input type="text" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:border-gray-600" 
                       id="username" 
                       name="username" 
                       required 
                       value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 dark:text-gray-200 text-sm font-bold mb-2">Password</label>
                <input type="password" 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 mb-3 leading-tight focus:outline-none focus:shadow-outline dark:bg-gray-700 dark:border-gray-600" 
                       id="password" 
                       name="password" 
                       required>
            </div>
            <div class="mb-4 flex justify-center">
                <div class="cf-turnstile" data-sitekey="" data-theme="auto"></div>
            </div>
            <button type="submit" 
                    class="w-full bg-blue-500 hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline transform transition-transform duration-300 hover:scale-110">
                Login
            </button>
        </form>

        <?php
        if(isset($_GET['login_gagal'])) {
            $message = '';
            $alertClass = 'bg-red-100 dark:bg-red-200 border border-red-400 dark:border-red-600 text-red-700 dark:text-red-900 px-4 py-3 rounded relative mt-4';
            
            switch($_GET['login_gagal']) {
                case 'username':
                    $message = 'Username salah atau tidak terdaftar';
                    break;
                case 'password':
                    $message = 'Password salah';
                    break;
                case 'lengkapi':
                    $message = 'Username dan password harus diisi';
                    break;
                case 'captcha':
                    $message = 'Verifikasi captcha gagal';
                    break;
                default:
                    $message = 'Terjadi kesalahan saat login';
            }
            
            if($message) {
                echo "<div class=\"$alertClass\" role=\"alert\">
                        <span class=\"block sm:inline\">$message</span>
                      </div>";
            }
        }

        if(isset($_GET['register_sukses']) && $_GET['register_sukses'] == '1') {
            echo '<div class="bg-green-100 dark:bg-green-200 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-900 px-4 py-3 rounded relative mt-4" role="alert">
                    <span class="block sm:inline">Registrasi berhasil. Silakan login untuk masuk halaman forum dan tarombo.</span>
                  </div>';
        }
        ?>

        <div class="mt-4 text-center">
            <div class="mt-6">
                <a href="../../index.php" 
                   class="bg-gray-500 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 text-white font-bold py-2 px-4 rounded transform transition-transform duration-300 hover:scale-110">
                    Kembali
                </a>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-6">
                Belum punya akun? 
                <a href="<?php echo BASE_URL; ?>src/loginPage/register.php" 
                   class="text-blue-500 hover:underline dark:text-blue-400">
                    Daftar Sekarang
                </a>
            </p>
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