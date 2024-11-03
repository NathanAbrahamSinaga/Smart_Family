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
        $error = curl_error($ch);
        curl_close($ch);
        return ['success' => false, 'error' => 'CURL Error: ' . $error];
    }
    
    curl_close($ch);
    
    $decodedResponse = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'error' => 'JSON Error: ' . json_last_error_msg()];
    }
    
    return $decodedResponse;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errors = [];
    
    $token = $_POST['cf-turnstile-response'] ?? '';
    $turnstileResult = validateTurnstile($token);
    
    if (!$turnstileResult['success']) {
        $captchaError = isset($turnstileResult['error']) ? 
            'captcha_error=' . urlencode($turnstileResult['error']) : 'login_gagal=captcha';
        header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php?" . $captchaError);
        exit();
    }

    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Database connection failed: " . $conn->connect_error);
        }

        $username = trim($_POST["username"] ?? '');
        $password = $_POST["password"] ?? '';

        if (empty($username) || empty($password)) {
            header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php?login_gagal=empty");
            exit();
        }

        $stmt = $conn->prepare("SELECT id, username, password FROM users_forum WHERE username = ?");
        if (!$stmt) {
            throw new Exception("Database prepare failed: " . $conn->error);
        }

        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            throw new Exception("Database execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php?login_gagal=username");
            exit();
        }

        $row = $result->fetch_assoc();
        if (!password_verify($password, $row['password'])) {
            header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php?login_gagal=password");
            exit();
        }

        session_regenerate_id(true);
        $_SESSION["user_id"] = $row['id'];
        $_SESSION["username"] = $row['username'];
        $_SESSION["user_type"] = "tarombo";

        $stmt->close();
        $conn->close();

        header("Location: " . BASE_URL . "src/taromboPage/tarombo.php");
        exit();

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        
        header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php?login_gagal=system");
        exit();
    }
}


header("Location: " . BASE_URL . "src/loginPage/loginTarombo.php");
exit();
?>