<?php
session_start();
require_once '../../server/config.php';

// Cek apakah pengguna sudah login
if (!isset($_SESSION["user_id"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
    exit();
}

// Ambil ID forum dari parameter GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: forumPage.php");
    exit();
}

$forum_id = intval($_GET['id']);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil detail forum
$stmt = $conn->prepare("SELECT tf.judul, tf.isi, tf.tanggal_dibuat, uf.username 
                        FROM topik_forum tf 
                        JOIN users_forum uf ON tf.id_pembuat = uf.id 
                        WHERE tf.id = ?");
$stmt->bind_param("i", $forum_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    header("Location: forumPage.php");
    exit();
}

$forum = $result->fetch_assoc();
$stmt->close();

// Ambil komentar
$stmt = $conn->prepare("SELECT cf.isi, cf.tanggal_dibuat, uf.username 
                        FROM komentar_forum cf 
                        JOIN users_forum uf ON cf.id_penulis = uf.id 
                        WHERE cf.id_topik = ? 
                        ORDER BY cf.tanggal_dibuat ASC");
$stmt->bind_param("i", $forum_id);
$stmt->execute();
$comments = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($forum['judul']); ?> - Smart Family Forum</title>
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
        <!-- Detail Forum -->
        <div class="bg-white p-6 rounded shadow">
            <h2 class="text-2xl font-bold mb-2"><?php echo htmlspecialchars($forum['judul']); ?></h2>
            <span class="text-sm text-gray-500">by <?php echo htmlspecialchars($forum['username']); ?> on <?php echo date("d M Y, H:i", strtotime($forum['tanggal_dibuat'])); ?></span>
            <p class="mt-4 text-gray-700"><?php echo nl2br(htmlspecialchars($forum['isi'])); ?></p>
        </div>

        <!-- Komentar -->
        <div class="mt-8">
            <h3 class="text-xl font-semibold mb-4">Komentar</h3>
            <?php if ($comments->num_rows > 0): ?>
                <div class="space-y-4">
                    <?php while($comment = $comments->fetch_assoc()): ?>
                        <div class="bg-white p-4 rounded shadow">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold"><?php echo htmlspecialchars($comment['username']); ?></span>
                                <span class="text-sm text-gray-500"><?php echo date("d M Y, H:i", strtotime($comment['tanggal_dibuat'])); ?></span>
                            </div>
                            <p class="mt-2 text-gray-700"><?php echo nl2br(htmlspecialchars($comment['isi'])); ?></p>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-700">Belum ada komentar.</p>
            <?php endif; ?>

            <!-- Form Tambah Komentar -->
            <div class="mt-6">
                <h4 class="text-lg font-semibold mb-2">Tambahkan Komentar</h4>
                <form action="../../server/validasi/addComment.php" method="POST" class="bg-white p-4 rounded shadow">
                    <input type="hidden" name="id_topik" value="<?php echo $forum_id; ?>">
                    <div class="mb-4">
                        <textarea name="isi" id="isi" rows="4" class="w-full border rounded p-2" placeholder="Tulis komentar Anda di sini..." required></textarea>
                    </div>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded">Submit</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white py-4 mt-20 flex justify-center items-center fixed bottom-0 left-0 right-0">
        <p class="text-center">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
