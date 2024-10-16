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
$stmt = $conn->prepare("SELECT tf.judul, tf.isi, tf.tanggal_dibuat, uf.username, tf.id_pembuat 
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
$stmt = $conn->prepare("SELECT cf.id, cf.isi, cf.tanggal_dibuat, uf.username, cf.id_penulis 
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
                <a href="../../server/validasi/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Logout</a>
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
                            
                            <?php if ($comment['id_penulis'] == $_SESSION["user_id"]): ?>
                                <!-- Tombol Hapus Komentar -->
                                <form action="../../server/validasi/deleteComment.php" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                    <button type="submit" class="mt-2 bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded">Hapus</button>
                                </form>
                            <?php endif; ?>
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

                <!-- Pesan Feedback -->
                <?php
                    if(isset($_GET['comment_gagal'])) {
                        $message = '';
                        $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
                        if($_GET['comment_gagal'] == 'empty') {
                            $message = 'Silakan isi komentar Anda.';
                        } elseif($_GET['comment_gagal'] == 'stmt_prepare') {
                            $message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
                        } elseif($_GET['comment_gagal'] == 'database') {
                            $message = 'Gagal menambahkan komentar. Silakan coba lagi.';
                        }
                        echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
                    }

                    if(isset($_GET['comment_sukses'])) {
                        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">Komentar berhasil ditambahkan.</div>';
                    }

                    if(isset($_GET['delete_comment_gagal'])) {
                        $message = '';
                        $alertClass = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4';
                        if($_GET['delete_comment_gagal'] == 'stmt_prepare') {
                            $message = 'Terjadi kesalahan pada sistem. Silakan coba lagi.';
                        } elseif($_GET['delete_comment_gagal'] == 'unauthorized') {
                            $message = 'Anda tidak memiliki izin untuk menghapus komentar ini.';
                        } elseif($_GET['delete_comment_gagal'] == 'not_found') {
                            $message = 'Komentar tidak ditemukan.';
                        } elseif($_GET['delete_comment_gagal'] == 'stmt_prepare_delete_comment') {
                            $message = 'Terjadi kesalahan saat menghapus komentar. Silakan coba lagi.';
                        } elseif($_GET['delete_comment_gagal'] == 'database') {
                            $message = 'Gagal menghapus komentar. Silakan coba lagi.';
                        } else {
                            $message = 'Terjadi kesalahan yang tidak dikenal. Silakan coba lagi.';
                        }
                        echo "<div class=\"$alertClass\" role=\"alert\">$message</div>";
                    }

                    if(isset($_GET['delete_comment_sukses'])) {
                        echo '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mt-4" role="alert">Komentar berhasil dihapus.</div>';
                    }
                ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-blue-500 text-white py-4 mt-20">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
<?php
$conn->close();
?>
