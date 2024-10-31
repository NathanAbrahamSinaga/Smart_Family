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

    $forum_id = intval($_POST["forum_id"]);
    $user_id = $_SESSION["user_id"];

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT id_pembuat FROM topik_forum WHERE id = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare");
        }

        $stmt->bind_param("i", $forum_id);
        $stmt->execute();
        $stmt->bind_result($id_pembuat);
        if ($stmt->fetch()) {
            if ($id_pembuat != $user_id) {
                $stmt->close();
                throw new Exception("unauthorized");
            }
        } else {
            $stmt->close();
            throw new Exception("not_found");
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM komentar_forum WHERE id_topik = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare_delete_comments");
        }
        $stmt->bind_param("i", $forum_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM topik_forum WHERE id = ?");
        if (!$stmt) {
            throw new Exception("stmt_prepare_delete_forum");
        }
        $stmt->bind_param("i", $forum_id);
        if (!$stmt->execute()) {
            throw new Exception("database");
        }
        $stmt->close();

        $conn->commit();

        header("Location: " . BASE_URL . "src/forumPage/daftarForumPage.php?delete_sukses=1");
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
        } elseif ($error == "stmt_prepare_delete_comments") {
            $redirect = "stmt_prepare_delete_comments";
        } elseif ($error == "stmt_prepare_delete_forum") {
            $redirect = "stmt_prepare_delete_forum";
        } elseif ($error == "database") {
            $redirect = "database";
        } else {
            $redirect = "unknown_error";
        }
        header("Location: " . BASE_URL . "src/forumPage/daftarForumPage.php?delete_gagal=" . $redirect);
        exit();
    } finally {
        $conn->close();
    }
} else {
    header("Location: " . BASE_URL . "src/forumPage/daftarForumPage.php");
    exit();
}
?>
