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
            $_SESSION['successMessage'] = "Forum berhasil dihapus.";
        } else {
            $_SESSION['errorMessage'] = "Gagal menghapus forum.";
        }
    } elseif (isset($_POST['delete_comment'])) {
        $commentId = $_POST['comment_id'];
        if (deleteComment($conn, $commentId)) {
            $_SESSION['successMessage'] = "Komentar berhasil dihapus.";
        } else {
            $_SESSION['errorMessage'] = "Gagal menghapus komentar.";
        }
    } elseif (isset($_POST['delete_user'])) {
        $userId = $_POST['user_id'];
        if (deleteUser($conn, $userId)) {
            $_SESSION['successMessage'] = "Pengguna berhasil dihapus.";
        } else {
            $_SESSION['errorMessage'] = "Gagal menghapus pengguna.";
        }
    }
    header("Location: adminPage.php");
    exit();
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

$conn->close();
?>