<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Membuat koneksi ke database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Mengambil dan membersihkan data dari form
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Validasi apakah semua field telah diisi
    if (empty($username) || empty($password)) {
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=lengkapi");
        exit();
    }

    // Menyiapkan statement SQL
    $stmt = $conn->prepare("SELECT id, username, password FROM users_forum WHERE username = ?");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=stmt_prepare");
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Memeriksa apakah pengguna ditemukan
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Verifikasi password
        if (password_verify($password, $row['password'])) {
            // Set variabel sesi
            $_SESSION["user_id"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            // Redirect ke halaman forum
            $stmt->close();
            $conn->close();
            header("Location: " . BASE_URL . "src/forumPage/forumPage.php");
            exit();
        } else {
            // Password salah
            $stmt->close();
            $conn->close();
            header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=password");
            exit();
        }
    } else {
        // Username tidak ditemukan
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=username");
        exit();
    }
} else {
    // Jika bukan metode POST, redirect ke halaman login
    header("Location: " . BASE_URL . "src/loginPage/loginForum.php");
    exit();
}
?>
