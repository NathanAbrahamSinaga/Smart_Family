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

    $comment_id = intval($_POST["comment_id"]);
    $user_id = $_SESSION["user_id"];

    // Mulai transaksi untuk memastikan konsistensi data
    $conn->begin_transaction();

    try {
        // Verifikasi bahwa pengguna adalah penulis komentar
        $stmt = $conn->prepare("SELECT id_penulis FROM komentar_forum WHERE id = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare");
        }

        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $stmt->bind_result($id_penulis);
        if ($stmt->fetch()) {
            if ($id_penulis != $user_id) {
                // Tidak berwenang
                $stmt->close();
                throw new Exception("unauthorized");
            }
        } else {
            // Komentar tidak ditemukan
            $stmt->close();
            throw new Exception("not_found");
        }
        $stmt->close();

        // Hapus komentar
        $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare_delete_comment");
        }
        $stmt->bind_param("i", $comment_id);
        if (!$stmt->execute()) {
            throw new Exception("database");
        }
        $stmt->close();

        // Commit transaksi
        $conn->commit();

        // Ambil ID topik untuk redirect kembali
        $stmt = $conn->prepare("SELECT id_topik FROM komentar_forum WHERE id = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare_fetch_topik");
        }
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $stmt->bind_result($id_topik);
        if ($stmt->fetch()) {
            // Jika komentar dihapus, id_topik tidak akan ada, jadi kita harus menangani ini secara terpisah
            // Sebagai gantinya, kita akan ambil dari topikForum.php sebelumnya
            // Maka, kita harus mengirimkan id_topik melalui POST atau GET
            // Untuk kesederhanaan, kita akan redirect kembali ke topikForum.php dengan menggunakan referer
            $stmt->close();
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?delete_comment_sukses=1");
            exit();
        }
        $stmt->close();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi error
        $conn->rollback();

        $error = $e->getMessage();
        if ($error == "stmt_prepare") {
            $redirect = "stmt_prepare";
        } elseif ($error == "unauthorized") {
            $redirect = "unauthorized";
        } elseif ($error == "not_found") {
            $redirect = "not_found";
        } elseif ($error == "stmt_prepare_delete_comment") {
            $redirect = "stmt_prepare_delete_comment";
        } elseif ($error == "database") {
            $redirect = "database";
        } elseif ($error == "stmt_prepare_fetch_topik") {
            $redirect = "stmt_prepare_fetch_topik";
        } else {
            $redirect = "unknown_error";
        }
        header("Location: " . $_SERVER['HTTP_REFERER'] . "?delete_comment_gagal=" . $redirect);
        exit();
    } finally {
        $conn->close();
    }
} else {
    header("Location: " . BASE_URL . "src/forumPage/forumPage.php");
    exit();
}
?>
