<?php
session_start();
require_once '../config.php';

function validateTurnstile($token) {
    $data = array(
        'secret' => '',
        'response' => $token
    );

    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        curl_close($ch);
        return ['success' => false, 'error' => 'CURL Error: ' . curl_error($ch)];
    }
    
    curl_close($ch);
    
    return json_decode($response, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['cf-turnstile-response'] ?? '';
    $turnstileResult = validateTurnstile($token);
    
    if (!$turnstileResult['success']) {
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php?login_gagal=captcha");
        exit();
    }

    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php?login_gagal=system");
        exit();
    }

    $username = trim($_POST["username"] ?? '');
    $password = $_POST["password"] ?? '';

    if (empty($username) || empty($password)) {
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php?login_gagal=empty");
        exit();
    }

    $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
    if (!$stmt) {
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php?login_gagal=system");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php?login_gagal=username");
        exit();
    }

    $row = $result->fetch_assoc();
    if (!password_verify($password, $row['password'])) {
        header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php?login_gagal=password");
        exit();
    }

    session_regenerate_id(true);
    $_SESSION["admin_id"] = $row['id'];
    $_SESSION["username"] = $row['username'];
    $_SESSION["user_type"] = "admin";

    header("Location: " . BASE_URL . "src/adminPage/adminPage.php");
    exit();
}

header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
exit();
?>
