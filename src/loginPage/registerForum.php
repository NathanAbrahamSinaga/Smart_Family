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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.2/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100">
    <!-- Container Utama -->
    <div class="container mx-auto text-center mt-20">
        <!-- Judul -->
        <h2 class="text-3xl font-semibold mb-8">Register Forum</h2>

        <!-- Form Register -->
        <form action="<?php echo BASE_URL; ?>server/registerUser.php" method="POST" class="max-w-sm mx-auto">
            <div class="mb-4">
                <label for="nama_lengkap" class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
            </div>
            <div class="mb-6">
                <label for="confirm_password" class="block text-gray-700 text-sm font-bold mb-2">Konfirmasi Password</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="w-40 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Register</button>
        </form>

        <?php
            if(isset($_GET['register_gagal'])) {
                $message = '';
                $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
                if($_GET['register_gagal'] == 'password') {
                    $message = 'Password tidak sama';
                } elseif($_GET['register_gagal'] == 'username') {
                    $message = 'Username sudah digunakan';
                } elseif($_GET['register_gagal'] == 'database') {
                    $message = 'Gagal menyimpan data. Silakan coba lagi.';
                }
                echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
            } elseif(isset($_GET['register_sukses'])) {
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">Registrasi berhasil. Silakan login.</div>';
            }
        ?>

        <!-- Link Kembali ke Halaman Forum -->
        <div class="mt-8">
            <a href="loginForumPage.php" class="w-40 bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Kembali</a>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white py-4 mt-20 flex justify-center items-center fixed bottom-0 left-0 right-0">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>
</body>
</html>