<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recaptcha_secret = "";
    $recaptcha_response = $_POST['g-recaptcha-response'];
    
    $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$recaptcha_secret.'&response='.$recaptcha_response);
    $response_data = json_decode($verify_response);
    
    if (!$response_data->success) {
        $_SESSION['login_error'] = "Mohon verifikasi reCAPTCHA";
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php?login_gagal=captcha");
        exit();
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_unset();
            session_destroy();
            session_start();
            
            $_SESSION["admin_id"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            $_SESSION["user_type"] = "admin";
            
            header("Location: " . BASE_URL . "src/adminPage/adminPage.php");
            exit();
        }
    }
    
    $_SESSION['login_error'] = "Username atau password salah";
    header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
    exit();

    $stmt->close();
    $conn->close();
}

header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
exit();
?>