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
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen px-4 md:px-0">
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-3xl font-semibold mb-6 text-center">Register</h2>

        <form action="<?php echo BASE_URL; ?>server/registerUser.php" method="POST">
            <div class="mb-4">
                <label for="nama_lengkap" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username (nama panggilan)</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="mb-6">
                <label for="kode" class="block text-gray-700 text-sm font-bold mb-2">Kode Registrasi</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="kode" name="kode" required>
            </div>
            <div class="mb-4 flex justify-center">
                <div class="cf-turnstile" data-sitekey="0x4AAAAAAAy9o9fJuZx_pvTU" data-theme="light"></div>
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Register</button>
        </form>

        <?php
            if (isset($_GET['register_gagal'])) {
                $message = '';
                $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
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
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">Registrasi berhasil. Silakan login.</div>';
            }
        ?>

        <div class="mt-6 text-center">
            <a href="../../index.php" class="w-full bg-gray-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Kembali</a>
        </div>
    </div>
</body>
</html>
