<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT tf.id, tf.judul, tf.isi, tf.tanggal_dibuat, uf.username 
        FROM topik_forum tf 
        JOIN users_forum uf ON tf.id_pembuat = uf.id 
        ORDER BY tf.tanggal_dibuat DESC";
$result = $conn->query($sql);

function truncateText($text, $limit = 50) {
    $words = explode(' ', $text);
    if (count($words) > $limit) {
        return implode(' ', array_slice($words, 0, $limit)) . '...';
    }
    return $text;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum - Smart Family</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/output.css">
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="../../index.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded"><</a>
                <h1 class="text-xl font-semibold ml-5">Forum</h1>
            </div>
            <div>
                <span class="mr-4"><?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
            </div>
        </div>
    </header>

    <div class="container mx-auto mt-8 px-4">
        <div class="flex justify-between mb-6">
            <h2 class="text-2xl font-semibold">Semua Forum</h2>
            <div class="space-x-4">
                <a href="daftarForumPage.php" class="bg-purple-500 hover:bg-purple-600 text-white font-semibold py-1 px-2 rounded sm:py-1 sm:px-2 md:py-2 md:px-4">
                    Daftar Forum
                </a>
            </div>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="space-y-4">
                <?php while($row = $result->fetch_assoc()): ?>
                    <div class="bg-white p-4 rounded shadow flex justify-between items-center">
                        <div class="w-full max-w-full break-words break-all">
                            <a href="topikForum.php?id=<?php echo $row['id']; ?>" class="text-blue-600 hover:underline font-semibold">
                                <?php echo htmlspecialchars($row['judul']); ?>
                            </a>
                            <p class="text-sm text-gray-500">by <?php echo htmlspecialchars($row['username']); ?> on <?php echo date("d M Y, H:i", strtotime($row['tanggal_dibuat'])); ?></p>
                            <p class="text-gray-700 mt-2 truncate w-full">
                                <?php echo htmlspecialchars(substr($row['isi'], 0, 50)) . (strlen($row['isi']) > 50 ? '...' : ''); ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-700">Belum ada forum. <a href="tambahForumPage.php" class="text-blue-500 hover:underline">Buat forum pertama Anda</a>.</p>
        <?php endif; ?>
    </div>

    <footer id="footer-static" class="bg-blue-500 text-white py-4 mt-20">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>

    <footer id="footer-fixed" class="bg-blue-500 text-white py-4 fixed bottom-0 left-0 right-0 flex justify-center items-center">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script>
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
<?php
$conn->close();
?>