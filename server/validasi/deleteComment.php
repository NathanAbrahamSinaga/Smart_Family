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
    $topic_id = null;

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT id_penulis, id_topik FROM komentar_forum WHERE id = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare");
        }

        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $stmt->bind_result($id_penulis, $topic_id);
        if ($stmt->fetch()) {
            if ($id_penulis != $user_id) {
                $stmt->close();
                throw new Exception("unauthorized");
            }
        } else {
            $stmt->close();
            throw new Exception("not_found");
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare_delete_comment");
        }
        $stmt->bind_param("i", $comment_id);
        if (!$stmt->execute()) {
            throw new Exception("database");
        }
        $stmt->close();

        $conn->commit();

        if ($topic_id !== null) {
            header("Location: " . BASE_URL . "src/forumPage/topikForum.php?id=" . $topic_id . "&delete_comment_sukses=1");
        } else {
            header("Location: " . $_SERVER['HTTP_REFERER'] . "?delete_comment_sukses=1");
        }
        exit();
    } catch (Exception $e) {
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
