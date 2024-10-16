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

    $judul = trim($_POST["judul"]);
    $isi = trim($_POST["isi"]);
    $id_pembuat = $_SESSION["user_id"];

    if (empty($judul) || empty($isi)) {
        header("Location: " . BASE_URL . "src/forumPage/tambahForumPage.php?add_gagal=lengkapi");
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO topik_forum (id_pembuat, judul, isi) VALUES (?, ?, ?)");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/forumPage/tambahForumPage.php?add_gagal=stmt_prepare");
        exit();
    }

    $stmt->bind_param("iss", $id_pembuat, $judul, $isi);

    if ($stmt->execute()) {
        header("Location: " . BASE_URL . "src/forumPage/forumPage.php?add_sukses=1");
        exit();
    } else {
        header("Location: " . BASE_URL . "src/forumPage/tambahForumPage.php?add_gagal=database");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: " . BASE_URL . "src/forumPage/tambahForumPage.php");
    exit();
}
?>
