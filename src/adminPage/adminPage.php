<?php
session_start();
require_once '../../server/config.php';

// Check if the user is logged in as an admin
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["username"])) {
    header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to delete a forum
function deleteForum($conn, $forumId) {
    // First, delete all comments associated with the forum
    $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id_topik = ?");
    $stmt->bind_param("i", $forumId);
    $stmt->execute();
    $stmt->close();

    // Then, delete the forum itself
    $stmt = $conn->prepare("DELETE FROM topik_forum WHERE id = ?");
    $stmt->bind_param("i", $forumId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

// Function to delete a comment
function deleteComment($conn, $commentId) {
    $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id = ?");
    $stmt->bind_param("i", $commentId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

// Function to delete a user
function deleteUser($conn, $userId) {
    // First, delete all comments by this user
    $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id_penulis = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Then, delete all forums created by this user
    $stmt = $conn->prepare("DELETE FROM topik_forum WHERE id_pembuat = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    // Finally, delete the user
    $stmt = $conn->prepare("DELETE FROM users_forum WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $result = $stmt->execute();
    $stmt->close();

    return $result;
}

// Handle delete actions
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

// Fetch forums with comments
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

// Fetch users
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
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <header class="bg-blue-500 text-white py-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-semibold ml-5">Smart Family Admin Panel</h1>
            <div>
                <span class="mr-4">Welcome, Admin <?php echo htmlspecialchars($_SESSION["username"]); ?></span>
                <a href="adminTaromboPage.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded">Manage Tarombo</a>
                <a href="../loginPage/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-1 px-3 rounded mr-5">Logout</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
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

        <!-- Forums Section with Dropdown Comments -->
        <section class="mb-8" x-data="{ openForumId: null }">
            <h2 class="text-2xl font-semibold mb-4">Manage Forums and Comments</h2>
            <div class="space-y-4">
                <?php foreach ($forums as $forum): ?>
                    <div class="bg-white shadow rounded-lg overflow-hidden">
                        <div class="p-4 flex justify-between items-center cursor-pointer" @click="openForumId = openForumId === <?php echo $forum['id']; ?> ? null : <?php echo $forum['id']; ?>">
                            <div>
                                <h3 class="font-semibold"><?php echo htmlspecialchars($forum['judul']); ?></h3>
                                <p class="text-sm text-gray-600">by <?php echo htmlspecialchars($forum['pembuat']); ?> on <?php echo date("d M Y, H:i", strtotime($forum['tanggal_dibuat'])); ?></p>
                            </div>
                            <div class="flex items-center">
                                <span class="mr-2"><?php echo count($forum['comments']); ?> comments</span>
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
                                                <div>
                                                    <p class="text-sm"><?php echo htmlspecialchars(substr($comment['isi'], 0, 100)) . (strlen($comment['isi']) > 100 ? '...' : ''); ?></p>
                                                    <p class="text-xs text-gray-600 mt-1">by <?php echo htmlspecialchars($comment['penulis']); ?> on <?php echo date("d M Y, H:i", strtotime($comment['tanggal_dibuat'])); ?></p>
                                                </div>
                                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this comment?');">
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

        <!-- Users Section -->
        <section class="mb-8">
            <h2 class="text-2xl font-semibold mb-4">Manage Users</h2>
            <div class="overflow-x-auto bg-white shadow rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="py-3 px-4 text-left">Username</th>
                            <th class="py-3 px-4 text-left">Nama Lengkap</th>
                            <th class="py-3 px-4 text-left">Action</th>
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

    <!-- Footer -->
    <footer class="bg-blue-500 text-white py-4 mt-8">
        <div class="container mx-auto text-center">
            <p>&copy; 2024 Smart Family. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>

<?php
$conn->close();
?>