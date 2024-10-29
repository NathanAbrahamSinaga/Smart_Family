<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Membuat koneksi ke database
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Memeriksa koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    // Mengambil dan membersihkan data dari form
    $nama_lengkap = trim($_POST["nama_lengkap"]);
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    $kode = trim($_POST["kode"]);

    // Validasi apakah semua field telah diisi
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($confirm_password) || empty($kode)) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=lengkapi");
        exit();
    }

    // Validasi reCAPTCHA
    $recaptcha_secret = "6LdHpG0qAAAAAOBLj-g0d9_3HFL8Mwr9xr5iGvel";
    $recaptcha_response = $_POST['g-recaptcha-response'];

    $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $response_data = json_decode($verify_response, true);

    if (!$response_data['success']) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=captcha");
        exit();
    }

    // Cek apakah password dan konfirmasi password sama
    if ($password !== $confirm_password) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=password");
        exit();
    }

    // Validasi kode
    $stmt = $conn->prepare("SELECT kode FROM kode WHERE kode = ?");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=stmt_prepare");
        exit();
    }
    $stmt->bind_param("s", $kode);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        // Kode tidak valid
        $stmt->close();
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=kode");
        exit();
    }

    $stmt->close();

    // Cek apakah username sudah ada
    $stmt = $conn->prepare("SELECT id FROM users_forum WHERE username = ?");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=stmt_prepare");
        exit();
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Username sudah digunakan
        $stmt->close();
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=username");
        exit();
    }

    $stmt->close();

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert pengguna baru
    $stmt = $conn->prepare("INSERT INTO users_forum (nama_lengkap, username, password) VALUES (?, ?, ?)");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=stmt_insert");
        exit();
    }
    $stmt->bind_param("sss", $nama_lengkap, $username, $hashed_password);

    if ($stmt->execute()) {
        // Registrasi berhasil, redirect ke halaman login dengan parameter sukses
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?register_sukses=1");
        exit();
    } else {
        // Gagal menyimpan data
        $stmt->close();
        $conn->close();
        header("Location: " . BASE_URL . "src/loginPage/register.php?register_gagal=database");
        exit();
    }
} else {
    // Jika bukan metode POST, redirect ke halaman register
    header("Location: " . BASE_URL . "src/loginPage/register.php");
    exit();
}
?>