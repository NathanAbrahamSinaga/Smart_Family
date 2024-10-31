<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recaptcha_secret = "";
    $recaptcha_response = $_POST["g-recaptcha-response"];

    $verify_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response);
    $response_data = json_decode($verify_response, true);

    if (!$response_data['success']) {
        $_SESSION['login_error'] = "Mohon verifikasi reCAPTCHA";
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?login_gagal=captcha");
        exit();
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        header("Location: " . BASE_URL . "src/loginPage/loginForum.php?error=empty");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM users_forum WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            session_unset();

            $_SESSION["user_id"] = $row['id'];
            $_SESSION["username"] = $row['username'];
            $_SESSION["user_type"] = "forum";

            header("Location: " . BASE_URL . "src/forumPage/forumPage.php");
            exit();
        }
    }

    header("Location: " . BASE_URL . "src/loginPage/loginForum.php?error=invalid");
    exit();

    $stmt->close();
    $conn->close();
}

header("Location: " . BASE_URL . "src/loginPage/loginForum.php");
exit();
?>