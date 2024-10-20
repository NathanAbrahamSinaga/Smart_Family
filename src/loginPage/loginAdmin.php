<?php
session_start();
if (isset($_SESSION["user_id"])) {
    header("location: ../adminPage/adminPage.php");
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
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <!-- Kotak Form Login -->
    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <!-- Judul -->
        <h2 class="text-3xl font-semibold mb-6 text-center">Login Admin</h2>
        <!-- Form Login -->
        <form action="../../server/validasi/validasiAdmin.php" method="POST">
            <div class="mb-4">
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username</label>
                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                <input type="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Login</button>
        </form>
        <?php
        if(isset($_GET['login_gagal'])) {
            if($_GET['login_gagal'] == 'username') {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">Username tidak ditemukan</div>';
            } elseif($_GET['login_gagal'] == 'password') {
                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">Password salah</div>';
            }
        }
        ?>
        <!-- Link Kembali ke Halaman Utama -->
        <div class="mt-6 text-center">
            <a href="../../index.php" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Kembali</a>
        </div>
    </div>
</body>
</html>
