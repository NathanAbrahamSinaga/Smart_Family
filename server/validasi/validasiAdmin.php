<?php
session_start();
require_once '../config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            // Clear any existing session data
            session_unset();
            session_destroy();
            session_start();
            
            // Set admin session
            $_SESSION["admin_id"] = $row['id'];
            $_SESSION["username"] = $row['username']; // Sesuaikan dengan yang digunakan di adminPage.php
            $_SESSION["user_type"] = "admin";
            
            header("Location: " . BASE_URL . "src/adminPage/adminPage.php");
            exit();
        }
    }
    
    // If login fails
    $_SESSION['login_error'] = "Username atau password salah";
    header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
    exit();

    $stmt->close();
    $conn->close();
}

// If not POST request
header("Location: " . BASE_URL . "src/loginPage/loginAdmin.php");
exit();
?>