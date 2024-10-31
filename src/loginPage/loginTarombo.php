<?php
session_start();
require_once '../../server/config.php';

if (isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/taromboPage/tarombo.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Tarombo - Smart Family</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/output.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4 md:px-0">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-3xl font-semibold mb-6 text-center">Login Tarombo</h2>

        <form action="<?php echo BASE_URL; ?>server/validasi/validasiTarombo.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" required 
                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
            </div>
            <div class="mb-6 flex justify-center">
                <div class="g-recaptcha" data-sitekey="6LfGfHEqAAAAAIJodDfPR2r-h0OoX0p4WuioxQo1"></div>
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Login</button>
        </form>

        <?php
            if(isset($_GET['login_gagal'])) {
                $message = '';
                $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
                if($_GET['login_gagal'] == 'username') {
                    $message = 'Username tidak ditemukan';
                } elseif($_GET['login_gagal'] == 'password') {
                    $message = 'Password salah';
                } elseif($_GET['login_gagal'] == 'lengkapi') {
                    $message = 'Silakan lengkapi semua field';
                } elseif($_GET['login_gagal'] == 'stmt_prepare') {
                    $message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
                } elseif($_GET['login_gagal'] == 'captcha') {
                    $message = 'Mohon verifikasi reCAPTCHA';
                }
                echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
            }

            if(isset($_GET['register_sukses']) && $_GET['register_sukses'] == '1') {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">Registrasi berhasil. Silakan login.</div>';
            }
        ?>

        <div class="mt-6 text-center">
            <a href="../../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Kembali</a>
        </div>
    </div>
</body>
</html>