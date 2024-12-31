<?php
session_start();
require_once '../../server/config.php';

if (isset($_SESSION["user_id"])) {
    session_unset();
    session_destroy();
}

if (isset($_SESSION["admin_id"]) && $_SESSION["user_type"] === "admin") {
    header("Location: " . BASE_URL . "src/adminPage/adminPage.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4 md:px-0">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-3xl font-semibold mb-6 text-center">Login Admin</h2>
        <form action="../../server/validasi/validasiAdmin.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
            </div>
            <div class="mb-4 flex justify-center">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAAy9o9fJuZx_pvTU" data-theme="light"></div>
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Login</button>
        </form>

        <?php
        if (isset($_GET['login_gagal'])) {
            $message = '';
            $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
            switch ($_GET['login_gagal']) {
                case 'username':
                    $message = 'Username salah atau tidak terdaftar';
                    break;
                case 'password':
                    $message = 'Password salah';
                    break;
                case 'captcha':
                    $message = 'Verifikasi captcha gagal';
                    break;
                case 'empty':
                    $message = 'Username dan password harus diisi';
                    break;
                case 'invalid':
                    $message = 'Username atau password salah';
                    break;
                case 'system':
                    $message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
                    break;
                default:
                    break;
            }
            if ($message) {
                echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
            }
        }
        ?>

        <div class="mt-6 text-center">
            <a href="../../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Kembali</a>
        </div>
    </div>
</body>
</html>
