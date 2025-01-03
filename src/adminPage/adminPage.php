<?php
session_start();
require_once '../../server/config.php';

if (!isset($_SESSION["admin_id"]) || $_SESSION["user_type"] !== "admin") {
    header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
    exit();
}


$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function deleteForum($conn, $forumId) {
    $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id_topik = ?");
    $stmt->bind_param("i", $forumId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM topik_forum WHERE id = ?");
    $stmt->bind_param("i", $forumId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}
function deleteComment($conn, $commentId) {
    $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

function deleteUser($conn, $userId) {
    $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id_penulis = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM topik_forum WHERE id_pembuat = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM users_forum WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_forum'])) {
        $forumId = $_POST['forum_id'];
        if (deleteForum($conn, $forumId)) {
            $successMessage = "Forum berhasil dihapus.";
        } else {
            $errorMessage = "Gagal menghapus forum.";
        }
    } elseif (isset($_POST['delete_comment'])) {
        $commentId = $_POST['comment_id'];
        if (deleteComment($conn, $commentId)) {
            $successMessage = "Komentar berhasil dihapus.";
        } else {
            $errorMessage = "Gagal menghapus komentar.";
        }
    } elseif (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];
        if (deleteUser($conn, $userId)) {
            $successMessage = "Pengguna berhasil dihapus.";
        } else {
            $errorMessage = "Gagal menghapus pengguna.";
        }
    }
}

$forumsQuery = "SELECT tf.id, tf.judul, uf.username as pembuat, tf.tanggal_dibuat 
                FROM topik_forum tf 
                JOIN users_forum uf ON tf.id_pembuat = uf.id 
                ORDER BY tf.tanggal_dibuat DESC";
$forumsResult = $conn->query($forumsQuery);

$forums = [];
while ($forum = $forumsResult->fetch_assoc()) {
    $forum['comments'] = [];
    $commentQuery = "SELECT kf.id, kf.isi, uf.username as penulis, kf.tanggal_dibuat 
                     FROM komentar_forum kf 
                     JOIN users_forum uf ON kf.id_penulis = uf.id 
                     WHERE kf.id_topik = ?
                     ORDER BY kf.tanggal_dibuat DESC";
    $stmt = $conn->prepare($commentQuery);
    $stmt->bind_param("i", $forum['id']);
    $stmt->execute();
    $commentResult = $stmt->get_result();
    while ($comment = $commentResult->fetch_assoc()) {
        $forum['comments'][] = $comment;
    }
    $forums[] = $forum;
    $stmt->close();
}

$usersQuery = "SELECT id, username, nama_lengkap FROM users_forum ORDER BY username";
$usersResult = $conn->query($usersQuery);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page - Smart Family</title>
    <link rel="stylesheet" href="../../assets/css/output.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.12.0/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="../../index.php" class="ml-5 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-1 px-3 rounded"><</a>
                <h1 class="text-xl font-semibold ml-5">Admin</h1>
            </div>
            
            <div x-data="{ open: false }" class="relative lg:hidden mr-5">
                <button @click="open = !open" class="text-white p-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg z-10">
                    <a href="adminTaromboPage.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">Tarombo</a>
                    <a href="../loginPage/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-200">Logout</a>
                </div>
            </div>

            <div class="hidden lg:flex items-center">
                <span class="mr-4">Admin <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="adminTaromboPage.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-1 px-3 rounded mr-4">Tarombo</a>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
            </div>
        </div>
    </header>

    <div class="container mx-auto mt-8 px-4">
        <?php if (isset($successMessage)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $successMessage; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errorMessage)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <section class="mb-8" x-data="{ openForumId: null }">
            <h2 class="text-2xl font-semibold mb-4">Atur Forum dan Komentar</h2>
            <div class="space-y-4">
                <?php foreach ($forums as $forum): ?>
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-4 flex justify-between items-center cursor-pointer" @click="openForumId = openForumId === <?php echo $forum['id']; ?> ? null : <?php echo $forum['id']; ?>">
                            <div class="flex-1">
                                <h3 class="font-semibold">
                                    <span class="md:hidden">
                                        <?php echo htmlspecialchars(strlen($forum['judul']) > 23 ? substr($forum['judul'], 0, 23) . '...' : $forum['judul']); ?>
                                    </span>
                                    <span class="hidden md:inline">
                                        <?php echo htmlspecialchars(strlen($forum['judul']) > 50 ? substr($forum['judul'], 0, 50) . '...' : $forum['judul']); ?>
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600">by <?php echo htmlspecialchars($forum['pembuat']); ?> on <?php echo date("d M Y, H:i", strtotime($forum['tanggal_dibuat'])); ?></p>
                            </div>
                            <div class="flex items-center ml-4">
                                <span class="mr-2"><?php echo count($forum['comments']); ?> komentar</span>
                                <svg class="w-4 h-4 transform transition-transform" :class="{'rotate-180': openForumId === <?php echo $forum['id']; ?>}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <div x-show="openForumId === <?php echo $forum['id']; ?>" x-cloak class="border-t">
                            <div class="p-4 bg-gray-50">
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this forum and all its comments?');" class="mb-4">
                                    <input type="hidden" name="forum_id" value="<?php echo $forum['id']; ?>">
                                    <button type="submit" name="delete_forum" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Delete Forum</button>
                                </form>
                                <?php if (empty($forum['comments'])): ?>
                                    <p class="text-gray-600">No comments for this forum.</p>
                                <?php else: ?>
                                    <?php foreach ($forum['comments'] as $comment): ?>
                                        <div class="bg-white p-3 rounded shadow mb-2">
                                            <div class="flex justify-between items-start">
                                                <div class="flex-1 pr-4">
                                                    <p class="text-sm">
                                                        <span class="md:hidden">
                                                            <?php echo htmlspecialchars(strlen($comment['isi']) > 23 ? substr($comment['isi'], 0, 23) . '...' : $comment['isi']); ?>
                                                        </span>
                                                        <span class="hidden md:inline">
                                                            <?php echo htmlspecialchars(strlen($comment['isi']) > 100 ? substr($comment['isi'], 0, 100) . '...' : $comment['isi']); ?>
                                                        </span>
                                                    </p>
                                                    <p class="text-xs text-gray-600 mt-1">by <?php echo htmlspecialchars($comment['penulis']); ?> on <?php echo date("d M Y, H:i", strtotime($comment['tanggal_dibuat'])); ?></p>
                                                </div>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');" class="flex-shrink-0">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" name="delete_comment" class="bg-red-500 hover:bg-red-600 text-white text-xs font-bold py-1 px-2 rounded">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Atur Pengguna</h2>
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left">Username</th>
                            <th class="py-3 px-4 text-left">Nama Lengkap</th>
                            <th class="py-3 px-4 text-left">Opsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($user = $usersResult->fetch_assoc()): ?>
                            <tr class="border-t">
                                <td class="py-3 px-4"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($user['nama_lengkap']); ?></td>
                                <td class="py-3 px-4">
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user? This will also delete all their forums and comments.');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

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