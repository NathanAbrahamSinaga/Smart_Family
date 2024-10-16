<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_SESSION["user_id"])) {
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=not_logged_in");
        exit();
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $id_topik = intval($_POST["id_topik"]);
    $isi = trim($_POST["isi"]);
    $id_penulis = $_SESSION["user_id"];

    if (empty($isi)) {
        header("Location: " . BASE_URL . "src/forumPage/topikForum.php?id=" . $id_topik . "&comment_gagal=empty");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO komentar_forum (id_topik, id_penulis, isi) VALUES (?, ?, ?)");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/forumPage/topikForum.php?id=" . $id_topik . "&comment_gagal=stmt_prepare");
        exit();
    }

    $stmt->bind_param("iis", $id_topik, $id_penulis, $isi);

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "src/forumPage/topikForum.php?id=" . $id_topik . "&comment_sukses=1");
        exit();
    } else {
        header("Location: " . BASE_URL . "src/forumPage/topikForum.php?id=" . $id_topik . "&comment_gagal=database");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: " . BASE_URL . "src/forumPage/forumPage.php");
    exit();
}
?>
