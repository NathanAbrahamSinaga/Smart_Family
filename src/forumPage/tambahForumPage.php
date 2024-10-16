<?php
session_start();
require_once '../../server/config.php';

// Cek apakah pengguna sudah login
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
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-semibold">Smart Family Forum</h1>
            <div>
                <span class="mr-4">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Logout</a>
            </div>
        </div>
    </header>

    <!-- Container Utama -->
    <div class="container mx-auto mt-8 px-4">
        <h2 class="text-2xl font-semibold mb-6">Tambah Forum Baru</h2>

        <form action="../../server/validasi/addForum.php" method="POST" class="bg-white p-6 rounded shadow max-w-lg mx-auto">
            <div class="mb-4">
                <label for="judul" class="block text-gray-700 font-bold mb-2">Judul</label>
                <input type="text" id="judul" name="judul" class="w-full border rounded p-2" required>
            </div>
            <div class="mb-4">
                <label for="isi" class="block text-gray-700 font-bold mb-2">Isi</label>
                <textarea id="isi" name="isi" rows="6" class="w-full border rounded p-2" required></textarea>
            </div>
            <div class="flex justify-between">
                <a href="forumPage.php" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 px-4 rounded">Kembali</a>
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">Tambah</button>
            </div>
        </form>

        <?php
            if(isset($_GET['add_gagal'])) {
                $message = '';
                $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
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
                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">Forum berhasil ditambahkan.</div>';
            }
        ?>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white py-4 mt-20 flex justify-center items-center fixed bottom-0 left-0 right-0">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>
</body>
</html>
