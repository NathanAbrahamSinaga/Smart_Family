<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $nama_lengkap = trim($_POST["nama_lengkap"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $kode = trim($_POST["kode"]);

    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($confirm_password) || empty($kode)) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=lengkapi");
        exit();
    }

    if (!is_valid_password($password)) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=password_format");
        exit();
    }

    $recaptcha_secret = "";
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $response_data = json_decode($verify_response, true);

    if (!$response_data['success']) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=captcha");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=password");
        exit();
    }

    $stmt = $conn->prepare("SELECT kode FROM kode WHERE kode = ?");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=stmt_prepare");
        exit();
    }
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        $stmt->close();
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=kode");
        exit();
    }

    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM users_forum WHERE username = ?");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=stmt_prepare");
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=username");
        exit();
    }

    $stmt->close();

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users_forum (nama_lengkap, username, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=stmt_insert");
        exit();
    }
    $stmt->bind_param("sss", $nama_lengkap, $username, $hashed_password);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?register_sukses=1");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=database");
        exit();
    }
} else {
    header("Location: " . BASE_URL . "src/loginPage/register.php");
    exit();
}

function is_valid_password($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*[\W_])(?=.*[a-zA-Z0-9]).{8,}$/', $password);
}
?>
